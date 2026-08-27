# Architecture

No framework here, which means no magic either — every request goes through
the same handful of files and you can trace it start to finish without
jumping into a vendor folder.

## Request lifecycle

Every request hits `public/index.php`. That's the only PHP file the web
server ever executes directly. It:

1. Loads `app/bootstrap.php` — sets up the autoloader, reads `.env`, checks
   a few things that would be bad to get wrong in production (debug mode on,
   missing encryption keys), registers error handlers.
2. Builds a `Request` from the superglobals.
3. Starts the session (custom DB-backed handler, more on that below).
4. Hands the request to the `Router`, which matches it against the table in
   `app/routes.php` and runs whatever middleware that route lists, then
   calls the controller action.
5. Wraps the whole thing in a try/catch — an `HttpException` becomes the
   matching error page (404, 419, 429...), anything else becomes a generic
   500 with the real error only shown when `APP_DEBUG=true`.
6. Security headers get slapped onto the response right before it goes out,
   regardless of what happened above — even error pages get CSP/X-Frame-Options/etc.

There's no dependency injection container. Controllers `new` up whatever
Repository or Service they need directly. For an app this size that's
simpler to read than wiring everything through a container, and there's
nothing here that needs swapping out for tests — the tests that exist hit
the real security primitives directly (see `tests/smoke.php`), not mocked
collaborators.

## Layers

```
Controllers/    thin — pull input, call a service or repo, pick a view
Services/       the actual business logic (diagnosis scoring, care
                scheduling, search, image processing, audit logging,
                outbound notifications)
Repositories/   all the SQL lives here, nowhere else. Every query is a
                prepared statement. Sort columns are read from an allowlist
                in config/content.php, never taken from user input directly.
Core/           the framework-shaped stuff: Router, Request, Response,
                View, Db, Session, Csrf, Crypto, Validator, RateLimit,
                Logger, Markdown, Helpers.php (global functions)
Middleware/     one class per cross-cutting concern, chained per-route
```

Views are plain PHP with one enforced rule: anything printed goes through
`e()`. There's a tiny layout system (`View::render()` looks for
`$this->layout('layouts/whatever', [...])` at the top of a template and
wraps the rendered output in it) but no templating language, no compiler —
it's just PHP requiring PHP files with `extract()`'d variables.

## Things that are worth knowing before you touch them

**Sessions live in the database**, not in files or Redis. That means access
can be revoked immediately — e.g. an admin setting a user's `status` to
`blocked` — instead of waiting for PHP's garbage collector to expire a file
on its own schedule. `RequireAuth` re-checks the user's `status` from the DB
on every gated request. See `App\Core\Session`.

**Nothing decides access from the session alone.** `RequireAuth` (the
middleware gating everything under `/app`) and `RequireAdmin` (everything
under `/admin` except the login/verify routes) both re-read status from the
database on every single request. No cached flag. Slightly more DB load,
way harder to get wrong.

**Auth is plain email + password**, Argon2id via `password_hash()`, nothing
more exotic for regular users. Admins get an optional second factor on top —
TOTP (RFC 6238, hand-rolled in `App\Support\Totp`, no dependency). An admin
enrolls it themselves at `/admin/security`; once enrolled, a correct password
alone only sets `admin_pending_id`, not `admin_id` — a live 6-digit code at
`/admin/login/verify` is what `RequireAdmin` actually trusts. The encrypted
TOTP secret (`admins.totp_secret`) uses the same `App\Core\Crypto`
AES-256-GCM helper (`APP_KEY`-derived) that the rest of the app's
at-rest secrets use.

**Uploaded images never touch the web root.** They land in `storage/uploads/`
and get served back through `MediaController`, re-encoded through GD on the
way in (kills EXIF data, GPS coordinates, and any embedded garbage in a
polyglot file) with a randomly generated filename.

## Cron jobs

Scripts in `cron/`, each wrapped in a file lock (`cron/_lock.php`) so two
overlapping runs don't double-process anything:

- `care_tasks.php` — daily, generates the next week of watering/fertilizing
  tasks for everyone's garden
- `cleanup.php` — daily, purges old rate-limit rows, stale sessions, aged-out
  audit log rows, and expired password-reset tokens
