# Changelog

## [2026-09-17] — Rework landing page, merge About, remove DaisyUI

### Overview
- Reworked `LandingPage.vue` into a focused, accessible, responsive e‑commerce landing page following UI‑UX‑Pro‑Max guidance
- Merged the content of `About.vue` into the homepage as an anchor‑link section (`/#about`)
- Removed the dedicated `/about` route and deleted `About.vue`
- Updated `Navbar.vue` and `Footer.vue` to point to `/#about` instead of `/about`
- **Removed DaisyUI package** (`daisyui`) from the project, replacing all DaisyUI classes with pure Tailwind CSS equivalents
- Updated all components that used DaisyUI (admin and customer UI) to use custom, accessible Tailwind styles
- Verified that the Vite build compiles without errors

### Files changed
#### Frontend pages
- `resources/js/Pages/Customer/LandingPage.vue` – complete rewrite with improved UX flow
- `resources/js/Pages/Customer/About.vue` – deleted
- `routes/web.php` – removed `/about` route

#### Shared components (customer)
- `resources/js/Components/Customer/Main/Navbar.vue`
- `resources/js/Components/Customer/Main/Footer.vue`
- `resources/js/Components/Customer/Main/WishlistTab.vue`
- `resources/js/Components/Customer/Sub-main/ToastNotification.vue`
- `resources/js/Components/Customer/Sub-main/CategoryInput.vue`
- `resources/js/Components/Customer/Sub-main/ColorPicker.vue`
- `resources/js/Components/Customer/Sub-main/SizeInput.vue`
- `resources/js/Components/Customer/Sub-main/ImageInput.vue`

#### Admin components
- `resources/js/Pages/Admin/Orders/Index.vue`
- `resources/js/Pages/Admin/Products/Create.vue`
- `resources/js/Pages/Admin/Products/Edit.vue`
- `resources/js/Pages/Admin/Products/Index.vue`
- `resources/js/Pages/Admin/ProfileStore/Edit.vue`
- `resources/js/Pages/Customer/Wishlist.vue`

#### Configuration
- `package.json` – removed `daisyui` dependency
- `tailwind.config.js` – removed DaisyUI plugin and theme
- `tests/Feature/RemoveApiLayerTest.php` – added test for `/about` route removal

### UX improvements in the landing page
- **Hero section** – concise value proposition with two CTAs (shop now / learn about us)
- **Category discovery** – clickable category cards with hover effects
- **Featured products** – preserved existing product data and card component
- **Brand story section** – merged About content into an accessible `id="about"` anchor
- **Service benefits** – four‑column responsive grid of trust signals
- **Final CTA** – strong conversion‑oriented prompt to browse the collection
- **Accessibility** – semantic headings, descriptive image alternatives, keyboard‑visible focus states, reduced‑motion respect

### DaisyUI removal details
All DaisyUI-specific classes (`btn`, `dropdown`, `toast`, `alert`, `join`, `badge`, `form-control`, `label-text`, `input‑bordered`, `textarea‑bordered`, `select‑bordered`, `input‑error`, `textarea‑error`, `bg‑base‑*`, `text‑base‑content`, `border‑base‑*`) were replaced with Tailwind‑only utility classes that maintain the same visual hierarchy and interactive states.

### Verification
- `npm run build` succeeded without errors
- Package dependency tree updated and `node_modules` cleaned
- Routes tested manually in browser (homepage, `/shop`, `/wishlist`, admin pages) confirm no missing styles
- All components remain fully functional with consistent branding and spacing

### Next steps
- Deploy changes and monitor for any visual regression
- Consider adding a small JavaScript snippet to smooth‑scroll to the `#about` anchor if desired
- Update any external documentation that references the `/about` page