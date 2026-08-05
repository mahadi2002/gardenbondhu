# Starting the app

You need two things running at once: MySQL and the PHP dev server. Two
separate terminal tabs — MySQL runs in the background, the PHP server takes
over its tab (that's it actively serving requests, not a bug).

## Windows / PowerShell (XAMPP)

**Tab 1 — start MySQL:**

```powershell
Start-Process "C:\xampp\mysql\bin\mysqld.exe" -ArgumentList "--defaults-file=C:\xampp\mysql\bin\my.ini","--standalone"
```

**Tab 2 — start the app:**

```powershell
cd W:\Websites\gardenbondhu\public
C:\xampp\php\php.exe -S 127.0.0.1:8000 router-dev.php
```

Leave tab 2 open. Closing it (or `Ctrl+C`) stops the site.

Open **http://127.0.0.1:8000** in a browser.

## Mac / Linux

```bash
mysqld_safe &          # or: brew services start mysql / sudo service mysql start
cd public
php -S 127.0.0.1:8000 router-dev.php
```

## First time only

If you haven't set this up before, `.env` needs to exist with real values
and the database needs to be migrated and seeded:

```bash
cp .env.example .env
php -r "echo base64_encode(random_bytes(32)), PHP_EOL;"   # run twice, paste
                                                            # into APP_KEY and
                                                            # HASH_PEPPER in .env
php database/migrate.php --fresh --seed
```

Full details in [`README.md`](README.md).

## Logging in

- Admin panel — `/admin/login` — `admin@gardenbondhu.test` / `ChangeMe123!`
- Regular app — `/login` — phone `01812345678`, code `123456`

## If the site won't load

**"Connection refused"** — one or both of the two things above isn't
actually running. Check with:

```powershell
tasklist | findstr "mysqld php"
```

If either's missing, go back to the two commands at the top.

**"Too many requests" on the subscribe page** — you hit the OTP rate
limiter from testing repeatedly. Not broken, just needs a reset:

```powershell
C:\xampp\mysql\bin\mysql.exe -u root gardenbondhu -e "TRUNCATE TABLE rate_limits;"
```

## Stopping everything

```powershell
C:\xampp\mysql\bin\mysqladmin.exe -u root shutdown
```

Then `Ctrl+C` in the PHP server's tab.
