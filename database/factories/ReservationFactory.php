<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['pending', 'confirmed', 'ready', 'completed', 'cancelled']);
        $createdAt = $this->faker->dateTimeBetween('-3 months', 'now');

        return [
            'confirmation_number' => 'PCV-' . strtoupper(Str::random(6)),
            'customer_id' => Customer::factory(),
            'subtotal' => 0,
            'tax_amount' => 0,
            'total_price' => 0,
            'item_count' => 0,
            'status' => $status,
            'pickup_date' => $this->faker->optional(0.5)->dateTimeBetween($createdAt, '+7 days'),
            'notes' => $this->faker->optional(0.2)->sentence(),
            'processed_by' => in_array($status, ['completed', 'cancelled']) ? 1 : null,
            'processed_at' => in_array($status, ['completed', 'cancelled']) ? $this->faker->dateTimeBetween($createdAt, 'now') : null,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'processed_by' => null,
            'processed_at' => null,
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'processed_by' => null,
            'processed_at' => null,
        ]);
    }

    public function ready(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'ready',
            'processed_by' => null,
            'processed_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'processed_by' => 1,
            'processed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
            'processed_by' => 1,
            'processed_at' => now(),
        ]);
    }

    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('today', 'now'),
            'updated_at' => now(),
        ]);
    }

    public function thisWeek(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-7 days', 'now'),
            'updated_at' => now(),
        ]);
    }
}
