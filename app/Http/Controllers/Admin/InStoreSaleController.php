<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InStoreSale;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InStoreSaleController extends Controller
{
    public function index()
    {
        $sales = InStoreSale::with('recorder:id,name')
            ->orderByDesc('created_at')
            ->paginate(25);

        return Inertia::render('Admin/InStoreSales/Index', [
            'sales' => $sales,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/InStoreSales/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $totalItems = 0;
            $totalAmount = 0;
            $lineItems = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                if ($product->track_inventory && $product->stock < $item['quantity']) {
                    DB::rollBack();
                    return back()->withErrors([
                        'stock' => "Insufficient stock for \"{$product->name}\". Only {$product->stock} available.",
                    ]);
                }

                $unitPrice = $product->price;
                $subtotal = $unitPrice * $item['quantity'];

                $lineItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ];

                $totalItems += $item['quantity'];
                $totalAmount += $subtotal;

                $product->decrementStock($item['quantity']);
            }

            $sale = InStoreSale::create([
                'recorded_by' => auth()->id(),
                'total_items' => $totalItems,
                'total_amount' => $totalAmount,
            ]);

            foreach ($lineItems as $lineItem) {
                $sale->items()->create($lineItem);
            }

            DB::commit();

            return redirect()->route('admin.in-store-sales.index')
                ->with('success', 'In-store sale recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to record sale. Please try again.']);
        }
    }

    public function show(InStoreSale $inStoreSale)
    {
        $inStoreSale->load(['recorder:id,name', 'items.product:id,name']);

        return Inertia::render('Admin/InStoreSales/Show', [
            'sale' => $inStoreSale,
        ]);
    }
}
