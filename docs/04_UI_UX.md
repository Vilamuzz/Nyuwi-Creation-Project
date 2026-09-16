# UI/UX Brief — Nyuwi Creation

## Design Principles
- Make browsing and purchasing products feel simple, trustworthy, and fast.
- Prioritize product imagery, price, availability, and purchase actions.
- Keep customer-facing flows welcoming and visually rich; keep admin tools efficient and data-focused.
- Use clear Bahasa Indonesia labels for customer and admin workflows, with concise supporting English only where already established.
- Design mobile-first and progressively enhance for tablet and desktop layouts.

## Visual Style
- **Vibe:** Warm, clean, and approachable e-commerce; product imagery is the main visual focus.
- **Theme:** DaisyUI `autumn` theme.
- **Primary color:** Orange (`orange-500` for primary CTAs; hover states use darker orange shades).
- **Accent:** Green for WhatsApp support and positive/success actions; yellow for ratings; red for destructive and low-stock states.
- **Neutral palette:** White surfaces with gray text, borders, and page backgrounds.
- **Font:** Figtree, followed by the Tailwind default sans-serif stack.
- **Border radius:** `rounded-lg` for cards and images; `rounded-full` for prominent pill buttons and floating support action.
- **Elevation:** Subtle `shadow` / `shadow-lg` on cards, modals, and floating elements.
- **Dark mode:** Not in v1. The application uses a light-only autumn theme.
- **Currency:** Format all prices as Indonesian Rupiah (`IDR`) using `id-ID` locale formatting.

## Component Library
- **Base UI:** Tailwind CSS v3 + DaisyUI v4 (`autumn` theme)
- **Icons:** `lucide-vue-next`; use familiar icons with accessible labels or tooltips
- **Navigation:** Inertia `Link` components for internal navigation
- **Forms:** Existing reusable form components (`TextInput`, `InputLabel`, `InputError`, `PrimaryButton`, `SecondaryButton`, `DangerButton`, `Checkbox`)
- **Dialogs:** Reusable `Modal.vue` component for confirmation and focused tasks
- **Notifications:** DaisyUI alert/toast-style feedback; success feedback should auto-dismiss after 3 seconds when it does not require action
- **Support:** Persistent floating WhatsApp action on customer-facing pages, positioned bottom-right without obscuring primary controls

## Layouts & Navigation

### Customer Layout
- Customer pages use a shared top navigation bar, main content area, footer, and floating WhatsApp support action.
- Navigation must expose Shop, Cart, Wishlist, Orders/Profile, and authentication actions based on login state.
- The cart indicator should show the current item count.
- On small screens, navigation must collapse into a touch-friendly menu; primary shopping actions remain easy to reach.
- Use responsive grids: one column on mobile, expanding to 3–4 columns for category and product cards on wider screens.

### Admin Layout
- Admin pages use a persistent left sidebar and top navbar.
- The sidebar can collapse; its state is retained in `localStorage`.
- Main content offsets correctly for expanded (`230px`) and collapsed (`64px`) sidebar states.
- Use page titles in the top navbar and group related content into cards, tables, and clearly labeled forms.
- Place destructive actions away from primary actions and require confirmation through a modal.

### Guest/Auth Layout
- Authentication screens center a branded logo above a focused, readable form card.
- Keep forms within a narrow max-width layout and provide a clear link back to the storefront.

## Key Screens

### Landing Page (`/`)
- **Hero:** Full-height product/lifestyle background, dark overlay for legibility, headline, supporting copy, and orange “Shop Now” CTA.
- **Category section:** Up to three image-led category cards with title; each card leads to a filtered shop experience when available.
- **Featured products:** Responsive product-card grid, showing image, name, category, IDR price, rating, and review count.
- **Social proof/promotion:** A high-contrast promotional section reinforcing product selection and linking to the shop.
- **Footer and support:** Footer contains store links/contact information; WhatsApp floating button offers immediate help.

### Shop / Product Catalog (`/shop`)
- Show searchable and filterable product grid with category filtering and sorting.
- Keep filters visible on desktop and accessible through a drawer, sheet, or collapsible area on mobile.
- Product cards must have a consistent image ratio, accessible image alt text, product name, price, rating, stock state where relevant, and direct product-detail link.
- Preserve selected filters and search terms when navigating back from a product detail page.

### Product Detail (`/product/{slug}`)
- Product image gallery is prominent, with image fallback when a product image is unavailable.
- Display name, category, IDR price, rating/review summary, description, availability, quantity controls, and primary “Add to Cart” action above the fold on desktop where possible.
- Provide a wishlist control adjacent to the cart action for authenticated customers.
- Clearly distinguish disabled/out-of-stock purchase controls from available products.
- Reviews appear below product details, with rating summary and a clear review CTA only for eligible authenticated customers.

### Cart (`/cart`)
- Present cart items in a clear list/table with thumbnail, name, unit price, quantity controls, line total, and remove action.
- Show order subtotal and a persistent checkout CTA.
- Quantity changes and removal provide immediate feedback; retain state and offer retry on API failure.
- Empty state includes a friendly message and “Continue Shopping” CTA.

### Checkout (`/checkout`)
- Use a linear, distraction-free flow: shipping address → shipping calculation → payment instructions/method → order summary → submit.
- Indonesian region inputs progress from province to regency, district, and village.
- Recalculate shipping visibly after destination changes and present total cost in IDR.
- Summarize products, quantities, shipping cost, total, and payment instructions before submission.
- Keep submitted payment proof requirements clear: accepted file types, size limit, and upload status.

### Customer Orders & Profile (`/customer/profile`, `/orders/*`)
- Order history uses status badges with text labels; color is never the only status indicator.
- Order detail shows order timeline/status, items, address, payment status, tracking information where available, and payment-proof upload action when required.
- Profile screens group personal information and account actions separately.

### Wishlist (`/wishlist`)
- Reuse product-card visual language.
- Each saved item provides “Add to Cart” and remove actions.
- Empty state points customers back to product browsing.

### Admin Dashboard (`/dashboard`)
- Welcome the signed-in admin by name.
- Present top-selling products, highest-rated products, and low-stock products as separate responsive cards.
- Each product entry includes image, name, relevant metric, and IDR price.
- Low stock uses a visible warning message in addition to red styling.

### Admin Inventory (`/inventory`)
- Use a searchable, sortable product table/list with thumbnail, name, category, price, stock, status, and actions.
- Create/edit forms group product basics, pricing/inventory, category, and image upload fields.
- Product image preview, upload validation, and save/cancel actions must be explicit.
- Destructive delete actions require a confirmation modal and explain the impact.

### Admin Orders (`/orders`)
- List orders with order reference, customer, date, total, payment state, and fulfillment status.
- Order detail prioritizes payment proof, customer address, line items, and status update actions.
- Use a deliberate status transition control; avoid accidental completion or cancellation.

### Store & Admin Profile Settings
- Group store identity, contact details, branding assets, and account settings into clearly labeled sections.
- Show image/logo previews before saving.
- Display inline validation messages next to the relevant inputs.

## Content & Interaction Standards
- Use short, action-oriented CTA labels: “Belanja Sekarang”, “Tambah ke Keranjang”, “Lanjut ke Checkout”, “Simpan Perubahan”, “Hapus”.
- Confirm irreversible actions, including deleting products, removing account data, and cancelling orders.
- Do not rely on icons alone: provide visible text, `aria-label`, or tooltip for icon-only controls.
- Use Indonesian Rupiah consistently; do not show ambiguous decimal formatting.
- Product imagery must use meaningful alt text based on product/category names.

## States (Never Skip These)

| State | Required UI |
|-------|-------------|
| **Loading** | Prefer layout-preserving skeletons for product cards, tables, and dashboard cards. Existing spinners may be used only for short inline operations. |
| **Empty** | Friendly contextual message, simple illustration/icon where available, and a relevant CTA (for example, “Belanja Sekarang” for an empty cart). |
| **Error** | Clear inline or alert message describing what failed, with retry action when safe. Preserve entered form data and current cart state. |
| **Success** | Toast/alert confirmation with clear outcome; auto-dismiss after 3 seconds unless user action is needed. |
| **Validation** | Display `InputError` directly below the affected field; keep labels visible and do not use placeholders as labels. |
| **Disabled / unavailable** | Visually and programmatically disable unavailable actions, explain why (for example, “Stok habis”), and avoid dead-end clicks. |
| **Authorization** | Redirect unauthorized users safely and show a concise explanation rather than exposing protected content. |

## Responsive Requirements
- Build mobile-first with a minimum touch target of 44 × 44 px for interactive controls.
- Avoid fixed-width layouts that cause horizontal scrolling on screens below 375 px.
- Product and category grids: 1 column on mobile, 2 on small tablet where appropriate, 3–4 on desktop.
- Checkout summary and cart totals remain visible and readable without requiring horizontal scroll.
- Floating WhatsApp support button must not overlap checkout, cart, or modal actions on small screens.

## Accessibility
- All interactive elements must be keyboard reachable with a visible focus state.
- Maintain WCAG 2.1 AA contrast, especially orange buttons, gray text, and status badges.
- Every form field has a persistent visible label and an associated error message.
- Use semantic headings in logical order and landmarks (`header`, `main`, `nav`, `footer`).
- Provide alternative text for informative images; mark decorative images appropriately.
- Modals trap focus, close with `Escape`, and return focus to their trigger.
- Status changes, cart updates, and validation errors should be announced to assistive technologies where feasible.
- Never communicate an order or stock status using color alone.
