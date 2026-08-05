# TODO

## Content
- Catalog's at 51 plants, 13 problems, 8 guides. Want more like 60/40/20 eventually.
- Missing problems: fruit fly, cutworm, anthracnose, bacterial wilt, nematode/root-knot.
- No guides yet under "tools" or a dedicated indoor-plants category.
- Someone who actually speaks Bangla natively needs to read through all the plant/problem
  copy before this goes live. I wrote it, it should be fine, but "should be fine" isn't
  good enough for something people are paying for.
- A few `toxic_to_pets` flags I couldn't pin down for sure against ASPCA's list — marigold
  (Tagetes vs Calendula naming is a mess), tuberose, neem, eggplant. Left them as-is,
  worth a second pair of eyes.

## Billing
- CarrierGateway is stubbed — endpoint paths are TODO constants because there's no
  API doc yet. Everything else (OTP, state machine, webhooks, cron) works end to end
  against the mock gateway. Swapping to prod is filling in ~5 constants + one .env var.
- Robi/Airtel prefix mapping (config/operators.php) — double check with the carrier billing provider before
  launch, BTRC numbering is a little different from what's commonly assumed.

## Before actually shipping this
- Fresh APP_KEY / HASH_PEPPER on the real server, not the dev ones.
- Delete the seeded admin account and demo subscriber (01812345678) before going live.
- Get real the carrier billing provider credentials, flip CARRIER_DRIVER to `carrier`, test one real charge.
- Nightly mysqldump + actually test a restore once, not just assume it works.
- Run a proper security scan (ZAP or similar) against a staging copy.

## Nice to have, not urgent
- FULLTEXT search only works on real MySQL — MariaDB doesn't ship the ngram parser,
  so it silently falls back to LIKE. Fine for now at this content size, would want
  real fulltext (or something like Meilisearch) if the catalog gets much bigger.
- Admin TOTP 2FA — spec mentions it as optional, never got to it.
- SMS care reminders — needs its own the carrier billing provider SMS quota, costs money per message,
  punting on this until the daily-charge margin can absorb it.
