# Changelog

## [2026-10-10] — PHP-FPM Concurrency & Storefront Performance Optimization for k6 Load Testing

- **Fixed PHP-FPM Pool Configuration & Worker Starvation:**
    - Fixed path misplacement of `pool.conf` by moving it to `docker/php/pool.conf` so `docker-compose.yml` mounts a valid configuration file instead of an auto-generated empty directory.
    - Scaled PHP-FPM worker pool from default 5 workers to 60 static workers (`pm = static`, `pm.max_children = 60`, `pm.max_requests = 1000`) matching available container memory limits (2GB RAM).
- **Nginx Upstream Keepalive, Buffer & Compression Tuning:**
    - Configured persistent FastCGI upstream keepalive (`upstream php-fpm { server app:9000; keepalive 32; }`, `fastcgi_keep_conn on`).
    - Tuned FastCGI response buffers (`fastcgi_buffer_size 128k`, `fastcgi_buffers 16 128k`, `fastcgi_busy_buffers_size 256k`) to prevent disk buffering of Inertia SSR responses.
    - Enabled Gzip compression on Nginx (`gzip on`, `gzip_min_length 1024`, `gzip_types text/plain text/css application/json application/javascript text/xml`) to drastically reduce network payload.
- **Application Caching & Query Optimization:**
    - Cached landing page product payload (`newProducts`, `featuredProducts`, and `categories`) in `ProductController::landingPage()` using `Cache::remember('landing_page_data', 60, ...)` to eliminate correlated subqueries under heavy concurrent traffic.
    - Cached category catalog pages (`/flowers`, `/boquets`, `/accessories`, `/bags`, `/sale`, `/new-featured`), individual product detail views, and search API responses (`apiSearch`).
    - Short-circuited empty guest cart queries in `CartService::guestItems()` to prevent empty database lookups on every page view.
    - Invalidated product cache on administrative mutations (`store`, `update`, `destroy`).
- **Load Test Verification:**
    - Re-ran full 8-minute k6 load test (`k6 run loadtest/script.js`) across all 5 stages up to 200 concurrent VUs.
    - All thresholds passed with 0 premature aborts:
        - `http_req_duration`: p(95) = **304.26ms** (threshold `<800ms`)
        - `http_req_duration{endpoint:home}`: p(95) = **324.14ms** (threshold `<800ms`)
        - `http_req_duration{endpoint:product}`: p(95) = **300.55ms** (threshold `<1000ms`)
        - `http_req_failed`: rate = **0.46%** (threshold `<1.00%`)
    - Handled 27,673 HTTP requests and 13,829 iterations.
- **Automated Tests:**
    - Created `tests/Feature/LandingPageTest.php` testing landing page inertia props and cache behavior. All tests passed.

### Files changed

- `docker/php/pool.conf`
- `docker/nginx/nginx.conf`
- `app/Http/Controllers/ProductController.php`
- `app/Services/CartService.php`
- `tests/Feature/LandingPageTest.php` _(New)_
- `docs/CHANGELOG.md`

## [2026-10-08] — k6 Load Test Script for Public Storefront

- **Rewrote `loadtest/script.js` against real routes:**
    - Replaced the placeholder path with a weighted mix of live public routes from `routes/web.php`: home, 6 category pages (`/flowers`, `/boquets`, `/new-featured`, `/accessories`, `/bags`, `/sale`), product detail, `/api/products/search`, and `/cart`.
    - Added a `setup()` step that discovers real product slugs via `GET /api/products/search`; product-detail requests are skipped when no slugs are found.
    - Tagged every request with `endpoint` and added grouped thresholds (`http_req_duration{endpoint:home}` p95 < 800ms, `endpoint:product` p95 < 1000ms) alongside the original abort-on-fail p95/error-rate thresholds.
    - Added `SMOKE=1` short-run mode (30s, 5 VUs) and `BASE_URL` override; kept the original 20 → 200 VU ramp stages.
    - Verified k6 behavior empirically (k6 v2.3.0): `discardResponseBodies` was rejected because it nulls response bodies in `setup()`, breaking slug discovery.
- **Documentation:** Added `loadtest/README.md` with run commands, traffic weights, thresholds, and scope notes (write/auth flows excluded — they need CSRF and login flows).
- **Verification:** `k6 inspect` passes for both full and smoke modes; `npm run build` completes with zero errors. Live smoke run pending — Docker stack was not reachable from this environment.

### Files changed

- `loadtest/script.js`
- `loadtest/README.md`
- `docs/CHANGELOG.md`

## [2026-10-05] — Review Authorization, Order Status Verification, and Duplicate Prevention

- **Server-Side Authorization & Eligibility in `OrderCompleteRequest`:**
    - Added request authorization verifying the authenticated user strictly owns the order (`user_id === auth()->id()`), rejecting unauthorized completion attempts with 403 Forbidden.
    - Enforced order state validation: orders can only be completed/reviewed if they are in `shipped` or `completed` status and `paid`/`settlement`. Pre-shipped or unpaid orders (`waiting`, `checking`, `processing`) are rejected.
    - Verified that every `product_id` submitted in reviews was genuinely purchased in that specific order, preventing forged reviews of unpurchased store products.
    - Added `distinct` validation on `reviews.*.product_id` to prevent submitting the same product multiple times within a single payload.
- **Idempotent Review Handling in `OrderController::complete`:**
    - Switched from raw `ProductReview::create` to `ProductReview::updateOrCreate`, ensuring idempotent review updates and preventing duplicate review rows upon multiple submissions.
    - Only transitioned order status to `completed` if the order is currently `shipped`.
- **Database Unique Constraint on `product_reviews`:**
    - Created migration `2026_10_05_135500_add_unique_index_to_product_reviews_table.php` adding a composite unique index on `['order_id', 'product_id']`.
- **Security Tests & Documentation:**
    - Created `tests/Feature/OrderReviewSecurityTest.php` with 6 dedicated test cases covering foreign order protection, pre-shipped order rejection, unpurchased product rejection, distinct product validation, idempotent review submission, and successful completion flow.
    - Updated `docs/03_APP_FLOW.md` resolving the Flow 5 Reviews open TODO item.
- **Verification:** Ran `npm run build` with zero errors.

### Files changed

- `database/migrations/2026_10_05_135500_add_unique_index_to_product_reviews_table.php`
- `app/Http/Requests/OrderCompleteRequest.php`
- `app/Http/Controllers/OrderController.php`
- `tests/Feature/OrderReviewSecurityTest.php`
- `docs/03_APP_FLOW.md`
- `docs/CHANGELOG.md`

## [2026-10-05] — Database Row Locking and Stock Reservation Lifecycle

- **Pessimistic Row-Level Locking on Checkout:**
    - Updated `OrderController::store` to extract unique product IDs, sort them in ascending order to prevent deadlocks, and acquire pessimistic row locks via `Product::whereIn('id', $productIds)->orderBy('id')->lockForUpdate()->get()`.
    - Aggregated required quantities across cart items and validated sufficient stock before checkout proceeds.
    - Atomically decremented product stock upon order placement within the existing database transaction, preventing overselling race conditions under concurrent checkouts.
- **Stock Restoration Lifecycle:**
    - Added `restoreStock()` helper to `Order` model to iterate over order items and restore product quantities.
    - Updated `OrderController::update` to restore stock when an order is transitioned to `cancelled`, and removed stock reduction on status `shipped` to eliminate double-decrementing.
    - Updated `MidtransWebhookController::syncOrderStatus` to mark orders as `cancelled` and restore reserved stock when receiving `expire`, `deny`, or `cancel` webhook notifications.
    - Created `orders:expire-unpaid` console command (`app/Console/Commands/ExpireUnpaidOrders.php`) to automatically cancel abandoned unpaid orders past the payment window (default 24h) and restore their reserved stock.
- **Documentation and Tests:**
    - Updated `docs/03_APP_FLOW.md` marking concurrent stock row locking as implemented.
    - Added comprehensive feature tests in `tests/Feature/OrderConcurrencyTest.php` covering stock deduction on checkout, out-of-stock rejection, and unpaid order expiration.
    - Updated `tests/Feature/AdminOrderUpdateTest.php`, `tests/Feature/MidtransIntegrationTest.php`, and `tests/Feature/GuestCheckoutTest.php` to assert stock reservation and restoration.
- **Verification:** Ran `npm run build` with zero errors.

### Files changed

- `app/Models/Order.php`
- `app/Http/Controllers/OrderController.php`
- `app/Http/Controllers/MidtransWebhookController.php`
- `app/Console/Commands/ExpireUnpaidOrders.php`
- `tests/Feature/OrderConcurrencyTest.php`
- `tests/Feature/AdminOrderUpdateTest.php`
- `tests/Feature/MidtransIntegrationTest.php`
- `tests/Feature/GuestCheckoutTest.php`
- `docs/03_APP_FLOW.md`
- `docs/CHANGELOG.md`

## [2026-10-05] — Mobile Search Dropdown Responsive Positioning

- **Repositioned Search Dropdown on Mobile Displays:**
    - Updated search floating dropdown in `resources/js/Components/Customer/Main/Navbar.vue` to use responsive positioning (`fixed left-4 right-4 top-[72px] sm:left-4 sm:right-auto sm:w-96 lg:absolute lg:inset-auto lg:right-0 lg:top-full lg:mt-3 lg:w-96`).
    - Positioned dropdown directly beneath the mobile search trigger on mobile viewports while preserving desktop alignment and maintaining click-outside behavior.
- **Verification:** Ran `npm run build` with zero errors.

### Files changed

- `resources/js/Components/Customer/Main/Navbar.vue`
- `docs/CHANGELOG.md`

## [2026-10-04] — Support Reusable Midtrans Snap Token for QRIS and Pending Orders

- **Added `gateway_order_id`, `snap_token`, and `snap_redirect_url` to `payment_transactions`:** Allowed storing the created Midtrans Snap token and custom gateway order identifier directly on the transaction.
- **Snap Token Reuse in `PaymentService`:** Updated `createSnapToken` to check if a pending transaction already possesses a valid, unexpired `snap_token` (within 24 hours), returning it directly instead of creating duplicate Midtrans transactions. This enables customers to re-open the Midtrans Snap modal with the exact same QRIS code or Virtual Account number intact.
- **Webhook Resilience in `MidtransWebhookController`:** Matched incoming notifications by `gateway_order_id` first with fallback segment parsing, ensuring 100% reliable transaction lookup and status synchronization (`settlement`, `expire`, `pending`).
- **Support `pending` payment status in Customer Order History:** Updated `OrderHistoryTab.vue` and `Dashboard.vue` so orders with `payment_status: 'pending'` keep the "Bayar Sekarang" action button visible, are grouped under the "Menunggu Pembayaran" badge and filter tab, and can re-trigger payment seamlessly.
- **Verification:** Ran `MidtransIntegrationTest` (7 passed), `PaymentTransactionTest` (3 passed), and `npm run build` with zero errors.

### Files changed

- `database/migrations/2026_09_24_093501_create_payment_transactions_table.php`
- `app/Models/PaymentTransaction.php`
- `app/Services/PaymentService.php`
- `app/Http/Controllers/MidtransWebhookController.php`
- `resources/js/Components/Customer/Main/OrderHistoryTab.vue`
- `resources/js/Pages/Customer/Dashboard.vue`
- `tests/Feature/MidtransIntegrationTest.php`
- `tests/Feature/PaymentTransactionTest.php`
- `docs/CHANGELOG.md`

## [2026-10-02] — Fix Midtrans payment token creation and eliminate duplicate trigger

- **Fixed missing `net_amount` on `PaymentTransaction`:** Added `'net_amount' => 0` default to `$attributes` in `PaymentTransaction.php` and explicitly provided `'net_amount' => $order->total_price` during transaction creation in `PaymentService.php`, preventing database 1364 strict mode constraint errors.
- **Added fallback for Midtrans `customer_details`:** Provided fallbacks for customer email, phone, and recipient name in `PaymentService.php` to prevent Midtrans API payload rejections on guest checkouts.
- **Enhanced error logging in `PaymentController`:** Caught `\Throwable` instead of only `RuntimeException`, writing failures to `Log::error` with order context and returning informative 500 JSON responses.
- **Eliminated duplicate auto-launch in `Cart.vue`:** Removed duplicate `onMounted` Snap payment trigger and unused imports from `Cart.vue`, making `Checkout.vue` the single owner of the Midtrans Snap modal lifecycle.
- **Verification:** Ran `npm run build` with 0 errors.

### Files changed

- `app/Models/PaymentTransaction.php`
- `app/Services/PaymentService.php`
- `app/Http/Controllers/PaymentController.php`
- `resources/js/Pages/Customer/Cart.vue`
- `docs/CHANGELOG.md`

## [2026-10-01] — Fix origin `district_id` in `ProfileStoreSeeder` for Komerce API

- **Updated `ProfileStoreSeeder` & active store profile:** Changed default `district_id` from legacy 7-digit BPS code (`3517120`) to valid Komerce API district ID `3866` (Peterongan, Jombang).
- **Changed Seeder to `updateOrCreate`:** Ensured re-running `php artisan db:seed` always updates existing profile records with valid Komerce IDs.
- **Fixed shipping calculation empty rates:** When origin was set to legacy BPS code, Komerce API returned `422 Origin or Destination not found`, resulting in empty rates `[]`. Setting valid Komerce district ID `3866` resolves shipping calculation for all couriers.
- **Verification:** Verified `calculate/district/domestic-cost` endpoint with origin `3866` and destination `1343`, successfully returning JNE/POS/TIKI shipping options.

### Files changed

- `database/seeders/ProfileStoreSeeder.php`
- `tests/Feature/ShippingCalculationTest.php`
- `docs/CHANGELOG.md`

- **Full path storage in `ProductController`:**
    - Updated `store()` and `update()` to store uploaded product images as full public paths (`/storage/products/{imageName}`).
    - Updated `update()` and `destroy()` to strip `'/storage/'` when checking and deleting images from the `public` disk.
    - Updated `apiSearch()` to handle full paths directly without extra prefixing.
- **Seeder updates (`ProductSeeder.php`):**
    - Updated `ProductSeeder` to map seeded product images with `/storage/products/` prefix.
- **Frontend resilience (`resources/js`):**
    - Updated image URL resolution across customer and admin components (`Product.vue`, `Customer/Product.vue`, `Edit.vue`, `CartDrawer.vue`, `Cart.vue`, `Checkout.vue`, `Dashboard.vue`, `OrderHistoryTab.vue`, `ReviewsTab.vue`) to safely handle full paths (`img.startsWith('/') ? img : '/storage/products/' + img`).
- **Verification:**
    - Built frontend production assets cleanly with `npm run build`.

### Files changed

- `app/Http/Controllers/ProductController.php`
- `database/seeders/ProductSeeder.php`
- `resources/js/Components/Customer/Sub-main/Product.vue`
- `resources/js/Pages/Customer/Product.vue`
- `resources/js/Pages/Admin/Products/Edit.vue`
- `resources/js/Components/Customer/Main/CartDrawer.vue`
- `resources/js/Pages/Customer/Cart.vue`
- `resources/js/Pages/Customer/Checkout.vue`
- `resources/js/Pages/Admin/Dashboard.vue`
- `resources/js/Components/Customer/Main/OrderHistoryTab.vue`
- `resources/js/Components/Customer/Main/ReviewsTab.vue`
- `docs/CHANGELOG.md`

## [2026-10-01] — Store full logo path in ProfileStore and simplify middleware

- **Full path storage in `ProfileStoreController`:**
    - Updated `update()` to store uploaded logos as full public path (`/storage/logo/...`) in the database.
    - Properly removes old logo files on the public disk by stripping `/storage/`.
- **Simplified `HandleInertiaRequests` middleware:**
    - `storeLogo` directly passes `$profileStore?->logo ?: '/img/Logo/Logo.svg'` without complex string parsing.
- **Updated `ProfileStoreSeeder`:**
    - Seeded logo set to full path: `'/storage/logo/logo.svg'`.
- **Admin & Component updates:**
    - `ProfileStore.vue`: adjusted logo previews to support full paths without double-prefixing.
    - `ProfileStoreTest.php`: added `test_admin_can_upload_and_update_store_logo` asserting `/storage/logo/` path persistence and file cleanup.
- **Verification:**
    - Built frontend production assets cleanly with `npm run build`.

### Files changed

- `app/Http/Controllers/ProfileStoreController.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `database/seeders/ProfileStoreSeeder.php`
- `resources/js/Pages/Admin/ProfileStore.vue`
- `tests/Feature/ProfileStoreTest.php`
- `docs/CHANGELOG.md`

## [2026-09-30] — Fix bugs and edge cases in `Customer/Checkout.vue` and `ShippingService`

- **Fixed HTTP 500 error on `/locations/provinces`:** Updated `map()` in `app/Services/ShippingService.php` to handle Komerce RajaOngkir API array format (`id` / `name` keys) alongside traditional RajaOngkir keys (`province_id`/`province_name`, `city_id`/`city_name`, `subdistrict_id`/`subdistrict_name`).
- **Resolved runtime `page` reference crashes:** Imported `usePage` from `@inertiajs/vue3` and initialized `const page = usePage()`, fixing undefined variable errors on contact email elements.
- **Fixed guest email validation:** Added email presence check on Step 1 (`goToStep('shipping')`) for guest users so missing emails are caught before advancing to shipping and payment.
- **Enhanced submission error handling:** Added error feedback via `ToastNotification` and auto-step navigation in `form.post` `onError` callback; added watcher on `page.props.flash` to display backend exception and flash messages.
- **Prevented duplicate shipping rate requests:** Guarded `calculateShippingRates()` with `isCalculatingShipping` and consolidated the `district_id` watcher.
- **Added empty cart state:** Handled empty cart scenarios with a friendly message and a link back to `/cart` instead of submitting empty orders.
- **Form submission & image safety:** Added `@submit.prevent` on the main form to prevent accidental submission on Enter key; added `getProductImage()` helper with fallback to prevent crashes on null/empty product images.
- **Cleaned up dead code:** Removed unreferenced `isLoading` and `error` states and no-op retry handlers.

### Files changed

- `app/Services/ShippingService.php`
- `resources/js/Pages/Customer/Checkout.vue`
- `docs/CHANGELOG.md`

## [2026-09-30] — Document shipping provider architecture

- **Documented the provider split:** shipping rate calculation & region data use the **RajaOngkir** API; shipment tracking uses the **BinderByte** API (both server-side, keys never exposed to the browser).
- Updated `docs/02_TRD.md` (architecture), `docs/06_IMPLEMENTATION_PLAN.md` (Phase 4 — also fixed the `RAJAONGKAR` typo), and `docs/specs/remove-api-layer.md` (decisions).
- **Note:** `ShippingService::calculate()` is currently BinderByte-backed from the previous consolidation; the documented target is RajaOngkir for calculation. Code unchanged in this entry.

### Files changed

- `docs/02_TRD.md`
- `docs/06_IMPLEMENTATION_PLAN.md`
- `docs/specs/remove-api-layer.md`
- `docs/CHANGELOG.md`

## [2026-09-30] — Remove `app/Services/Shipping/`, consolidate into `ShippingService`

- **Removed the driver-based shipping architecture** (`app/Services/Shipping/`): `ShippingManager`, `Contracts/ShippingCalculatorInterface`, `Drivers/BinderByteShippingDriver`, `Drivers/MockShippingDriver` are deleted.
- **`app/Services/ShippingService.php` is now the single shipping provider** (BinderByte-backed, plus the existing RajaOngkir region lookups):
    - Ported `calculate()` (BinderByte `/cost`, kg rounding, courier filter, rate normalization), `getSupportedCouriers()`, and `getCities(?string $search)` (BinderByte `/list_city` + cache) from the deleted `BinderByteShippingDriver`.
    - `track()` (moved here earlier) and private `normalizeCourierCode()` unchanged.
    - _Interim:_ the documented target architecture is calculate via RajaOngkir / tracking via BinderByte (see the TRD update above); `calculate()` stayed BinderByte-backed in this change.
- **Consumers switched from `ShippingManager` to `ShippingService`:**
    - `ShippingController::calculate()` → `$shippingService->calculate(...)`; `cities()` → `$shippingService->getCities($q)`; `couriers()` → `$shippingService->getSupportedCouriers()`.
    - `OrderController::store()` / `calculateShippingCost()` now inject `ShippingService`; the `ShippingManager` import was removed.
- **`AppServiceProvider`:** removed the `ShippingManager` singleton and `ShippingCalculatorInterface` bindings (no longer needed — `ShippingService` is auto-resolved).
- **Tests:**
    - `ShippingCalculationTest` dropped the manager/mock/rajaongkir-driver tests; the BinderByte cost test now targets `ShippingService`; controller tests use `Http::fake` against the BinderByte endpoints and set `services.binderbyte.api_key`; `ProfileStore::create()` calls include `embedded_map` (required by the current schema).
    - `GuestCheckoutTest` no longer sets `shipping.default = mock` (dead config); it now fakes BinderByte `/cost` with the old mock rates (JNE REG = 18000) and returns empty costs for `sicepat` to preserve the “courier not offered” test.

### Files changed

- `app/Services/ShippingService.php`
- `app/Http/Controllers/ShippingController.php`
- `app/Http/Controllers/OrderController.php`
- `app/Providers/AppServiceProvider.php`
- `tests/Feature/ShippingCalculationTest.php`
- `tests/Feature/GuestCheckoutTest.php`
- `app/Services/Shipping/` (deleted)

## [2026-09-29] — Replace manual `fetch` with axios in `Customer/Checkout.vue`

- **`resources/js/Pages/Customer/Checkout.vue`:**
    - Region loading now uses axios and is driven by `watch` instead of manual `fetch` + template handlers: `onMounted(fetchProvinces)` loads provinces, `watch(form.province_id)` loads cities (and clears city/district), and `watch(form.city_id)` loads districts.
    - `watch(form.district_id)` derives `district_name` and resets the shipping choice; a second watcher on the same source re-calculates rates, so switching destination refetches automatically.
    - `calculateShippingRates()` now POSTs via axios to `shipping.calculate` using the district id (previously a hand-built `fetch` with manual CSRF/XHR headers and a city fallback).
    - Removed the now-unused `regionConfig`, `currentType`, and `fetchRegions` helpers, plus the `@update:model-value="fetchRegions(...)"` handlers on the region selects.
    - `goToStep('shipping')` only fetches when no rates are loaded yet, avoiding a double request now that the watcher fires on district selection.
- **`resources/js/bootstrap.js`:** configure the axios CSRF token from the `csrf-token` meta tag (standard Laravel pattern) so component code doesn't repeat headers.

### Files changed

- `resources/js/Pages/Customer/Checkout.vue`
- `resources/js/bootstrap.js`

## [2026-09-29] — Recalculate shipping cost server-side at order creation

- **Security fix — shipping cost is no longer trusted from the client:**
    - `OrderController::store()` now recomputes the shipping cost via a new private `calculateShippingCost()` helper using `ShippingManager`, using the store origin (`ProfileStore.district_id`, falling back to `services.rajaongkir.origin`), the cart-derived weight, and the destination district (falling back to city). It matches the client-requested `courier` + `shipping_service` against the freshly fetched rates and stores the authoritative cost.
    - If the requested courier/service is not offered for the destination, the method throws and the order is rejected (no partial/invalid order).
    - `OrderStoreRequest` no longer accepts `shipping_cost`, so it cannot be submitted at all. `total_price = subtotal + shipping_cost` is now fully server-derived.
- **Frontend (`Customer/Checkout.vue`):**
    - Removed `shipping_cost` from the submitted form; it is now a display-only `shippingCost` ref mirroring the selected rate. Shipping totals and the "Metode Pengiriman" summary still render for the customer, but nothing authoritative is sent to the server.
- **Tests:**
    - `GuestCheckoutTest` pins `shipping.default = mock` and asserts the server-computed shipping cost (JNE REG = 18000 at the 1kg tier) and total.
    - Added a tampering test proving a client-supplied `shipping_cost: 0` / `total_price: 1` is ignored, and a test asserting an unavailable courier/service aborts order creation.

### Files changed

- `app/Http/Controllers/OrderController.php`
- `app/Http/Requests/OrderStoreRequest.php`
- `resources/js/Pages/Customer/Checkout.vue`
- `tests/Feature/GuestCheckoutTest.php`

## [2026-09-29] — Migrate `Customer/Checkout.vue` to the new order payload

- **Form payload now matches `OrderController::store()` / `OrderStoreRequest`:**
    - Replaced `name` → `recipient_name`; `province`/`city`/`district` → `province_id`/`province_name`, `city_id`/`city_name`, `district_id`/`district_name`; dropped `village`, `note`, `payment_method`, `shipping_method`; added `postal_code`, `courier`, `shipping_service`.
    - Region `<FormSelect>`s now bind to the region **id** (`option-value="id"`); the matching `*_name` is derived with `watch` so the order keeps a human-readable snapshot alongside the API id.
    - Shipping option selection stores `courier` + `shipping_service` (selected-state matching moved into an `isShippingSelected()` helper); the shipping-rate call sends the district id directly.
    - Payment radio binds to a local `selectedPayment` ref — payment method is no longer submitted (Midtrans Snap handles it after the order is created).
    - Step guards and the "Kirim ke" / "Metode Pengiriman" summaries updated to the new fields; `postal_code` input added (optional).
- **Pre-existing build blocker (not introduced here):** `npm run build` fails because `Pages/Customer/Cart.vue` imports `Components/Customer/Sub-main/PaymentInformationModal.vue`, which was deleted in the earlier QRIS-removal change. `Checkout.vue` itself compiles cleanly.

### Files changed

- `resources/js/Pages/Customer/Checkout.vue`

## [2026-09-29] — Align order backend with the new `orders` schema

- **`OrderController`:**
    - `store()` now persists the current `orders` columns: `recipient_name`, `province_id`/`province_name`, `city_id`/`city_name`, `district_id`/`district_name`, `postal_code`, `courier`, `shipping_service`, `weight`, `shipping_cost`, `subtotal`, `total_price`. Dropped the removed `name`/`city`/`district`/`village`/`province`/`payment_method`/`shipping_method`/`note` fields. `order_number` is generated by the model.
    - New orders start at `status = waiting`, `payment_status = unpaid`; `subtotal` and `weight` are derived server-side from the cart (`CartService::summarize()`) rather than trusted from the client; `total_price = subtotal + shipping_cost`.
    - `store()` unified logged-in and guest paths on `CartService::items()` (cart rows still locked via `lockForUpdate()` for authenticated checkouts), removing the duplicated ternaries.
    - `update()` uses the corrected status `shipped` (was `shiping`) for tracking + stock deduction, and blocks cancellation once an order is `shipped`.
    - `tracking()`/`adminTracking()` track via `$order->courier` (replacing `shipping_method`); the GoSend no-tracking guard now matches the courier code `gosend`.
    - `paymentToken()` accepts `unpaid` or `pending` (new orders start `unpaid`).
- **Requests:**
    - `OrderStoreRequest` validates the new region ID/name pairs, `postal_code`, `courier`, `shipping_service`, and `shipping_cost`; no longer accepts `payment_method`, `shipping_method`, or `note`.
    - `OrderStatusUpdateRequest` status enum updated to `waiting, checking, pending, processing, shipped, completed, cancelled`.
- **`DashboardController`:** recent-orders mapping reads `recipient_name`, `courier`, `shipping_service`, and `order_number` (was reading removed `name`/`payment_method`).
- **Tests:**
    - Updated `GuestCheckoutTest`, `CustomerDashboardTest`, `MidtransIntegrationTest`, `ShippingCalculationTest`, `PaymentTransactionTest`, and `AdminDashboardTest` order fixtures to the new schema.
    - Added `AdminOrderUpdateTest` covering the shipped/cancel/status-flow behaviour of the admin status endpoint.
    - Added guest-checkout tests for server-side totals and required region/courier fields.
- **Known follow-ups (not in this change):**
    - `MidtransService` still reads removed `$order->name`/`$order->shipping_method` (left as-is per request); will need `recipient_name` and `courier`+`shipping_service`.
    - `MidtransWebhookController` and `PaymentTransactionTest` write `raw_status`/`raw_payload`, but the `payment_transactions` table/model use `status`/`raw_response` — a pre-existing mismatch.
    - Frontend pages (`Customer/Checkout.vue`, `Admin/Orders/*`) still send/read the old field names (`name`, `city`, `shipping_method`, `village`); migrated separately.

### Files changed

- `app/Http/Controllers/OrderController.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Requests/OrderStoreRequest.php`
- `app/Http/Requests/OrderStatusUpdateRequest.php`
- `tests/Feature/AdminOrderUpdateTest.php`
- `tests/Feature/GuestCheckoutTest.php`
- `tests/Feature/CustomerDashboardTest.php`
- `tests/Feature/MidtransIntegrationTest.php`
- `tests/Feature/ShippingCalculationTest.php`
- `tests/Feature/PaymentTransactionTest.php`
- `tests/Feature/AdminDashboardTest.php`

## [2026-09-29] — Remove indoregion, QRIS, and store-city implementation

- **Dependency removal:**
    - Removed `azishapidin/indoregion` (deprecated, unmaintained since 2021) and its transitive `parsecsv/php-parsecsv` from `composer.json`/`composer.lock`.
    - Region data now comes from the RajaOngkir API via `App\Services\ShippingService` (`/locations/*` routes); local `provinces`/`regencies`/`districts`/`villages` tables and models deleted.
- **Store profile alignment (`profile_stores`):**
    - Form (`Admin/ProfileStore.vue`), `StoreProfileUpdateRequest`, and `ProfileStoreController` now persist the model's actual fields: `embedded_map`, `district_id`, `district_name`.
    - Dropped `city`, `qris`, `shipping_origin_city_id`, `shipping_origin_district_id` everywhere (form, request, controller, shared props, views).
    - Origin for shipping is now `district_id` (falling back to `services.rajaongkir.origin`); both shipping drivers hard-fail instead of falling back to deleted local region tables.
- **QRIS removal:**
    - Removed the QRIS payment option from checkout (`Checkout.vue`), the QRIS tab from `PaymentInformationModal.vue`, the QRIS upload zone, and the footer claim.
    - `OrderStoreRequest` now accepts only `digital_wallet`.
    - `MidtransService` uses `$order->city_name` (the order's actual field) instead of the nonexistent `$order->city`.
- **Tests:**
    - Updated `tests/Feature/ProfileStoreTest.php` and `tests/Feature/ShippingCalculationTest.php` to the new schema (no `city`/`qris`/`shipping_origin_*`).
    - Updated seeded `payment_method` values from `qris` to `digital_wallet` in feature tests.

### Files changed

- `composer.json`, `composer.lock`, `bootstrap/cache/*`
- `app/Http/Controllers/ProfileStoreController.php`
- `app/Http/Controllers/ShippingController.php`
- `app/Http/Requests/StoreProfileUpdateRequest.php`
- `app/Http/Requests/OrderStoreRequest.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `app/Services/ShippingService.php`
- `app/Services/Shipping/Drivers/BinderByteShippingDriver.php`
- `app/Services/Shipping/Drivers/RajaOngkirShippingDriver.php`
- `app/Services/MidtransService.php`
- `resources/js/Pages/Admin/ProfileStore.vue`
- `resources/js/Pages/Customer/Checkout.vue`
- `resources/js/Components/Customer/Sub-main/PaymentInformationModal.vue`
- `resources/js/Components/Customer/Main/Footer.vue`
- `routes/web.php`
- `app/Models/ProfileStore.php`
- `tests/Feature/ProfileStoreTest.php`
- `tests/Feature/ShippingCalculationTest.php`
- `tests/Feature/GuestCheckoutTest.php`
- `tests/Feature/CustomerDashboardTest.php`
- `tests/Feature/MidtransIntegrationTest.php`
- `tests/Feature/PaymentTransactionTest.php`
- `docs/AGENTS.md`, `README.md`, `docs/02_TRD.md`, `docs/05_BACKEND_SCHEMA.md`, `docs/06_IMPLEMENTATION_PLAN.md`
- `docs/CHANGELOG.md`

## [2026-09-28] — Region Dropdowns & Mandatory Origin City and Subdistrict IDs

- **Region Dropdown Cascades & Easier Input:**
    - Updated `ProfileStoreController@index` to pre-load `provinces`, current store's province ID, and matching `regencies` and `districts` from IndoRegion models.
    - Enhanced `RegionController` (`provinces`, `regencies`, `districts`, `villages`) to support JSON responses when called via AJAX/fetch.
    - Updated `resources/js/Pages/Admin/ProfileStore.vue` with interactive, cascading dropdowns for Provinsi, Kota/Kabupaten, and Kecamatan, displaying official region names along with their database IDs.
    - Automatically synchronizes `form.city` and `form.shipping_origin_city_id` when a Kota/Kabupaten is selected, and loads districts for selecting `form.shipping_origin_district_id`.
- **Validation & Seeding:**
    - Made `shipping_origin_city_id` and `shipping_origin_district_id` mandatory (`required`) in `StoreProfileUpdateRequest`.
    - Updated `ProfileStoreSeeder` with valid default city and district IDs for Jombang & Peterongan (`3517` and `3517120`).
- **Testing & Verification:**
    - Updated `tests/Feature/ProfileStoreTest.php` with assertions verifying that omitting origin city or district ID produces validation errors (all 9 tests passing).
    - Verified `tests/Feature/ShippingCalculationTest.php` (all 11 tests passing).
    - Built production assets cleanly with `npm run build`.

### Files changed

- `database/migrations/2026_09_28_000001_add_shipping_origin_district_id_to_profile_stores_table.php` _(New)_
- `database/seeders/ProfileStoreSeeder.php`
- `app/Models/ProfileStore.php`
- `app/Http/Requests/StoreProfileUpdateRequest.php`
- `app/Http/Controllers/RegionController.php`
- `app/Http/Controllers/ProfileStoreController.php`
- `app/Http/Controllers/ShippingController.php`
- `resources/js/Pages/Admin/ProfileStore.vue`
- `tests/Feature/ProfileStoreTest.php`
- `tests/Feature/ShippingCalculationTest.php`
- `docs/CHANGELOG.md`

## [2026-09-27] — Fix RajaOngkir HTTP Post Arguments & Komerce RajaOngkir Integration

- **RajaOngkir Shipping Driver Fix & Komerce Support:**
    - Fixed `Http::post()` argument count error in `RajaOngkirShippingDriver::calculate()` by removing the 3rd argument and setting headers using `Http::withHeaders(['key' => $this->apiKey])`.
    - Added support for Komerce RajaOngkir API (`https://rajaongkir.komerce.id/api/v1`) with automatic endpoint detection (`POST /calculate/domestic-cost` for Komerce vs `POST /cost` for legacy).
    - Formatted request payload with `asForm()` and weight in grams.
    - Implemented response parser supporting both Komerce (`data` list) and legacy (`data.costs` or `rajaongkir.results.costs`) JSON structures.
    - Improved API key resolution in `RajaOngkirShippingDriver::__construct()` to check `$config['key']`, `$config['api_key']`, and respective fallback configs (`shipping.drivers.rajaongkir.key`, `services.rajaongkir.key`, `services.rajaongkir.api_key`).
    - Added test case `test_rajaongkir_driver_formats_komerce_api_response` in `tests/Feature/ShippingCalculationTest.php` (all 10 tests passing).
    - Verified with real Komerce API test call and compiled frontend assets (`npm run build`).

### Files changed

- `config/services.php`
- `app/Services/Shipping/Drivers/RajaOngkirShippingDriver.php`
- `tests/Feature/ShippingCalculationTest.php`
- `docs/CHANGELOG.md`

## [2026-09-25] — BinderByte Shipping Driver & Unified Package Tracking Integration

- **BinderByte Shipping Driver:**
    - Implemented `BinderByteShippingDriver` supporting cost calculation (`/v1/cost`), airway bill tracking (`/v1/track`), city fallback resolution, and supported couriers.
    - Added `track(string $courier, string $trackingNumber)` to `ShippingCalculatorInterface`, implemented across `BinderByteShippingDriver`, `MockShippingDriver`, and `ShippingDriver`.
    - Registered `createBinderbyteDriver()` and `track()` proxy in `ShippingManager`, setting `binderbyte` as the default shipping driver.
    - Configured `config/shipping.php`, `config/services.php`, and `.env.example` with `SHIPPING_DRIVER=binderbyte` and `BINDERBYTE_API_KEY`.
- **Unified Order Tracking:**
    - Updated `OrderController::tracking` and `OrderController::adminTracking` to delegate package tracking to `ShippingManager::track()` with automatic courier name normalization (e.g. `'JNE - REG'` normalized to `'jne'`).
- **Testing & Verification:**
    - Added test cases in `tests/Feature/ShippingCalculationTest.php` for BinderByte cost calculation, package tracking, and order tracking flow (all 9 tests passing).
    - Verified integration tests and compiled frontend assets (`npm run build`).

### Files changed

- `app/Services/Shipping/Contracts/ShippingCalculatorInterface.php`
- `app/Services/Shipping/Drivers/BinderByteShippingDriver.php` _(New)_
- `app/Services/Shipping/Drivers/MockShippingDriver.php`
- `app/Services/Shipping/Drivers/ShippingDriver.php`
- `app/Services/Shipping/ShippingManager.php`
- `app/Http/Controllers/OrderController.php`
- `config/shipping.php`
- `config/services.php`
- `.env.example`
- `tests/Feature/ShippingCalculationTest.php`
- `docs/CHANGELOG.md`

## [2026-09-25] — Modular Shipping Cost Calculation & Store Courier Settings

- **Modular Shipping Calculation Architecture:**
    - Created provider-agnostic `ShippingCalculatorInterface` defining `calculate()`, `getSupportedCouriers()`, and `getCities()`.
    - Implemented `RajaOngkirShippingDriver` supporting starter/pro RajaOngkir endpoints (`/cost`, `/city`), city resolution, and standardized rate output format (`courier`, `service`, `name`, `description`, `cost`, `etd`).
    - Implemented `MockShippingDriver` for offline local development and deterministic automated test execution.
    - Implemented `ShippingManager` extending Laravel's `Manager` pattern to resolve configured driver dynamically (`env('SHIPPING_DRIVER', 'rajaongkir')`).
    - Added `config/shipping.php` and updated `config/services.php` & `.env.example`.
- **Store Courier Settings (Admin):**
    - Created migration `2026_09_25_000001_add_shipping_settings_to_profile_stores_table.php` adding `shipping_origin_city_id` and `shipping_couriers` to `profile_stores`.
    - Updated `ProfileStore` model with fillable attributes, JSON casting, and `getEnabledCouriers()` helper method.
    - Updated `StoreProfileUpdateRequest` and `ProfileStoreController` to persist courier configurations and provide supported couriers to the frontend.
    - Updated `resources/js/Pages/Admin/ProfileStore.vue` with an interactive "Ekspedisi & Pengiriman" section allowing admins to toggle active couriers (JNE, POS, TIKI, SiCepat, J&T) and origin city, displayed in both View and Edit modes.
- **Dynamic Customer Checkout (Customer):**
    - Updated `CartService::summarize()` to calculate `totalWeight` from products in cart.
    - Updated `resources/js/Pages/Customer/Checkout.vue` to dynamically calculate real-time shipping costs via `/shipping/calculate` based on the store's enabled couriers and customer's destination city, with loading and error states.
    - Relaxed `OrderStoreRequest` validation for `shipping_method` from hardcoded `'in:JNE,GoSend'` to `'string|max:100'` to accept any configured courier service.
- **Testing & Verification:**
    - Created feature test suite `tests/Feature/ShippingCalculationTest.php` (6 tests passing).
    - Verified `tests/Feature/ProfileStoreTest.php` (9 tests passing) and `tests/Feature/GuestCheckoutTest.php` (6 tests passing).
    - Verified frontend asset compilation (`npm run build`).

### Files changed

- `database/migrations/2026_09_25_000001_add_shipping_settings_to_profile_stores_table.php` _(New)_
- `config/shipping.php` _(New)_
- `config/services.php`
- `.env.example`
- `app/Services/Shipping/Contracts/ShippingCalculatorInterface.php` _(New)_
- `app/Services/Shipping/Drivers/RajaOngkirShippingDriver.php` _(New)_
- `app/Services/Shipping/Drivers/MockShippingDriver.php` _(New)_
- `app/Services/Shipping/ShippingManager.php` _(New)_
- `app/Providers/AppServiceProvider.php`
- `app/Models/ProfileStore.php`
- `app/Http/Requests/StoreProfileUpdateRequest.php`
- `app/Http/Controllers/ProfileStoreController.php`
- `app/Http/Requests/ShippingCalculateRequest.php`
- `app/Http/Controllers/ShippingController.php`
- `app/Http/Requests/OrderStoreRequest.php`
- `app/Services/CartService.php`
- `routes/web.php`
- `resources/js/Pages/Admin/ProfileStore.vue`
- `resources/js/Pages/Customer/Checkout.vue`
- `tests/Feature/ShippingCalculationTest.php` _(New)_
- `docs/05_BACKEND_SCHEMA.md`
- `docs/CHANGELOG.md`

## [2026-09-24] — Remove "Add New Category" Capability

- Removed the ability to create categories inline while creating or editing a product. Admins can now only pick from existing categories.
- Simplified `resources/js/Components/Customer/Sub-main/CategoryInput.vue` into a plain category `<select>`: dropped the `newCategory` prop, `update:newCategory` emit, the "Tambah Kategori Baru" option, and the inline new-category name input.
- Removed `new_category` from the form payloads and the `"new"` selection branches in `resources/js/Pages/Admin/Products/Create.vue` and `resources/js/Pages/Admin/Products/Edit.vue`; submissions now always set `category_id` from the selected option.
- Hardened `app/Http/Controllers/ProductController.php`: dropped the `new_category` validation rule and the `Category::create()` block in both `store()` and `update()`.
- Removed the stale `new_category` rule from `app/Http/Requests/StoreProductRequest.php` for consistency.
- Existing category data, relationships, filtering, and display remain untouched.
- Verified with `npm run build` (passes). `php artisan test` could not be run in this environment (no PHP/Docker access) — run it locally.

### Files changed

- `resources/js/Components/Customer/Sub-main/CategoryInput.vue`
- `resources/js/Pages/Admin/Products/Create.vue`
- `resources/js/Pages/Admin/Products/Edit.vue`
- `app/Http/Controllers/ProductController.php`
- `app/Http/Requests/StoreProductRequest.php`
- `docs/CHANGELOG.md`

## [2026-09-24] — Dedicated Navbar Category Pages & Seeder Synchronization

- Created dedicated catalog pages matching all customer navigation links:
    - `/new-featured` (`NewFeaturedPage.vue`): Newest products and featured favorites across all categories with search, sorting, and pagination.
    - `/boquets` (`BoquetsPage.vue`): Scoped specifically to `Buket` category. Fixed `route("shop")` reference to `route("boquets")`.
    - `/flowers` (`FlowersPage.vue`): Scoped specifically to `Bunga` category with custom Hero, breadcrumbs, and filters.
    - `/accessories` (`AccessoriesPage.vue`): Scoped specifically to `Aksesoris` category with custom Hero, breadcrumbs, and filters.
    - `/bags` (`BagsPage.vue`): Scoped specifically to `Tas` category with custom Hero, breadcrumbs, and filters.
    - `/sale` (`SalePage.vue`): Serves all product categories with flexible filters. Fixed `route("shop")` reference to `route("sale")`.
- Updated navigation links in `resources/js/Components/Customer/Main/Navbar.vue` and `MenuDrawer.vue` to point to `/new-featured`, `/boquets`, `/flowers`, `/accessories`, `/bags`, `/sale`.
- Updated database seeders:
    - `database/seeders/CategorySeeder.php`: Added `Bunga` and `Tas` alongside `Aksesoris`, `Buket`, `Dekorasi`.
    - `database/seeders/ProductSeeder.php`: Added sample products for `Bunga` (Mawar & Matahari Rajut) and `Tas` (Tas Bahu & Tas Jinjing Mini) and updated category mapping.
- Added controller actions in `app/Http/Controllers/ProductController.php`: `newFeaturedPage`, `flowersPage`, `accessoriesPage`, `bagsPage`, and reusable `renderCategoryPage` helper.
- Created feature test suite `tests/Feature/CategoryPagesTest.php` covering all 6 endpoints, Inertia component rendering, and category filtering.
- Verified all feature tests and production asset compilation (`npm run build`).

### Files changed

- `database/seeders/CategorySeeder.php`
- `database/seeders/ProductSeeder.php`
- `routes/web.php`
- `app/Http/Controllers/ProductController.php`
- `resources/js/Pages/Customer/NewFeaturedPage.vue` _(New)_
- `resources/js/Pages/Customer/FlowersPage.vue` _(New)_
- `resources/js/Pages/Customer/AccessoriesPage.vue` _(New)_
- `resources/js/Pages/Customer/BagsPage.vue` _(New)_
- `resources/js/Pages/Customer/BoquetsPage.vue`
- `resources/js/Pages/Customer/SalePage.vue`
- `resources/js/Components/Customer/Main/Navbar.vue`
- `resources/js/Components/Customer/Main/MenuDrawer.vue`
- `tests/Feature/CategoryPagesTest.php` _(New)_
- `docs/CHANGELOG.md`

## [2026-09-24] — Header Live Product Search Floating Dropdown

- Integrated live debounced product search with a floating dropdown window in `resources/js/Components/Customer/Main/Navbar.vue`:
    - Search trigger icon/button available in **both** Authenticated (`isLoggedIn`) and Guest navbar layouts, plus mobile search trigger.
    - Floating search popover window anchored below header search action with animated entrance.
    - Live debounced API search (`300ms`) querying `/api/products/search?q={query}`.
    - Displays search results with product thumbnail, title, category badge, formatted IDR price (`Rp 150.000`), and direct link to product detail (`/product/{slug}`).
    - View all results link navigating to `/sale?search={query}` on button click or `Enter` key submission.
    - Handles click-outside dismissal and `Escape` key shortcut.
- Added `apiSearch` endpoint in `app/Http/Controllers/ProductController.php` and public route `Route::get('/api/products/search')` in `routes/web.php`.
- Created feature test `tests/Feature/ProductSearchTest.php`.
- Verified asset build with `npm run build`.

### Files changed

- `resources/js/Components/Customer/Main/Navbar.vue`
- `app/Http/Controllers/ProductController.php`
- `routes/web.php`
- `tests/Feature/ProductSearchTest.php`
- `docs/CHANGELOG.md`

## [2026-09-24] — Product Create & Edit Forms Executive UX Elevation

- Redesigned `resources/js/Pages/Admin/Products/Create.vue` and `resources/js/Pages/Admin/Products/Edit.vue` with executive 2-column card layout:
    - **Left 2 Columns**: Product Spec details (_Informasi Utama_, _Harga/Stok/Berat_, _Variasi Warna & Ukuran_).
    - **Right Column**: Media Galeri and real-time **Live Storefront Preview Card** showing how the product will appear in customer view (Title, formatted price in Rp, category badge, stock status badge, and color/size chips).
    - **Input Adornments**: `Rp` currency prefix with live Indonesian locale formatting (`formatRupiah`), `Package` stock icon, and `Scale` weight icon with `gram` unit suffix.
    - **Sticky Action Footer**: Bottom floating action bar featuring `Kembali` link button and `Simpan Produk` / `Perbarui Produk` button with `form.processing` spinner state.
- Fixed stray top-line text in `resources/js/Components/Customer/Sub-main/ImageInput.vue` for clean Vite compilation.

### Files changed

- `resources/js/Pages/Admin/Products/Create.vue`
- `resources/js/Pages/Admin/Products/Edit.vue`
- `resources/js/Components/Customer/Sub-main/ImageInput.vue`
- `docs/CHANGELOG.md`

- Overhauled `resources/js/Pages/Admin/ProfileStore.vue` with executive dashboard UX:
    - Added hero identity banner with store logo zoom effect, verified badge, and address card.
    - Added dedicated QRIS payment card with interactive zoom modal (`showQrisModal`).
    - Added brand-styled social media cards (Instagram, Facebook, TikTok) with 1-click clipboard copy and direct link launch.
    - Added drag-and-drop media file upload zones for Logo and QRIS images with live preview overlays.
    - Added sticky bottom save bar during edit mode for smooth submission without excessive scrolling.
- Consolidated editing directly into single-page `ProfileStore.vue` and removed `Edit.vue`.
- Updated backend seeders (`CategorySeeder`, `ProductSeeder`, `UserSeeder`, `ProfileStoreSeeder`) to use idempotent `firstOrCreate` logic.

### Files changed

- `resources/js/Pages/Admin/ProfileStore.vue`
- `resources/js/Pages/Admin/ProfileStore/Edit.vue` _(Deleted)_
- `routes/web.php`
- `app/Http/Controllers/ProfileStoreController.php`
- `tests/Feature/ProfileStoreTest.php`
- `docs/CHANGELOG.md`

## [2026-09-23] — Prevent Admin Account Deletion (Frontend & Backend)

- Added role check in `app/Http/Controllers/ProfileController.php@destroy` to block requests from users with `$user->role === 'admin'`, returning an error redirect (`"Akun admin tidak dapat dihapus."`).
- Removed `<DeleteUserForm />` component and import from `resources/js/Pages/Admin/Profile/Edit.vue` to prevent admin accounts from self-deleting on the frontend.
- Added valid Vue SFC structure to `resources/js/Pages/Admin/ProfileStore.vue` and `resources/js/Pages/Admin/ProfileStore/Edit.vue` for clean Vite compilation.
- Verified build with `npm run build`.

### Files changed

- `app/Http/Controllers/ProfileController.php`
- `resources/js/Pages/Admin/Profile/Edit.vue`
- `resources/js/Pages/Admin/ProfileStore.vue`
- `resources/js/Pages/Admin/ProfileStore/Edit.vue`
- `docs/CHANGELOG.md`

## [2026-09-23] — Revamp Admin Orders List Page UX & Interaction

- Completely overhauled `resources/js/Pages/Admin/Orders/Index.vue` with modern, responsive order management interface following the DaisyUI autumn theme and design guidelines.
- **Order KPI Cards**: Added 4 top metric summary cards (Total Pesanan, Perlu Tindakan, Sedang Diproses/Kirim, Pesanan Selesai) with interactive one-click filtering.
- **Advanced Search & Filtering Toolbar**:
    - Real-time live search matching Order ID (`#ID`), Customer Name, Email, Phone, City, and Tracking Number with quick clear (`X`) button.
    - Order status dropdown supporting all core lifecycle states (`waiting`, `checking`, `processing`, `shiping`, `completed`, `cancelled`) plus quick groups (`needs_action`, `in_progress`).
    - Payment method filter (`digital_wallet`, `cod`).
    - Sorter selector (Terbaru, Terlama, Total Tertinggi, Total Terendah).
    - Active filter chips bar with one-click "Reset Semua Filter".
- **Enhanced Order Table**:
    - Order reference with date & time formatted in Indonesian locale (`12 Sep 2026, 14:30 WIB`).
    - Customer info with initials avatar, full name, email / phone, and city badge.
    - Payment method badge with payment proof status indicator (Bukti Diunggah / Belum Ada Bukti).
    - Semantic status badges with color-coded dot and icon.
    - Shipping method and courier tracking number indicator.
    - Direct actions: Quick Preview modal and "Kelola" full detail page link.
- **Quick Order Preview Modal**:
    - Modal displaying customer shipping destination, payment proof preview thumbnail, total amount, shipping info, and customer notes without full page reload.
- **Client-side Pagination**:
    - Smooth client-side pagination with configurable page size (10, 25, 50), page numbers with ellipsis windowing, and dedicated empty states.
- Verified build with `npm run build`.

### Files changed

- `resources/js/Pages/Admin/Orders/Index.vue`
- `docs/CHANGELOG.md`

## [2026-09-23] — Revamp Admin Products List Page UX & Visual Design

- Completely redesigned `resources/js/Pages/Admin/Products/Index.vue` with modern, data-dense and accessible UI matching the DaisyUI autumn theme and brand orange palette.
- **KPI Summary Cards**: Added 4 top metric cards (Total Produk, Stok Aman (>5), Stok Menipis (1-5), and Stok Habis (0)) with interactive quick-filter toggling.
- **Advanced Search & Filter Toolbar**:
    - Debounced search input (350ms) with clear (`X`) button and Lucide search icon.
    - Category selector dropdown and stock status dropdown.
    - Sort dropdown with direction indicators (Terbaru, Terlama, Nama A-Z / Z-A, Harga, Stok).
    - View switcher between Table View and Grid/Card View (persisted in `localStorage`).
    - Active filter chips bar with one-click "Reset Semua Filter" button.
- **Refined Table View**:
    - Image thumbnail with multi-image indicator badge (`+N`) and fallback for missing photos.
    - Product name, slug, category badge, and formatted Indonesian Rupiah pricing.
    - Dynamic stock status badges (green for available, amber for low stock, red for out-of-stock).
    - Weight in grams with Lucide scale icon.
    - Row action buttons: quick preview, edit, and delete.
- **Responsive Grid / Card View**:
    - High quality card layout with category and stock badges, image aspect ratio, multi-image counter, price, and quick actions.
- **Custom Modals**:
    - Replaced browser `window.confirm()` with a custom confirmation `Modal.vue` displaying product thumbnail, name, category, and loading state while deleting.
    - Added Quick Product Details Preview modal displaying full image gallery with thumbnail navigation, pricing, weight, sizes, colors, description, and link to storefront.
- **Smart Pagination & Empty States**:
    - Added windowed pagination numbers with ellipsis to prevent overflow on large datasets.
    - Added distinct empty states for zero search/filter results and an empty inventory.
- Verified build with `npm run build`.

### Files changed

- `resources/js/Pages/Admin/Products/Index.vue`
- `docs/CHANGELOG.md`

## [2026-09-23] — Refactor Admin Dashboard & Layout for Responsiveness and UX

- Redesigned `resources/js/Layouts/AdminLayout.vue` to remove fixed margins (`ml-[230px]` / `ml-16`) on mobile; introduced mobile-first full-width content with responsive `lg:ml-60` / `lg:ml-20` for desktop collapsed states.
- Replaced the floating vertical toggle button at `top-1/2` in `resources/js/Components/Admin/Main/Sidebar.vue` with an off-canvas slide-over drawer on screens `< lg` with a dark backdrop overlay (`bg-stone-900/50 backdrop-blur-xs`) and auto-closing on Inertia navigation and Escape key.
- Upgraded `resources/js/Components/Admin/Main/Navbar.vue` with a mobile hamburger toggle button, desktop sidebar collapse button, "Lihat Toko" quick storefront preview link, and an enriched admin user profile dropdown showing user name, email, and navigation links.
- Updated `app/Http/Controllers/DashboardController.php` to calculate and provide store KPI metrics (`totalRevenue`, `pendingOrdersCount`, `totalProductsCount`, `lowStockCount`) and `recentOrders` for immediate operational visibility.
- Completely redesigned `resources/js/Pages/Admin/Dashboard.vue` with a personalized greeting header with current date and quick action buttons ("+ Tambah Produk", "Kelola Pesanan"), 4 responsive KPI cards, a responsive recent orders table/card section, and 3-column product analytics cards (Top Selling, Top Rated, Low Stock) with image fallbacks and friendly empty states.
- Added feature test `tests/Feature/AdminDashboardTest.php` testing guest redirection, customer authorization (403), and admin access with all dashboard props.
- Verified with `docker compose exec app php artisan test tests/Feature` (52 passed) and `npm run build`.

### Files changed

- `app/Http/Controllers/DashboardController.php`
- `tests/Feature/AdminDashboardTest.php`
- `resources/js/Layouts/AdminLayout.vue`
- `resources/js/Components/Admin/Main/Sidebar.vue`
- `resources/js/Components/Admin/Main/Navbar.vue`
- `resources/js/Pages/Admin/Dashboard.vue`

## [2026-09-22] — Polish checkout information step (form section)

- Redesigned the left checkout section in `resources/js/Pages/Customer/Checkout.vue` into a centered `max-w-md` column (was a half-width, right-aligned, double-nested layout) so the form reads cleanly on both desktop and mobile; parent container changed from `h-screen` to `min-h-screen` so the form scrolls instead of clipping on short screens.
- Rebuilt the step indicator (Cart → Information → Shipping → Payment): active step is now `font-semibold` with an orange dot, inactive steps are muted `text-gray-400`, separators are consistent 16px chevrons, and the `nav` got an `aria-label`.
- Replaced placeholder-only fields with real `<label>`s (Email, Nama, Alamat, Provinsi, Kota, Kecamatan, Kelurahan, Telepon) via a new optional `label` prop on `FormInput`/`FormSelect`; placeholders remain as hints. Required fields show a red `*`.
- When a user is logged in, the Contact block now shows their email as a muted read-only row instead of an empty heading.
- Switched input focus styling from `indigo` to the brand `orange-500`, and unified corner radius to `rounded-lg` (previously mixed `rounded-lg`/`rounded-md`).
- Section headings now have numbered orange badges (1 · 2) for hierarchy.
- "Kembali" is now a `Link` to `route('cart.show')` with a proper hover state; "Lanjutkan" is a styled `type="button"` (visual-only for now — the Shipping/Payment steps that provide `payment_method`/`shipping_method` aren't built yet, so submission stays unwired).
- Added `role="alert"` to `InputError.vue` so validation errors are announced to screen readers.
- Verified with `npm run build` and `php artisan test`.

### Files changed

- `resources/js/Pages/Customer/Checkout.vue`
- `resources/js/Components/FormInput.vue`
- `resources/js/Components/FormSelect.vue`
- `resources/js/Components/InputError.vue`

## [2026-09-19] — Remove confirmation prompt when deleting item from Cart Drawer

- Removed `confirm(...)` browser prompt in `removeItem(item)` method in `resources/js/Components/Customer/Main/CartDrawer.vue`.

### Files changed

- `resources/js/Components/Customer/Main/CartDrawer.vue`

## [2026-09-19] — Bottom-right option selection confirmation pop-up for product add-to-cart

- Added hover overlay button to `resources/js/Components/Customer/Sub-main/Product.vue`.
- Added a bottom-right option confirmation pop-up window (`Teleport` to `body`, styled consistently with cart/filter drawers).
- Added smooth dark backdrop overlay transition (`bg-black/30`) with `@click="closeOptionModal"` to dismiss modal on outside click.
- Safely parsed `colors` and `sizes` (handling arrays, stringified JSON, or null) into computed `parsedColors` and `parsedSizes`.
- Passed `:colors` and `:sizes` props across all `<Product />` list instances (`LandingPage.vue`, `BoquetsPage.vue`, `SalePage.vue`, `Pages/Customer/Product.vue`).
- Submits selected options (`product_id`, `quantity`, `color`, `size`) to `cart.store` via Inertia `router.post`.

### Files changed

- `resources/js/Components/Customer/Sub-main/Product.vue`
- `resources/js/Pages/Customer/LandingPage.vue`
- `resources/js/Pages/Customer/BoquetsPage.vue`
- `resources/js/Pages/Customer/SalePage.vue`
- `resources/js/Pages/Customer/Product.vue`

## [2026-09-19] — Guest checkout: buy without authentication

- Moved `GET /checkout` and `POST /customer/orders/checkout` (order placement) into the public `customer` route group, so guests can complete a purchase without an account. Order history, tracking, payment-proof upload, and the review/complete flow stay login-only.
- `orders.user_id` is now nullable and an `orders.email` column was added via migration `2026_09_19_053218_make_orders_user_id_nullable_add_email.php`, so guest orders carry no user reference but capture the buyer's email (required for guests, defaults to the account email for logged-in users).
- `OrderController::store` now handles both flows in one transaction: authenticated users order from their DB cart, guests order from the session cart (`CartService`); the guest session cart is cleared after the order is placed.
- `CartController` now delegates cart read/write to `CartService` (single source for guest and user carts); `EnsureCartNotEmpty` uses the service too.
- `OrderStoreRequest` requires `email` for guests via `Rule::requiredIf(!Auth::check())`; added an email field to `Checkout.vue` (shown only for guests).
- The cart drawer's guest CTA changed from "Login untuk Checkout" to a direct `Checkout` link.
- Added `tests/Feature/GuestCheckoutTest.php` (guest checkout page access, empty-cart redirect, guest order placement with email, missing-email validation, empty-cart rejection, authenticated user order linking) and updated `RemoveApiLayerTest.php` (order placement no longer redirects guests to `/login`).
- Verified with `php artisan test --testsuite=Feature` (49 passed) and `npm run build`.

### Files changed

- `database/migrations/2026_09_19_053218_make_orders_user_id_nullable_add_email.php`
- `app/Http/Controllers/CartController.php`
- `app/Http/Controllers/OrderController.php`
- `app/Http/Middleware/EnsureCartNotEmpty.php`
- `app/Services/CartService.php`
- `app/Models/Order.php`
- `app/Http/Requests/OrderStoreRequest.php`
- `routes/web.php`
- `resources/js/Pages/Customer/Checkout.vue`
- `resources/js/Components/Customer/Main/CartDrawer.vue`
- `tests/Feature/GuestCheckoutTest.php`
- `tests/Feature/RemoveApiLayerTest.php`

## [2026-09-22] — Modernize Authentication UI & Remove Default Breeze Styling

- Replaced default Laravel Breeze gray theme (`bg-gray-100`) and standard centered box in `GuestLayout.vue` with an ambient warm-toned background gradient (`from-amber-50/50 via-stone-50 to-orange-50/40`), brand logo, and a top navigation link to return to the storefront ("Kembali ke Beranda").
- Enhanced `GuestLayout` card container with responsive padding, modern rounded corners (`rounded-2xl`), subtle borders, and soft elevation (`shadow-xl shadow-stone-200/60`).
- Updated core form components (`TextInput.vue`, `Checkbox.vue`, `PrimaryButton.vue`) to replace Breeze default indigo accents and dark gray uppercase buttons with Nyuwi Creation brand orange styling (`orange-500`, `focus:ring-orange-400/500`, modern border radii).
- Modernized all authentication views:
    - `Login.vue`: Welcoming title and subtitle, brand input placeholders, full-width CTA button, and clear link to registration.
    - `Register.vue`: Modern stacked fields, full-width CTA button, and link to login.
    - `ForgotPassword.vue`: Clean instructions, branded status alerts, full-width reset link button, and link back to login.
    - `ResetPassword.vue`: Clean password reset card with full-width submit button.
    - `VerifyEmail.vue`: Branded verification status card with mail icon, full-width resend button, and logout action.
    - `ConfirmPassword.vue`: Secure area prompt with lock icon and branded controls.
- Verified with `docker compose exec app php artisan test tests/Feature/Auth/` (all 18 tests passed) and `npm run build` (built cleanly).

### Files changed

- `resources/js/Layouts/GuestLayout.vue`
- `resources/js/Components/TextInput.vue`
- `resources/js/Components/Checkbox.vue`
- `resources/js/Components/PrimaryButton.vue`
- `resources/js/Pages/Auth/Login.vue`
- `resources/js/Pages/Auth/Register.vue`
- `resources/js/Pages/Auth/ForgotPassword.vue`
- `resources/js/Pages/Auth/ResetPassword.vue`
- `resources/js/Pages/Auth/VerifyEmail.vue`
- `resources/js/Pages/Auth/ConfirmPassword.vue`
- `docs/CHANGELOG.md`

## [2026-09-22] — Update README with Current Setup, Migrations, Seeders & Admin Command

- Updated project overview from outdated "simple to do list using laravel and react" to Nyuwi Creation E-Commerce (Laravel 11, Vue 3 Inertia, Tailwind CSS).
- Documented Docker and local startup steps, ports, and Vite dev server usage.
- Documented database migrations (`php artisan migrate`) and seeders (`php artisan db:seed`) along with default test accounts.
- Documented CLI command for creating admin accounts (`php artisan create:admin` interactive and option flags).

### Files changed

- `README.md`
- `docs/CHANGELOG.md`

## [2026-09-24] — Split Order Statuses, Add Payment Transactions Table & Drop Payment Proof

- Created migration `2026_09_24_093500_split_orders_status_and_drop_payment_proof.php`:
    - Added `payment_status` enum (`pending`, `paid`, `failed`, `expired`, `refunded`) with default `pending`.
    - Migrated existing orders status values into `payment_status` and new fulfillment-only `status`.
    - Altered `status` enum column to fulfillment-only: `processing`, `shiping`, `completed`, `cancelled` (default: `processing`).
    - Dropped obsolete `payment_proof` column.
- Created migration `2026_09_24_093501_create_payment_transactions_table.php`:
    - Created `payment_transactions` table with `order_id` (foreign key cascading on delete), `gateway`, `gateway_transaction_id`, `payment_channel`, `amount`, `raw_status`, `expired_at`, `paid_at`, `raw_payload`, and timestamps.
    - Added composite index on `['gateway', 'gateway_transaction_id']` for webhook idempotency and audit lookups.
- Created `App\Models\PaymentTransaction` Eloquent model with `$fillable`, `casts()` (`decimal:2`, `datetime`, `array`), and `belongsTo(Order::class)`.
- Updated `App\Models\Order` to include `payment_status`, remove `payment_proof`, and define `hasMany(PaymentTransaction::class)`.
- Updated `OrderStatusUpdateRequest` to validate fulfillment statuses (`processing,shiping,completed,cancelled`).
- Updated `OrderController`:
    - In `store()`: sets `'payment_status' => 'pending'` and `'status' => 'processing'`.
    - In `update()`: removed obsolete checks on payment proof and digital wallet status waiting.
    - In `uploadPaymentProof()`: safe return indicating manual proof uploads are discontinued.
- Updated `DashboardController`:
    - Updated KPI total revenue calculation to query `payment_status = 'paid'`.
    - Updated `pendingOrdersCount` and `topSelling` subqueries to align with separated payment and fulfillment statuses.
- Updated UI components to display both payment and fulfillment statuses:
    - `Admin/Orders/Index.vue`: Added `getPaymentStatusBadge`, updated table row and preview drawer.
    - `Admin/Orders/Show.vue`: Added payment status details, updated fulfillment badges, removed payment proof modal.
    - `Admin/Dashboard.vue`: Added `getPaymentStatusBadge` and updated recent orders status column.
    - `Customer/Dashboard.vue`: Removed payment proof modal and upload form.
    - `Customer/Main/OrderHistoryTab.vue`: Added `getPaymentStatusClass`, rendered both statuses, removed manual upload button.
    - `Customer/Main/Navbar.vue`: Checked `order.payment_status === 'pending'` for pending order alerts.
- Added `EnsureCustomer` middleware and registered alias `customer` in `bootstrap/app.php`.
- Created feature test `tests/Feature/PaymentTransactionTest.php` and updated `GuestCheckoutTest.php` & `AdminDashboardTest.php`.
- Updated backend documentation in `docs/05_BACKEND_SCHEMA.md` with schema tables, ER diagram, relationships, and indexes.

### Files changed

- `database/migrations/2026_09_24_093500_split_orders_status_and_drop_payment_proof.php`
- `database/migrations/2026_09_24_093501_create_payment_transactions_table.php`
- `app/Models/PaymentTransaction.php`
- `app/Models/Order.php`
- `app/Http/Requests/OrderStatusUpdateRequest.php`
- `app/Http/Controllers/OrderController.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Middleware/EnsureCustomer.php`
- `bootstrap/app.php`
- `resources/js/Pages/Admin/Dashboard.vue`
- `resources/js/Pages/Admin/Orders/Index.vue`
- `resources/js/Pages/Admin/Orders/Show.vue`
- `resources/js/Pages/Customer/Dashboard.vue`
- `resources/js/Components/Customer/Main/OrderHistoryTab.vue`
- `resources/js/Components/Customer/Main/Navbar.vue`
- `tests/Feature/PaymentTransactionTest.php`
- `tests/Feature/GuestCheckoutTest.php`
- `tests/Feature/AdminDashboardTest.php`
- `docs/05_BACKEND_SCHEMA.md`
- `docs/CHANGELOG.md`

## [2026-09-25] — Integrate Midtrans Payment Gateway (Snap API & Webhooks)

- Configured Midtrans credentials and URLs in `config/services.php` and placeholders in `.env.example`.
- Created `App\Services\MidtransService` encapsulating Midtrans Snap token generation and SHA-512 notification signature verification using native Laravel HTTP client (`Http::withBasicAuth()`), with automatic line item shipping cost balancing.
- Created `App\Http\Controllers\MidtransWebhookController` to handle incoming Midtrans notifications:
    - Validates SHA-512 signature against order and server key.
    - Guarantees idempotency via `payment_transactions` lookup (`order_id`, `gateway`, `gateway_transaction_id`, `raw_status`).
    - Maps Midtrans status codes (`capture`, `settlement`, `pending`, `expire`, `cancel`, `deny`, `refund`) directly to `orders.payment_status` within an atomic database transaction.
- Added `paymentToken` endpoint to `OrderController` and updated `store` to flash `placedOrderId`.
- Excluded `/webhooks/midtrans` from CSRF verification in `bootstrap/app.php`.
- Registered webhook and payment token routes in `routes/web.php`.
- Shared Midtrans client key and Snap JS URL via `HandleInertiaRequests` middleware.
- Created client-side utility `resources/js/Utils/midtrans.js` for dynamic Snap SDK loading and modal execution (`triggerSnapPayment`).
- Updated `OrderHistoryTab.vue` to add "Bayar Sekarang" action for pending orders.
- Updated `Cart.vue` to auto-trigger Midtrans payment popup when redirected from checkout with `placedOrderId`.
- Created feature test suite `tests/Feature/MidtransIntegrationTest.php` covering token generation, webhook signature validation, idempotency, and status updates (all 7 tests passing).

### Files changed

- `config/services.php`
- `.env.example`
- `app/Services/MidtransService.php`
- `app/Http/Controllers/MidtransWebhookController.php`
- `app/Http/Controllers/OrderController.php`
- `bootstrap/app.php`
- `routes/web.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `resources/js/Utils/midtrans.js`
- `resources/js/Components/Customer/Main/OrderHistoryTab.vue`
- `resources/js/Pages/Customer/Cart.vue`
- `tests/Feature/MidtransIntegrationTest.php`
- `docs/CHANGELOG.md`

## [2026-09-25] - Customer Dashboard UX Enhancement

### Added & Changed

- **Customer Dashboard Redesign (`resources/js/Pages/Customer/Dashboard.vue`):**
    - Revamped into a fully responsive mobile-first customer hub with Autumn theme aesthetic (`orange-500` accents, soft warm background gradient, rounded-3xl cards).
    - Added customer profile banner with user avatar initials, greeting, member badge, email, and join date.
    - Added quick KPI stat summary cards (Total Pesanan, Belum Dibayar, Dalam Pengiriman, Ulasan Diberikan) with interactive navigation shortcuts.
    - Replaced rigid desktop-only sidebar with a sticky desktop sidebar and a horizontal scrollable tab bar on mobile/tablet screens.
    - Added toast notification feedback integration.
- **Order History Upgrade (`resources/js/Components/Customer/Main/OrderHistoryTab.vue`):**
    - Added status filter pills (Semua, Menunggu Pembayaran, Diproses, Dikirim, Selesai, Dibatalkan) with live badge counts.
    - Added order search bar supporting search by Order ID and product title.
    - Redesigned order cards with product thumbnail preview, variant tags (size/color), item count, expandable product lists, and clear status badges.
    - Integrated direct Midtrans payment CTA ("Bayar Sekarang") for pending orders.
    - Upgraded Order Details modal with comprehensive order information, delivery address card, courier tracking status timeline, and inline star rating for completed deliveries.
    - Added warm empty state with shopping bag illustration and CTA to product catalog.
- **Reviews Center (`resources/js/Components/Customer/Main/ReviewsTab.vue`):**
    - Replaced broken stub with a dedicated review management center with two subtabs: "Ulasan Saya" (displaying submitted reviews with product image, star ratings, date, and product link) and "Menunggu Diulas" (completed orders eligible for rating).
    - Connected review actions to automatically open the rating modal in Order History.
    - Added empty states for both review views.
- **Account Settings Implementation (`resources/js/Components/Customer/Main/SettingsTab.vue`):**
    - Replaced duplicate review code with complete Customer Account Settings.
    - Implemented Profile Information form (Name, Email, email verification status notice) and Password Update form (Current, New, Confirm) with validation error feedback.
- **Feature Tests (`tests/Feature/CustomerDashboardTest.php`):**
    - Added test coverage for guest redirection, customer dashboard access, and order/review rendering.

### Files changed

- `resources/js/Pages/Customer/Dashboard.vue`
- `resources/js/Components/Customer/Main/OrderHistoryTab.vue`
- `resources/js/Components/Customer/Main/ReviewsTab.vue`
- `resources/js/Components/Customer/Main/SettingsTab.vue`
- `tests/Feature/CustomerDashboardTest.php`
- `docs/CHANGELOG.md`
