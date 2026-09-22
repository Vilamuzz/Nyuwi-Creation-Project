<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CartService
{
    private const SESSION_KEY = 'cart';

    /**
     * Get the current cart items for the request.
     *
     * Logged-in users get their carts from the database, guests get the
     * session-backed cart so the drawer works without authentication.
     *
     * @return array<int, array{id: int|string, product_id: int, quantity: int, price: string, size: string|null, color: string|null, product: \App\Models\Product|null}>
     */
    public function items(Request $request): array
    {
        if ($request->user()) {
            return $this->databaseItems($request);
        }

        return $this->guestItems($request);
    }

    /**
     * Build the summary payload for a set of cart items.
     *
     * @param  array<int, array{quantity: int, price: string|float}>  $items
     * @return array{subtotal: float, totalItems: int, itemCount: int}
     */
    public function summarize(array $items): array
    {
        return [
            'subtotal' => collect($items)->sum(fn ($item) => (float) $item['price'] * $item['quantity']),
            'totalItems' => collect($items)->sum('quantity'),
            'itemCount' => count($items),
        ];
    }

    /**
     * Add a product to the cart, merging with a matching existing item.
     *
     * @return string|null An error message when the quantity exceeds stock.
     */
    public function add(Request $request, Product $product, int $quantity, ?string $size, ?string $color): ?string
    {
        if ($request->user()) {
            return $this->addDatabaseItem($request, $product, $quantity, $size, $color);
        }

        return $this->addGuestItem($request, $product, $quantity, $size, $color);
    }

    /**
     * Update the quantity of a cart item.
     *
     * @return string|null An error message when the quantity exceeds stock.
     */
    public function update(Request $request, int|string $id, int $quantity): ?string
    {
        if ($request->user()) {
            $cartItem = Cart::where('id', $id)
                ->where('user_id', $request->user()->getAuthIdentifier())
                ->firstOrFail();

            $product = Product::findOrFail($cartItem->product_id);

            if ($quantity > $product->stock) {
                return "Jumlah melebihi stok yang tersedia (stok: {$product->stock}).";
            }

            $cartItem->update(['quantity' => $quantity]);

            return null;
        }

        $items = $this->getSessionItems($request);

        $key = $this->findGuestItemKey($items, $id);
        if ($key === null) {
            abort(404);
        }

        $product = Product::findOrFail($items[$key]['product_id']);

        if ($quantity > $product->stock) {
            return "Jumlah melebihi stok yang tersedia (stok: {$product->stock}).";
        }

        $items[$key]['quantity'] = $quantity;
        $this->putSessionItems($request, $items);

        return null;
    }

    /**
     * Remove a cart item.
     */
    public function destroy(Request $request, int|string $id): void
    {
        if ($request->user()) {
            Cart::where('id', $id)
                ->where('user_id', $request->user()->getAuthIdentifier())
                ->firstOrFail()
                ->delete();

            return;
        }

        $items = $this->getSessionItems($request);

        $key = $this->findGuestItemKey($items, $id);
        if ($key === null) {
            abort(404);
        }

        unset($items[$key]);
        $this->putSessionItems($request, array_values($items));
    }

    /**
     * Empty the guest cart from the session.
     */
    public function clear(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
    }

    /**
     * Cart items stored in the database for a logged-in user.
     */
    private function databaseItems(Request $request): array
    {
        return Cart::with('product')
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->get()
            ->map(fn (Cart $cart) => [
                'id' => $cart->id,
                'product_id' => $cart->product_id,
                'quantity' => $cart->quantity,
                'price' => $cart->price,
                'size' => $cart->size,
                'color' => $cart->color,
                'product' => $cart->product,
            ])
            ->values()
            ->all();
    }

    /**
     * Cart items stored in the session for a guest.
     */
    private function guestItems(Request $request): array
    {
        $items = $this->getSessionItems($request);

        $products = Product::whereIn('id', collect($items)->pluck('product_id'))
            ->get()
            ->keyBy('id');

        return collect($items)
            ->map(function (array $item) use ($products) {
                return [
                    'id' => $item['id'],
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => (string) $item['price'],
                    'size' => $item['size'] ?? null,
                    'color' => $item['color'] ?? null,
                    'product' => $products->get($item['product_id']),
                ];
            })
            ->filter(fn (array $item) => $item['product'] !== null)
            ->values()
            ->all();
    }

    private function addDatabaseItem(Request $request, Product $product, int $quantity, ?string $size, ?string $color): ?string
    {
        $existingCartItem = Cart::where([
            'user_id' => $request->user()->getAuthIdentifier(),
            'product_id' => $product->id,
            'size' => $size,
            'color' => $color,
        ])->first();

        $totalQuantity = $quantity;
        if ($existingCartItem) {
            $totalQuantity += $existingCartItem->quantity;
        }

        if ($totalQuantity > $product->stock) {
            return "Jumlah melebihi stok yang tersedia (stok: {$product->stock}).";
        }

        if ($existingCartItem) {
            $existingCartItem->update([
                'quantity' => $totalQuantity,
                'price' => $product->price,
            ]);

            return null;
        }

        Cart::create([
            'user_id' => $request->user()->getAuthIdentifier(),
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price' => $product->price,
            'size' => $size,
            'color' => $color,
        ]);

        return null;
    }

    private function addGuestItem(Request $request, Product $product, int $quantity, ?string $size, ?string $color): ?string
    {
        $items = $this->getSessionItems($request);

        $existingKey = collect($items)->search(fn (array $item) =>
            $item['product_id'] === $product->id
            && ($item['size'] ?? null) === $size
            && ($item['color'] ?? null) === $color
        );

        $totalQuantity = $quantity;
        if ($existingKey !== false) {
            $totalQuantity += $items[$existingKey]['quantity'];
        }

        if ($totalQuantity > $product->stock) {
            return "Jumlah melebihi stok yang tersedia (stok: {$product->stock}).";
        }

        if ($existingKey !== false) {
            $items[$existingKey]['quantity'] = $totalQuantity;
        } else {
            $items[] = [
                'id' => Str::random(16),
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => (string) $product->price,
                'size' => $size,
                'color' => $color,
            ];
        }

        $this->putSessionItems($request, $items);

        return null;
    }

    /**
     * Find the index of a guest item by its session key.
     *
     * @param  array<int, array{id: string}>  $items
     */
    private function findGuestItemKey(array $items, int|string $id): int|null
    {
        $key = collect($items)->search(fn (array $item) => $item['id'] === (string) $id);

        return $key === false ? null : (int) $key;
    }

    private function getSessionItems(Request $request): array
    {
        return $request->session()->get(self::SESSION_KEY, []);
    }

    private function putSessionItems(Request $request, array $items): void
    {
        $request->session()->put(self::SESSION_KEY, $items);
    }
}