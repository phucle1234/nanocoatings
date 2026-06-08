<?php

namespace App\Http\Controllers\langding;

use App\Http\Controllers\Controller;
use App\Traits\CarSearch;
use App\Http\Controllers\Api\CartController as ApiCartController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\User;
use App\Providers\TelegramServiceProvider;
use App\Models\Province;

class CartController extends Controller
{
    use CarSearch;

    /**
     * Hiển thị trang giỏ hàng
     */
    public function index(Request $request, $orderNumber = null)
    {
        $cartData = [
            'success' => true,
            'data' => [
                'items' => [],
                'totals' => [
                    'subtotal' => 0,
                    'shipping_fee' => 0,
                    'total' => 0,
                ],
            ],
        ];

        $provainces = Province::where('country_id', 1)->active()->ordered()->get();
        $distributors = collect();

        if (Auth::check()) {
            $user = Auth::user();

            $cart = Cart::with(['items.product'])
                ->where('user_id', $user->id)
                ->where('is_checked_out', false)
                ->where('type', 'customer')
                ->first();

            if ($cart && $cart->items->isNotEmpty()) {
                $items = [];
                $subtotal = 0;

                foreach ($cart->items as $cartItem) {
                    $product = $cartItem->product;
                    if (!$product || (int) $product->is_active !== 1) {
                        continue;
                    }

                    $translation = \DB::table('product_translations')
                        ->where('product_id', $product->id)
                        ->where('language', app()->getLocale())
                        ->first();

                    $name = $translation->name ?? $product->name ?? '';
                    $description = $translation->description ?? $product->description ?? '';

                    $price = (float) $product->price;
                    $quantity = (int) $cartItem->quantity;
                    $subtotal += ($price * $quantity);

                    $items[] = [
                        'product' => [
                            'id' => $product->id,
                            'name' => $name,
                            'description' => $description,
                            'code' => $product->code ?? '',
                            'sku' => $product->sku ?? '',
                            'price' => $price,
                            'old_price' => (float) ($product->old_price ?? 0),
                            'has_display_price' => $product->hasDisplayablePrice(),
                            'price_display' => $product->priceDisplayLabel(),
                            'image' => $product->image_urls[0] ?? asset('images/no-image.png'),
                        ],
                        'quantity' => $quantity,
                        'cart_id' => $product->id,
                    ];
                }

                $cartData['data']['items'] = $items;
                $cartData['data']['totals']['subtotal'] = $subtotal;
                $cartData['data']['totals']['shipping_fee'] = 0;
                $cartData['data']['totals']['total'] = $subtotal;
            }

            $distributors = User::query()
                ->select([
                    'code',
                    'parent_code',
                    'name',
                    'email',
                    'address',
                    'city_name',
                    'country',
                    'latitude',
                    'longitude',
                    'phone',
                    'city_code',
                ])
                ->whereNotNull('parent_code')
                ->where('parent_code', '!=', '')
                ->where('city_code', '=', $user->city_code)
                ->where('role', 'dealer')
                ->orderBy('name')
                ->get();
        }

        // Nếu có order number, load thông tin đơn hàng
        $orderData = null;
        if ($orderNumber) {
            $order = \App\Models\Order::where('order_number', $orderNumber)->first();

            if ($order) {
                $orderItems = \App\Models\OrderItem::where('order_id', $order->id)->get();

                $orderData = [
                    'order_number' => $order->order_number,
                    'order_id' => $order->id,
                    'total_amount' => $order->total_amount,
                    'payment_method' => $order->payment_method,
                    'created_at' => $order->created_at->format('H:i - d/m/Y'),
                    'items' => $orderItems->map(function ($item) {
                        return [
                            'product' => [
                                'name' => $item->product_name,
                                'image_urls' => $item->product->image_urls ?? [asset('images/no-image.png')],
                            ],
                            'quantity' => $item->quantity,
                            'unit_price' => (float) $item->unit_price,
                            'total_price' => (float) $item->total_price,
                        ];
                    })->toArray(),
                ];
            }
        }

        return view('langding.cart', compact('cartData', 'orderData', 'distributors', 'provainces'));
    }

    public function distributorsByCity(Request $request)
    {
        $cityCode = strtoupper(trim($request->get('city_code', '')));

        if (!$cityCode) {
            return response()->json([
                'success' => false,
                'message' => 'city_code is required',
                'data' => [],
            ], 422);
        }

        $dealers = User::query()
            ->select([
                'name',
                'user_name',
                'email',
                'address',
                'latitude',
                'longitude',
                'phone',
                'city_code',
                'code',
                'city_name',
            ])
            ->where('role', 'dealer')
            ->where('city_code', $cityCode)
            ->where('is_active', 1)
            ->whereNotNull('parent_code')
            ->where('parent_code', '!=', '')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $dealers,
        ]);
    }
    /**
     * Thêm sản phẩm vào giỏ hàng - gọi API
     */
    public function add(Request $request)
    {
        $apiCartController = app(ApiCartController::class);
        $apiResponse = $apiCartController->add($request);
        $result = json_decode($apiResponse->getContent(), true);

        $statusCode = $result['success'] ? 200 : ($result['message'] === 'Sản phẩm không tồn tại hoặc đã bị xóa.' ? 404 : 400);

        return response()->json($result, $statusCode);
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ hàng - gọi API
     */
    public function update(Request $request, $id)
    {
        $apiCartController = app(ApiCartController::class);

        $apiResponse = $apiCartController->update($request, $id);
        $result = json_decode($apiResponse->getContent(), true);

        $statusCode = $result['success'] ? 200 : 404;

        return response()->json($result, $statusCode);
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng - gọi API
     */
    public function remove($id)
    {
        $apiCartController = app(ApiCartController::class);
        $apiResponse = $apiCartController->remove($id);
        $result = json_decode($apiResponse->getContent(), true);

        $statusCode = $result['success'] ? 200 : 404;

        return response()->json($result, $statusCode);
    }

    /**
     * Xóa toàn bộ giỏ hàng - gọi API
     */
    public function clear()
    {
        $apiCartController = app(ApiCartController::class);
        $apiResponse = $apiCartController->clear();
        $result = json_decode($apiResponse->getContent(), true);

        return response()->json($result);
    }

    /**
     * Checkout - tạo đơn hàng
     */
    public function checkout(Request $request)
    {
        $apiCartController = app(ApiCartController::class);
        $apiResponse = $apiCartController->checkout($request);
        $result = json_decode($apiResponse->getContent(), true);

        if ($result['success']) {
            $orderNumber = $result['data']['order_number'];

            try {
                $order = \App\Models\Order::with('items')->where('order_number', $orderNumber)->first();

                if ($order) {
                    $addressData = $order->address;
                    if (is_string($addressData)) {
                        $addressData = json_decode($addressData, true);
                    } elseif (is_object($addressData)) {
                        $addressData = (array) $addressData;
                    }

                    $recipientName = $addressData['name'] ?? Auth::user()->name ?? 'N/A';
                    $recipientPhone = $addressData['phone'] ?? Auth::user()->phone ?? 'N/A';
                    $recipientAddress = $addressData['address'] ?? 'N/A';
                    $recipientNote = $addressData['note'] ?? '';

                    $itemsText = '';
                    foreach ($order->items as $idx => $item) {
                        $lineNo = $idx + 1;
                        $lineTotal = number_format((float) $item->total_price, 0, ',', '.');
                        $unitPrice = number_format((float) $item->unit_price, 0, ',', '.');
                        $itemsText .= $lineNo . '. ' . ($item->product_name ?? 'Sản phẩm') .
                            ' | SL: ' . (int) $item->quantity .
                            ' | Giá: ' . $unitPrice . "đ" .
                            ' | Thành tiền: ' . $lineTotal . "đ\n";
                    }

                    $totalText = number_format((float) $order->total_amount, 0, ',', '.');

                    $message = "🛒 <b>ĐƠN ĐẶT HÀNG MỚI</b>\n";
                    $message .= str_repeat("━", 26) . "\n";
                    $message .= "📦 <b>Mã đơn:</b> {$order->order_number}\n";
                    $message .= "💳 <b>Thanh toán:</b> {$order->payment_method}\n";
                    $message .= "🏬 <b>Mã NPP:</b> " . ($order->dealer_code ?? 'N/A') . "\n";
                    $message .= "🏬 <b>City code:</b> " . ($request->city_code ?? '') . "\n";
                    $message .= "👤 <b>Người nhận:</b> {$recipientName}\n";
                    $message .= "📞 <b>SĐT:</b> {$recipientPhone}\n";
                    $message .= "📍 <b>Địa chỉ:</b> {$recipientAddress}\n";
                    if (!empty($recipientNote)) {
                        $message .= "📝 <b>Lời nhắn:</b> {$recipientNote}\n";
                    }
                    $message .= "⏰ <b>Thời gian:</b> " . now()->format('d/m/Y H:i:s') . "\n";
                    $message .= "\n📋 <b>Chi tiết sản phẩm:</b>\n" . ($itemsText ?: "Không có dữ liệu\n");
                    $message .= "\n💰 <b>Tổng tiền:</b> {$totalText}đ";

                    (new TelegramServiceProvider())->sendMessage($message);
                }
            } catch (\Throwable $e) {
                \Log::error('Send telegram order success failed: ' . $e->getMessage());
            }


            return redirect()->route('cart.success', ['orderNumber' => $orderNumber])
                ->with('toast_success', __('messages.order_success2') . $orderNumber);
        } else {
            return back()->withInput()->with('toast_error', __('messages.order_failure'));
        }
    }
}
