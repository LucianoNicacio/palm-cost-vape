<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * Display customer's order history.
     */
    public function index(Request $request): Response
    {
        $customer = $request->user()->customer;

        $orders = $customer ? $customer->reservations()
            ->with('items.product')
            ->paginate(10)
            ->through(fn ($reservation) => [
                'id' => $reservation->id,
                'confirmation_number' => $reservation->confirmation_number,
                'status' => $reservation->status,
                'status_label' => $reservation->status_label,
                'total_price' => $reservation->total_price,
                'item_count' => $reservation->items->count(),
                'items' => $reservation->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'product' => $item->product ? [
                        'id' => $item->product->id,
                        'image_url' => $item->product->image_url,
                        'in_stock' => $item->product->in_stock,
                    ] : null,
                ]),
                'created_at' => $reservation->created_at->toDateTimeString(),
                'created_at_formatted' => $reservation->created_at->format('M d, Y \a\t g:i A'),
            ]) : collect();

        return Inertia::render('Customer/Orders/Index', [
            'orders' => $orders,
        ]);
    }

    /**
     * Display a specific order.
     */
    public function show(Request $request, Reservation $reservation): Response
    {
        $customer = $request->user()->customer;

        // Ensure this order belongs to the customer
        if (!$customer || $reservation->customer_id !== $customer->id) {
            abort(403, 'This order does not belong to you.');
        }

        $reservation->load('items.product');

        return Inertia::render('Customer/Orders/Show', [
            'order' => [
                'id' => $reservation->id,
                'confirmation_number' => $reservation->confirmation_number,
                'status' => $reservation->status,
                'status_label' => $reservation->status_label,
                'subtotal' => $reservation->subtotal,
                'tax_amount' => $reservation->tax_amount,
                'total_price' => $reservation->total_price,
                'notes' => $reservation->notes,
                'created_at' => $reservation->created_at->toDateTimeString(),
                'created_at_formatted' => $reservation->created_at->format('M d, Y \a\t g:i A'),
                'items' => $reservation->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'product' => $item->product ? [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'image_url' => $item->product->image_url,
                        'price' => $item->product->price,
                        'in_stock' => $item->product->in_stock,
                        'stock' => $item->product->stock,
                    ] : null,
                ]),
            ],
            'storeInfo' => [
                'name' => config('store.name', 'Palm Coast Vape and Glassware'),
                'address' => config('store.address', '29 Old Kings Rd N, Suite 2-A'),
                'city' => config('store.city', 'Palm Coast, FL 32137'),
                'phone' => config('store.phone', '(386) 597-2838'),
            ],
        ]);
    }

    /**
     * Reorder - add all items from a previous order to cart.
     */
    public function reorder(Request $request, Reservation $reservation): RedirectResponse
    {
        $customer = $request->user()->customer;

        // Ensure this order belongs to the customer
        if (!$customer || $reservation->customer_id !== $customer->id) {
            abort(403, 'This order does not belong to you.');
        }

        $reservation->load('items.product');

        $cart = session()->get('cart', []);
        $addedItems = 0;
        $unavailableItems = [];

        foreach ($reservation->items as $item) {
            $product = $item->product;

            // Skip if product no longer exists or is not active
            if (!$product || !$product->is_active) {
                $unavailableItems[] = $item->product_name;
                continue;
            }

            // Check stock availability
            $currentCartQty = $cart[$product->id]['quantity'] ?? 0;
            $availableQty = $product->track_inventory 
                ? max(0, $product->stock - $currentCartQty)
                : PHP_INT_MAX;

            if ($availableQty <= 0) {
                $unavailableItems[] = $product->name . ' (out of stock)';
                continue;
            }

            $qtyToAdd = min($item->quantity, $availableQty);

            // Add to cart
            if (isset($cart[$product->id])) {
                $cart[$product->id]['quantity'] += $qtyToAdd;
            } else {
                $cart[$product->id] = [
                    'product_id' => $product->id,
                    'quantity' => $qtyToAdd,
                ];
            }

            $addedItems++;
        }

        session()->put('cart', $cart);

        $message = "{$addedItems} item(s) added to your cart.";
        
        if (count($unavailableItems) > 0) {
            $message .= ' Some items were unavailable: ' . implode(', ', $unavailableItems);
            return redirect()->route('cart.index')->with('warning', $message);
        }

        return redirect()->route('cart.index')->with('success', $message);
    }
}
