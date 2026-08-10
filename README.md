# বাগানবন্ধু (GardenBondhu)

Bangla gardening app for people in Bangladesh who are new to growing things.
Plant care guides, a leaf-symptom checker for figuring out what's wrong with
a sick plant, a personal garden log with watering/fertilizing reminders, and
a Q&A section. Free stuff is limited; the rest needs a subscription — ৳2.78 a
day billed straight through your mobile carrier, works with Robi and Airtel,
cancel whenever.

PHP backend, no frameworks, no Composer packages. Runs on cheap shared
hosting just as well as a VPS, which was the whole point — no build step to
babysit, no `npm install` breaking six months from now.

## Stack

Plain PHP 8.2+ (PDO, cURL, OpenSSL, GD, mbstring — nothing else), MySQL/
MariaDB, one CSS file, vanilla JS. Front controller + hand-rolled routing,
sessions stored in the DB instead of files so a subscription can be revoked
mid-session instead of at next login.

## Running it locally

You need PHP 8.2+ and MySQL or MariaDB.

```bash
cp .env.example .env
php -r "echo base64_encode(random_bytes(32)), PHP_EOL;"   # run this twice
```

Drop the two generated values into `APP_KEY` and `HASH_PEPPER` in `.env`,
point `DB_*` at your database, then:

```bash
php database/migrate.php --fresh --seed
php tests/smoke.php
php -S 127.0.0.1:8000 public/router-dev.php
```

Open `localhost:8000`. `router-dev.php` only exists because PHP's built-in
server doesn't know how to serve static files alongside a router script by
default — Apache and nginx handle that natively in production and never
touch this file.

**Windows/PowerShell:** the two long-running commands need separate terminal
tabs — MySQL in the background, the PHP server blocking its own tab (that's
just it actively running):

```powershell
Start-Process "C:\xampp\mysql\bin\mysqld.exe" -ArgumentList "--defaults-file=C:\xampp\mysql\bin\my.ini","--standalone"
cd public
C:\xampp\php\php.exe -S 127.0.0.1:8000 router-dev.php
```

### Stopping it

`Ctrl+C` in the PHP server's tab — that's the whole app, stopped. MySQL
keeps running in the background afterward on purpose (it's shared
infrastructure, not something to restart every time); shut it down too if
you actually want it down:

```powershell
C:\xampp\mysql\bin\mysqladmin.exe -u root shutdown
```

On Mac/Linux, `Ctrl+C` the PHP server and `mysqladmin -u root shutdown` (or
`brew services stop mysql` / `sudo service mysql stop`, matching however
you started it).

### Logging in without setting up real billing

Two accounts get created by the seed:

- Admin: `admin@gardenbondhu.test` / `ChangeMe123!` at `/admin/login`
- A subscriber already set up with an active mock subscription: phone
  `01812345678`, OTP `123456`, at `/login` — skips straight to `/app`.

The billing gateway defaults to a mock implementation, so you don't need
real carrier billing credentials to test any of this. OTPs actually get generated and
written to `storage/logs/otp-*.log`, or you can just always type `123456`.
Want to see a failed subscription? Use a number ending in `00` (simulates
low balance) or `99` (hard failure). Note: retrying the same number the
same day won't re-attempt the charge — that's on purpose, not a bug, use a
different number if you want to test a clean success.

## Docs

- [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) — how a request flows through the app, why it's structured this way
- [`docs/ROUTES.md`](docs/ROUTES.md) — every route, what it does, what guards it
- [`docs/DATABASE.md`](docs/DATABASE.md) — the schema, table by table
- [`docs/FEATURES.md`](docs/FEATURES.md) — how the subscription state machine, diagnosis scoring, and care scheduler actually work
- [`docs/SECURITY.md`](docs/SECURITY.md) — what's protecting what, and why
- [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md) — getting this onto shared hosting or a VPS
- [`docs/DEVELOPMENT.md`](docs/DEVELOPMENT.md) — conventions, testing, how to not break things
- [`STARTING.md`](STARTING.md) — quick reference for firing up the local server (and un-breaking it)
- [`TODO.md`](TODO.md) — what's left
- [`PHOTO_CREDITS.md`](PHOTO_CREDITS.md) — where the plant photos came from

## A note on the plant photos

They're real, pulled from Wikimedia Commons, each one checked for an actual
open license (public domain, CC0, CC-BY, or CC-BY-SA) rather than just
grabbed off Google Images. Credit for each one is in `PHOTO_CREDITS.md`.
Those licenses are the photographer's, not this project's — if you reuse an
individual photo elsewhere, follow its own license, not this repo's.

## License

Private — all rights reserved (except the individually-licensed photos, see above).
