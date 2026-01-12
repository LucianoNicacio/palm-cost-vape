<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);
        $cartItems = $this->getCartItemsWithProducts($cart);
        $totals = $this->calculateTotals($cartItems);

        return Inertia::render('Cart/Index', [
            'cartItems' => $cartItems,
            'totals' => $totals,
            'taxRate' => config('app.tax_rate', 0.06),
        ]);
    }

    public function add(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        // Check stock availability
        if ($product->track_inventory && $product->stock < $request->quantity) {
            return back()->with('error', 'Not enough stock available.');
        }

        // Get current cart
        $cart = session('cart', []);

        // Add or update quantity
        $currentQty = $cart[$product->id] ?? 0;
        $cart[$product->id] = $currentQty + $request->quantity;

        // Save to session
        session(['cart' => $cart]);

        return back()->with('success', "{$product->name} added to cart!");
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0|max:99',
        ]);

        $cart = session('cart', []);

        if ($request->quantity <= 0) {
            unset($cart[$product->id]);
        } else {
            $cart[$product->id] = $request->quantity;
        }

        session(['cart' => $cart]);

        return back()->with('success', 'Cart updated!');
    }

    public function remove(Product $product)
    {
        $cart = session('cart', []);
        unset($cart[$product->id]);
        session(['cart' => $cart]);

        return back()->with('success', 'Item removed from cart.');
    }

    public function clear()
    {
        session()->forget('cart');

        return back()->with('success', 'Cart cleared.');
    }

    // ==========================================
    // PRIVATE HELPER METHODS
    // ==========================================

    private function getCartItemsWithProducts(array $cart): array
    {
        if (empty($cart)) {
            return [];
        }

        // Get all products in one query
        $products = Product::whereIn('id', array_keys($cart))
            ->active()
            ->with('category')
            ->get()
            ->keyBy('id');

        // Build cart items array
        $items = [];
        foreach ($cart as $productId => $quantity) {
            if (isset($products[$productId])) {
                $product = $products[$productId];
                $items[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'pricing' => $product->calculateItemPricing($quantity),
                ];
            }
        }

        return $items;
    }

    private function calculateTotals(array $cartItems): array
    {
        $subtotal = 0;
        $taxAmount = 0;
        $totalItems = 0;

        foreach ($cartItems as $item) {
            $subtotal += $item['pricing']['subtotal'];
            $taxAmount += $item['pricing']['tax_amount'];
            $totalItems += $item['quantity'];
        }

        return [
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($taxAmount, 2),
            'total_price' => round($subtotal + $taxAmount, 2),
            'item_count' => $totalItems,
        ];
    }

    // Static method for navbar cart count
    public static function getCartCount(): int
    {
        return array_sum(session('cart', []));
    }
}