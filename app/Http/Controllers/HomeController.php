<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        return Inertia::render('Home', [
            'featuredProducts' => Product::active()
                ->inStock()
                ->where('is_featured', true)
                ->with('category')
                ->take(8)
                ->get(),

            'categories' => Category::active()
                ->sorted()
                ->withCount([
                    'products' => fn($q) => $q->active()->inStock()
                ])
                ->get(),

            'storeInfo' => [
                'name' => 'Palm Coast Vape and Glassware',
                'address' => '29 Old Kings Rd N, Suite 2-A',
                'city' => 'Palm Coast, FL 32137',
                'phone' => '(386) 597-2838',
            ],

            'taxRate' => config('app.tax_rate', 0.06),
        ]);
    }
}