<?php

namespace App\Http\Controllers\Dealer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function list(Request $request)
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'casumina-parent';
        $sidebarChildActive = 'customer';
        $user = User::find(Auth::id());

        $query = User::query()->where('role', 'customer')->where('type', 'customer_info')->where('parent_code', $user->parent_code ?? $user->code);

        if ($request->keyword) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('phone', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('email', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('code', 'LIKE', '%' . $keyword . '%');
            });
        }

        $customers = $query->withSum(['orders' => function ($q) {
            $q->where('status', '!=', -1)->where('type', 'dealer_sale');
        }], 'total_amount')->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();


        if ($request->ajax()) {
            return view('dealer.layout.customer._table-customer', compact('customers'))->render();
        }

        return view('dealer.layout.customer.list', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'customers'));
    }

    public function listOnline(Request $request)
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'casumina-parent';
        $sidebarChildActive = 'customer';
        $user = User::find(Auth::id());

        $query = User::query()->where('role', 'customer')->where('channel', 'online')->where('type', 'customer_info')->where('parent_code', $user->parent_code ?? $user->code);

        if ($request->keyword) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('phone', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('email', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('code', 'LIKE', '%' . $keyword . '%');
            });
        }

        $customers = $query->withSum(['orders' => function ($q) {
            $q->where('status', '!=', -1)->where('type', 'dealer_sale');
        }], 'total_amount')->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();

        if ($request->ajax()) {
            return view('dealer.layout.customer._table-customer', compact('customers'))->render();
        }

        return view('dealer.layout.customer.list-online', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'customers'));
    }

    public function listOffline(Request $request)
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'casumina-parent';
        $sidebarChildActive = 'customer';
        $user = User::find(Auth::id());

        $query = User::query()->where('role', 'customer')->where('channel', 'offline')->where('type', 'customer_info')->where('parent_code', $user->parent_code ?? $user->code);

        if ($request->keyword) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('phone', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('email', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('code', 'LIKE', '%' . $keyword . '%');
            });
        }

        $customers = $query->withSum(['orders' => function ($q) {
            $q->where('status', '!=', -1)->where('type', 'dealer_sale');
        }], 'total_amount')->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();

        if ($request->ajax()) {
            return view('dealer.layout.customer._table-customer', compact('customers'))->render();
        }

        return view('dealer.layout.customer.list-offline', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'customers'));
    }

    public function detail($id)
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'casumina-parent';
        $sidebarChildActive = 'customer';
        $user = User::find(Auth::id());
        try {
            $customer = User::where('role', 'customer')->where('type', 'customer_info')->where('parent_code', $user->parent_code ?? $user->code)->findOrFail($id);
            $orders = Order::where('user_id', $customer->id)
                ->orderBy('created_at', 'desc')->get();
            return view('dealer.layout.customer.detail', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'customer', 'orders'));
        } catch (\Exception $e) {
            return redirect()->route('dealer.customer')->with('toast_error', 'Khách hàng không tồn tại.');
        }
    }
}
