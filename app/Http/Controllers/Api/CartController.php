<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\CartManagement;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\CasuminaApi\CartService;

class CartController extends Controller
{
    use CartManagement;
    protected $cartService;
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }
    /**
     * Lấy danh sách sản phẩm trong giỏ hàng
     */
    public function index()
    {
        $products = $this->getCartItemsWithProducts();
        foreach ($products as &$item) {
            if (isset($item['product'])) {
                $productId = $item['product']['id'];
                $translation = \DB::table('product_translations')
                    ->where('product_id', $productId)
                    ->where('language', app()->getLocale())
                    ->first();

                if ($translation) {
                    $item['product']['name'] = $translation->name;
                    $item['product']['description'] = $translation->description ?? '';
                }
            }
        }

        $totals = $this->calculateCartTotals();

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $products,
                'totals' => $totals,
            ],
        ]);
    }

    /**
     * Thêm sản phẩm vào giỏ hàng
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $result = $this->addToCart(
            (int) $request->product_id,
            (int) $request->quantity
        );

        $statusCode = $result['success'] ? 200 : ($result['message'] === 'Sản phẩm không tồn tại hoặc đã bị xóa.' ? 404 : 400);

        return response()->json($result, $statusCode);
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ hàng
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $result = $this->updateCartItem(
            (int) $id,
            (int) $request->quantity
        );

        $statusCode = $result['success'] ? 200 : 404;

        return response()->json($result, $statusCode);
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng
     */
    public function remove($id)
    {
        $result = $this->removeFromCart((int) $id);

        $statusCode = $result['success'] ? 200 : 404;

        return response()->json($result, $statusCode);
    }

    /**
     * Xóa toàn bộ giỏ hàng
     */
    public function clear()
    {
        $this->clearCart();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa toàn bộ giỏ hàng.',
        ]);
    }

    public function checkout(Request $request)
    {
        // Kiểm tra đăng nhập
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để tiếp tục mua hàng',
                'redirect' => route('login')
            ], 401);
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:cod,atm,visa,vnpay',
            'dealer_name' => 'nullable|string',
            'dealer_address' => 'nullable|string',
            'dealer_code' => 'nullable|string|max:50',


            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:30',
            'recipient_address' => 'required|string|max:1000',
            'recipient_note' => 'nullable|string|max:2000',
        ], [
            'recipient_name.required' => 'Tên người nhận là bắt buộc.',
            'recipient_phone.required' => 'Số điện thoại người nhận là bắt buộc.',
            'recipient_address.required' => 'Địa chỉ người nhận là bắt buộc.',
        ]);


        try {
            DB::beginTransaction();

            // Lấy giỏ hàng
            $products = $this->getCartItemsWithProducts();

            if (empty($products)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Giỏ hàng trống'
                ], 400);
            }

            // Load product translations
            foreach ($products as &$item) {
                if (isset($item['product'])) {
                    $productId = $item['product']['id'];
                    $translation = \DB::table('product_translations')
                        ->where('product_id', $productId)
                        ->where('language', app()->getLocale())
                        ->first();

                    if ($translation) {
                        $item['product']['name'] = $translation->name;
                    }
                }
            }
            unset($item);


            $totals = $this->calculateCartTotals();

            // Tạo order number
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

            $recipient = [
                'name' => $validated['recipient_name'],
                'phone' => $validated['recipient_phone'],
                'address' => $validated['recipient_address'],
                'note' => $validated['recipient_note'] ?? null,
                'city_code' => $request['city_code'] ?? null,
            ];

            // Tạo đơn hàng
            $order = Order::create([
                'user_id' => Auth::id(),
                'order_number' => $orderNumber,
                'status' => 0,
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'pending',
                'subtotal' => $totals['subtotal'],
                'tax_amount' => 0,
                'shipping_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => $totals['total'],
                'notes' => $validated['recipient_note'] ?? null,
                'address' => json_encode($recipient, JSON_UNESCAPED_UNICODE),
                'type' => 'customer',
                'dealer_code' => $validated['dealer_code'] ?? null,
            ]);


            // Tạo order items
            foreach ($products as $item) {
                $product = $item['product'];
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product['id'],
                    'product_name' => $product['name'],
                    'product_sku' => $product['sku'] ?? '',
                    'quantity' => $item['quantity'],
                    'unit_price' => $product['price'],
                    'total_price' => $product['price'] * $item['quantity'],
                    'options' => null,
                ]);
            }

            $result = $this->cartService->checkout(
                Auth::user()->user_name,
                [
                    'username'  => Auth::user()->email,
                    'fullname'  => Auth::user()->name,
                    'email'     => Auth::user()->email,
                    'phone'     => Auth::user()->phone,
                    'address' => Auth::user()->address ?? Auth::user()->email,
                    'city_code' => $request['city_code'] ?? null,
                    'source_code' => $validated['dealer_code'] ?? null,
                    'note'      => $request->input('note'),
                ],
                collect($products)->map(function ($item) {
                    $product = $item['product'];
                    return (object) [
                        'item_no'  => $product['sku'] ?? '',
                        'quantity' => $item['quantity'],
                        'price'    => (int) $product['price'],
                    ];
                })->toArray()
            );

            if (isset($result->error_no) && $result->error_no === "") {
                Order::where('id', $order->id)->update([
                    'order_number' => $result->order_no,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Đặt hàng thất bại',
                    'data' => $result->error_no,
                ], 400);
            }
            // Xóa giỏ hàng sau khi đặt hàng thành công
            $this->clearCart();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đặt hàng thành công',
                'data' => [
                    'order_number' => $result->order_no,
                    'order_id' => $order->id,
                    'total_amount' => $order->total_amount,
                    'payment_method' => $order->payment_method,
                    'created_at' => $order->created_at->format('H:i - d/m/Y'),
                    'items' => $products,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
}
