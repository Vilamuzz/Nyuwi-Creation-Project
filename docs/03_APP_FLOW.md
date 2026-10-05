# App Flow — Nyuwi Creation

## Screen Inventory

| Screen                    | Route                    | Auth Required | Role     | Purpose                                                                   |
| ------------------------- | ------------------------ | ------------- | -------- | ------------------------------------------------------------------------- |
| Landing Page              | `/`                      | No            | Guest    | Marketing, featured products, CTA to shop                                 |
| About Page                | `/about`                 | No            | Guest    | Store information                                                         |
| Shop / Product Catalog    | `/shop`                  | No            | Guest    | Browse products, search, filter by category                               |
| Product Detail            | `/product/{slug}`        | No            | Guest    | View product details and add to cart                                      |
| Login                     | `/login`                 | No            | Guest    | User authentication                                                       |
| Register                  | `/register`              | No            | Guest    | Customer registration                                                     |
| Customer Profile          | `/customer/profile`      | Yes           | Customer | View order history, profile info                                          |
| Shopping Cart             | `/cart`                  | Yes           | Customer | Manage cart items, quantities, view totals                                |
| Checkout                  | `/checkout`              | Yes           | Customer | Shipping address, payment via Midtrans Snap, order review                 |
| Order History             | `/customer/orders/info`  | Yes           | Customer | List all orders with status                                               |
| Order Detail              | `/customer/orders/{id}`  | Yes           | Customer | View order items, shipping, payment status                                |
| Product Reviews (User)    | `/user/reviews`          | Yes           | Customer | Manage submitted reviews                                                  |
| Product Reviews (Product) | `/products/{id}/reviews` | Yes           | Customer | View/write reviews for specific product                                   |
| Admin Dashboard           | `/dashboard`             | Yes           | Admin    | Sales overview, recent orders, stats                                      |
| Product Management        | `/inventory`             | Yes           | Admin    | CRUD products, categories, images                                         |
| Order Management (Admin)  | `/admin/orders`          | Yes           | Admin    | View all orders, update status, process                                   |
| Order Detail (Admin)      | `/admin/orders/{id}`     | Yes           | Admin    | View full order, customer info, items, payment transactions               |
| Store Profile Settings    | `/profile-store/{name}`  | Yes           | Admin    | Configure store name, logo, branding                                      |
| Admin Profile             | `/profile`               | Yes           | Admin    | Manage admin account settings                                             |
| Database Management       | `/data`                  | Yes           | Admin    | Database backup tools (single admin account; access scope accepted as-is) |

---

## Key Flows

### Flow 1: Customer Discovery & Purchase

```
1. Guest lands on `/` (Landing Page)
   → Sees featured products, store branding
   → Clicks "Shop Now" → `/shop`

2. Shop Page `/shop`
   → Browse products with search/filter
   → Clicks product → `/product/{slug}`

3. Product Detail `/product/{slug}`
   → Views images, description, price, reviews
   → Clicks "Add to Cart" → Cart updated (API)

4. Cart Page `/cart`
   → Reviews items, updates quantities
   → Clicks "Checkout" → `/checkout`

5. Checkout `/checkout` (middleware: check.cart)
   → Selects/enters shipping address (Indonesian regions via RajaOngkir)
   → Shipping cost calculated via API
   → Order created on submit:
     - Stock is decremented immediately (reserved) for each item, with row locking
     - Order created with payment_status: unpaid
   → Midtrans Snap token issued, Snap popup opens
   → Redirects to `/customer/orders/{id}`

6. Order Detail `/customer/orders/{id}`
   → Sees order summary, live payment status
   → Payment status updates automatically once Midtrans webhook confirms payment
     (no manual proof upload — payment_status flips from webhook notification)

7. Order Complete
   → Webhook confirms settlement → payment_status: paid, status: checking
   → Admin processes order → Status: processing → shipped → completed
   → Customer sees status update on their order page
   → Can leave review after delivery
```

### Flow 1b: Payment Failure / Abandonment

```
1. Customer closes Snap popup without paying, or payment is denied/expired
   → Midtrans webhook (or scheduled expiry job for no-webhook abandonment)
     sets payment_status: failed / expired
   → Reserved stock is restored (incremented back) for each order item
   → Order status set to cancelled (for expired) or left for customer retry (for failed, if retry is supported)

2. Scheduled job `orders:expire-unpaid` runs hourly
   → Finds orders still unpaid past the payment window (e.g. 24h)
   → Restores stock, marks payment_status: expired, status: cancelled
```

### Flow 2: Admin Product Management

```
1. Admin logs in → `/dashboard`
   → Sees sales stats, recent orders, low stock alerts

2. Navigates to `/inventory` (ProductController@index)
   → Lists all products with stock, status
   → Clicks "Create Product" → `/inventory/create`

3. Create Product Form
   → Fills: name, slug, description, price, stock, weight, category
   → Uploads product images (Intervention/Image processing)
   → Submits → Product saved, redirects to index

4. Edit Product `/inventory/{id}/edit`
   → Updates details, manages images
   → Toggles active/inactive status
   → Saves changes
```

### Flow 3: Admin Order Processing

```
1. Admin views `/admin/orders` (OrderController@index)
   → Sees all orders with status badges:
     waiting, checking, pending, processing, shipped, completed, cancelled

2. Clicks order → `/admin/orders/{id}` (detail)
   → Views customer info, items, shipping address, payment transaction history

3. Updates order status:
   - waiting → checking (set automatically once payment_status = paid via webhook)
   - checking → processing (admin confirms order, begins fulfillment)
   - processing → shipped (admin adds tracking number)
   - shipped → completed (delivery confirmed)
   - any (pre-shipped) → cancelled (with reason)

4. Customer automatically sees updated status on their order page
```

### Flow 4: Authentication & Registration

```
Customer Registration:
1. Guest clicks "Register" → `/register`
2. Fills form (name, email, password, password_confirmation)
3. Submits → Email verification sent (if enabled)
4. Verified → Redirected to `/shop` or intended page

Admin Registration:
1. Guest visits `/register` (guest only)
2. Fills admin details (name, email, password, store name)
3. Submits → Admin account created with `is_admin` flag
4. Auto-login → Redirected to `/dashboard`

Login (both roles):
1. Guest visits `/login`
2. Submits credentials
3. Validated → Role checked via middleware
   - Admin → `/dashboard`
   - Customer → intended page or `/shop`
```

### Flow 5: Reviews

```
Reviews:
1. Customer on completed order detail page
2. Clicks "Write Review" for eligible products
3. Submits rating (1-5) + comment
4. Review appears on product page for other customers

Security & Verification Guards :
- Server-side order ownership check (OrderCompleteRequest::authorize).
- Order status and payment validation (order must be 'shipped' or 'completed' and 'paid').
- Product eligibility verification (only items actually purchased in the order can be reviewed).
- Distinct validation & unique database constraint ['order_id', 'product_id'] to prevent duplicate reviews per order item.
```

---

## Edge Cases & Error Handling

| Scenario                                    | Handling                                                                                                                                                                                 |
| ------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Expired session**                         | Redirect to `/login` with `intended` URL, show toast "Session expired, please log in again"                                                                                              |
| **Empty cart checkout**                     | Middleware `check.cart` blocks access, redirects to `/cart` with error                                                                                                                   |
| **Invalid product slug**                    | 404 page with "Product not found" and link back to shop                                                                                                                                  |
| **Out of stock during checkout**            | Stock validated and decremented atomically (row-locked) at order creation; insufficient stock throws an error before payment is reached                                                  |
| **Order never paid (abandoned or expired)** | Scheduled hourly job (`orders:expire-unpaid`) marks order expired/cancelled and restores reserved stock                                                                                  |
| **Payment denied/cancelled via Midtrans**   | Webhook sets payment_status: failed, restores reserved stock                                                                                                                             |
| **Region API (RajaOngkir) unavailable**     | Request fails with a 500 error; no manual-entry fallback implemented                                                                                                                     |
| **Image upload exceeds limit**              | Client: 5MB max, Server: Intervention/Image rejects, shows "Image too large"                                                                                                             |
| **Network error on cart API**               | Toast notification "Failed to update cart", retry button, local state preserved                                                                                                          |
| **Concurrent order on same product**        | Implemented: database row locking (`lockForUpdate`) with ascending ID ordering ensures serialized stock decrement and prevents overselling; insufficient stock returns error immediately |
| **Midtrans webhook signature invalid**      | Rejected with 403, logged; order/payment state unchanged                                                                                                                                 |
| **Midtrans webhook for unresolvable order** | Logged as error, responds 200 to stop retry storm; requires manual investigation                                                                                                         |

---

## Navigation Structure

```
Guest Navigation:
├── Landing (/)
├── About (/about)
├── Shop (/shop)
├── Product Detail (/product/{slug})
├── Login (/login)
└── Register (/register)

Customer Navigation (authenticated):
├── Shop (/shop)
├── Product Detail (/product/{slug})
├── Cart (/cart) → Checkout (/checkout)
├── Orders
│   ├── Order List (/customer/orders/info)
│   └── Order Detail (/customer/orders/{id})
├── Profile (/customer/profile)
└── Reviews
    ├── My Reviews (/user/reviews)
    └── Product Reviews (/products/{id}/reviews)

Admin Navigation (authenticated):
├── Dashboard (/dashboard)
├── Inventory Management (/inventory)
│   ├── List (/inventory)
│   ├── Create (/inventory/create)
│   ├── Edit (/inventory/{id}/edit)
│   └── Show (/inventory/{id})
├── Order Management (/admin/orders)
│   ├── List (/admin/orders)
│   ├── Detail (/admin/orders/{id})
│   └── Update (/admin/orders/{id})
├── Store Settings (/profile-store/{name})
├── Profile (/profile)
└── Database Tools (/data)
```
