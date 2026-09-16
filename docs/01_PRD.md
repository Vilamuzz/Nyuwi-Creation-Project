# PRD — Nyuwi Creation

## Problem
Small to medium e-commerce businesses in Indonesia need a simple, customizable platform to sell products online with inventory management, order tracking, and Indonesian market features (regional data, shipping calculation).

## Target User
- **Primary**: Store owner/manager (Admin) — needs to manage products, orders, inventory, and store settings
- **Secondary**: End customers — browse products, make purchases, track orders, and leave reviews

## Core Value Proposition
A tailored e-commerce solution with built-in Indonesian region data and shipping calculation, offering a streamlined shopping experience without the complexity of enterprise platforms.

## Features (MVP Scope)

| # | Feature | Priority | Acceptance Criteria |
|---|---------|----------|---------------------|
| 1 | Landing page | P0 | Displays featured products, store branding, and navigation to shop |
| 2 | Shop page | P0 | Product catalog with search, filter by category, and sorting |
| 3 | Product detail page | P0 | Shows product images, description, price, and add-to-cart button |
| 4 | User registration & login | P0 | Customer and admin registration, secure authentication |
| 5 | Cart management | P0 | Add, update quantity, remove items, view cart total |
| 6 | Checkout flow | P0 | Select shipping address, calculate shipping cost, complete order |
| 7 | Order management (Customer) | P0 | View order history, order details, upload payment proof |
| 8 | Admin dashboard | P0 | Overview of sales, orders, and key metrics |
| 9 | Product/inventory management | P0 | Create, read, update, delete products and categories |
| 10 | Order management (Admin) | P0 | View all orders, update order status, process orders |
| 11 | Customer profile | P1 | View and update profile information |
| 12 | Wishlist management | P1 | Save products for later, remove from wishlist |
| 13 | Store profile settings | P1 | Customize store name, logo, and branding |
| 14 | Indonesian region data | P1 | Province, regency, district, village data for addresses |
| 15 | Shipping calculation | P1 | Calculate shipping cost based on destination |
| 16 | Product reviews | P2 | Customers can rate and review purchased products |

## Out of Scope (v1)
- Payment gateway integration (manual payment proof upload for now)
- Multi-vendor/marketplace support
- Advanced analytics and reporting
- Mobile native app (responsive web only)
- Email notifications and marketing
- Discount/coupon system
- Inventory alerts and low-stock notifications
- Blog/content management
- Social media integration
- Multi-language support

## Success Metrics
- Admin can add product → customer finds → purchases in < 3 minutes
- Admin processes order → customer receives confirmation in < 2 minutes
- 95% of page loads complete in < 2 seconds
- Customer checkout flow has < 5% abandonment rate
- 100% of form validations provide clear error messages

## Open Questions
1. When should payment gateway integration be planned (v2)?
2. What email notifications are required (order confirmation, shipping updates)?
3. Are there SEO requirements beyond basic meta tags?
4. What reporting/analytics does the admin need in v2?
5. Should product images support multiple photos per product?