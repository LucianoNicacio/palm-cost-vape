<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\ReservationItem;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::where('is_active', true)->get();

        if ($products->isEmpty()) {
            $this->command->warn('No active products found. Run ProductSeeder first.');
            return;
        }

        // Create customers
        $customers = Customer::factory()->count(30)->create();
        $this->command->info('Created 30 customers.');

        $totalReservations = 0;
        $taxRate = 0.07;

        // Create reservations for TODAY - all statuses
        $todayStatuses = [
            'pending' => 5,
            'confirmed' => 3,
            'ready' => 2,
            'completed' => 4,
            'cancelled' => 1,
        ];

        foreach ($todayStatuses as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                $this->createReservation($customers, $products, $status, 'today', $taxRate);
                $totalReservations++;
            }
        }
        $this->command->info('Created 15 reservations from TODAY.');

        // Create reservations for THIS WEEK - all statuses
        $weekStatuses = [
            'pending' => 4,
            'confirmed' => 3,
            'ready' => 3,
            'completed' => 8,
            'cancelled' => 2,
        ];

        foreach ($weekStatuses as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                $this->createReservation($customers, $products, $status, 'week', $taxRate);
                $totalReservations++;
            }
        }
        $this->command->info('Created 20 reservations from THIS WEEK.');

        // Create reservations for THIS MONTH - all statuses
        $monthStatuses = [
            'pending' => 3,
            'confirmed' => 2,
            'ready' => 2,
            'completed' => 15,
            'cancelled' => 3,
        ];

        foreach ($monthStatuses as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                $this->createReservation($customers, $products, $status, 'month', $taxRate);
                $totalReservations++;
            }
        }
        $this->command->info('Created 25 reservations from THIS MONTH.');

        // Create older reservations (past 3 months) - mostly completed
        $olderStatuses = [
            'completed' => 20,
            'cancelled' => 5,
        ];

        foreach ($olderStatuses as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                $this->createReservation($customers, $products, $status, 'older', $taxRate);
                $totalReservations++;
            }
        }
        $this->command->info('Created 25 older reservations.');

        $this->command->newLine();
        $this->command->info("✅ Total: {$totalReservations} reservations created.");
    }

    protected function createReservation($customers, $products, string $status, string $period, float $taxRate): void
    {
        $customer = $customers->random();

        // Determine date based on period using Carbon
        $now = Carbon::now();
        $createdAt = match ($period) {
            'today' => $now->copy()->subMinutes(rand(1, 600)),
            'week' => $now->copy()->subDays(rand(1, 6))->subHours(rand(0, 12)),
            'month' => $now->copy()->subDays(rand(7, 28))->subHours(rand(0, 12)),
            'older' => $now->copy()->subDays(rand(30, 90))->subHours(rand(0, 12)),
            default => $now,
        };

        // Create reservation with appropriate status
        $reservation = Reservation::factory()
            ->{$status}()
            ->create([
                'customer_id' => $customer->id,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

        // Add 1-5 unique items
        $itemCount = rand(1, min(5, $products->count()));
        $orderSubtotal = 0;
        $orderTaxTotal = 0;

        $selectedProducts = $products->random($itemCount);

        foreach ($selectedProducts as $product) {
            $quantity = rand(1, 3);
            $unitPrice = $product->price;
            $subtotal = $unitPrice * $quantity;
            $taxAmount = round($subtotal * $taxRate, 2);
            $totalPrice = $subtotal + $taxAmount;

            ReservationItem::create([
                'reservation_id' => $reservation->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_price' => $totalPrice,
            ]);

            $orderSubtotal += $subtotal;
            $orderTaxTotal += $taxAmount;
        }

        // Update reservation totals
        $reservation->update([
            'subtotal' => $orderSubtotal,
            'tax_amount' => $orderTaxTotal,
            'total_price' => $orderSubtotal + $orderTaxTotal,
            'item_count' => $itemCount,
        ]);

        // Update customer stats for completed orders
        if ($status === 'completed') {
            $customer->increment('total_reservations');
            $customer->increment('total_spent', $orderSubtotal + $orderTaxTotal);
            $customer->update(['last_reservation_at' => $createdAt]);
        }
    }
}
