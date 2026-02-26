<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Reservation;
use App\Models\Reward;

class RewardService
{
    public function earnRewards(Reservation $reservation): ?Reward
    {
        $amount = $reservation->rewardsEligibleAmount();

        if ($amount <= 0) {
            return null;
        }

        // Don't double-earn for the same reservation
        if (Reward::where('reservation_id', $reservation->id)->where('type', 'earned')->exists()) {
            return null;
        }

        return Reward::create([
            'customer_id' => $reservation->customer_id,
            'amount' => $amount,
            'type' => 'earned',
            'reservation_id' => $reservation->id,
            'description' => "Earned \${$amount} reward from order {$reservation->confirmation_number}",
        ]);
    }

    public function redeemReward(Customer $customer, Reservation $reservation): Reward
    {
        $balance = $this->getBalance($customer);

        if ($balance < 10) {
            throw new \Exception('Insufficient rewards balance. You need at least $10.00.');
        }

        // Check if this reservation already has a reward redeemed
        $alreadyRedeemed = Reward::where('reservation_id', $reservation->id)
            ->where('type', 'redeemed')
            ->exists();

        if ($alreadyRedeemed) {
            throw new \Exception('A reward has already been applied to this order.');
        }

        $reward = Reward::create([
            'customer_id' => $customer->id,
            'amount' => 10,
            'type' => 'redeemed',
            'reservation_id' => $reservation->id,
            'description' => "Redeemed \$10.00 reward on order {$reservation->confirmation_number}",
        ]);

        // Apply discount to reservation
        $reservation->reward_discount = 10;
        $reservation->recalculateTotals();

        return $reward;
    }

    public function getBalance(Customer $customer): float
    {
        return $customer->rewards_balance;
    }
}
