<?php

namespace App\Http\Controllers;

use App\Models\AgeVerification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AgeVerificationController extends Controller
{
    public function show()
    {
        // If already verified, redirect to home
        if (session('age_verified')) {
            return redirect()->route('home');
        }

        // Show the age gate page
        return Inertia::render('AgeGate', [
            'ageRequirement' => config('app.age_requirement', 21),
            'shopName' => config('app.name'),
        ]);
    }

    public function verify(Request $request)
    {
        // Validate that user confirmed
        $request->validate([
            'confirmed' => 'required|accepted',
        ]);

        //Store in session
        session(['age_verified' => true]);

        // Record verification in database
        AgeVerification::recordVerification(
            session()->getId(),
            $request->ip(),
            $request->userAgent(),
        );

        // Redirect to intended page or home
        return redirect()->intended('home');
    }
}


