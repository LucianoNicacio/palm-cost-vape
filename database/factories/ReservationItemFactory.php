<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Reservation;
use App\Models\ReservationItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationItemFactory extends Factory
{
    protected $model = ReservationItem::class;

    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 3);
        $unitPrice = $this->faker->randomFloat(2, 10, 50);
        $taxRate = 0.07; // 7% tax
        $subtotal = $unitPrice * $quantity;
        $taxAmount = round($subtotal * $taxRate, 2);

        return [
            'reservation_id' => Reservation::factory(),
            'product_id' => Product::factory(),
            'product_name' => $this->faker->words(3, true),
            'product_sku' => strtoupper($this->faker->lexify('???-???-###')),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'total_price' => $subtotal + $taxAmount,
        ];
    }
}
