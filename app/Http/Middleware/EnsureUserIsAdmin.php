<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            if ($request->user()?->isCustomer()) {
                return redirect()->route('customer.dashboard');
            }
            abort(403, 'Unauthorized. Admin access required.');
        }
        return $next($request);
    }
}