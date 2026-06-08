<?php

namespace App\Http\Controllers\Dealer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    private function baseCommonData(): array
    {
        return [
            'headerWhite' => 'header-secondary',
            'sidebarActive' => 'order-parent',
        ];
    }

    // Lấy danh sách showroom của NPP và cả NPP để lọc đơn hàng
    private function _getDealerUserIds(): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->isParentDealer()) {
            return array_merge($user->showrooms()->pluck('id')->toArray(), [$user->id]);
        }

        return [$user->id];
    }

    public function orderHistory(Request $request)
    {
        $sidebarChildActive = 'order-history';
        $user = Auth::user();
        $userIds = $this->_getDealerUserIds();

        $order = Order::query();
        $order->whereIn('user_id', $userIds)->where('type', 'dealer_buy');
        if ($request->keyword) {
            $keyword = $request->keyword;
            $order->where('order_number', 'LIKE', '%' . $keyword . '%');
        }
        $list = $order->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();
        if ($request->ajax()) {
            return view('dealer.layout.order._table-order-history', compact('list'))->render();
        }

        return view('dealer.layout.order.order-history', array_merge(
            $this->baseCommonData(),
            compact('user', 'sidebarChildActive', 'list')
        ));
    }
    public function orderHistoryNew(Request $request)
    {
        $sidebarChildActive = 'order-history';
        $user = Auth::user();
        $userIds = $this->_getDealerUserIds();

        $order = Order::query();
        $order->whereIn('user_id', $userIds)->where('type', 'dealer_buy')->where('status', 0);
        if ($request->keyword) {
            $keyword = $request->keyword;
            $order->where('order_number', 'LIKE', '%' . $keyword . '%');
        }
        $list = $order->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();
        if ($request->ajax()) {
            return view('dealer.layout.order._table-order-history', compact('list'))->render();
        }

        return view('dealer.layout.order.order-history-new', array_merge(
            $this->baseCommonData(),
            compact('user', 'sidebarChildActive', 'list')
        ));
    }
    public function orderHistoryPending(Request $request)
    {
        $sidebarChildActive = 'order-history';
        $user = Auth::user();
        $userIds = $this->_getDealerUserIds();

        $order = Order::query();
        $order->whereIn('user_id', $userIds)->where('type', 'dealer_buy')->where('status', 1);
        if ($request->keyword) {
            $keyword = $request->keyword;
            $order->where('order_number', 'LIKE', '%' . $keyword . '%');
        }
        $list = $order->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();
        if ($request->ajax()) {
            return view('dealer.layout.order._table-order-history', compact('list'))->render();
        }

        return view('dealer.layout.order.order-history-pending', array_merge(
            $this->baseCommonData(),
            compact('user', 'sidebarChildActive', 'list')
        ));
    }
    public function orderHistoryResponded(Request $request)
    {
        $sidebarChildActive = 'order-history';
        $user = Auth::user();
        $userIds = $this->_getDealerUserIds();

        $order = Order::query();
        $order->whereIn('user_id', $userIds)->where('type', 'dealer_buy')->where('status', 2);
        if ($request->keyword) {
            $keyword = $request->keyword;
            $order->where('order_number', 'LIKE', '%' . $keyword . '%');
        }
        $list = $order->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();
        if ($request->ajax()) {
            return view('dealer.layout.order._table-order-history', compact('list'))->render();
        }

        return view('dealer.layout.order.order-history-responded', array_merge(
            $this->baseCommonData(),
            compact('user', 'sidebarChildActive', 'list')
        ));
    }
    public function orderHistoryCreated(Request $request)
    {
        $sidebarChildActive = 'order-history';
        $user = Auth::user();
        $userIds = $this->_getDealerUserIds();

        $order = Order::query();
        $order->whereIn('user_id', $userIds)->where('type', 'dealer_buy')->where('status', 3);
        if ($request->keyword) {
            $keyword = $request->keyword;
            $order->where('order_number', 'LIKE', '%' . $keyword . '%');
        }
        $list = $order->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();
        if ($request->ajax()) {
            return view('dealer.layout.order._table-order-history', compact('list'))->render();
        }

        return view('dealer.layout.order.order-history-created', array_merge(
            $this->baseCommonData(),
            compact('user', 'sidebarChildActive', 'list')
        ));
    }
    public function orderHistoryInvoiced(Request $request)
    {
        $sidebarChildActive = 'order-history';
        $user = Auth::user();
        $userIds = $this->_getDealerUserIds();

        $order = Order::query();
        $order->whereIn('user_id', $userIds)->where('type', 'dealer_buy')->where('status', 4);
        if ($request->keyword) {
            $keyword = $request->keyword;
            $order->where('order_number', 'LIKE', '%' . $keyword . '%');
        }
        $list = $order->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();
        if ($request->ajax()) {
            return view('dealer.layout.order._table-order-history', compact('list'))->render();
        }

        return view('dealer.layout.order.order-history-invoiced', array_merge(
            $this->baseCommonData(),
            compact('user', 'sidebarChildActive', 'list')
        ));
    }
    public function orderHistoryCompleted(Request $request)
    {
        $sidebarChildActive = 'order-history';
        $user = Auth::user();
        $userIds = $this->_getDealerUserIds();

        $order = Order::query();
        $order->whereIn('user_id', $userIds)->where('type', 'dealer_buy')->where('status', 5);
        if ($request->keyword) {
            $keyword = $request->keyword;
            $order->where('order_number', 'LIKE', '%' . $keyword . '%');
        }
        $list = $order->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();
        if ($request->ajax()) {
            return view('dealer.layout.order._table-order-history', compact('list'))->render();
        }

        return view('dealer.layout.order.order-history-completed',  array_merge(
            $this->baseCommonData(),
            compact('user', 'sidebarChildActive', 'list')
        ));
    }
    public function orderHistoryCancelled(Request $request)
    {
        $sidebarChildActive = 'order-history';
        $user = Auth::user();
        $userIds = $this->_getDealerUserIds();

        $order = Order::query();
        $order->whereIn('user_id', $userIds)->where('type', 'dealer_buy')->where('status', -1);
        if ($request->keyword) {
            $keyword = $request->keyword;
            $order->where('order_number', 'LIKE', '%' . $keyword . '%');
        }
        $list = $order->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();
        if ($request->ajax()) {
            return view('dealer.layout.order._table-order-history', compact('list'))->render();
        }

        return view('dealer.layout.order.order-history-cancelled',  array_merge(
            $this->baseCommonData(),
            compact('user', 'sidebarChildActive', 'list')
        ));
    }

    public function orderHistoryDetail($id)
    {
        try {
            $sidebarChildActive = 'order-history';
            $user = Auth::user();
            $userIds = $this->_getDealerUserIds();
            $order = Order::with([
                'items.product.translations',
                'items.product.primaryCategory.translations',
            ])->whereIn('user_id', $userIds)->where('type', 'dealer_buy')
                ->findOrFail($id);

            return view('dealer.layout.order.order-history-detail', array_merge(
                $this->baseCommonData(),
                compact('user', 'sidebarChildActive', 'order')
            ));
        } catch (\Exception $e) {
            return redirect()->route('dealer.order-history')->with('toast_error', 'Đơn hàng không tồn tại.');
        }
    }

    public function orderHistoryDetailWarehouse($id)
    {
        try {
            $sidebarChildActive = 'order-history';
            $user = Auth::user();
            $userIds = $this->_getDealerUserIds();
            $order = Order::with([
                'items.product.translations',
                'items.product.primaryCategory.translations',
            ])->whereIn('user_id', $userIds)->where('type', 'dealer_buy')
                ->findOrFail($id);

            return view('dealer.layout.order.order-history-detail-warehouse', array_merge(
                $this->baseCommonData(),
                compact('user', 'sidebarChildActive', 'order')
            ));
        } catch (\Exception $e) {
            return redirect()->route('dealer.order-history')->with('toast_error', 'Đơn hàng không tồn tại.');
        }
    }

    public function orderDiary(Request $request)
    {
        $sidebarChildActive = 'order-diary';
        $user = Auth::user();
        $userIds = $this->_getDealerUserIds();

        $order = Order::query();
        $order->whereIn('user_id', $userIds)->where('type', 'dealer_buy')->where('status', 5);
        if ($request->keyword) {
            $keyword = $request->keyword;
            $order->where('order_number', 'LIKE', '%' . $keyword . '%');
        }
        $list = $order->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();
        if ($request->ajax()) {
            return view('dealer.layout.order._table-order-diary', compact('list'))->render();
        }

        return view('dealer.layout.order.order-diary', array_merge(
            $this->baseCommonData(),
            compact('user', 'sidebarChildActive', 'list')
        ));
    }

}
