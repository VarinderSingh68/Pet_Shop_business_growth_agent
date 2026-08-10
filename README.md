# Happy Tails Pet Store

A pet shop storefront, admin panel, and Growth Agent marketing engine — hand-rolled PHP MVC (no framework), MySQL, server-rendered views with Tailwind CDN and Alpine.js. Built to deploy on plain shared hosting.

See [DESIGN.md](DESIGN.md) for the visual design plan, [SCHEMA.md](SCHEMA.md) for the database schema and ERD, and [API.md](API.md) for the REST API reference.

## Requirements

- PHP 8.2+ with `pdo_mysql`, `mbstring`, `gd`, `fileinfo`, `curl`, `openssl` extensions
- MySQL 8 or MariaDB 10.6+
- Composer

## Setup

```bash
composer install
cp .env.example .env
```

Edit `.env` with your database credentials and a generated `APP_KEY`:

```bash
php -r "echo base64_encode(random_bytes(32));"   # paste into APP_KEY
```

Create the database, then run migrations and seed demo data:

```bash
php database/migrate.php fresh --seed
```

This creates every table, seeds ~53 products across 8 categories and 6 brands, 40 demo customers with pets and 12 months of order history, services and staff with bookable slots, blog/FAQ/legal content, and prints the owner/manager/developer login credentials to the console — **copy them, they're shown once.**

Start the app:

```bash
php -S localhost:8000 -t public public/router.php
```

`public/router.php` is only needed for PHP's built-in dev server, so it can resolve pretty URLs (`/shop/some-product`) the same way `.htaccess` does on real Apache hosting. On shared hosting, point the domain at `public/` and `.htaccess` handles routing — no router script needed there.

Visit `http://localhost:8000`, and `http://localhost:8000/admin` for the admin panel.

## The Growth Agent cron worker

The marketing automation engine (RFM scoring, segments, abandoned-cart recovery, replenishment reminders, win-back campaigns, pet lifecycle triggers, loyalty expiry) runs via `cron.php`, intended every 15 minutes:

```
*/15 * * * * php /path/to/petshop/cron.php >> /path/to/petshop/storage/logs/cron.log 2>&1
```

Every job is idempotent — re-running it, or running it late, never double-sends anything. You can also trigger it manually from **Admin → Developer tools → Cron monitor**, or on the command line:

```bash
php cron.php
```

## Payments

Cash on delivery works out of the box. For online payment, set `RAZORPAY_KEY_ID`, `RAZORPAY_KEY_SECRET`, and `RAZORPAY_WEBHOOK_SECRET` in `.env`. Without those, the storefront shows COD only and online payment is cleanly disabled rather than broken.

## Email

Without SMTP configured (`MAIL_MODE=log`, the default), every email the app would send is written to the `mail_logs` table instead — nothing breaks, and you can read every message (with HTML preview) at **Admin → Developer tools → Mail log**. Set `MAIL_MODE=smtp` and the `MAIL_*` variables to send real email.

If using Gmail, `MAIL_HOST=smtp.gmail.com`, `MAIL_PORT=587`, and `MAIL_PASSWORD` must be a 16-character **App Password** (Google Account → Security → 2-Step Verification → App Passwords), not your normal login password — Gmail rejects real passwords for SMTP. Quote the value in `.env` since it contains spaces: `MAIL_PASSWORD="xxxx xxxx xxxx xxxx"`.

## "Continue with Google" sign-in

Off by default — the button only appears once both `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET` are set in `.env`. To set it up:

1. In [Google Cloud Console](https://console.cloud.google.com/apis/credentials), create an **OAuth 2.0 Client ID** (Web application).
2. Add an **Authorized redirect URI**: `{APP_URL}/account/login/google/callback` (e.g. `http://localhost:8000/account/login/google/callback` locally).
3. Copy the Client ID and Client Secret into `.env`.

Signing in with Google auto-links to an existing password-based account if the (Google-verified) email matches one already in the database, rather than creating a duplicate. New Google-only accounts have no password until the customer sets one.

## Default accounts

Printed once at the end of seeding. If you lose them, reset with:

```bash
php database/migrate.php fresh --seed
```

(This drops and rebuilds the entire database — only use it in development.)

## Developer tools

Gated behind the `developer` role and `DEVELOPER_TOOLS=true` in `.env` (the default). Sign in with the developer account and visit `/admin/dev` for migrations, log viewer, query profiler, cron monitor, mail log, webhook delivery log with replay, an API explorer with a "try it" console, API token management, feature flags, database backup/restore, and a health check page.

## Tests

There's no automated test suite — correctness was verified through direct, live testing of every feature against the running application and database throughout development (see commit history / build notes). If you add automated tests, PHPUnit is the natural fit; none is bundled to keep the `composer.json` dependency list minimal for shared hosting.

## Project structure

```
public/          Single entry point (index.php), assets, uploads-facing dir (empty; real uploads live in storage/)
app/
  Core/          Router, Request, Response, DB, View, Auth, Validator, Mailer, Cache
  Controllers/   Site/, Admin/, Api/
  Models/        One per table, thin, query-scoped
  Services/      Fat services — CartService, OrderService, GrowthEngine, Growth/*, etc.
  Middleware/    auth, admin, developer, csrf, throttle, maintenance, api_token
  Views/         layouts/, site/, admin/, components/
database/
  migrations/    Numbered, each with up()/down()
  seeds/         Numbered, idempotent (safe to re-run)
routes/          web.php, admin.php, api.php
storage/         logs/, cache/, backups/, uploads/ (outside the web root — served via a controller)
cron.php         Growth Agent worker entry point
```

## Security notes

- Prepared statements everywhere (PDO, no string-concatenated SQL)
- CSRF tokens on every state-changing request; rate limiting on login, checkout, and the API
- Passwords hashed with Argon2id; session regenerated on login and privilege change
- Uploaded images are re-encoded through GD (not just extension-checked) and served from outside the web root through a controller
- Payment webhooks are HMAC-signature-verified and idempotent; stock is decremented inside a row-locked transaction
- Invoice links for guest orders are HMAC-signed and expire

## Assumptions made during the build

Business config wasn't fully specified up front, so these defaults were used throughout (see the design plan and phase-by-phase build notes for the reasoning): India/INR/Asia-Kolkata, English only, Razorpay + COD, PHPMailer with a DB-backed catch-all log (no live SMS vendor), supplies-and-services only (no live-animal sales), and shared-hosting-compatible architecture as the deployment target.
