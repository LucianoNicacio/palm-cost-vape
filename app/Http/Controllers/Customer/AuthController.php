<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    /**
     * Display the customer registration view.
     */
    public function showRegister(): Response
    {
        return Inertia::render('Customer/Auth/Register');
    }

    /**
     * Handle customer registration request.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        // Create user with customer role
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'customer',
        ]);

        // Find or create customer and link to user
        $customer = Customer::where('email', $validated['email'])->first();

        if ($customer) {
            // Link existing customer (may have guest orders)
            $user->update(['customer_id' => $customer->id]);
            
            // Update customer name if it was from guest checkout
            $customer->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? $customer->phone,
            ]);
        } else {
            // Create new customer
            $customer = Customer::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'is_subscribed' => false,
            ]);
            
            $user->update(['customer_id' => $customer->id]);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('customer.dashboard');
    }

    /**
     * Display the customer login view.
     */
    public function showLogin(): Response
    {
        return Inertia::render('Customer/Auth/Login', [
            'canResetPassword' => true,
            'status' => session('status'),
        ]);
    }

    /**
     * Handle customer login request.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Check if user exists and is a customer
        $user = User::where('email', $credentials['email'])->first();

        if ($user && $user->isAdmin()) {
            return back()->withErrors([
                'email' => 'Please use the admin login page.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Ensure customer is linked
            $user = Auth::user();
            if (!$user->customer_id) {
                $user->linkOrCreateCustomer();
            }

            return redirect()->intended(route('customer.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Log the customer out.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
