<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Traits\HasImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    use HasImage;

    private const STATUS_NEW = 0;
    private const STATUS_CONFIRM = 1;
    private const STATUS_CANCEL = -1;

    public function orderList(Request $request)
    {
        return $this->buildOrderList($request, null, 'customer.order-list');
    }

    public function orderListNew(Request $request)
    {
        return $this->buildOrderList($request, self::STATUS_NEW, 'customer.order-list-new');
    }

    public function orderListConfirm(Request $request)
    {
        return $this->buildOrderList($request, self::STATUS_CONFIRM, 'customer.order-list-confirm');
    }

    public function orderListCancel(Request $request)
    {
        return $this->buildOrderList($request, self::STATUS_CANCEL, 'customer.order-list-cancel');
    }

    private function buildOrderList(Request $request, ?int $status, string $currentRouteName)
    {
        $user = Auth::user();
        $locale = app()->getLocale();

        $orders = Order::query()
            ->where('user_id', $user->id)
            ->with([
                'items.product' => function ($query) {
                    $query->select('id', 'image_urls', 'sku');
                },
                'items.product.translations' => function ($query) use ($locale) {
                    $query->select('product_id', 'language', 'name')
                        ->where('language', $locale);
                },
            ])
            ->when(!is_null($status), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($request->filled('keyword'), function ($query) use ($request) {
                $keyword = trim($request->keyword);
                $query->where('order_number', 'like', '%' . $keyword . '%');
            })
            ->latest()
            ->paginate(10)
            ->onEachSide(1)
            ->withQueryString();

        $this->mapOrderItems($orders, $locale);

        if ($request->ajax()) {
            return view('customer.layout.partials._order-list', compact('orders'))->render();
        }

        return view('customer.layout.order', [
            'user' => $user,
            'headerWhite' => 'header-secondary',
            'sidebarActive' => 'order',
            'orders' => $orders,
            'listUrl' => route($currentRouteName),
            'status' => $status,
        ]);
    }

    private function mapOrderItems($orders, string $locale): void
    {
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $product = $item->product;

                $item->product_image = $this->getImageJson($product?->image_urls);

                $translation = $product?->translations->first();

                $item->product_name = $translation?->name
                    ?? $item->product_name
                    ?? $product?->sku
                    ?? $item->product_sku
                    ?? 'Sản phẩm không tồn tại';
            }
        }
    }

    public function orderDetail($id)
    {
        $user = Auth::user();

        $order = Order::query()
            ->where('user_id', $user->id)
            ->with(['items.product.translations'])
            ->findOrFail($id);

        return view('customer.layout.order-detail', [
            'user' => $user,
            'headerWhite' => 'header-secondary',
            'sidebarActive' => 'order',
            'order' => $order,
        ]);
    }
}
