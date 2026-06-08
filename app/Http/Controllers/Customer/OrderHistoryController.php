<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use App\Traits\HasImage;

class OrderHistoryController extends Controller
{
    use HasImage;

    public function orderHistory(Request $request)
    {
        $user = Auth::user();
        
        // Get orders
        $query = Order::where('user_id', $user->id)
            ->with(['items.product.translations'])
            ->orderBy('created_at', 'desc');

        // Paginate
        $orders = $query->paginate(10);

        // Xử lý ảnh sản phẩm
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $item->product_image = $this->getImageJson($item->product->image_urls);
                $item->product_name = $item->product->translations
                    ->where('language', app()->getLocale())
                    ->first()
                    ->name ?? $item->product->sku;
            }
        }

        // Statistics
        $stats = [
            'total' => Order::where('user_id', $user->id)->count(),
            'pending' => Order::where('user_id', $user->id)->where('status', 'pending')->count(),
            'processing' => Order::where('user_id', $user->id)->where('status', 'processing')->count(),
            'completed' => Order::where('user_id', $user->id)->where('status', 'completed')->count(),
            'cancelled' => Order::where('user_id', $user->id)->where('status', 'cancelled')->count(),
        ];

        return view('user.layout.order-history', compact('orders', 'stats'));
    }
}