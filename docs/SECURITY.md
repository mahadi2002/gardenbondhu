# Security

What's actually protecting what, and why it's built the way it is. The main
things worth protecting here are user accounts (email + password) and admin
access — that's the whole threat model, and most of what follows exists
because of one or the other.

## SQL

Every query is a prepared statement, no exceptions, including `ORDER BY` —
sort columns come from a fixed allowlist in `config/content.php`, never
built from a request parameter directly. `PDO::ATTR_EMULATE_PREPARES` is
off, so PHP isn't even capable of accidentally interpolating a value into a
query string.

## Output

Everything printed in a view goes through `e()`. Rich text (plant/problem/
guide bodies) is authored as Markdown and rendered through a hand-rolled
whitelist renderer (`App\Core\Markdown`) instead of trusting raw HTML — the
source text gets escaped *first*, then specific patterns like `**bold**` or
`[link](url)` get turned into tags. There's no path where authored content
can inject a script tag, because the escaping happens before any tag is
reintroduced, not after.

## Passwords

`users.password_hash` uses PHP's default `password_hash()` algorithm
(Argon2id on current PHP); `admins.password_hash` is hashed and rehashed
with `PASSWORD_ARGON2ID` explicitly, so an admin account stays on Argon2id
even if a future PHP version changes what "default" means. Neither is ever
reversible. Login failure messages are deliberately generic ("email or
password is wrong") so the login and password-reset forms can't be used to
enumerate registered emails. See `App\Controllers\AuthController` and
`App\Controllers\Admin\AdminAuthController`.

## Sessions

Stored in the database specifically so access can be revoked immediately
instead of at next login — see ARCHITECTURE.md. Bound to a hash of the
user-agent string, not the IP. Binding to IP sounds more secure but on
mobile data in Bangladesh the IP changes constantly, so it'd just log
people out randomly throughout the day for no real security benefit.
Session ID gets regenerated on login and on any role change.

## CSRF

Every POST/PUT/DELETE checks a per-session token, no exceptions — there's no
route in `app/routes.php` today that skips the `csrf` middleware.

## Rate limiting

A handful of buckets, each with its own limit, backed by a `rate_limits`
table rather than assuming Redis is available: `login`, `register`,
`password_reset`, `admin_login`, `admin_totp`, `qa_post`, `search`,
`contact`, and `diagnose_demo` — see `App\Core\RateLimit::BUCKETS` for the
exact limits/windows and `app/routes.php` for which route hits which bucket
via `rl:<name>`. Plus a honeypot field on the register and contact forms —
no CAPTCHA, because a CAPTCHA on a free registration flow would tank
conversion for approximately zero additional protection against anything a
determined attacker actually cares about.

## Headers / CSP

```
Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; ...
```

No `unsafe-inline`, anywhere, on either scripts or styles. That constraint
shaped a few things: any data JS needs from PHP goes through a
`<script type="application/json">` block and gets `JSON.parse`'d, and any
value that would normally be an inline `style="width:N%"` (a confidence
bar, a chart, the dashboard's progress ring) is instead a server-computed
CSS class from a pre-generated set in 5% steps — `.w-pct-65`, `.progress-40`,
that kind of thing. It's a little more code than an inline style would've
been, and it means the CSP header isn't a suggestion.

## Uploads

See FEATURES.md — the short version is finfo-checked, GD-re-encoded,
randomly renamed, stored outside the web root, served through a controller
that sets its own Content-Type. Never trusts the client.

## Admin

Separate authentication from regular users entirely — its own `admins`
table, own email+password login at `/admin/login`, Argon2id specifically
(not PHP's default, which can silently downgrade). Optional IP allowlist via
`ADMIN_IP_ALLOWLIST` if you want to lock it down further.

Optional TOTP 2FA (`app/Support/Totp.php`, RFC 6238, hand-rolled on
`hash_hmac` — no dependency) — an admin enrolls it themselves from
`/admin/security`. Once enrolled, a password-only login lands in a pending
state (`admin_pending_id` in session, never `admin_id`) until a live code
also checks out at `/admin/login/verify`; `RequireAdmin` only ever trusts
`admin_id`. Enrollment requires one live code back before the secret is
persisted, and disabling it back requires the current password. The secret
(`admins.totp_secret`) is encrypted at rest with the same `App\Core\Crypto`
AES-256-GCM helper used elsewhere in the app.

## Before this goes live for real

See TODO.md — fresh encryption keys, delete the seeded test accounts, an
actual security scan against a staging copy, and a backup restore that's
been tested at least once, not just assumed to work.
