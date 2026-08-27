# TODO

## Content
- Catalog's at 51 plants, 18 problems, 8 guides. Want more like 60/40/20 eventually.
- No guides yet under "tools" or a dedicated indoor-plants category.
- Someone who actually speaks Bangla natively needs to read through all the plant/problem
  copy before this goes live. I wrote it, it should be fine, but "should be fine" isn't
  good enough for something people will actually act on in their own garden.
- A few `toxic_to_pets` flags I couldn't pin down for sure against ASPCA's list — marigold
  (Tagetes vs Calendula naming is a mess), tuberose, neem, eggplant. Left them as-is,
  worth a second pair of eyes.

## Before actually shipping this
- Fresh APP_KEY / HASH_PEPPER on the real server, not the dev ones.
- Delete the seeded admin account and demo user (demo@kishalay.test) before going live.
- Nightly mysqldump + actually test a restore once, not just assume it works.
- Run a proper security scan (ZAP or similar) against a staging copy.
- Actually deploy it somewhere (see docs/DEPLOYMENT.md) and point an uptime
  monitor at `/health` — right now this only exists on a local dev server,
  so it has zero real uptime by definition, not because of a code problem.
- Set SUPPORT_EMAIL in .env once there's a real inbox to send it to. Without
  it, contact-form submissions still land in /admin/contact just fine — the
  email is a nice-to-have heads-up, not the only way to see them.

## Nice to have, not urgent
- Search is LIKE-only, permanently — see SearchService.php and
  005_indexes.sql. Fine for now at this content size (60/40/20-ish); would
  want real fulltext (or something like Meilisearch) only if the catalog
  gets much bigger.
- SMS care reminders — needs its own SMS gateway account and costs money per
  message, punting on this until there's a reason to spend on it.
