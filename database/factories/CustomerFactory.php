<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->optional(0.7)->phoneNumber(),
            'dob' => $this->faker->optional(0.3)->dateTimeBetween('-60 years', '-21 years'),
            'is_subscribed' => $this->faker->boolean(30),
            'source' => $this->faker->randomElement(['website', 'walk-in', 'referral', null]),
            'notes' => $this->faker->optional(0.1)->sentence(),
            'total_reservations' => 0,
            'total_spent' => 0,
            'last_reservation_at' => null,
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }

    public function subscribed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_subscribed' => true,
        ]);
    }

    public function withOrders(): static
    {
        $orderCount = $this->faker->numberBetween(1, 10);
        $totalSpent = $this->faker->randomFloat(2, 20, 500);

        return $this->state(fn (array $attributes) => [
            'total_reservations' => $orderCount,
            'total_spent' => $totalSpent,
            'last_reservation_at' => $this->faker->dateTimeBetween('-3 months', 'now'),
        ]);
    }
}
