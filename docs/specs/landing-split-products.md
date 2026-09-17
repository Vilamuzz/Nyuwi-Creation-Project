# Landing Page: Split New Drops & Featured Products

## Goal
Replace the single `products` prop on the landing page with two distinct lists:
1. **newProducts** – newest products, ordered by creation date
2. **featuredProducts** – products ranked by total quantity sold (completed orders only)

This supports the UI requirement for separate horizontally-scrollable product rails with meaningful merchandising labels.

## Data Schema & Queries

### Completed Order Status
The order status field likely includes values like `completed`, `pending`, `cancelled`, etc. The `Order` model’s `status` column is used to filter for “completed” sales.

### Product Query (New Drops)
```php
Product::query()
    ->withCount('reviews as total_reviews')
    ->withAvg('reviews as average_rating', 'rating')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();
```

### Product Query (Produk Unggulan / Featured by Sales)
```php
Product::query()
    ->selectRaw('products.*, COALESCE(SUM(order_items.quantity), 0) as total_sold')
    ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
    ->leftJoin('orders', 'order_items.order_id', '=', 'orders.id')
    ->where('orders.status', 'completed') // only count completed orders
    ->withCount('reviews as total_reviews')
    ->withAvg('reviews as average_rating', 'rating')
    ->groupBy('products.id')
    ->orderBy('total_sold', 'desc')
    ->orderBy('products.created_at', 'desc')
    ->limit(10)
    ->get();
```

## Inertia Props
Update `ProductController::landingPage()` to pass:

```php
return Inertia::render('Customer/LandingPage', [
    'canLogin'      => Route::has('login'),
    'canRegister'   => Route::has('register'),
    'categories'    => Category::all(),
    'newProducts'   => $newProducts,
    'featuredProducts' => $featuredProducts,
]);
```

**Note:** `$newProducts` and `$featuredProducts` must include the same computed rating/review fields as before (`average_rating`, `total_reviews`) to keep the product card component compatible.

## Frontend Changes
- Update `LandingPage.vue`:
  - Replace single product grid sections with two horizontal scroll rails.
  - Add manual-scroll buttons (previous/next) for each rail.
  - Pass correct product list to each rail.
  - Remove the duplicate second “Produk Unggulan” section.
- Update `Product.vue`:
  - Increase card size and add optional category label.
  - Add wishlist interaction improvements (visible button).
  - Show availability, colors, sizes if provided.

## Test Updates
- Modify `tests/Feature/RemoveApiLayerTest.php` to assert the presence of both `newProducts` and `featuredProducts`.
- Ensure existing tests still pass after the controller change.

## Migration Impact
No database migration needed. The queries rely on existing `orders` and `order_items` tables; if no completed orders exist, `total_sold` will be `0` and the featured list will be ordered by creation date as fallback.

## Edge Cases
- **No completed orders:** Featured list will be empty (or fall back to all products sorted by creation date). Consider a fallback to random or highly rated products if needed.
- **Missing images:** Use existing fallback image chain (local → Unsplash).
- **Performance:** Both queries use `limit(10)`; ensure `indexes` exist on `order_items.product_id`, `orders.status`, `products.created_at`.

## Changelog
Add entry after implementation.