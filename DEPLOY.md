# Deploying to Render

This app now ships with everything Render needs: a `Dockerfile` (Render has no
native PHP runtime, so it builds and runs this app as a container), a
`render.yaml` Blueprint (defines the web service), and TLS support in the
database layer for a managed MySQL provider.

Render's native Cron Job service type requires a paid plan (no free tier), so
the Growth Agent runs via a secret-protected HTTP endpoint
(`GET /api/v1/cron/run?secret=...`) instead of `php cron.php` on a schedule —
step 8 below wires up a free external scheduler to call it every 15 minutes.

Render doesn't offer managed MySQL, so this guide uses **Aiven's free MySQL
tier** (genuinely free, no card required, no time limit) for the database.

## 1. Create the database (Aiven)

1. Sign up at [aiven.io](https://aiven.io) (no credit card needed for the free plan).
2. Create a new service → **MySQL** → pick the **Free** plan → choose any region → create.
3. Once it's provisioning, open the service's **Overview** tab and note:
   - `Host`
   - `Port`
   - `User` (usually `avnadmin`)
   - `Password`
   - `Default database name` (usually `defaultdb`)
4. On the same Overview page, download the **CA Certificate** (`ca.pem`) — Aiven requires TLS, and you'll need this file in two places (Render, and your own machine for running migrations).

## 2. Generate an APP_KEY

Run this locally and save the output — you'll paste it into Render in step 4:

```bash
php -r "echo base64_encode(random_bytes(32));"
```

## 3. Push the deployment files to GitHub

These files were just added to your project: `Dockerfile`, `docker/entrypoint.sh`, `.dockerignore`, `.gitattributes`, `render.yaml`, plus small code changes (`app/Core/Database.php`, `config/config.php`, `.env.example`, `app/Controllers/Api/CronController.php`, `routes/api.php`) to support TLS to Aiven and the HTTP cron trigger. Commit and push them — Render reads `render.yaml` directly from your repo.

```bash
git add Dockerfile docker/ .dockerignore .gitattributes render.yaml app/Core/Database.php config/config.php .env.example app/Controllers/Api/CronController.php routes/api.php DEPLOY.md
git commit -m "Add Docker/Render deployment setup"
git push
```

## 4. Deploy the Blueprint on Render

1. Sign up at [render.com](https://render.com) and connect your GitHub account.
2. **New +** → **Blueprint** → select your `Pet_Shop_business_growth_agent` repo. Render will detect `render.yaml` and show the `happy-tails-pet-shop` web service.
3. It'll prompt you for every variable marked `sync: false`. Fill in:
   - `APP_URL` — leave as `https://happy-tails-pet-shop.onrender.com` for now (or whatever Render shows as the planned URL); you'll confirm/fix this in step 6 once the real URL is assigned.
   - `APP_KEY` — the value from step 2.
   - `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` — from Aiven (step 1). `DB_PORT` is already set to `3306` as a default — **check Aiven's actual port** (managed MySQL providers often use a non-default port) and correct it if needed.
   - `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` — leave blank for now if you're not setting up Google sign-in immediately.
   - `RAZORPAY_KEY_ID` / `RAZORPAY_KEY_SECRET` / `RAZORPAY_WEBHOOK_SECRET` — leave blank; the storefront cleanly falls back to COD-only checkout without them.
   - `MAIL_HOST` / `MAIL_USERNAME` / `MAIL_PASSWORD` / `MAIL_FROM_ADDRESS` — leave blank if you haven't set up SMTP yet; `MAIL_MODE=log` means emails just get written to the in-app Mail Log instead of failing.
4. Click **Apply**. Render builds the Docker image and starts the service — the first build takes a few minutes (compiling PHP extensions).

## 5. Add the Aiven CA certificate as a Secret File

The database connection needs Aiven's `ca.pem` at runtime.

1. On the `happy-tails-pet-shop` service → **Environment** tab → **Secret Files** → **Add Secret File**.
2. Filename: `ca.pem`. Contents: paste the full contents of the `ca.pem` you downloaded from Aiven in step 1.
3. `DB_SSL_CA` is already set to `/etc/secrets/ca.pem` in `render.yaml` — that's exactly where Render places this file, so no further change needed.

## 6. Fix APP_URL

Once the web service finishes its first deploy, Render shows its real URL at the top of the service page (e.g. `https://happy-tails-pet-shop.onrender.com`). Go to **Environment** → set `APP_URL` to that exact URL (no trailing slash) → save (this triggers a redeploy).

## 7. Run migrations and seed data

Render's free tier doesn't support the "pre-deploy command" feature, so run migrations from your own machine, pointed at the Aiven database directly:

```bash
# In your local project directory, temporarily export the Aiven credentials
# (use the same values you put into Render in step 4):
export DB_HOST=your-aiven-host
export DB_PORT=your-aiven-port
export DB_DATABASE=defaultdb
export DB_USERNAME=avnadmin
export DB_PASSWORD=your-aiven-password
export DB_SSL_CA=/path/to/your/downloaded/ca.pem

php database/migrate.php fresh --seed
```

This creates every table and seeds demo products, customers, and services —
and **prints the owner/admin login credentials to your terminal**. Copy them
now; they're shown once. (On Windows PowerShell, use `$env:DB_HOST = "..."`
instead of `export`.)

## 8. Verify

Visit your Render URL. You should see the storefront homepage. Sign in to
`/admin` with the credentials step 7 printed.

## 9. Schedule the Growth Agent (free)

The web service has a `CRON_SECRET` value Render auto-generated during setup
(`render.yaml` has `generateValue: true` for it). Find it under the service's
**Environment** tab, then point a free external scheduler at:

```
https://<your-render-url>/api/v1/cron/run?secret=<CRON_SECRET>
```

The simplest option is [cron-job.org](https://cron-job.org) (free, no card):
create an account, add a new cron job with that exact URL, and set it to run
every 15 minutes. Every job the Growth Agent runs is idempotent, so a missed
or doubled-up run is harmless. A GitHub Actions scheduled workflow calling
the same URL with `curl` works equally well if you'd rather keep it in your
repo.

You can also trigger a run manually any time from **Admin → Developer tools →
Cron monitor** while signed in, or by visiting that URL directly yourself.

## Known limitations on the free tier

- **Cold starts**: the web service spins down after 15 minutes idle and takes
  ~30-60s to wake up on the next visit. Your external cron pings every 15
  minutes will incidentally keep it warm most of the time.
- **No persistent disk**: anything written to `storage/` (product images
  uploaded through the admin panel, log files, DB backups made via the admin
  backup tool) is wiped on every redeploy or restart. The demo product images
  already committed to the repo are baked into the image and are fine — this
  only affects *new* uploads made after deployment. If this becomes a real
  problem, the fix is either a paid Render instance with a persistent disk
  mounted at `storage/uploads`, or switching image storage to S3-compatible
  object storage — neither is set up yet.

## Updating Google OAuth / Razorpay later

If you add `GOOGLE_CLIENT_ID`/`GOOGLE_CLIENT_SECRET` later, update the
redirect URI in Google Cloud Console to
`https://<your-render-url>/account/login/google/callback`. Razorpay keys can
be added the same way — just set the env vars on the web service and
redeploy, no code changes needed.
