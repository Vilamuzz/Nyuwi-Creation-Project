# Changelog

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
