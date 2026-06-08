<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Cart;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function showLoginForm(Request $request)
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required',
        ], [], [
            'username' => __('auth.attr_username'),
            'password' => __('auth.attr_password'),
        ]);

        $user = User::where(['user_name' => $request->input('username'), 'is_admin' => 0])->where('role', '!=', 'admin')->first();

        if ($user) {
            if ($user->status != 'active' || $user->is_active === '0') {
                return back()->withInput()->with('error', __('auth.login_account_inactive'));
            }

            if (Auth::attempt(['user_name' => $request->input('username'), 'password' => $request->input('password')], $request->boolean('remember'))) {
                $request->session()->regenerate();

                //đồng bộ session cart với đb khi login
                if ($user->role !== 'dealer') {
                    $sessionKey = 'bags_cart_items';
                    $sessionCart = Session::get($sessionKey, []);

                    if (!empty($sessionCart)) {
                        $cart = Cart::getOrCreateCart(Auth::id(), 'customer', $request->session()->getId());

                        foreach ($sessionCart as $productId => $item) {
                            $qty = (int) ($item['quantity'] ?? 0);
                            if ($qty > 0) {
                                $cart->addItem((int) $productId, $qty);
                            }
                        }
                    }

                    // lưu lại session mới sau khi đã đồng bộ với DB để tránh login lại bị cộng trùng lần nữa
                    $freshCart = [];
                    $dbCart = Cart::with('items')
                        ->where('user_id', Auth::id())
                        ->where('type', 'customer')
                        ->where('is_checked_out', false)
                        ->first();

                    if ($dbCart) {
                        foreach ($dbCart->items as $row) {
                            $freshCart[$row->product_id] = [
                                'quantity' => (int) $row->quantity,
                                'added_at' => now()->toDateTimeString(),
                            ];
                        }
                    }

                    Session::put($sessionKey, $freshCart);
                }

                return $user->role === 'dealer'
                    ? redirect()->intended(route('dealer.dashboard'))
                    : redirect()->intended(route('customer.dashboard'));
            }
        }

        return back()->withInput()->with('error', __('auth.login_invalid_credentials'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
