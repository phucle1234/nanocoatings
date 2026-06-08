<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureCustomer
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('toast_error', __('auth.please_login'));
        }

        if (Auth::user()->role !== 'customer') {
            // Không logout — chỉ từ chối truy cập và redirect đúng portal
            if (Auth::user()->role === 'dealer') {
                return redirect()->route('dealer.dashboard')->with('toast_error', __('auth.no_access_customer'));
            }
            return redirect()->route('home')->with('toast_error', __('auth.no_access_area'));
        }

        return $next($request);
    }
}
