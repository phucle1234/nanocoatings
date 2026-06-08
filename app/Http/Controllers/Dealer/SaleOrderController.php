<?php

namespace App\Http\Controllers\Dealer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Services\CasuminaApi\WarrantyService;
use App\Services\CasuminaApi\OrderService;
use App\Models\User;

class SaleOrderController extends Controller
{
    public function __construct(
        protected WarrantyService $warrantyService,
        protected OrderService $orderService,
    ) {}

    private function _getDealerCodes(): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->isParentDealer()) {
            return array_merge($user->showrooms()->pluck('code')->toArray(), [$user->code]);
        }

        return [$user->code];
    }
    public function orderDiary(Request $request)
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'casumina-parent';
        $sidebarChildActive = 'sale-order-diary';
        $user = Auth::user();

        $dealerCodes = $this->_getDealerCodes();

        $order = Order::query();
        $order->whereIn('dealer_code', $dealerCodes)->whereIn('type', ['customer', 'dealer_sale'])->where('status', 5);
        if ($request->keyword) {
            $keyword = $request->keyword;
            $order->where('order_number', 'LIKE', '%' . $keyword . '%');
        }
        $list = $order->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();
        if ($request->ajax()) {
            return view('dealer.layout.sale-order._table-order', compact('list'))->render();
        }

        return view('dealer.layout.sale-order.order-diary', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'list'));
    }

    public function orderHistory(Request $request)
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'casumina-parent';
        $sidebarChildActive = 'sale-order-history';
        $user = Auth::user();
        $dealerCodes = $this->_getDealerCodes();

        $order = Order::query();
        $order->whereIn('dealer_code', $dealerCodes)->where('type', 'dealer_sale');
        if ($request->keyword) {
            $keyword = $request->keyword;
            $order->where('order_number', 'LIKE', '%' . $keyword . '%');
        }
        $list = $order->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();
        if ($request->ajax()) {
            return view('dealer.layout.sale-order._table-order-history', compact('list'))->render();
        }

        return view('dealer.layout.sale-order.order-history', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'list'));
    }

    public function orderHistoryNew(Request $request)
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'casumina-parent';
        $sidebarChildActive = 'sale-order-history';
        $user = Auth::user();
        $dealerCodes = $this->_getDealerCodes();

        $order = Order::query();
        $order->whereIn('dealer_code', $dealerCodes)->where('type', 'dealer_sale')->where('status', 0);
        if ($request->keyword) {
            $keyword = $request->keyword;
            $order->where('order_number', 'LIKE', '%' . $keyword . '%');
        }
        $list = $order->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();
        if ($request->ajax()) {
            return view('dealer.layout.sale-order._table-order-history', compact('list'))->render();
        }

        return view('dealer.layout.sale-order.order-history-new', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'list'));
    }

    public function orderHistoryPending(Request $request)
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'casumina-parent';
        $sidebarChildActive = 'sale-order-history';
        $user = Auth::user();
        $dealerCodes = $this->_getDealerCodes();

        $order = Order::query();
        $order->whereIn('dealer_code', $dealerCodes)->where('type', 'dealer_sale')->where('status', 1);
        if ($request->keyword) {
            $keyword = $request->keyword;
            $order->where('order_number', 'LIKE', '%' . $keyword . '%');
        }
        $list = $order->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();
        if ($request->ajax()) {
            return view('dealer.layout.sale-order._table-order-history', compact('list'))->render();
        }

        return view('dealer.layout.sale-order.order-history-pending', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'list'));
    }

    public function orderHistoryWarehouse(Request $request)
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'casumina-parent';
        $sidebarChildActive = 'sale-order-history';
        $user = Auth::user();
        $dealerCodes = $this->_getDealerCodes();

        $order = Order::query();
        $order->whereIn('dealer_code', $dealerCodes)->where('type', 'dealer_sale')->where('status', 2);
        if ($request->keyword) {
            $keyword = $request->keyword;
            $order->where('order_number', 'LIKE', '%' . $keyword . '%');
        }
        $list = $order->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();
        if ($request->ajax()) {
            return view('dealer.layout.sale-order._table-order-history', compact('list'))->render();
        }

        return view('dealer.layout.sale-order.order-history-warehouse', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'list'));
    }

    public function orderHistoryInvoice(Request $request)
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'casumina-parent';
        $sidebarChildActive = 'sale-order-history';
        $user = Auth::user();
        $dealerCodes = $this->_getDealerCodes();

        $order = Order::query();
        $order->whereIn('dealer_code', $dealerCodes)->where('type', 'dealer_sale')->where('status', 3);
        if ($request->keyword) {
            $keyword = $request->keyword;
            $order->where('order_number', 'LIKE', '%' . $keyword . '%');
        }
        $list = $order->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();
        if ($request->ajax()) {
            return view('dealer.layout.sale-order._table-order-history', compact('list'))->render();
        }

        return view('dealer.layout.sale-order.order-history-invoice', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'list'));
    }

    public function orderHistoryDelivery(Request $request)
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'casumina-parent';
        $sidebarChildActive = 'sale-order-history';
        $user = Auth::user();
        $dealerCodes = $this->_getDealerCodes();

        $order = Order::query();
        $order->whereIn('dealer_code', $dealerCodes)->where('type', 'dealer_sale')->where('status', 4);
        if ($request->keyword) {
            $keyword = $request->keyword;
            $order->where('order_number', 'LIKE', '%' . $keyword . '%');
        }
        $list = $order->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();
        if ($request->ajax()) {
            return view('dealer.layout.sale-order._table-order-history', compact('list'))->render();
        }

        return view('dealer.layout.sale-order.order-history-delivery', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'list'));
    }

    public function orderHistoryCompleted(Request $request)
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'casumina-parent';
        $sidebarChildActive = 'sale-order-history';
        $user = Auth::user();
        $dealerCodes = $this->_getDealerCodes();

        $order = Order::query();
        $order->whereIn('dealer_code', $dealerCodes)->where('type', 'dealer_sale')->where('status', 5);
        if ($request->keyword) {
            $keyword = $request->keyword;
            $order->where('order_number', 'LIKE', '%' . $keyword . '%');
        }
        $list = $order->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();
        if ($request->ajax()) {
            return view('dealer.layout.sale-order._table-order-history', compact('list'))->render();
        }

        return view('dealer.layout.sale-order.order-history-completed', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'list'));
    }

    public function orderHistoryCancelled(Request $request)
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'casumina-parent';
        $sidebarChildActive = 'sale-order-history';
        $user = Auth::user();
        $dealerCodes = $this->_getDealerCodes();

        $order = Order::query();
        $order->whereIn('dealer_code', $dealerCodes)->where('type', 'dealer_sale')->where('status', -1);
        if ($request->keyword) {
            $keyword = $request->keyword;
            $order->where('order_number', 'LIKE', '%' . $keyword . '%');
        }
        $list = $order->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();
        if ($request->ajax()) {
            return view('dealer.layout.sale-order._table-order-history', compact('list'))->render();
        }

        return view('dealer.layout.sale-order.order-history-cancelled', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'list'));
    }

    public function orderDetail($id)
    {
        try {
            $headerWhite = 'header-secondary';
            $sidebarActive = 'casumina-parent';
            $sidebarChildActive = 'sale-order-history';
            $user = Auth::user();
            $order = Order::with([
                'items.product.translations',
                'items.product.primaryCategory.translations',
            ])->where(function ($q) use ($user) {
                if ($user->parent_code === null) {
                    // Là cha: xem được của mình và tất cả dealer con
                    $childCodes = User::where('parent_code', $user->code)->pluck('code')->toArray();
                    $q->whereIn('dealer_code', array_merge([$user->code], $childCodes));
                } else {
                    // Là con: chỉ xem của chính mình
                    $q->where('dealer_code', $user->code);
                }
            })->where('type', 'dealer_sale')
                ->findOrFail($id);

            $dealer = User::where('code', $order->dealer_code)
                ->where('role', 'dealer')
                ->first();

            $recipient = User::find($order->user_id);
            return view('dealer.layout.sale-order.order-detail', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'order', 'dealer', 'recipient'));
        } catch (\Exception $e) {
            return redirect()->route('dealer.sale-order-history')->with('toast_error', 'Đơn hàng không tồn tại.');
        }
    }
}
