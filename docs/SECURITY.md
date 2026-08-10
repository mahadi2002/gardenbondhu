# Security

What's actually protecting what, and why it's built the way it is. The two
things worth protecting here are phone numbers and billing state — that's
the whole threat model, and most of what follows exists because of one or
the other.

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

## Phone numbers

Encrypted at rest (AES-256-GCM), looked up via a separate HMAC hash — see
ARCHITECTURE.md and DATABASE.md, this is described in a couple places
because it's the thing most worth getting right. Full numbers never appear
in a log line, an admin screen, or anywhere else — only the last 4 digits,
or a hash prefix if something needs to correlate log entries without
revealing the number itself.

## Sessions

Stored in the database specifically so access can be revoked immediately
instead of at next login — see ARCHITECTURE.md. Bound to a hash of the
user-agent string, not the IP. Binding to IP sounds more secure but on
mobile data in Bangladesh the IP changes constantly, so it'd just log
people out randomly throughout the day for no real security benefit.
Session ID gets regenerated on login and on any role change.

## CSRF

Every POST/PUT/DELETE checks a per-session token. The one exception is the
carrier billing webhook, which obviously can't carry a session token since
it's not a browser making the request — that's verified by signature and IP
allowlist instead.

## Rate limiting

A handful of buckets (OTP requests, OTP verification attempts, admin login,
search, contact form, question posting), each with its own limit, backed by
a `rate_limits` table rather than assuming Redis is available. Plus a
honeypot field and a minimum 2-second fill time on the subscribe form — no
CAPTCHA, because a CAPTCHA on a ৳2.78/day impulse-subscribe flow would tank
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

Separate authentication from regular users entirely (email+password,
Argon2id, not phone+OTP). User search only ever works by last-4-digits,
and no screen anywhere shows a full phone number. Optional IP allowlist via
`ADMIN_IP_ALLOWLIST` if you want to lock it down further.

## Before this goes live for real

See TODO.md — fresh encryption keys, delete the seeded test accounts, real
carrier billing credentials, an actual security scan against a staging copy,
and a backup restore that's been tested at least once, not just assumed to work.
