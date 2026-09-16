# Specification: Remove API Layer & Migrate to Inertia-Only Data Flow

## Status
Draft — pending implementation on `dev` branch.

## Context
The project currently maintains a **dual controller layer**:
- **Web controllers** (`app/Http/Controllers/`) — return Inertia responses for page rendering.
- **API controllers** (`app/Http/Controllers/API/`) — return JSON for AJAX operations (cart, wishlist, shipping, orders, regions, catalog).

This specification removes the API layer entirely and consolidates all data flow through **Laravel web routes + Inertia.js props/forms**. Sanctum is also removed because token-based API authentication becomes unnecessary.

## Goals
1. Eliminate `app/Http/Controllers/API/` and `routes/api.php`.
2. Remove `laravel/sanctum` from dependencies, configuration, and `User` model.
3. Make every page receive its initial data via Inertia props instead of client-side `axios`/`fetch` calls to `/api/*`.
4. Handle mutations (cart updates, wishlist changes, checkout, reviews) through Inertia `useForm` / `router` with server-side redirects and flash messages.
5. Support dynamic UI (region dropdowns, shipping recalculation) via **Inertia partial reloads**, not JSON endpoints.
6. Enforce server-side ownership checks and stock validation on every mutation.
7. Replace inline `$request->validate()` with dedicated Form Request classes.

## Decisions
- **Region data**: Only provinces are passed as initial props. Regencies, districts, and villages are loaded via Inertia partial reloads when the parent selection changes. Never load the full Indonesian region dataset into shared/page props.
- **Shipping calculation**: Handled as a partial reload or inline prop update during checkout, not a standalone JSON endpoint.
- **External tracking (BinderByte)**: Moved to the server. The frontend requests tracking through a web route; the server calls BinderByte and returns the result as an Inertia prop or JSON-like payload inside an Inertia response. This prevents exposing the API key in the browser.
- **Sanctum**: Removed entirely. Session-based auth with CSRF is sufficient for the Inertia SPA-like experience.

## Current API Surface (to be removed)

| Domain | API Endpoint(s) | Frontend Consumer(s) |
|--------|----------------|----------------------|
| Catalog | `GET /api/home` | `LandingPage.vue` |
| Catalog | `GET /api/shop` | `ShopingPage.vue` (implied) |
| Catalog | `GET /api/product/{slug}` | `Product.vue` |
| Cart | `GET /api/cart` | `Cart.vue`, `Checkout.vue` |
| Cart | `POST /api/cart/add` | `Product.vue` |
| Cart | `PUT /api/cart/{id}` | `Cart.vue` |
| Cart | `DELETE /api/cart/{id}` | `Cart.vue` |
| Cart | `GET /api/cart/count` | (check if used) |
| Cart | `GET /api/cart/validate` | (check if used) |
| Wishlist | `GET /api/wishlist` | `WishlistTab.vue` |
| Wishlist | `POST /api/wishlist/add` | `Product.vue`, `Sub-main/Product.vue` |
| Wishlist | `DELETE /api/wishlist/{id}` | `WishlistTab.vue` |
| Wishlist | `GET /api/wishlist/count` | (check if used) |
| Orders | `GET /api/orders` | `OrderHistoryTab.vue` |
| Orders | `GET /api/orders/{orderId}` | `OrderHistoryTab.vue` |
| Orders | `POST /api/orders/complete` | `OrderHistoryTab.vue` |
| Orders | `GET /api/orders/{orderId}/status` | `OrderHistoryTab.vue` |
| Orders | `GET /api/tracking/{trackingNumber}` | `OrderHistoryTab.vue`, `Admin/Orders/Show.vue` |
| Regions | `GET /api/provinces` | `Checkout.vue`, `Admin/ProfileStore/Edit.vue` |
| Regions | `GET /api/regencies/{provinceId}` | `Checkout.vue`, `Admin/ProfileStore/Edit.vue` |
| Regions | `GET /api/districts/{regencyId}` | `Checkout.vue` |
| Regions | `GET /api/villages/{districtId}` | `Checkout.vue` |
| Regions | `GET /api/city/{cityName}` | `Admin/ProfileStore/Edit.vue` |
| Regions | `GET /api/search/regions` | (check if used) |
| Shipping | `GET /api/shipping/calculate` | `Checkout.vue` |
| Web JSON | `GET /orders/info` (`OrderController@getInfo`) | `CustomersLayout.vue` |

## Proposed Architecture

### Controller layer (single layer)
All controllers live under `app/Http/Controllers/` and return `Inertia::render(...)` or `redirect()->back()->with(...)`. No `Controllers/API/` namespace.

### Data flow
1. **Initial page load**: Controller queries data, passes it to `Inertia::render('PageName', [...])`.
2. **Form submission**: Vue uses `useForm()` or `router.post(...)` to a named web route.
3. **Server response**: Controller validates (Form Request), authorizes, mutates, then redirects with flash message.
4. **Inertia visit**: Refreshes page props automatically. Use `only: ['propName']` and `preserveScroll: true` for lightweight partial reloads.

### Shared props (lightweight)
Keep in `HandleInertiaRequests::share()`:
- `auth.user`
- `flash.message`
- `recaptchaSiteKey`
- `storeName`
- Cart count and wishlist count (optional, if needed for header badge)

### Page-specific props
| Page | Props |
|------|-------|
| `LandingPage` | `products` (featured), `categories` |
| `ShopingPage` | `products` (paginated), `categories`, `filters` |
| `Product` | `product`, `categories`, `productRating`, `relatedProducts`, `isInWishlist` |
| `Cart` | `cartItems`, `summary` (subtotal, totalItems, itemCount) |
| `Checkout` | `cartItems`, `summary`, `provinces`, `regencies?`, `districts?`, `villages?`, `shippingOptions?` |
| `Wishlist` | `wishlistItems` (paginated) |
| `Customer/Dashboard` | `orders`, `reviews`, `wishlistItems`, `mustVerifyEmail`, `status` |
| `Admin/Orders/Show` | `order`, `trackingData?` |
| `Admin/ProfileStore/Edit` | `profile`, `provinces`, `cities?` |

## Route plan

### Resolve naming collisions
Current `routes/web.php` has both customer and admin order routes using `orders.*` prefix. This must be split:
- Customer: `customer.orders.*`
- Admin: `admin.orders.*`

### Customer routes (`auth,customer` middleware)
```
GET    /                           ProductController@landingPage        name: home
GET    /about                      (closure)                            name: about
GET    /shop                       ProductController@shopPage           name: shop
GET    /product/{slug}             ProductController@product            name: product

GET    /cart                       CartController@showCart              name: cart.show
POST   /cart                       CartController@store                 name: cart.store
PUT    /cart/{id}                  CartController@update                name: cart.update
DELETE /cart/{id}                  CartController@destroy               name: cart.destroy

GET    /checkout                   CartController@showCheckout          name: checkout
POST   /checkout                   OrderController@store                name: orders.store

GET    /wishlist                   WishlistController@index             name: wishlist.index
POST   /wishlist                   WishlistController@store             name: wishlist.store
DELETE /wishlist/{id}              WishlistController@destroy           name: wishlist.destroy

GET    /customer/profile           OrderController@orderUser            name: customer.profile
GET    /customer/orders            OrderController@userOrders           name: customer.orders.index
GET    /customer/orders/{id}       OrderController@userOrderDetail      name: customer.orders.show
POST   /customer/orders/complete   OrderController@complete             name: customer.orders.complete
POST   /customer/orders/proof      OrderController@uploadPaymentProof   name: customer.orders.proof

GET    /regions/provinces          RegionController@provinces           name: regions.provinces
GET    /regions/regencies/{id}     RegionController@regencies           name: regions.regencies
GET    /regions/districts/{id}     RegionController@districts           name: regions.districts
GET    /regions/villages/{id}      RegionController@villages            name: regions.villages
GET    /regions/city/{name}        RegionController@cityByName          name: regions.city
GET    /regions/search             RegionController@search              name: regions.search

POST   /shipping/calculate         ShippingController@calculate         name: shipping.calculate
```

### Admin routes (`auth,admin` middleware)
```
GET    /dashboard                  DashboardController@index            name: dashboard

GET    /inventory                  ProductController@index              name: admin.products.index
GET    /inventory/create           ProductController@create             name: admin.products.create
POST   /inventory                  ProductController@store              name: admin.products.store
GET    /inventory/{id}/edit        ProductController@edit               name: admin.products.edit
PUT    /inventory/{id}             ProductController@update             name: admin.products.update
DELETE /inventory/{id}             ProductController@destroy            name: admin.products.destroy

GET    /admin/orders               OrderController@show                 name: admin.orders.index
GET    /admin/orders/{id}          OrderController@detail               name: admin.orders.show
PUT    /admin/orders/{id}          OrderController@update               name: admin.orders.update

GET    /admin/tracking/{number}    OrderController@tracking             name: admin.orders.tracking

GET    /profile-store/{name}       ProfileStoreController@edit          name: profile-store.edit
PUT    /profile-store/update/{name} ProfileStoreController@update       name: profile-store.update

GET    /profile                    ProfileController@edit               name: profile.edit
PATCH  /profile                    ProfileController@update             name: profile.update
DELETE /profile                    ProfileController@destroy            name: profile.destroy

GET    /data                       (closure)                            name: data
```

### Guest routes
```
GET    /admin/register             AdminRegistrationController@create   name: admin.register
POST   /admin/register             AdminRegistrationController@store
```

## Form Requests to create
- `CartStoreRequest` — `product_id`, `quantity`, `size`, `color`
- `CartUpdateRequest` — `quantity`
- `WishlistStoreRequest` — `slug`
- `OrderStoreRequest` — address fields, payment_method, shipping_method, shipping_cost
- `OrderCompleteRequest` — `order_id`, `reviews[]`
- `PaymentProofRequest` — `order_id`, `payment_proof`
- `OrderStatusUpdateRequest` — `status`, `tracking_number`
- `ShippingCalculateRequest` — `courier`, `origin`, `destination`, `weight`
- `RegionSearchRequest` — `q`, `type`

## Implementation phases

### Phase 1 — Documentation
- Update `AGENTS.md` structure diagram: remove `API/` folder.
- Update `docs/02_TRD.md`: single controller layer, remove Sanctum, document Inertia partial reloads.
- Commit this spec to `docs/specs/remove-api-layer.md`.

### Phase 2 — Form Requests & prop contracts
- Create all Form Request classes under `app/Http/Requests/`.
- Define page prop contracts (no code changes to controllers yet).

### Phase 3 — Read operations → web controllers
- `ProductController`: `landingPage`, `shopPage`, `product` pass props.
- `CartController`: `showCart`, `showCheckout` pass props.
- `WishlistController`: `index` passes paginated items.
- `OrderController`: consolidate `orderUser`, add `userOrders`, `userOrderDetail`.
- `RegionController` (new web controller): provinces, regencies, districts, villages, city lookup, search.
- `ShippingController` (new web controller): calculate (returns as Inertia prop or redirect).

### Phase 4 — Mutations → web routes
- Cart: `store`, `update`, `destroy` in `CartController`.
- Wishlist: `store`, `destroy` in `WishlistController`.
- Orders: `store`, `complete`, `uploadPaymentProof` in `OrderController`.
- Admin orders: `update`, `tracking` in `OrderController`.
- Enforce ownership checks and DB transactions.
- Replace all `{success,data}` JSON responses with redirects + flash.

### Phase 5 — Frontend refactor
- Replace `axios`/`fetch` with `useForm` / `router` in all listed Vue files.
- Receive initial data as props (remove `onMounted` data fetching).
- Use partial reloads for region dropdowns, shipping calculation, cart quantity changes.
- Move BinderByte tracking call to server-side `OrderController@tracking`.

### Phase 6 — Remove API routes & controllers
- Rewrite `routes/web.php` with the route plan above.
- Remove `api: .../routes/api.php` from `bootstrap/app.php`.
- Delete `routes/api.php`.
- Delete `app/Http/Controllers/API/` and all its files.
- Remove unused API-only resources if no controller references them.

### Phase 7 — Remove Sanctum
- `composer remove laravel/sanctum`.
- Remove `HasApiTokens` from `app/Models/User.php`.
- Delete `config/sanctum.php`.
- Remove Sanctum middleware references from any route or middleware configuration.

### Phase 8 — Tests & verification
- Feature tests for every controller action.
- Assert no `/api/*` routes registered.
- Run `php artisan test`.
- Run `npm run build`.
- Append summary to `docs/CHANGELOG.md`.

## Risks & mitigations
| Risk | Mitigation |
|------|------------|
| Route name collisions break existing links | Split into `customer.orders.*` / `admin.orders.*`; audit all `route()` calls in Vue/PHP. |
| Full region dataset in props hurts performance | Only provinces initially; load children via partial reloads. |
| Frontend relies on `response.data.success` pattern | Replace with Inertia error bag + flash message checks. |
| Sanctum removal breaks something unexpected | Audit `composer.json`, `User.php`, middleware, config before removal. |
| Stock validation skipped during checkout | Revalidate cart stock server-side in `OrderController@store` inside a transaction. |

## Acceptance criteria
- [ ] No `app/Http/Controllers/API/` directory exists.
- [ ] No `routes/api.php` file exists.
- [ ] No `/api/*` endpoints respond (404).
- [ ] `laravel/sanctum` is not in `composer.json`.
- [ ] `User` model does not use `HasApiTokens`.
- [ ] All customer pages load initial data via Inertia props.
- [ ] All mutations use Inertia forms/requests with server-side redirects.
- [ ] Region dropdowns work via Inertia partial reloads.
- [ ] Shipping calculation works via Inertia partial reloads.
- [ ] BinderByte tracking is server-side only.
- [ ] All inline validation replaced with Form Request classes.
- [ ] `php artisan test` passes.
- [ ] `npm run build` succeeds.
- [ ] `docs/CHANGELOG.md` updated.
