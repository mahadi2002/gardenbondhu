# বাগানবন্ধু (GardenBondhu)

A Bangla gardening knowledge platform for new/hobbyist gardeners in Bangladesh —
plant care guides, leaf-symptom disease diagnosis, a personal garden tracker,
and a community Q&A, gated behind a carrier billing daily micro-subscription (৳2.78/day,
Robi & Airtel only, cancel any day — never framed as a monthly plan).

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
| CSS | One hand-written file, no framework, no CDN — gradient/glass/motion design system |
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

**On Windows in PowerShell**, the two commands above need to run in separate
terminal tabs (MySQL in the background, PHP's dev server blocks its terminal
on purpose — that's it actively serving):

```powershell
Start-Process "C:\xampp\mysql\bin\mysqld.exe" -ArgumentList "--defaults-file=C:\xampp\mysql\bin\my.ini","--standalone"
cd public
C:\xampp\php\php.exe -S 127.0.0.1:8000 router-dev.php
```

### Demo credentials

The seed creates two ready-to-use logins:

| | |
|---|---|
| **Admin panel** — `/admin/login` | `admin@gardenbondhu.test` / `ChangeMe123!` — change before launch |
| **Subscriber demo** — `/login` | Phone `01812345678`, OTP code `123456` — pre-seeded with an active mock subscription, skips straight into `/app` |

With `CARRIER_DRIVER=mock` (the default), OTPs are generated for real, logged
to `storage/logs/otp-*.log`, and flashed on screen when `APP_DEBUG=true`. The
universal dev code `123456` also always verifies for any number. Simulate
billing failures by using an MSISDN ending in `00` (insufficient balance →
grace) or `99` (hard failure → expired) — see spec §7.4. Note: retrying the
same number the same calendar day after a failure won't re-attempt the charge
(idempotency is scoped per day) — use a different number to test a fresh
successful subscribe.

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
4. `php database/migrate.php` (add `--seed` only on a fresh install — and
   delete the seeded admin/demo-subscriber rows afterward, see §9.13).
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
  seeds/          categories/seasons/symptoms SQL + content.php (plant/problem/
                 guide catalog — see "Content" below) + plant_photo_credits*.json
cron/             charge_cycle (hourly), queue_worker (*/1), care_tasks (daily
                 05:00), cleanup (daily 03:00) — each flock-guarded
views/            Plain PHP templates, layouts + partials
public/           Web root — index.php is the only PHP entry point
public/assets/img/plants/  Real, license-verified plant photos (see "Photos")
tests/smoke.php   CLI checks for the security-critical primitives
```

## Content

The catalog covers plants actually grown or sold in Bangladesh — common
vegetables, fruits, flowers, spices, indoor plants and succulents, plus a
handful of internationally-originated ornamentals now common in local
nurseries (bougainvillea, dragon fruit, anthurium, etc.). It is not, and
cannot practically be, "every plant in the world" — the whole design (Bangla
seasons in the care calendar, BDT pricing, local availability) is calibrated
for Bangladesh gardening specifically. See `PROGRESS.md` for the current
count and what's still being expanded.

Every plant/problem/guide follows the spec's free/paid split: `summary_bn` is
always public (what shows on listing pages and the teaser), `body_bn` (the
full care guide) is truncated server-side before it ever reaches a
non-subscriber's HTML — see §9.1 and the paywall notes below.

### Photos

Plant photos are real, sourced from Wikimedia Commons under verified open
licenses (Public Domain, CC0, CC-BY, or CC-BY-SA — never scraped from
unlicensed sources, per spec §14). Each image's author, exact license, and
source page is recorded in `database/seeds/plant_photo_credits*.json` and
consolidated in `PHOTO_CREDITS.md` at the repo root. `database/seeds/content.php`
wires a plant's `hero_image` automatically by checking whether
`public/assets/img/plants/<slug>.jpg` exists on disk — no hand-maintained
mapping to keep in sync, and re-running the seed after adding more photos
later backfills any plant that didn't have one yet.

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
- CSP has no `unsafe-inline` — anywhere. Every dynamic style value (chart
  bars, confidence meters, the dashboard progress ring) is rendered as a
  server-computed utility class (`.w-pct-N`, `.h-pct-N`, `.progress-N` in
  5% steps), never an inline `style=""` attribute, so nothing here depends
  on relaxing the CSP. Anything JS needs from PHP travels through
  `<script type="application/json">` and `JSON.parse`, never an inline
  `<script>` block.
- Uploaded images are re-encoded through GD (strips EXIF/GPS and any embedded
  payload), stored outside the web root, and served through
  `MediaController` with a fixed `Content-Type`.

## Interface

The design system (`public/assets/css/app.css`) layers gradients, soft
elevation and motion onto the spec's locked "ভেজা পাতা" palette — no colours
were replaced, just given depth. Notable interactive pieces, all vanilla
JS/CSS with no dependency:

- Animated stat counters, a conic-gradient progress ring on the dashboard
  (today's care tasks done/total), a floating action button for quick
  actions in the gated app, button ripple, pointer-tracked card tilt.
- Dismissable, auto-fading flash notices — progressively enhances the same
  server-rendered banner markup, so it's still fully readable with zero JS.
- The leaf-diagnoser's hotspots pulse gently until the first tap.
- Logout is reachable from every page while logged in, regardless of layout
  (public pages, `/account`, `/expired`, and the gated app shell each render
  it — this was a real gap fixed after testing surfaced it, see git log).

## What's deliberately incomplete

The `CarrierGateway` endpoint paths are `// TODO:` constants per spec §7.3 —
the carrier billing provider API docs were never part of this brief, so guessing an endpoint would
be worse than leaving it explicit. Everything else — OTP, billing state
machine, webhooks, admin — is built and tested end-to-end against
`MockGateway`; swapping to production is a single `.env` change plus filling
those constants.

Content continues to grow incrementally (see `PROGRESS.md`) — each batch is
hand-written conversational Bangla, not machine-translated, per the spec's
own caution that AI-drafted Bangla reads as machine-made to native speakers.
A native-speaker editing pass before public launch is still recommended.

## License

Private project — all rights reserved. Plant photos are individually
licensed per `PHOTO_CREDITS.md` — those retain their original Creative
Commons / Public Domain terms regardless of this project's own license.
