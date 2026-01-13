<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by status
        if ($request->filled('status')) {
            match ($request->status) {
                'active' => $query->where('is_active', true),
                'inactive' => $query->where('is_active', false),
                'featured' => $query->where('is_featured', true),
                'low_stock' => $query->where('track_inventory', true)->where('stock', '<=', 5)->where('stock', '>', 0),
                'out_of_stock' => $query->where('track_inventory', true)->where('stock', '<=', 0),
                default => null,
            };
        }

        return Inertia::render('Admin/Products/Index', [
            'products' => $query->orderBy('name')
                ->paginate(25)
                ->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
            'filters' => $request->only(['search', 'category', 'status']),
            'stats' => [
                'total' => Product::count(),
                'active' => Product::where('is_active', true)->count(),
                'featured' => Product::where('is_featured', true)->count(),
                'low_stock' => Product::where('track_inventory', true)->where('stock', '<=', 5)->where('stock', '>', 0)->count(),
                'out_of_stock' => Product::where('track_inventory', true)->where('stock', '<=', 0)->count(),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Products/Create', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'brand' => 'nullable|string|max:100',
            'stock' => 'nullable|integer|min:0',
            'track_inventory' => 'boolean',
            'is_taxable' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'age_restricted' => 'boolean',
            'image' => 'nullable|image|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        // Set defaults
        $validated['track_inventory'] = $request->boolean('track_inventory', true);
        $validated['is_taxable'] = $request->boolean('is_taxable', true);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['is_featured'] = $request->boolean('is_featured', false);
        $validated['age_restricted'] = $request->boolean('age_restricted', true);

        Product::create($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        return Inertia::render('Admin/Products/Edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:products,sku,' . $product->id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'brand' => 'nullable|string|max:100',
            'stock' => 'nullable|integer|min:0',
            'track_inventory' => 'boolean',
            'is_taxable' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'age_restricted' => 'boolean',
            'image' => 'nullable|image|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image && !str_starts_with($product->image, 'http')) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        // Set boolean defaults
        $validated['track_inventory'] = $request->boolean('track_inventory');
        $validated['is_taxable'] = $request->boolean('is_taxable');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['age_restricted'] = $request->boolean('age_restricted');

        $product->update($validated);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function updateStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'stock' => 'required|integer|min:0',
        ]);

        $product->update($validated);

        return back()->with('success', 'Stock updated.');
    }
}
