# Deployment

Built for cheap shared hosting first, VPS second — if it runs on cPanel
it'll run anywhere.

## Shared hosting (cPanel)

Keep the app code out of the web-facing directory:

```
/home/USER/gardenbondhu/        ← this repo, NOT web-accessible
/home/USER/public_html/         ← needs to point at gardenbondhu/public
```

If your host lets you symlink, `public_html` → `gardenbondhu/public` and
you're done. If not, copy `public/`'s contents into `public_html/` directly
and add one line near the top of that copy's `index.php`:

```php
define('APP_ROOT', '/home/USER/gardenbondhu');
```

Then:

1. `cp .env.example .env`, fill in real values. Generate a fresh
   `APP_KEY` and `HASH_PEPPER` on the server itself — don't copy the ones
   from your dev machine over.
2. Set up two MySQL users, not one: an app user with only
   `SELECT, INSERT, UPDATE, DELETE`, and a separate migrate user with full
   DDL rights that only `database/migrate.php` ever uses. If a SQL
   injection bug somehow slips through anyway, the app user physically
   can't drop a table.
3. `php database/migrate.php` (add `--seed` only if this is a genuinely
   fresh install, and delete the seeded admin/demo accounts afterward).
4. Add the four cron entries — each script has the exact crontab line in a
   comment at the top of the file.
5. `APP_ENV=production`, `APP_DEBUG=false`, `CARRIER_DRIVER=carrier` once you
   actually have the carrier billing provider credentials. The app refuses to boot if you set
   `APP_ENV=production` while still on the mock driver or with debug on —
   that's intentional, not a bug to work around.

## VPS

Same app, no code changes — swap nginx + PHP-FPM for Apache if you want,
Redis for the DB-backed sessions/rate-limits if you're at a scale where
that matters, systemd timers instead of cron. None of that is set up here
because none of it was needed at this scale, but nothing about the
architecture assumes shared hosting specifically.

## Uptime monitoring

`GET /health` actually runs a query (`SELECT 1`) instead of just returning
200 for "PHP is alive" — point an external monitor at it, not at `/`.
UptimeRobot's free tier is enough to start: a 5-minute HTTP check against
`https://yourdomain/health` that alerts on anything but a 200. Nothing about
this app pings that URL itself — it's a passive endpoint, the monitoring
service is the thing that has to actually exist and be configured.

## Scaling past one server

Nothing in the request path assumes a single machine — sessions live in
MySQL, not local files, so you can put two app servers behind a load
balancer and either one can serve either user's session. The one thing that
doesn't survive that split as-is is `storage/uploads` (Q&A images): those
land on local disk, so a second app server won't see what the first one
saved. Fine at one-server scale; if you ever actually need a second app
server, that's the one piece to move to shared/object storage first —
`app/Services/ImageService.php` is the only file that touches the upload
path, so it's a contained change.

## Backups

```
0 2 * * * mysqldump --single-transaction -u gb_migrate -p'...' gardenbondhu \
  | gzip > storage/backups/gb-$(date +\%F).sql.gz
```

Test a restore into a scratch database at least once before you actually
need it. An untested backup isn't a backup, it's a hope.

## Checklist before it's actually live

- `.env` is chmod 600, outside the web root, obviously not in git
- `curl -I https://yourdomain/.env` → 403 or 404
- `curl -I https://yourdomain/storage/logs/app.log` → 403 or 404
- Admin password isn't the seeded default
- Fresh encryption keys, not the dev ones
- CSP header has no `unsafe-inline`
- Session gets killed when a subscription lapses — check this by hand once,
  in a second browser, don't just trust the code
