<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display customer profile.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $customer = $user->customer;

        return Inertia::render('Customer/Profile/Edit', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'customer' => $customer ? [
                'phone' => $customer->phone,
                'is_subscribed' => $customer->is_subscribed,
            ] : null,
        ]);
    }

    /**
     * Update customer profile.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'is_subscribed' => ['boolean'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Update customer record
        if ($user->customer) {
            $user->customer->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'is_subscribed' => $validated['is_subscribed'] ?? false,
            ]);
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Update customer password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }
}
