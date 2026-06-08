<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureDealer
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('toast_error', __('auth.please_login'));
        }

        if (Auth::user()->role !== 'dealer') {
            if (Auth::user()->role === 'customer') {
                return redirect()->route('customer.dashboard')->with('toast_error', __('auth.no_access_dealer'));
            }
            return redirect()->route('home')->with('toast_error', __('auth.no_access_area'));
        }

        return $next($request);
    }
}
