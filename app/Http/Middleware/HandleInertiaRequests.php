<?php

namespace App\Http\Middleware;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $cart = session('cart', []);
        $itemCount = 0;
        $subtotal = 0;

        // Cart format is: [product_id => quantity]
        if (is_array($cart)) {
            foreach ($cart as $productId => $quantity) {
                if (is_int($quantity) || is_numeric($quantity)) {
                    $itemCount += (int) $quantity;
                }
            }
            
            // Get product prices if cart has items
            if ($itemCount > 0 && !empty($cart)) {
                $products = Product::whereIn('id', array_keys($cart))->pluck('price', 'id');
                foreach ($cart as $productId => $quantity) {
                    if (isset($products[$productId])) {
                        $subtotal += $products[$productId] * $quantity;
                    }
                }
            }
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'cart' => [
                'item_count' => $itemCount,
                'subtotal' => round($subtotal, 2),
            ],
        ]);
    }
}
