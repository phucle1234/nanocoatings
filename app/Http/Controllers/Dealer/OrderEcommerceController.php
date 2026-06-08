<?php

namespace App\Http\Controllers\Dealer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use App\Services\CasuminaApi\WarrantyService;
use App\Services\CasuminaApi\OrderService;

class OrderEcommerceController extends Controller
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

    private function _getDealerUserIds(): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->isParentDealer()) {
            return array_merge($user->showrooms()->pluck('id')->toArray(), [$user->id]);
        }

        return [$user->id];
    }
    public function index(Request $request)
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'casumina-parent';
        $sidebarChildActive = 'ecommerce';
        $user = Auth::user();

        $dealerCodes = $this->_getDealerCodes();
        $dealerUserIds = $this->_getDealerUserIds();
        $cancelledOrderIds = OrderStatusHistory::whereIn('changed_by', $dealerUserIds)
            ->where('new_status', -1)
            ->pluck('order_id')
            ->toArray();

        $order = Order::query();
        $order->where('type', 'customer')
            ->where(function ($q) use ($dealerCodes, $cancelledOrderIds) {
                $q->whereIn('dealer_code', $dealerCodes)
                    ->orWhereIn('id', $cancelledOrderIds);
            });
        // $order->whereIn('dealer_code', $dealerCodes)->where('type', 'customer');
        if ($request->keyword) {
            $keyword = $request->keyword;
            $order->where('order_number', 'LIKE', '%' . $keyword . '%');
        }
        $list = $order->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();
        if ($request->ajax()) {
            return view('dealer.layout.ecommerce._table-order', compact('list'))->render();
        }

        return view('dealer.layout.ecommerce.index', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'list'));
    }
    public function new(Request $request)
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'casumina-parent';
        $sidebarChildActive = 'ecommerce';
        $user = Auth::user();

        $dealerCodes = $this->_getDealerCodes();

        $order = Order::query();
        $order->whereIn('dealer_code', $dealerCodes)->where('type', 'customer')->where('status', 0);
        if ($request->keyword) {
            $keyword = $request->keyword;
            $order->where('order_number', 'LIKE', '%' . $keyword . '%');
        }
        $list = $order->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();
        if ($request->ajax()) {
            return view('dealer.layout.ecommerce._table-order', compact('list'))->render();
        }

        return view('dealer.layout.ecommerce.new', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'list'));
    }
    public function pending(Request $request)
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'casumina-parent';
        $sidebarChildActive = 'ecommerce';
        $user = Auth::user();

        $dealerCodes = $this->_getDealerCodes();

        $order = Order::query();
        $order->whereIn('dealer_code', $dealerCodes)->where('type', 'customer')->where('status', 1);
        if ($request->keyword) {
            $keyword = $request->keyword;
            $order->where('order_number', 'LIKE', '%' . $keyword . '%');
        }
        $list = $order->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();
        if ($request->ajax()) {
            return view('dealer.layout.ecommerce._table-order', compact('list'))->render();
        }

        return view('dealer.layout.ecommerce.pending', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'list'));
    }
    public function warehouse(Request $request)
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'casumina-parent';
        $sidebarChildActive = 'ecommerce';
        $user = Auth::user();

        $dealerCodes = $this->_getDealerCodes();

        $order = Order::query();
        $order->whereIn('dealer_code', $dealerCodes)->where('type', 'customer')->where('status', 2);
        if ($request->keyword) {
            $keyword = $request->keyword;
            $order->where('order_number', 'LIKE', '%' . $keyword . '%');
        }
        $list = $order->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();
        if ($request->ajax()) {
            return view('dealer.layout.ecommerce._table-order', compact('list'))->render();
        }

        return view('dealer.layout.ecommerce.warehouse', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'list'));
    }
    public function invoice(Request $request)
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'casumina-parent';
        $sidebarChildActive = 'ecommerce';
        $user = Auth::user();

        $dealerCodes = $this->_getDealerCodes();

        $order = Order::query();
        $order->whereIn('dealer_code', $dealerCodes)->where('type', 'customer')->where('status', 3);
        if ($request->keyword) {
            $keyword = $request->keyword;
            $order->where('order_number', 'LIKE', '%' . $keyword . '%');
        }
        $list = $order->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();
        if ($request->ajax()) {
            return view('dealer.layout.ecommerce._table-order', compact('list'))->render();
        }

        return view('dealer.layout.ecommerce.invoice', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'list'));
    }
    public function delivery(Request $request)
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'casumina-parent';
        $sidebarChildActive = 'ecommerce';
        $user = Auth::user();

        $dealerCodes = $this->_getDealerCodes();

        $order = Order::query();
        $order->whereIn('dealer_code', $dealerCodes)->where('type', 'customer')->where('status', 4);
        if ($request->keyword) {
            $keyword = $request->keyword;
            $order->where('order_number', 'LIKE', '%' . $keyword . '%');
        }
        $list = $order->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();
        if ($request->ajax()) {
            return view('dealer.layout.ecommerce._table-order', compact('list'))->render();
        }

        return view('dealer.layout.ecommerce.delivery', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'list'));
    }
    public function completed(Request $request)
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'casumina-parent';
        $sidebarChildActive = 'ecommerce';
        $user = Auth::user();

        $dealerCodes = $this->_getDealerCodes();

        $order = Order::query();
        $order->whereIn('dealer_code', $dealerCodes)->where('type', 'customer')->where('status', 5);
        if ($request->keyword) {
            $keyword = $request->keyword;
            $order->where('order_number', 'LIKE', '%' . $keyword . '%');
        }
        $list = $order->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();
        if ($request->ajax()) {
            return view('dealer.layout.ecommerce._table-order', compact('list'))->render();
        }

        return view('dealer.layout.ecommerce.completed', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'list'));
    }
    public function cancelled(Request $request)
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'casumina-parent';
        $sidebarChildActive = 'ecommerce';
        $user = Auth::user();

        $dealerCodes = $this->_getDealerCodes();
        $dealerUserIds = $this->_getDealerUserIds();
        $cancelledOrderIds = OrderStatusHistory::whereIn('changed_by', $dealerUserIds)
            ->where('new_status', -1)
            ->pluck('order_id')
            ->toArray();

        $order = Order::query();
        $order->where('type', 'customer')
            ->whereIn('id', $cancelledOrderIds);

        // $order->whereIn('dealer_code', $dealerCodes)->where('type', 'customer')->where('status', -1);
        if ($request->keyword) {
            $keyword = $request->keyword;
            $order->where('order_number', 'LIKE', '%' . $keyword . '%');
        }
        $list = $order->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();
        if ($request->ajax()) {
            return view('dealer.layout.ecommerce._table-order', compact('list'))->render();
        }

        return view('dealer.layout.ecommerce.cancelled', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'list'));
    }
    public function detail($id)
    {
        try {
            $headerWhite = 'header-secondary';
            $sidebarActive = 'casumina-parent';
            $sidebarChildActive = 'ecommerce';
            $user = Auth::user();
            $dealerCodes = $this->_getDealerCodes();
            $dealerUserIds = $this->_getDealerUserIds();
            $cancelledOrderIds = OrderStatusHistory::whereIn('changed_by', $dealerUserIds)
                ->where('new_status', -1)
                ->pluck('order_id')
                ->toArray();

            $order = Order::with([
                'user',
                'items.product.translations',
                'items.product.primaryCategory.translations',
            ])->where('type', 'customer')
                ->where(function ($q) use ($dealerCodes, $cancelledOrderIds) {
                    $q->whereIn('dealer_code', $dealerCodes)
                        ->orWhereIn('id', $cancelledOrderIds);
                })
                ->findOrFail($id);

            $cancelHistory = in_array($order->id, $cancelledOrderIds)
                ? OrderStatusHistory::whereIn('changed_by', $dealerUserIds)
                ->where('order_id', $order->id)
                ->where('new_status', -1)
                ->latest()
                ->first()
                : null;

            // $order = Order::with([
            //     'user',
            //     'items.product.translations',
            //     'items.product.primaryCategory.translations',
            // ])->whereIn('dealer_code', $dealerCodes)->where('type', 'customer')
            //     ->findOrFail($id);
            return view('dealer.layout.ecommerce.detail', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'order', 'cancelHistory'));
        } catch (\Exception $e) {
            return redirect()->route('dealer.ecommerce')->with('toast_error', 'Đơn hàng không tồn tại.');
        }
    }
    public function certification(Request $request)
    {
        try {
            $orderCode = $request->input('orderCode');
            $itemId = $request->input('itemId');
            $qrcode = $request->input('qrcode');
            $itemInfo = OrderItem::find($itemId);
            if (!$itemInfo) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Yêu cầu không hợp lệ.',
                ]);
            }
            $warrantyInfo = $this->warrantyService->saveWarrantyInfo($orderCode, $qrcode);
            if (!$warrantyInfo || $warrantyInfo->error_no != '') {
                return response()->json([
                    'status' => 0,
                    'message' => $warrantyInfo->error_no ?: 'Lưu thông tin QR bảo hành thất bại.',
                ]);
            }
            if ($warrantyInfo->order_no && $warrantyInfo->order_no != $orderCode) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Mã QR code sản phẩm này của đơn hàng khác.',
                ]);
            }
            if ($warrantyInfo->item_no && $warrantyInfo->item_no != $itemInfo->product_sku) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Mã QR code này của sản phẩm khác.',
                ]);
            }
            $orderItem = OrderItem::query()->where('id', $itemId)->first();
            if ($orderItem) {
                $qrcodeCurrent = $orderItem->qrcode ?? [];
                if (!in_array($qrcode, $qrcodeCurrent)) {
                    $qrcodeCurrent[] = $qrcode;
                    $orderItem->qrcode = !empty($qrcodeCurrent) ? $qrcodeCurrent : null;
                    $orderItem->save();
                }
            }
            return response()->json([
                'status' => 1,
                'message' => 'Kích hoạt bảo hành thành công.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Lỗi không xác định từ server.',
            ]);
        }
    }
    public function changeStatusOrder(Request $request)
    {
        try {
            $type = $request->input('type');
            $orderCode = $request->input('orderCode');
            $reason = $request->input('reason', '');
            $status = $request->input('status', 0);

            if ($type === 'cancelled' && !$reason) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Vui lòng nhập lý do hủy đơn.',
                ]);
            }

            if (!in_array($status, [0, 1, 2, 3, 4, 5, -1])) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Trạng thái không hợp lệ.',
                ]);
            }

            $order = Order::query()->where('order_number', $orderCode)->first();
            if (!$order) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Đơn hàng không tồn tại.',
                ]);
            }

            $changeStatusInfo = $this->orderService->updateOrderEcommerceStatus($orderCode, $status, $reason);
            if (!$changeStatusInfo || $changeStatusInfo->error_no != '') {
                return response()->json([
                    'status' => 0,
                    'message' => $changeStatusInfo?->error_no ?: 'Cập nhật trạng thái đơn hàng thất bại.',
                    'orderStatus' => $status,
                ]);
            }
            if ($status != -1) {
                Order::query()->where('order_number', $orderCode)->update(['status' => $status]);
            }
            if ($status == -1) {
                Order::query()->where('order_number', $orderCode)->update(['status' => 0, 'dealer_code' => null]);
                OrderStatusHistory::create([
                    'order_id'   => $order->id,
                    'old_status' => $order->status,
                    'new_status' => -1,
                    'notes'      => "{$order->dealer_code} hủy đơn. Lý do: {$reason}",
                    'changed_by' => Auth::id(),
                ]);
            }

            return response()->json([
                'status' => 1,
                'message' => 'Cập nhật trạng thái đơn hàng thành công.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Lỗi không xác định từ server.',
            ]);
        }
    }
}
