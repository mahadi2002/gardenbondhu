# PROGRESS.md

Running log of build phases against `01-BUILD-SPEC.md` §13. Paste the
relevant section into a new AI session before continuing work, per
`04-AI-BUILD-PLAYBOOK.md`.

## Status: P0–P11 built and smoke-tested. P12 (full content) and P13
## (the carrier billing provider production credentials + hardening checklist) remain.

| Phase | Status | Notes |
|---|---|---|
| P0 Skeleton | ✅ Done | Router, Request, Response, View, Db, Helpers, error views, `.htaccess` |
| P1 Data layer | ✅ Done | `database/migrations/001–005`, `migrate.php` (idempotent, tolerates MariaDB's missing `ngram` parser) |
| P2 Security core | ✅ Done | Crypto (AES-256-GCM + blind index), DB Session, Csrf, RateLimit, Validator, Logger; `tests/smoke.php` passes 27/27 |
| P3 Landing page | ✅ Done | All 11 sections, all §5.1 strings verbatim, leaf-diagnoser signature element, mobile-first |
| P4 Auth (MockGateway) | ✅ Done | Full subscribe → `/app` verified via curl; all §10 OTP error rows implemented |
| P5 Subscription gate | ✅ Done | DB re-read on every gated request; unsubscribe → session rows deleted, verified live |
| P6 Content | ✅ Done | Plant/Problem/Guide controllers, paywall; verified `body_bn` is absent from anonymous HTML |
| P7 Diagnoser | ✅ Done | Weighted scoring verified against seeded symptoms (root-rot correctly ranked "সম্ভাবনা বেশি") |
| P8 Personal | ✅ Done | Garden CRUD, CareScheduler, calendar, calculators; IDOR verified → 404 |
| P9 Q&A + uploads | ✅ Done | ImageService (finfo → GD re-encode → EXIF strip → random name) |
| P10 Billing | ✅ Done | `charge_cycle.php`, `queue_worker.php`, `cleanup.php`, `care_tasks.php`, webhook idempotency |
| P11 Admin | ✅ Done | Full CRUD for plants/problems/guides/qa/users, Argon2id auth, `msisdn_last4`-only display |
| P12 Content | 🟡 Partial | 20 plants (full beginner list from brief) / 13 problems / 8 guides. Spec minimum is 60/40/20 — see "Next content batch" below |
| P13 Harden & ship | ⬜ Not started | Needs real the carrier billing provider developer-portal credentials (not available in this brief) |

## Verified end-to-end (live, against a running MariaDB 10.4 + PHP 8.2)

- Full subscribe flow: phone → OTP send → OTP verify → user created → first
  charge → `active` subscription, via real HTTP requests (not unit mocks).
- Paywall: anonymous `/plants/pudina` contains zero occurrences of paid
  content; `/app/plants/pudina` (subscribed) contains the full guide.
- CSRF: POST without `_token` → HTTP 419.
- IDOR: `/app/garden/{other-user-id}` → HTTP 404, not 403.
- Session revocation: unsubscribing deletes the session row server-side
  immediately; the same browser cookie is bounced on its very next request.
- Admin login (Argon2id) → dashboard renders live KPIs from seeded data.
- `database/migrate.php --fresh` runs clean twice in a row; MariaDB's missing
  `ngram` FULLTEXT parser is caught and logged, `SearchService` falls back to
  `LIKE` automatically (this is the documented, expected path on MariaDB).

## Visual redesign (post-launch pass)

The original build followed spec §6 literally (flat cards, no motion beyond
scroll-reveal). A second pass rewrote `public/assets/css/app.css` and
`app.js`/`diagnose.js` for a more modern, interactive feel — inspired by
current plant-care app UI patterns (Planta/Plantify-style "modern
naturalism": deep-green gradients, glass surfaces, soft elevation) — while
keeping every existing CSS class name so no view had to be restructured:

- Gradient/elevation/motion design tokens (`--gradient-leaf`, `--shadow-md`,
  `--ease-spring`, etc.) layered onto the same `--leaf`/`--marigold`/`--brick`
  palette from spec §6.1 — no colours were replaced, only given depth.
- New interactive components: animated stat counters, a conic-gradient
  progress ring on the dashboard, a floating action button in the gated app,
  button ripple, pointer-tracked card tilt, a header that gains a shadow on
  scroll, an animated hamburger icon, and a rotating FAQ icon.
- Flash notices are now dismissable and auto-fade for success/info messages,
  progressively enhancing the same server-rendered markup (no JS = the
  banner just stays visible, exactly as before).
- The leaf-diagnoser hotspots pulse gently until the first tap, inviting
  interaction without being required for the feature to work.

**A real bug was found and fixed during this pass, unrelated to styling:**
`App\Core\Env::load()`'s comment-stripping regex broke on a value that is
*only* a trailing comment (`ADMIN_IP_ALLOWLIST=   # comma-separated...`) —
`trim()` ran before the comment strip, eating the leading whitespace the
regex needed, so the comment text itself became the config value. This
silently populated `admin_ip_allowlist` and made `/admin` return 403 for
everyone. Fixed in `app/Core/Env.php`; `tests/smoke.php` still passes 27/27.

**CSP compliance fix:** the original build had ~35 inline `style=""`
attributes scattered across 24 admin/app views — invisible to `curl`-based
testing (which doesn't enforce CSP) but silently blocked by real browsers
under the locked-down `style-src 'self'` policy (spec §9.9, no
`unsafe-inline`). All were replaced with static utility classes
(`.max-w-sm`, `.mt-1`, `.pre-line`, etc.) or, for genuinely dynamic values
(confidence bar width, admin charge-trend bar heights, progress-ring fill),
a stepped 5%-granularity class set (`.w-pct-N` / `.h-pct-N` / `.progress-N`)
computed server-side via the new `pct_step()` helper in `Helpers.php` — so
these work correctly with zero JavaScript, not just as a JS-only fallback.
Verified with a full route sweep: every public, gated, and admin page now
renders with `grep -c 'style="'` returning `0`.

## Known deviations from the spec bundle

- **Operator prefix map** (`config/operators.php`): the brief says Robi =
  016/019, Airtel = 017. Per BTRC allocation, Robi/Airtel actually share
  016/018, and 017/013 are Grameenphone. The spec itself flags this
  discrepancy in §7.1 and asks to verify before launch — I kept the spec's
  own flagged-but-technically-correct mapping (016/018 → robi, 017/013 →
  grameenphone) rather than the possibly-wrong brief text, and left the
  warning comment in place. **Confirm with the carrier billing provider before launch** — this is
  a one-line change in `config/operators.php`, nothing else touches it.
- `public/router-dev.php` is new, not in the original file manifest — it
  exists solely so `php -S` (used for local development) serves static
  assets the same way Apache's `.htaccess` does in production. It is never
  loaded by Apache or nginx.

## Next content batch (the real remaining work)

Per spec §12/§13, growing from the current 20/13/8 to the 60/40/20 minimum:

- **40 more plants** — the brief names the first 20 explicitly (done); the
  remaining 40 need selecting and drafting. Budget ~15 min/plant of human
  editing after an AI first draft, per `04-AI-BUILD-PLAYBOOK.md` — this is
  the single largest remaining cost (~10-15 hours).
- **27 more problems** — common Bangladesh pests/diseases not yet covered:
  ফলের মাছি (fruit fly), কাটুই পোকা (cutworm), অ্যানথ্রাকনোজ, ব্যাকটেরিয়াল
  উইল্ট, শিকড়ে গিঁট রোগ (nematode), etc.
- **12 more guides** — categories with zero guides so far: `tools`,
  `indoor` (beyond what's touched in plant pages).

Do this with `database/seeds/content.php` as the pattern: plain PHP arrays,
`INSERT ... WHERE NOT EXISTS` idempotency already handled by the seed
runner. Write a first draft, then **hand-edit the Bangla** — this is the step
the spec says decides whether the product feels native or machine-translated.

## Anti-drift checklist (run before merging new work)

- [ ] No Composer dependency added
- [ ] No colour/font outside `public/assets/css/app.css` §6.1 tokens
- [ ] No route added outside `app/routes.php`
- [ ] No raw `$_POST`/`$_GET` used without `Validator`
- [ ] No `echo $var` without `e()`
- [ ] No string concatenation inside a SQL query
- [ ] No full MSISDN or OTP logged in plaintext
- [ ] No inline `<script>` or `style=""` (breaks the CSP)
- [ ] No `/app/*` route deciding access from `$_SESSION` instead of the DB
- [ ] No invented the carrier billing provider endpoint — `// TODO:` constants only
