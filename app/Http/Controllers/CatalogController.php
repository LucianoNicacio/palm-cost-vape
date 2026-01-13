<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        // Start building query
        $query = Product::active()->with(['category']);

        // Filter by category
        if ($request->filled('category')) {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        // Search filter
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // In-stock filter
        if ($request->boolean('in_stock')) {
            $query->inStock();
        }

        // Sorting
        $sort = $request->get('sort', 'name');
        match($sort) {
            'price' => $query->orderBy('price', 'asc'),
            'created_at' => $query->orderBy('created_at', 'desc'),
            default => $query->orderBy('name', 'asc'),
        };

        // Return paginated results
        return Inertia::render('Catalog/Index', [
            'products' => $query->paginate(24)->withQueryString(),

            'categories' => Category::active()->sorted()
                ->withCount(['products' => fn($q) => $q->active()])
                ->get(),

            'filters' => $request->only(['category', 'search', 'in_stock', 'sort']),

            'taxRate' => config('app.tax_rate', 0.06),

        ]);
    }
}
