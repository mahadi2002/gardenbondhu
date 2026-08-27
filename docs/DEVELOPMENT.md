# Development notes

## Running the tests

There's no PHPUnit here, just `tests/smoke.php` — a plain CLI script using
a hand-rolled `check(string $label, bool $condition)` helper, no assertions
library. It checks the stuff that would be genuinely bad to get wrong
silently: encrypt/decrypt round-tripping, the blind-index hash being stable
and not looking reversible, CSRF token comparison, email validation, the
Markdown renderer actually stripping raw script tags, TOTP codes against a
published RFC 6238 test vector (plus the one-step clock-drift tolerance),
and the email + password auth path — `UserRepo::create()` persisting an
account and `findForAuth()` returning a hash that verifies against the
original password. Run it with `php tests/smoke.php`.

The Repository/auth checks need a real database — they open a transaction
against whatever `.env` points at, insert throwaway fixtures, and always
roll back, win or lose. No live DB configured? Those checks print a `SKIP`
line and the rest of the file still runs. CI spins up a real MySQL service
and runs `php database/migrate.php --fresh` first, so they run for real
there.

If you add something to `Crypto`, `Csrf`, `Validator`, or `Totp`, add a
check here too — this file is meant to be the "did I just break something
load-bearing" gate, not exhaustive coverage.

## Conventions, mostly enforced by habit rather than tooling

- No Composer packages. If you're reaching for one, first check whether PHP
  already has what you need built in — it usually does.
- Every DB call is a prepared statement. Every echoed variable goes through
  `e()`. If you're writing raw `$_POST['whatever']` into a query or a view
  without going through `Validator` or `e()` first, stop and fix it before
  it becomes someone else's bug to find later.
- Bangla typography: `line-height: 1.85` on body text (Bangla needs more
  vertical room than Latin scripts at the same size), never `letter-spacing`
  on Bangla text (it visibly breaks conjunct characters), never
  `text-transform: uppercase` on it either (does nothing, just signals
  nobody checked).
- No inline `style=""` or inline `<script>` — the CSP doesn't allow it and
  won't be relaxed to make something convenient. If a value is genuinely
  dynamic (a percentage bar, a chart), generate a CSS class server-side
  instead — see the `.w-pct-N` / `.progress-N` pattern in `app.css` and
  `Helpers::pct_step()`.
- `/app/*` routes decide access by asking the database, not by checking a
  session flag. Every time. This one's non-negotiable — see SECURITY.md
  for why.

## Local setup gotchas

- The dev server needs `router-dev.php`, not `index.php` directly — see the
  README. Using plain `index.php` with `php -S` means CSS/JS/images all
  404 because the front controller doesn't know how to fall through to a
  static file.
- Search is `LIKE '%term%'`, permanently — see `SearchService.php` and
  `005_indexes.sql`. That file is an intentional no-op placeholder now, not
  a bug to "fix" by adding FULLTEXT back.
- If you're testing `/login`, `/register`, or `/admin/login` repeatedly from
  the same machine, you'll hit the rate limiter fast (see the `rl:*` entries
  in `routes.php` and `App\Core\RateLimit::BUCKETS` for the actual limits).
  That's the security feature working correctly, not a bug — just
  `TRUNCATE TABLE rate_limits;` when you need to reset it during testing.
- Testing admin TOTP locally: enroll at `/admin/security/totp/setup` with a
  real authenticator app, or just run the code by hand with
  `App\Support\Totp::code($secret)` in a scratch script if you don't want to
  install one.

## If you're adding a new plant/problem/guide

`database/seeds/content.php` is just a PHP array — copy the shape of an
existing entry. The seed runner checks by slug before inserting, so running
it again after adding new entries only inserts what's new. Write
`summary_bn` (shown to anonymous visitors) so it's genuinely useful on its
own, not a teaser that feels like the real content got hidden — `body_bn`
(shown once someone's logged in, which is free) should feel like *more*,
not like the public part was gutted to push registration.

Write it in actual conversational Bangla, second person (আপনি), like
you're explaining it to someone standing in their own garden — not a
literal translation of an English gardening article. It reads different,
and native speakers can tell immediately when it's not.
