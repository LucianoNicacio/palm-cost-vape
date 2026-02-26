<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\RewardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RewardController extends Controller
{
    public function __construct(
        private RewardService $rewardService,
    ) {}

    public function index(Request $request): Response
    {
        $customer = $request->user()->customer;

        $rewards = $customer->rewards()
            ->with('reservation:id,confirmation_number')
            ->latest()
            ->paginate(20);

        // Calculate spending progress toward next reward
        $totalSpent = (float) $customer->reservations()
            ->where('status', 'completed')
            ->sum('subtotal');
        $totalEarned = (float) $customer->rewards()->earned()->sum('amount');
        // How much spending has already been "credited" as rewards
        $creditedSpending = ($totalEarned / 10) * 100;
        // Remaining spending toward next $100 threshold
        $progressSpent = fmod($totalSpent - $creditedSpending, 100);
        if ($progressSpent < 0) {
            $progressSpent = 0;
        }

        return Inertia::render('Customer/Rewards/Index', [
            'rewards' => $rewards,
            'balance' => $this->rewardService->getBalance($customer),
            'progress' => [
                'total_spent' => round($totalSpent, 2),
                'toward_next' => round($progressSpent, 2),
                'remaining' => round(100 - $progressSpent, 2),
            ],
        ]);
    }
}
