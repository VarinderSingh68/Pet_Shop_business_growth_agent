# API Reference

Base URL: `/api/v1`. All responses are JSON. An interactive version of this — with a live "try it" console — is available in the admin panel at **Developer tools → API explorer** (developer role required).

## Authentication

Two of the endpoints below require a Bearer token; the rest are either fully public or use the site's own session/CSRF (not a token).

```
Authorization: Bearer pst_<64 hex characters>
```

Create and revoke tokens at **Developer tools → API tokens**. The plaintext token is shown exactly once, at creation. Each token has its own rate limit (default 60 requests/minute); responses include `X-RateLimit-Limit` and `X-RateLimit-Remaining` headers.

| Status | Meaning |
|---|---|
| `401` | Missing, invalid, or revoked token |
| `429` | Rate limit exceeded for this token |

## Endpoints

### `GET /api/v1/health`

Public. No auth. Basic liveness check.

```json
{ "status": "ok", "time": "2026-08-10 09:30:00" }
```

### `GET /api/v1/search/autocomplete?q=<query>`

Public. Rate-limited (30/min per IP, not per token). Typo-tolerant product search used by the storefront's search box.

```json
{
  "query": "chikken",
  "results": [
    { "name": "Chicken & Brown Rice Adult Dog Food", "url": "/shop/chicken-brown-rice-adult-dog-food", "price": "₹499.00" }
  ]
}
```

### `GET /api/v1/products` 🔒 requires Bearer token

Read-only paginated product export — built for a POS or marketplace sync integration.

Query params: `page` (default 1, 25 per page).

```json
{
  "data": [
    { "id": 1, "name": "Chicken & Brown Rice Adult Dog Food", "slug": "...", "pet_type": "dog", "status": "active", "min_price_paise": 49900, "total_stock": 94 }
  ],
  "page": 1,
  "per_page": 25
}
```

### `GET /api/v1/orders` 🔒 requires Bearer token

Read-only paginated order export — built for an external fulfillment or accounting integration. Does not include line items or customer PII beyond order-level totals.

Query params: `page` (default 1, 25 per page).

```json
{
  "data": [
    { "id": 1, "order_number": "HT-2026-000123", "status": "delivered", "payment_status": "paid", "total_paise": 60295, "currency": "INR", "placed_at": "2026-07-15 10:22:00" }
  ],
  "page": 1,
  "per_page": 25
}
```

### `POST /api/v1/payments/verify`

Used by the storefront's checkout page after the Razorpay Checkout.js widget completes. CSRF-protected (session-based, not a Bearer token) — not intended for third-party use.

Body: `order_number`, `razorpay_order_id`, `razorpay_payment_id`, `razorpay_signature`.

### `POST /api/v1/payments/webhook/razorpay`

Server-to-server webhook from Razorpay. Not CSRF-protected (Razorpay can't send our session token) — instead verified via the `X-Razorpay-Signature` HMAC header against `RAZORPAY_WEBHOOK_SECRET`. Every delivery (valid or not) is logged at **Developer tools → Webhooks**, with a replay button for reprocessing.

## Rate limiting (unauthenticated routes)

Public routes that accept input are throttled per-IP regardless of token:

| Route | Limit |
|---|---|
| `/account/login`, `/admin/login` | 10/min |
| `/account/register` | 10/min |
| `/checkout` | 10/min |
| `/search/autocomplete` | 30/min |
| `/reviews`, `/contact`, `/newsletter/subscribe` | 5–10/min |

## Errors

Errors are always `{ "message": "..." }` with an appropriate HTTP status — `401`/`403` for auth, `404` for missing resources, `422` for validation failures, `429` for rate limits, `500` only for genuine server errors (logged internally, never leaks internals to the response in production).
