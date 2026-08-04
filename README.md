# বাগানবন্ধু (GardenBondhu)

A Bangla gardening knowledge platform for new/hobbyist gardeners in Bangladesh —
plant care guides, leaf-symptom disease diagnosis, a personal garden tracker,
and a community Q&A, gated behind a carrier billing daily micro-subscription (৳2.78/day,
Robi & Airtel only).

Built strictly to spec: **PHP 8.2+, zero Composer dependencies**, hand-rolled
MVC, plain-PHP views, one hand-written CSS file, vanilla JS. Runs unchanged on
cPanel shared hosting or a VPS.

## Stack

| Layer | Choice |
|---|---|
| Backend | PHP 8.2, PDO / cURL / OpenSSL / GD / mbstring only |
| Database | MySQL 8 or MariaDB 10.4+, `utf8mb4` |
| Sessions | Custom DB-backed handler (`sessions` table) — enables server-side revocation |
| Templating | Plain PHP views + mandatory `e()` escape helper |
| CSS | One hand-written file, no framework, no CDN |
| JS | Vanilla ES6, progressive enhancement — every flow works without it |
| Payments | `SubscriptionGateway` interface — `MockGateway` (dev) / `CarrierGateway` (prod stub) |

## Quick start (local development)

Requires PHP 8.2+ with `pdo_mysql`, `openssl`, `curl`, `gd`, `mbstring`,
`fileinfo`, and a MySQL/MariaDB server.

```bash
cp .env.example .env
php -r "echo base64_encode(random_bytes(32)), PHP_EOL;"   # run twice
# paste the two values into APP_KEY and HASH_PEPPER in .env
# set DB_* to your local MySQL/MariaDB credentials

php database/migrate.php --fresh --seed   # create schema + starter content + admin user
php tests/smoke.php                       # crypto / CSRF / validator / markdown checks

php -S 127.0.0.1:8000 public/router-dev.php
```

Open `http://127.0.0.1:8000`. `public/router-dev.php` exists only so PHP's
built-in server serves static assets correctly during development — Apache
(`public/.htaccess`, included) and nginx handle this natively in production
and never touch that file.

Default seeded admin: `admin@gardenbondhu.test` / `ChangeMe123!` —
**change this immediately**, it is a development convenience only.

With `CARRIER_DRIVER=mock` (the default), OTPs are generated for real, logged
to `storage/logs/otp-*.log`, and flashed on screen when `APP_DEBUG=true`. The
universal dev code `123456` also always verifies. Simulate billing failures by
using an MSISDN ending in `00` (insufficient balance → grace) or `99` (hard
failure → expired) — see spec §7.4.

## Production deployment (cPanel shared hosting)

```
/home/USER/gardenbondhu/        ← this repo (NOT web-accessible)
/home/USER/public_html/         ← symlink to gardenbondhu/public, or its contents
```

1. Point the docroot at `public/` (symlink `public_html` → `gardenbondhu/public`,
   or copy `public/`'s contents into `public_html/` and set
   `define('APP_ROOT', '/home/USER/gardenbondhu');` at the top of its `index.php`).
2. `cp .env.example .env`, fill in production values. Generate **fresh**
   `APP_KEY`/`HASH_PEPPER` on the server — never reuse the dev ones.
3. Create two MySQL users — see `03-ENV-AND-CONFIG.md` in the original spec
   bundle: an app user with `SELECT,INSERT,UPDATE,DELETE` only, and a migrate
   user with full DDL rights, used solely by `database/migrate.php`.
4. `php database/migrate.php` (add `--seed` only on a fresh install).
5. Add the four cron entries from `cron/` (see comments at the top of each file).
6. Set `APP_ENV=production`, `APP_DEBUG=false`, `CARRIER_DRIVER=carrier` once the
   real the carrier billing provider credentials are in `.env` — `bootstrap.php` refuses to boot
   otherwise.
7. Walk the pre-launch checklist in `01-BUILD-SPEC.md` §9.13.

## Project layout

```
app/
  Core/          Router, Request, Response, View, Db, Session, Csrf, Crypto,
                 Validator, RateLimit, Logger, Markdown, Helpers
  Middleware/     SecurityHeaders, CsrfGuard, RateLimiter, RequireAuth,
                 RequireSubscription, RequireAdmin, GuestOnly
  Controllers/    One per feature area, thin — logic lives in Services
  Services/       SubscriptionGateway (+ Mock/Carrier impls), OtpService,
                 SubscriptionService (state machine), DiagnosisEngine,
                 CareScheduler, SearchService, ImageService, AuditService
  Repositories/   All SQL lives here — prepared statements only
config/           app, database, carrier, operators, content — read via config()
database/
  migrations/     001–005, applied in order by migrate.php
  seeds/          categories/seasons/symptoms SQL + content.php (20 plants,
                 13 problems, 8 guides — a starter set, see PROGRESS.md)
cron/             charge_cycle (hourly), queue_worker (*/1), care_tasks (daily
                 05:00), cleanup (daily 03:00) — each flock-guarded
views/            Plain PHP templates, layouts + partials
public/           Web root — index.php is the only PHP entry point
tests/smoke.php   CLI checks for the security-critical primitives
```

## Security notes

- Every DB query is a prepared statement; `ORDER BY` columns come from an
  allowlist in `config/content.php`, never from user input.
- Every echoed variable goes through `e()`. Rich text (`body_bn`) is authored
  as Markdown and rendered through a whitelist renderer
  (`App\Core\Markdown`) — the source text is escaped before any tag is
  reintroduced, so authored content cannot inject markup.
- MSISDNs are stored AES-256-GCM encrypted (`users.msisdn_enc`) with a
  separate HMAC blind index (`users.msisdn_hash`) for lookups — the index is
  one-way, the ciphertext needs `APP_KEY` to reverse.
- The subscription gate (`RequireSubscription` middleware) re-reads the
  database on every request. No session flag or cookie ever decides access.
  Losing access deletes every session row for that user immediately.
- CSP has no `unsafe-inline`; anything JS needs from PHP travels through
  `<script type="application/json">` and `JSON.parse`, never an inline
  `<script>` block.
- Uploaded images are re-encoded through GD (strips EXIF/GPS and any embedded
  payload), stored outside the web root, and served through
  `MediaController` with a fixed `Content-Type`.

## What's deliberately incomplete

This is a working, launch-shaped build of the full spec, with one honestly-
scoped gap: **content volume**. The spec's own estimate (`04-AI-BUILD-PLAYBOOK.md`)
puts full content authoring at 25–40 hours — the single largest cost in the
project — because it has to be reviewed by a native Bangla speaker, not
machine-translated. This repo ships 20 plants (the exact beginner list named
in the brief), 13 problems and 8 guides, hand-written in conversational
Bangla, so every feature is genuinely exercised end-to-end. Growing that to
the spec's 60/40/20 minimum is tracked in `PROGRESS.md`.

The `CarrierGateway` endpoint paths are `// TODO:` constants per spec §7.3 —
the carrier billing provider API docs were never part of this brief, so guessing an endpoint would
be worse than leaving it explicit. Everything else — OTP, billing state
machine, webhooks, admin — is built and tested end-to-end against
`MockGateway`; swapping to production is a single `.env` change plus filling
those constants.

## License

Private project — all rights reserved.
