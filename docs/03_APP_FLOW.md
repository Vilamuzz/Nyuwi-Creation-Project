# App Flow — Nyuwi Creation

## Screen Inventory

| Screen | Route | Auth Required | Role | Purpose |
|--------|-------|---------------|------|---------|
| Landing Page | `/` | No | Guest | Marketing, featured products, CTA to shop |
| About Page | `/about` | No | Guest | Store information |
| Shop / Product Catalog | `/shop` | No | Guest | Browse products, search, filter by category |
| Product Detail | `/product/{slug}` | No | Guest | View product details, add to cart/wishlist |
| Admin Registration | `/admin/register` | No | Guest | Create admin account (store owner) |
| Login | `/login` | No | Guest | User authentication |
| Register | `/register` | No | Guest | Customer registration |
| Customer Profile | `/customer/profile` | Yes | Customer | View order history, profile info |
| Shopping Cart | `/cart` | Yes | Customer | Manage cart items, quantities, view totals |
| Checkout | `/checkout` | Yes | Customer | Shipping address, payment method, order review |
| Wishlist | `/wishlist` | Yes | Customer | Saved products for later |
| Order History | `/orders/info` | Yes | Customer | List all orders with status |
| Order Detail | `/orders/{id}` | Yes | Customer | View order items, shipping, payment proof |
| Upload Payment Proof | `/orders/upload-proof` | Yes | Customer | Upload bank transfer screenshot |
| Product Reviews (User) | `/user/reviews` | Yes | Customer | Manage submitted reviews |
| Product Reviews (Product) | `/products/{id}/reviews` | Yes | Customer | View/write reviews for specific product |
| Admin Dashboard | `/dashboard` | Yes | Admin | Sales overview, recent orders, stats |
| Product Management | `/inventory` | Yes | Admin | CRUD products, categories, images |
| Order Management (Admin) | `/orders` | Yes | Admin | View all orders, update status, process |
| Order Detail (Admin) | `/orders/{id}` | Yes | Admin | View full order, customer info, items |
| Store Profile Settings | `/profile-store/{name}` | Yes | Admin | Configure store name, logo, branding |
| Admin Profile | `/profile` | Yes | Admin | Manage admin account settings |
| Database Management | `/data` | Yes | Admin | Database tools, backups |

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
   → Selects/enters shipping address (Indonesian regions)
   → Shipping cost calculated via API
   → Chooses payment method (manual transfer)
   → Submits order → Order created, redirects to order detail

6. Order Detail `/orders/{id}`
   → Sees order summary, payment instructions
   → Uploads payment proof → Admin notified
   → Waits for admin confirmation

7. Order Complete
   → Admin verifies payment → Status: "Completed"
   → Customer sees confirmed status
   → Can leave review after delivery
```

### Flow 2: Admin Product Management
```
1. Admin logs in → `/dashboard`
   → Sees sales stats, recent orders, low stock alerts

2. Navigates to `/inventory` (ProductController@index)
   → Lists all products with stock, status
   → Clicks "Create Product" → `/inventory/create`

3. Create Product Form
   → Fills: name, slug, description, price, stock, category
   → Uploads product images (Intervention/Image processing)
   → Submits → Product saved, redirects to index

4. Edit Product `/inventory/{id}/edit`
   → Updates details, manages images
   → Toggles active/inactive status
   → Saves changes
```

### Flow 3: Admin Order Processing
```
1. Admin views `/orders` (OrderController@show)
   → Sees all orders with status badges (Pending, Processing, Completed, Cancelled)

2. Clicks order → `/orders/{id}` (detail)
   → Views customer info, items, shipping address, payment proof

3. Updates order status:
   - Pending → Processing (admin confirms payment proof)
   - Processing → Completed (shipped/delivered)
   - Any → Cancelled (with reason)

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
1. Guest visits `/admin/register` (guest only)
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

### Flow 5: Wishlist & Reviews
```
Wishlist:
1. Customer on product page clicks "Add to Wishlist" (heart icon)
2. API POST `/api/wishlist/add` → Saved to wishlist
3. Customer views `/wishlist` → Sees saved products
4. Can move to cart or remove

Reviews:
1. Customer on completed order detail page
2. Clicks "Write Review" for eligible products
3. Submits rating (1-5) + comment
4. Review appears on product page for other customers
```

---

## Edge Cases & Error Handling

| Scenario | Handling |
|----------|----------|
| **Expired session** | Redirect to `/login` with `intended` URL, show toast "Session expired, please log in again" |
| **Empty cart checkout** | Middleware `check.cart` blocks access, redirects to `/cart` with error |
| **Invalid product slug** | 404 page with "Product not found" and link back to shop |
| **Out of stock during checkout** | API validates stock before order creation, shows error if insufficient |
| **Failed payment proof upload** | Client-side validation (file type/size), server validation, retry without losing form |
| **Unauthorized admin access** | Middleware `admin` redirects non-admins to `/` with 403 |
| **Region API unavailable** | Fallback to manual text input for address fields |
| **Image upload exceeds limit** | Client: 5MB max, Server: Intervention/Image rejects, shows "Image too large" |
| **Network error on cart API** | Toast notification "Failed to update cart", retry button, local state preserved |
| **Concurrent order on same product** | Database row locking on stock decrement, second request gets "Out of stock" error |

---

## Navigation Structure

```
Guest Navigation:
├── Landing (/)
├── About (/about)
├── Shop (/shop)
├── Product Detail (/product/{slug})
├── Login (/login)
├── Register (/register)
└── Admin Register (/admin/register)

Customer Navigation (authenticated):
├── Shop (/shop)
├── Product Detail (/product/{slug})
├── Cart (/cart) → Checkout (/checkout)
├── Wishlist (/wishlist)
├── Orders
│   ├── Order List (/orders/info)
│   ├── Order Detail (/orders/{id})
│   └── Upload Proof (/orders/upload-proof)
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
├── Order Management (/orders)
│   ├── List (/orders)
│   ├── Detail (/orders/{id})
│   └── Update (/orders/{id})
├── Store Settings (/profile-store/{name})
├── Profile (/profile)
└── Database Tools (/data)
```