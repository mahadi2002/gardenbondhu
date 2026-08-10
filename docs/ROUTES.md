# Routes

The actual source of truth is `app/routes.php` — this is just that table
laid out more readably. Middleware shorthand: `csrf` checks the token on
POST/PUT/DELETE, `guest` bounces you if you're already logged in, `auth`
requires a login, `sub` requires an *active* subscription (re-checked from
the DB every time, see ARCHITECTURE.md), `admin` requires an admin login,
`rl:name` rate-limits against the bucket of that name.

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
| `GET /plants`, `/plants/{slug}` | Plant catalog + detail (teaser only if not subscribed) |
| `GET /problems`, `/problems/{slug}` | Same deal, for pests/diseases |
| `GET /guides`, `/guides/{slug}` | Same deal, for how-to articles |
| `GET /media/{token}` | Serves uploaded Q&A images (never a direct file path) |
| `GET /sitemap.xml` | Generated from published content |

## Getting in

| Route | What it does |
|---|---|
| `GET /subscribe` | Phone number form |
| `POST /subscribe/otp` | Sends the OTP |
| `GET /subscribe/verify`, `POST /subscribe/verify` | Enter the code |
| `POST /subscribe/resend` | Resend, rate-limited so you can't spam it |
| `GET /login` | Same flow, framed as "log back in" instead of "subscribe" for people who already pay |
| `POST /logout` | Kills the session |

## Inside the app (needs an active subscription)

| Route | What it does |
|---|---|
| `GET /app` | Dashboard — today's tasks, quick links |
| `GET /app/plants`, `/finder`, `/{slug}` | Full plant content + the filterable finder |
| `GET /app/problems/{slug}`, `/app/guides/{slug}` | Full content, no paywall |
| `GET /app/diagnose`, `POST /app/diagnose` | The leaf-symptom checker |
| `GET /app/calendar` | What to do this month, by season |
| `GET /app/tools`, `POST .../water`, `.../fertilizer`, `.../pot` | The three calculators |
| `GET /app/garden`, `/add`, `POST /app/garden`, `/{id}`, `/{id}/delete` | Personal plant log |
| `POST /app/garden/task/{id}/done` | Mark a care task complete |
| `GET /app/qa`, `/ask`, `POST /app/qa`, `/{id}`, `POST .../answer` | Q&A |

## Account (works even without an active subscription — you need to be able to fix billing)

| Route | What it does |
|---|---|
| `GET /account` | Status, subscription details, charge history |
| `GET /expired` | Where you land when access has lapsed |
| `GET /account/unsubscribe`, `POST` | Cancel |
| `POST /account/delete` | Anonymize + cancel, permanent |

## Behind the scenes

| Route | What it does |
|---|---|
| `POST /webhooks/carrier` | Carrier billing provider calling us back — no CSRF (can't, it's not a browser), verified by signature/IP instead |

## Admin (`/admin/*`)

Separate login (email+password, not phone+OTP — admins might not have a
Robi number). Standard CRUD for plants, problems, guides, and Q&A
moderation, a users list that only ever shows the last 4 digits of a phone
number, a contact-form inbox (`/admin/contact`), and an audit log viewer.
Nothing exotic — see `app/Controllers/Admin/` if you need the specifics.
