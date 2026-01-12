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
            $query->search($request->search);
        }

        // In-stock filter
        if ($request->boolean('in_stock')) {
            $query->in_stock();
        }

        // Sorting
        $sortField = $request->get('sort', 'name');
        $allowedSorts = ['name', 'price', 'created_at'];

        if (!in_array($sortField, $allowedSorts)) {
            $direction = $sortField === 'created_at' ? 'desc' : 'asc';
            $query->orderBy($sortField, $direction);
        }

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
