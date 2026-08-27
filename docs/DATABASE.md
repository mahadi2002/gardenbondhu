# Database

MySQL 8 or MariaDB 10.4+, `utf8mb4` throughout because Bangla needs it.
Migrations live in `database/migrations/`, numbered, applied in order by
`database/migrate.php`. Running the migrate script twice is a no-op — it
tracks what's already applied in a `migrations` table.

## Identity

- **`users`** — one row per account. Plain email + password (`email` unique,
  `password_hash` via `password_hash()`) — see `008_email_auth.sql`, which
  replaced the old phone/carrier-OTP columns with these. `status` is
  `pending`, `active`, or `blocked`; `RequireAuth` re-checks it from the DB
  on every gated request.
- **`password_resets`** — one-time reset tokens for `/forgot-password`. Only
  the SHA-256 hash of the raw token is stored — the raw token lives in the
  emailed link and nowhere else.
- **`admins`** — separate from `users` entirely. Argon2id passwords, optional
  encrypted TOTP secret (`totp_secret`) for 2FA (see SECURITY.md and
  FEATURES.md).

## Content

- **`plants`**, **`problems`**, **`guides`** — the actual knowledge base.
  Each has a short public-facing part (`summary_bn` / `description_bn` /
  `excerpt_bn`) shown to anonymous visitors, and a fuller part (`body_bn`,
  `organic_remedy_bn`, `chemical_remedy_bn`, etc.) that renders once the
  viewer is logged in — free registration gates it, not a paywall. The
  detail-page query always selects every column; it's the view that decides
  what to render based on `$isLoggedIn`. `guides.is_premium` is a leftover
  column from the old paid-tier split — admins can still toggle it in the
  guide form, but nothing reads it for access control any more.
- **`plant_categories`**, **`seasons`** — lookup tables.
- **`symptoms`** — feeds the leaf-picker on the diagnosis page, grouped by
  which part of the plant they show up on (leaf, stem, root, flower, fruit,
  soil, or "whole plant").
- **`problem_symptoms`** — many-to-many, with a weight (1–10) per pairing.
  This weight is what the diagnosis scoring actually adds up — see
  FEATURES.md.
- **`plant_problems`**, **`plant_seasons`** — which problems commonly hit
  which plants, and what to do with a plant in a given season.

## What a logged-in user owns

- **`user_plants`** — someone's personal garden log.
- **`care_tasks`** — auto-generated watering/fertilizing reminders, tied to
  a `user_plants` row.
- **`questions`**, **`answers`** — Q&A. Questions start `pending` and need
  admin approval before anyone else sees them.

## Plumbing

- **`sessions`** — yes, sessions are a table, not files. See ARCHITECTURE.md.
- **`rate_limits`** — sliding-window counters, one row per bucket+key
  combination (e.g. `login:0:ip:<hash>`, `register:0:ip:<hash>`,
  `admin_totp:0:ip:<hash>`) — see `App\Core\RateLimit` and the `rl:*`
  middleware entries in `routes.php`.
- **`audit_log`** — who did what, when. Never stores a raw password, TOTP
  secret, or password-reset token, only hashes/prefixes.

## Full-text search

`005_indexes.sql` adds FULLTEXT indexes with an ngram parser, which MySQL 8
has and MariaDB doesn't. If that migration fails on MariaDB it's expected —
`migrate.php` catches it, logs a note, and moves on. `SearchService` checks
at runtime whether the indexes actually exist and falls back to `LIKE '%…%'`
if not. At 50-ish plants that's plenty fast; if the catalog gets into the
thousands this is the first thing that'd need revisiting.
