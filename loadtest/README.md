# Load testing with k6

Simulated traffic against Nyuwi Creation's public storefront using [k6](https://k6.io).

## Prerequisites

- `k6` installed locally (`k6 version` — tested with v2.3.0)
- App running: `docker compose up -d` (serves at `http://localhost:8080`)
- Seeded products in the database (product detail pages are driven by real slugs)

## Commands

```bash
# Full load test: ramp 20 -> 200 VUs over ~8 minutes
k6 run loadtest/script.js

# Smoke test first: 30s, 5 VUs, relaxed thresholds
SMOKE=1 k6 run loadtest/script.js

# Against another environment
BASE_URL=https://staging.example.com k6 run loadtest/script.js
```

k6 exits non-zero if a threshold fails, so it is CI-friendly.

## What the script does

- `setup()` runs once and discovers real product slugs from
  `GET /api/products/search` (tries several search terms). If none are
  found, product-detail requests are skipped and only static pages run.
- Each virtual user runs an endless loop of **2 weighted-random GETs**
  with ~1s think time between them:

    | Target         | Path                                                                      | Weight |
    | -------------- | ------------------------------------------------------------------------- | ------ |
    | Home           | `/`                                                                       | 30     |
    | Category pages | `/flowers`, `/boquets`, `/new-featured`, `/accessories`, `/bags`, `/sale` | 56     |
    | Product detail | `/product/{slug}`                                                         | 14     |
    | Search API     | `/api/products/search?q=...`                                              | 5      |
    | Cart           | `/cart`                                                                   | 5      |

- Every request is tagged with `endpoint: <name>`, so k6 reports
  per-endpoint latency. See the `http_req_duration{endpoint:home}` and
  `http_req_duration{endpoint:product}` thresholds.

## Thresholds (full run)

- `http_req_duration` p95 < 800ms (aborts the run after a 30s grace if violated)
- `http_req_failed` rate < 1% (same abort behavior)
- Per-endpoint p95: home < 800ms, product detail < 1000ms

Tune these in `loadtest/script.js` once you have a baseline.

## Not covered (yet)

Write/authenticated flows are intentionally excluded because they need
CSRF token extraction and a login flow in k6:

- `POST /cart`, checkout, order creation
- Customer login → `/customer/*`
- Admin login → `/dashboard`, `/inventory`

Also excluded: `/checkout` (redirects when the cart is empty, see the
`check.cart` middleware).
