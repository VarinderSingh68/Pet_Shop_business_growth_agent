# Delivery app — backend notes

What was added to the PHP project so the delivery-partner Android app (in
`petshop-delivery-app.zip`) has something real to talk to. See
[API.md](API.md) for the endpoint reference (delivery endpoints are appended
there) and [SCHEMA.md](SCHEMA.md) for the two new tables.

## What's new

- **`delivery` role** (`database/seeds/001_roles_and_permissions.php`) — a rider is a normal
  `users` row with this role. No separate account system.
- **`delivery_assignments` and `delivery_locations` tables** (migration `062`) — which rider has
  which order, what state it's in, and their location pings.
- **`DeliveryTokenMiddleware`** (registered as `delivery_token` in `Router`) — same bearer-token
  mechanism as the existing `api_token` middleware, restricted to the `delivery` role.
- **`App\Controllers\Api\DeliveryController`** at `/api/v1/delivery/*` — login, assigned orders,
  order detail, status updates, location pings, and a public signed tracking endpoint.
- **`App\Services\DeliveryService`** — the actual logic (assignment lookup, status transitions
  that also update `orders`/`shipments`/`order_status_history` so the admin panel needs no
  changes to show what a rider is doing).
- **Admin panel**: the order detail page (`/admin/orders/{id}`) now has a "Delivery partner" card
  to assign/reassign a rider, right below the existing Shipment card.
- A demo rider account is seeded alongside the owner/manager/developer accounts — run
  `php database/migrate.php fresh --seed` (or just re-run `php database/seed.php` on an existing
  database; every seed file here is idempotent) and copy the printed credentials.

## Running it end to end

1. Apply the new migration and reseed (or just add the role/demo account to an existing DB):
   ```bash
   php database/migrate.php up
   php database/seed.php
   ```
2. Start the server as usual: `php -S localhost:8000 -t public public/router.php`.
3. In **Admin → Orders**, open any order and assign it to the demo rider.
4. In the Android app, sign in with the rider's email/password (base URL: your machine's LAN IP
   or `10.0.2.2` if the app is running in the Android emulator, e.g.
   `http://10.0.2.2:8000/api/v1/delivery/`).
5. The assigned order shows up in the app; advancing its status (Picked up → Out for delivery →
   Delivered) updates the order and shipment in the admin panel immediately.

## Adding more riders

There's no admin UI for creating users yet beyond what already existed — create one the same way
you would any staff account (or insert a `users` row with `role_id` set to the `delivery` role's
id), then assign orders to them from the order detail page.

## Not built (natural next steps)

- A live map of active riders in the admin panel (the `delivery_locations` table is ready for it —
  it's just not surfaced anywhere yet).
- Wiring `DeliveryController::signedTrackingUrl()` into the storefront's order-confirmation/
  tracking page so a customer sees a live "your rider is here" map — the signed public endpoint
  (`GET /api/v1/delivery/track/{orderNumber}`) already exists for this.
- Push notifications (Firebase Cloud Messaging) for new assignments — the app currently polls the
  orders list instead, which needed no new backend dependency.
- Proof-of-delivery photo/signature capture and upload.
