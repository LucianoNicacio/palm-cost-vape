<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\ReservationItem;
use App\Notifications\ReservationConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;

class ReservationController extends Controller
{
    public function checkout()
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('shop')
                ->with('error', 'Your cart is empty.');
        }

        $user = Auth::user();

        $prefill = null;
        if ($user && $user->isCustomer() && $user->customer) {
            $prefill = [
                'customer_name' => $user->customer->name,
                'customer_email' => $user->customer->email,
                'customer_phone' => $user->customer->phone ?? '',
            ];
        }

        $cartItems = $this->getCartItemsWithProducts($cart);
        $totals = $this->calculateTotals($cartItems);
        $taxRate = (float) config('store.tax_rate', 0.07);

        return Inertia::render('Checkout/Index', [
            'cartItems' => $cartItems,
            'totals' => $totals,
            'ageRequirement' => (int) config('store.age_requirement', 21),
            'taxRate' => $taxRate,  // Now it's defined
            'isLoggedIn' => $user && $user->isCustomer(),
            'prefill' => $prefill,
        ]);
    }

    public function store(Request $request)
    {
        // Honeypot check
        if ($request->filled('website')) {
            return back()->with('error', 'Something went wrong. Please try again.');
        }

        // Rate limit: max 3 orders per hour per IP
        $key = 'checkout:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()->with('error', 'Too many orders. Please try again later.');
        }

        RateLimiter::hit($key, 3600); // 1 hour

        $user = Auth::user();
        $isLoggedIn = $user && $user->isCustomer() && $user->customer;

        // Validate customer information - logged-in users only need phone
        if ($isLoggedIn) {
            $validated = $request->validate([
                'customer_phone' => 'required|string|max:20',
                'is_subscribed' => 'boolean',
            ]);
            $validated['customer_name'] = $user->customer->name;
            $validated['customer_email'] = $user->customer->email;
            $validated['customer_dob'] = $user->customer->dob?->format('Y-m-d');
        } else {
            $validated = $request->validate([
                'customer_name' => 'required|string|max:255',
                'customer_email' => 'required|email|max:255',
                'customer_phone' => 'required|string|max:20',
                'customer_dob' => 'required|date|before_or_equal:-21 years',
                'is_subscribed' => 'boolean',
            ], [
                'customer_dob.before' => 'You must be at least 21 years old.',
            ]);
        }

        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('shop')
                ->with('error', 'Your cart is empty.');
        }

        try {
            // Start database transaction
            DB::beginTransaction();

            // Find or create customer
            $customer = Customer::findOrCreateByEmail([
                'email' => $validated['customer_email'],
                'name' => $validated['customer_name'],
                'phone' => $validated['customer_phone'],
                'dob' => $validated['customer_dob'],
            ]);

            // Update phone and subscription preference
            $customer->phone = $validated['customer_phone'];
            $customer->is_subscribed = $request->boolean('is_subscribed');
            $customer->save();

            // Create reservation
            $reservation = Reservation::create([
                'customer_id' => $customer->id,
                'status' => 'pending',
            ]);

            // Get all products
            $products = Product::whereIn('id', array_keys($cart))
                ->get()
                ->keyBy('id');

            // Create reservation items
            foreach ($cart as $productId => $quantity) {
                if (!isset($products[$productId])) {
                    continue;
                }

                $product = $products[$productId];

                // Check stock
                if ($product->track_inventory && $product->stock < $quantity) {
                    throw new \Exception("Not enough stock for {$product->name}");
                }

                // Create line item
                ReservationItem::createFromProduct($reservation, $product, $quantity);

                // Decrement stock
                $product->decrementStock($quantity);
            }

            // Recalculate totals
            $reservation->load('items');
            $reservation->recalculateTotals();

            // Update customer stats
            $customer->updateStats();

            // Clear cart
            session()->forget('cart');

            // Send confirmation email (queued)
            $customer->notify(new ReservationConfirmation($reservation));

            // Commit transaction
            DB::commit();

            return redirect()->route('reserve.confirmation', $reservation->confirmation_number);

        } catch (\Exception $e) {
            // Rollback on any error
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

    public function confirmation(string $confirmationNumber)
    {
        $reservation = Reservation::with(['customer', 'items.product'])
            ->where('confirmation_number', $confirmationNumber)
            ->firstOrFail();

        return Inertia::render('Checkout/Confirmation', [
            'reservation' => $reservation,
            'storeInfo' => [
                'name' => 'Palm Coast Vape and Glassware',
                'address' => '29 Old Kings Rd N, Suite 2-A',
                'city' => 'Palm Coast, FL 32137',
                'phone' => '(386) 597-2838',
                'showCreateAccount' => !Auth::check(),
                'customerEmail' => $reservation->customer->email,
            ],
        ]);
    }

    // Same helper methods as CartController
    private function getCartItemsWithProducts(array $cart): array
    {
        if (empty($cart)) {
            return [];
        }

        $products = Product::whereIn('id', array_keys($cart))
            ->active()
            ->with('category')
            ->get()
            ->keyBy('id');

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
}