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
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'role' => $request->user()->role,
                    'is_admin' => $request->user()->isAdmin(),
                    'is_customer' => $request->user()->isCustomer(),
                    'rewards_balance' => $request->user()->isCustomer() && $request->user()->customer
                        ? $request->user()->customer->rewards_balance
                        : 0,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
            ],
            'cart_count' => fn () => $this->getCartCount($request),
            'geo' => [
                'allowed_zips' => config('geo.allowed_zips', []),
                'service_area' => config('geo.service_area', 'Flagler County, FL'),
            ],
        ];
    }

    protected function getCartCount(Request $request): int
    {
        $cart = $request->session()->get('cart', []);
        return array_sum($cart);
    }
}
