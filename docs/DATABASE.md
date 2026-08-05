# Database

MySQL 8 or MariaDB 10.4+, `utf8mb4` throughout because Bangla needs it.
Migrations live in `database/migrations/`, numbered, applied in order by
`database/migrate.php`. Running the migrate script twice is a no-op — it
tracks what's already applied in a `migrations` table.

## Identity & billing

- **`users`** — one row per subscriber. Phone number is stored encrypted
  (`msisdn_enc`) plus a separate one-way hash for lookups (`msisdn_hash`) —
  see ARCHITECTURE.md for why it's split that way. `msisdn_last4` is the
  only part ever shown in any UI, including admin.
- **`subscriptions`** — a user can have more than one over time (re-subscribe
  after cancelling creates a new row, history is never deleted). Status is
  one of `pending → active → grace → expired` or `unsubscribed`, and
  `current_period_end` is what actually gates access.
- **`charge_transactions`** — one row per billing attempt, keyed by an
  idempotency string so the hourly cron can't accidentally double-charge
  someone if it gets interrupted mid-run.
- **`otp_requests`** — codes are hashed (`password_hash()`), never stored
  plain, and expire after a few minutes.
- **`admins`** — separate from `users` entirely. Argon2id passwords.

## Content

- **`plants`**, **`problems`**, **`guides`** — the actual knowledge base.
  Each has a free part (`summary_bn` / `description_bn` / `excerpt_bn`) and
  a paid part (`body_bn`, `organic_remedy_bn`, `chemical_remedy_bn`, etc.)
  — the paid columns just aren't selected in the query at all for a
  non-subscriber, which is a stronger guarantee than hiding them in the UI.
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
- **`bookmarks`** — saved plants/problems/guides.

## Plumbing

- **`sessions`** — yes, sessions are a table, not files. See ARCHITECTURE.md.
- **`rate_limits`** — sliding-window counters, one row per bucket+key
  combination (e.g. `otp_request:0:ip:<hash>`).
- **`audit_log`** — who did what, when. Never stores a full phone number or
  an OTP code, only hashes/prefixes.
- **`webhook_events`** — every the carrier billing provider callback we've ever received, keyed by
  their event ID so a retried webhook applies exactly once.
- **`jobs`** — a bare-bones queue. Right now the only job type is applying a
  webhook event after it's already been acknowledged.

## Full-text search

`005_indexes.sql` adds FULLTEXT indexes with an ngram parser, which MySQL 8
has and MariaDB doesn't. If that migration fails on MariaDB it's expected —
`migrate.php` catches it, logs a note, and moves on. `SearchService` checks
at runtime whether the indexes actually exist and falls back to `LIKE '%…%'`
if not. At 50-ish plants that's plenty fast; if the catalog gets into the
thousands this is the first thing that'd need revisiting.
