# Routes

The actual source of truth is `app/routes.php` — this is just that table
laid out more readably. Middleware shorthand: `csrf` checks the token on
POST/PUT/DELETE, `guest` bounces you if you're already logged in, `auth`
requires a login (any logged-in user, re-checked from the DB, see
ARCHITECTURE.md), `admin` requires an admin login, `rl:name` rate-limits
against the bucket of that name.

## Ops

| Route | What it does |
|---|---|
| `GET /health` | Hits the DB with `SELECT 1` and returns JSON — point an uptime monitor or a load balancer health check at this, not at `/` |

## Public

| Route | What it does |
|---|---|
| `GET /` | Landing page |
| `GET /privacy`, `/terms`, `/about` | Static pages |
| `GET /contact`, `POST /contact` | Contact form |
| `GET /search` | Search across plants/problems/guides |
| `GET /plants`, `/plants/{slug}` | Plant catalog + detail (teaser only if not logged in) |
| `GET /problems`, `/problems/{slug}` | Same deal, for pests/diseases |
| `GET /guides`, `/guides/{slug}` | Same deal, for how-to articles |
| `GET /media/{slug}` | Serves uploaded Q&A images (never a direct file path) |
| `GET /sitemap.xml` | Generated from published content |

## Getting in

| Route | What it does |
|---|---|
| `GET /register`, `POST /register` | Email + password sign-up |
| `GET /login`, `POST /login` | Email + password sign-in |
| `POST /logout` | Kills the session |
| `GET /forgot-password`, `POST /forgot-password` | Request a password-reset email |
| `GET /reset-password/{slug}`, `POST /reset-password/{slug}` | Set a new password from the emailed link (`{slug}` is the reset token) |

## Inside the app (any logged-in user — no billing gate)

| Route | What it does |
|---|---|
| `GET /app` | Dashboard — today's tasks, quick links |
| `GET /app/plants`, `/finder`, `/{slug}` | Full plant content + the filterable finder |
| `GET /app/problems`, `/{slug}`, `/app/guides`, `/{slug}` | Full content, no paywall |
| `GET /app/diagnose`, `POST /app/diagnose` | The leaf-symptom checker |
| `GET /app/calendar` | What to do this month, by season |
| `GET /app/tools`, `POST .../water`, `.../fertilizer`, `.../pot` | The three calculators |
| `GET /app/garden`, `/add`, `POST /app/garden`, `/{id}`, `/{id}/delete` | Personal plant log |
| `POST /app/garden/task/{id}/done` | Mark a care task complete |
| `GET /app/qa`, `/ask`, `POST /app/qa`, `/{id}`, `POST .../answer` | Q&A |

## Account

| Route | What it does |
|---|---|
| `GET /account` | Account status |
| `POST /account/delete` | Anonymize + delete, permanent |

## Admin (`/admin/*`)

| Route | What it does |
|---|---|
| `GET /admin/login`, `POST /admin/login` | Email + password sign-in |
| `GET /admin/login/verify`, `POST /admin/login/verify` | Second step for an admin who has enrolled TOTP — password success alone lands in a pending state, not a full admin session |
| `POST /admin/logout` | Kills the admin session |
| `GET /admin` | Dashboard |
| `GET /admin/security` | 2FA status |
| `GET /admin/security/totp/setup` | Enroll TOTP — shows the secret + `otpauth://` URI |
| `POST /admin/security/totp/confirm` | Confirm enrollment with a live code |
| `POST /admin/security/totp/disable` | Disable 2FA (requires current password) |
| `GET /admin/plants`, `/new`, `/{id}`; `POST /admin/plants`, `/{id}`, `/{id}/delete` | Plant catalog CRUD |
| `GET /admin/problems`, `/new`, `/{id}`; `POST /admin/problems`, `/{id}`, `/{id}/delete` | Problem catalog CRUD |
| `GET /admin/guides`, `/new`, `/{id}`; `POST /admin/guides`, `/{id}`, `/{id}/delete` | Guide catalog CRUD |
| `GET /admin/qa`, `/{id}`; `POST /admin/qa/{id}` | Q&A moderation |
| `GET /admin/users`, `/{id}`; `POST /admin/users/{id}` | User list + per-user status changes |
| `GET /admin/contact`, `/{id}`; `POST /admin/contact/{id}/resolve` | Contact-form inbox |
| `GET /admin/logs` | Audit log viewer |

Nothing exotic beyond that — see `app/Controllers/Admin/` if you need the specifics.
