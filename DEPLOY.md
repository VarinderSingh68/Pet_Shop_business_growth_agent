# Deploying to Render

This app ships with everything Render needs: a `Dockerfile` (Render has no
native PHP runtime, so it builds and runs this app as a container) and a
`render.yaml` Blueprint that defines the web service.

The database is a single **SQLite file** inside the container (`storage/database.sqlite`)
— no external database service, no signups, no connection credentials of any
kind. The container automatically runs migrations and seeds fresh demo data
on every boot (see "How the database works on Render" below for what that
means in practice).

Render's native Cron Job service type requires a paid plan (no free tier), so
the Growth Agent runs via a secret-protected HTTP endpoint
(`GET /api/v1/cron/run?secret=...`) instead of `php cron.php` on a schedule —
step 5 below wires up a free external scheduler to call it every 15 minutes.

## 1. Generate an APP_KEY

Run this locally and save the output — you'll paste it into Render in step 3:

```bash
php -r "echo base64_encode(random_bytes(32));"
```

## 2. Push the deployment files to GitHub

If you haven't already, commit and push `Dockerfile`, `docker/entrypoint.sh`,
`.dockerignore`, `.gitattributes`, `render.yaml`, and `DEPLOY.md`. Render reads
`render.yaml` directly from your repo.

```bash
git add Dockerfile docker/ .dockerignore .gitattributes render.yaml DEPLOY.md
git commit -m "Add Docker/Render deployment setup"
git push
```

## 3. Deploy the Blueprint on Render

1. Sign up at [render.com](https://render.com) and connect your GitHub account.
2. **New +** → **Blueprint** → select your repo. Render detects `render.yaml`
   and shows the `happy-tails-pet-shop` web service.
3. It'll prompt you for every variable marked `sync: false`. Fill in:
   - `APP_URL` — leave as the suggested `*.onrender.com` URL for now; you'll
     confirm/fix this in step 4 once the real URL is assigned.
   - `APP_KEY` — the value from step 1.
   - `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` — leave blank if you're not
     setting up Google sign-in immediately.
   - `RAZORPAY_KEY_ID` / `RAZORPAY_KEY_SECRET` / `RAZORPAY_WEBHOOK_SECRET` —
     leave blank; the storefront cleanly falls back to COD-only checkout.
   - `MAIL_HOST` / `MAIL_USERNAME` / `MAIL_PASSWORD` / `MAIL_FROM_ADDRESS` —
     leave blank if you haven't set up SMTP; `MAIL_MODE=log` writes emails to
     the in-app Mail Log instead of failing.
4. Click **Apply**. Render builds the Docker image and starts the service —
   the first build takes a few minutes (compiling PHP extensions). The
   container's entrypoint automatically runs migrations and seeds demo data
   on this first boot, so there's no separate database-setup step.

## 4. Fix APP_URL

Once the web service finishes its first deploy, Render shows its real URL at
the top of the service page (e.g. `https://happy-tails-pet-shop.onrender.com`).
Go to **Environment** → set `APP_URL` to that exact URL (no trailing slash) →
save (this triggers a redeploy).

## 5. Verify and get your login credentials

Visit your Render URL — you should see the storefront homepage. To sign in to
`/admin`, you need the owner credentials the seeder generated during boot —
open the service's **Logs** tab in the Render dashboard and search for
`Created owner:` — that log line has the email and password (regenerated
fresh on every boot, so re-check the logs if you ever redeploy and forget it).

## 6. Schedule the Growth Agent (free)

Copy the auto-generated `CRON_SECRET` from the service's **Environment** tab,
then point a free external scheduler at:

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

## How the database works on Render

Render's free tier has no persistent disk, so anything written to the
container's filesystem — including `storage/database.sqlite` — is wiped on
every redeploy or restart. Rather than fight that, the entrypoint script
leans into it: **every container boot re-runs `php database/migrate.php fresh
--seed`**, so the live site always comes back with a clean, fully-seeded demo
database automatically. No manual migration step, ever.

The tradeoff: anything a real visitor does on the live site — placing an
order, an admin editing a product, a new signup — is **not durable**. It
survives until the next redeploy or the free-tier instance restarts (which
also happens after 15 minutes of inactivity), then resets to the seeded demo
state. For a portfolio/demo deployment this is usually a feature, not a bug —
the site is always in a clean, working state for whoever looks at it next.

If you need real data to persist, the fix is a paid Render instance with a
persistent disk mounted at `storage/`, and setting `DB_AUTO_SEED=false` in
the environment so the entrypoint stops re-seeding over your real data on
every boot.

## Known limitations on the free tier

- **Cold starts**: the web service spins down after 15 minutes idle and takes
  ~30-60s to wake up on the next visit. Your external cron pings every 15
  minutes will incidentally keep it warm most of the time.
- **No persistent disk**: see above — this also affects product images
  uploaded through the admin panel after deployment; they live only in the
  running container and are gone on the next redeploy or restart. The seeded
  demo catalogue doesn't ship with product photos at all (`storage/uploads/`
  is intentionally not committed — see `.gitignore`), so products show their
  placeholder state until images are uploaded through the admin panel.

## Pre-deploy checklist

- [ ] `DEVELOPER_TOOLS` is `false` for any environment reachable by the public
  (already set this way in `render.yaml`; double-check if you deploy some
  other way).
- [ ] The owner login shown in the boot logs (step 5) has been noted and,
  for a deployment meant to stay up and hold real data, changed to something
  only you know — the seeder generates a random password per boot, but
  anyone with log access at that moment can read it.
- [ ] `APP_DEBUG` is `false` in production — a stack trace on a live 500 page
  is a gift to an attacker, not a debugging aid.
- [ ] If this deployment is meant to persist real data (not the free-tier
  reset-on-every-boot demo mode above), `DB_AUTO_SEED` is set to `false` and
  `storage/` is on a persistent disk.

## Updating Google OAuth / Razorpay later

If you add `GOOGLE_CLIENT_ID`/`GOOGLE_CLIENT_SECRET` later, update the
redirect URI in Google Cloud Console to
`https://<your-render-url>/account/login/google/callback`. Razorpay keys can
be added the same way — just set the env vars on the web service and
redeploy, no code changes needed.
