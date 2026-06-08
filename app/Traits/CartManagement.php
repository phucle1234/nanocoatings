<?php

namespace App\Traits;

use App\Models\Product;
use Illuminate\Support\Facades\Session;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait CartManagement
{
    /**
     * Session key để lưu giỏ hàng
     */
    protected function getCartSessionKey(): string
    {
        return 'bags_cart_items';
    }

    /**
     * Lấy giỏ hàng từ session - nếu user đã đăng nhập thì sẽ đồng bộ với DB nếu session rỗng
     * 
     * @return array
     */
    protected function getCart(): array
    {
        $cart = Session::get($this->getCartSessionKey(), []);

        if (!empty($cart) || !Auth::check()) {
            return $cart;
        }

        $dbCart = Cart::with('items')
            ->where('user_id', Auth::id())
            ->where('type', 'customer')
            ->where('is_checked_out', false)
            ->first();

        if (!$dbCart) {
            return [];
        }

        $cart = [];
        foreach ($dbCart->items as $item) {
            $qty = (int) $item->quantity;
            if ($qty > 0) {
                $cart[$item->product_id] = [
                    'quantity' => $qty,
                    'added_at' => now()->toDateTimeString(),
                ];
            }
        }

        Session::put($this->getCartSessionKey(), $cart);

        return $cart;
    }

    /**
     * Lưu giỏ hàng vào session
     * 
     * @param array $cart
     * @return void
     */
    protected function saveCart(array $cart): void
    {
        Session::put($this->getCartSessionKey(), $cart);
    }

    /**
     * Thêm sản phẩm vào giỏ hàng
     * 
     * @param int $productId
     * @param int $quantity
     * @return array ['success' => bool, 'message' => string, 'cart_count' => int|null]
     */
    protected function addToCart(int $productId, int $quantity): array
    {
        // Kiểm tra sản phẩm tồn tại và đang active
        $product = Product::where('id', $productId)
            ->where('is_active', 1)
            ->first();

        if (!$product) {
            return [
                'success' => false,
                'message' => 'Sản phẩm không tồn tại hoặc đã bị xóa.',
                'cart_count' => null,
            ];
        }

        // Kiểm tra số lượng còn lại
        if ($product->amount !== null && $quantity > $product->amount) {
            return [
                'success' => false,
                'message' => 'Số lượng sản phẩm không đủ. Còn lại: ' . $product->amount . ' sản phẩm.',
                'cart_count' => null,
            ];
        }

        // Lấy giỏ hàng hiện tại
        $cart = $this->getCart();

        // Kiểm tra sản phẩm đã có trong giỏ hàng chưa
        if (isset($cart[$productId])) {
            // Cộng thêm số lượng
            $newQuantity = $cart[$productId]['quantity'] + $quantity;

            // Kiểm tra lại số lượng tổng
            if ($product->amount !== null && $newQuantity > $product->amount) {
                $newQuantity = $product->amount;
            }

            $cart[$productId]['quantity'] = $newQuantity;
        } else {
            // Thêm mới sản phẩm
            $cart[$productId] = [
                'quantity' => $quantity,
                'added_at' => now()->toDateTimeString(),
            ];
        }

        // Lưu vào session
        $this->saveCart($cart);

        // Lưu vào Table Cart nếu user đã đăng nhập
        $this->syncAddToPersistentCart($productId, $quantity);

        // Tính tổng số lượng sản phẩm trong giỏ hàng
        $totalQuantity = $this->getCartTotalQuantity($cart);

        return [
            'success' => true,
            'message' => 'Đã thêm sản phẩm vào giỏ hàng.',
            'cart_count' => $totalQuantity,
        ];
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ hàng
     * 
     * @param int $productId
     * @param int $quantity
     * @return array ['success' => bool, 'message' => string, 'subtotal' => float, 'shipping_fee' => float, 'total' => float]
     */
    protected function updateCartItem(int $productId, int $quantity): array
    {
        $cart = $this->getCart();

        if (!isset($cart[$productId])) {
            return [
                'success' => false,
                'message' => 'Sản phẩm không tồn tại trong giỏ hàng.',
                'subtotal' => 0,
                'shipping_fee' => 0,
                'total' => 0,
            ];
        }

        $product = Product::where('id', $productId)
            ->where('is_active', 1)
            ->first();

        if (!$product) {
            // Xóa sản phẩm không tồn tại
            unset($cart[$productId]);
            $this->saveCart($cart);
            $this->syncRemoveFromPersistentCart($productId);

            return [
                'success' => false,
                'message' => 'Sản phẩm không tồn tại hoặc đã bị xóa.',
                'subtotal' => 0,
                'shipping_fee' => 0,
                'total' => 0,
            ];
        }

        // Kiểm tra số lượng còn lại
        if ($product->amount !== null && $quantity > $product->amount) {
            return [
                'success' => false,
                'message' => 'Số lượng sản phẩm không đủ. Còn lại: ' . $product->amount . ' sản phẩm.',
                'subtotal' => 0,
                'shipping_fee' => 0,
                'total' => 0,
            ];
        }

        // Cập nhật số lượng
        $cart[$productId]['quantity'] = $quantity;
        $this->saveCart($cart);

        // Cập nhật vào Table Cart nếu user đã đăng nhập
        $this->syncUpdateToPersistentCart($productId, $quantity);

        // Tính lại tổng tiền
        $totals = $this->calculateCartTotals($cart);

        return [
            'success' => true,
            'message' => 'Đã cập nhật số lượng.',
            'subtotal' => $totals['subtotal'],
            'shipping_fee' => $totals['shipping_fee'],
            'total' => $totals['total'],
        ];
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng
     * 
     * @param int $productId
     * @return array ['success' => bool, 'message' => string, 'subtotal' => float, 'shipping_fee' => float, 'total' => float]
     */
    protected function removeFromCart(int $productId): array
    {
        $cart = $this->getCart();

        if (!isset($cart[$productId])) {
            return [
                'success' => false,
                'message' => 'Sản phẩm không tồn tại trong giỏ hàng.',
                'subtotal' => 0,
                'shipping_fee' => 0,
                'total' => 0,
            ];
        }

        // Xóa sản phẩm khỏi giỏ hàng
        unset($cart[$productId]);
        $this->saveCart($cart);
        $this->syncRemoveFromPersistentCart($productId);

        // Tính lại tổng tiền
        $totals = $this->calculateCartTotals($cart);

        return [
            'success' => true,
            'message' => 'Đã xóa sản phẩm khỏi giỏ hàng.',
            'subtotal' => $totals['subtotal'],
            'shipping_fee' => $totals['shipping_fee'],
            'total' => $totals['total'],
        ];
    }

    /**
     * Xóa toàn bộ giỏ hàng
     * 
     * @return void
     */
    protected function clearCart(): void
    {
        Session::forget($this->getCartSessionKey());
        // Xóa toàn bộ sản phẩm khỏi Table Cart nếu user đã đăng nhập
        $this->syncClearPersistentCart();
    }

    /**
     * Tính tổng số lượng sản phẩm trong giỏ hàng
     * 
     * @param array|null $cart
     * @return int
     */
    protected function getCartTotalQuantity(?array $cart = null): int
    {
        $cart = $cart ?? $this->getCart();
        $totalQuantity = 0;

        foreach ($cart as $item) {
            $totalQuantity += $item['quantity'];
        }

        return $totalQuantity;
    }

    /**
     * Tính tổng tiền giỏ hàng
     * 
     * @param array|null $cart
     * @param float $shippingFee
     * @return array ['subtotal' => float, 'shipping_fee' => float, 'total' => float]
     */
    protected function calculateCartTotals(?array $cart = null, float $shippingFee = 0): array
    {
        $cart = $cart ?? $this->getCart();
        $subtotal = 0;
        foreach ($cart as $productId => $item) {
            $product = Product::where('id', $productId)
                ->where('is_active', 1)
                ->first();

            if ($product) {
                $subtotal += $product->price * $item['quantity'];
            }
        }

        $total = $subtotal + $shippingFee;

        return [
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'total' => $total,
        ];
    }

    /**
     * Lấy danh sách sản phẩm trong giỏ hàng với thông tin đầy đủ
     * 
     * @return array
     */
    protected function getCartItemsWithProducts(): array
    {
        $cartItems = $this->getCart();
        $products = [];
        $subtotal = 0;

        // Lấy thông tin sản phẩm từ database
        foreach ($cartItems as $productId => $item) {
            $product = Product::where('id', $productId)
                ->where('is_active', 1)
                ->first();

            if ($product) {
                $product->has_display_price = $product->hasDisplayablePrice();
                $product->price_display = $product->priceDisplayLabel();
                $products[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'cart_id' => $productId, // Dùng ProductID làm cart_id
                ];
                $subtotal += $product->Price * $item['quantity'];
            } else {
                // Xóa sản phẩm không tồn tại khỏi giỏ hàng
                unset($cartItems[$productId]);
                $this->saveCart($cartItems);
            }
        }

        return $products;
    }


    // các hàm sync với Table Cart để lưu trữ giỏ hàng lâu dài cho user đã đăng nhập
    protected function getCustomerPersistentCart(): ?Cart
    {
        $sessionId = session()->getId();
        if (!Auth::check()) {
            return null;
        }

        $userId = Auth::id();

        // user_id phải luôn có
        if (!$userId) {
            return null;
        }

        // Khi đã đăng nhập thì lấy/tạo cart theo user_id
        return Cart::getOrCreateCart($userId, 'customer', $sessionId);
    }

    protected function syncAddToPersistentCart(int $productId, int $quantity): void
    {
        if (!Auth::check()) {
            return;
        }

        try {
            $cart = $this->getCustomerPersistentCart();
            if (!$cart) {
                return;
            }
            $cart->addItem($productId, $quantity);
        } catch (\Throwable $e) {
            Log::error('syncAddToPersistentCart failed', [
                'product_id' => $productId,
                'quantity' => $quantity,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function syncUpdateToPersistentCart(int $productId, int $quantity): void
    {
        if (!Auth::check()) {
            return;
        }

        try {
            $cart = $this->getCustomerPersistentCart();
            if (!$cart) {
                return;
            }
            $cart->updateItemQuantity($productId, $quantity);
        } catch (\Throwable $e) {
            Log::error('syncUpdateToPersistentCart failed', [
                'product_id' => $productId,
                'quantity' => $quantity,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function syncRemoveFromPersistentCart(int $productId): void
    {
        try {
            $cart = Cart::getCart(Auth::id(), 'customer', session()->getId());
            if ($cart) {
                $cart->removeItem($productId);
            }
        } catch (\Throwable $e) {
            Log::error('syncRemoveFromPersistentCart failed', [
                'product_id' => $productId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function syncClearPersistentCart(): void
    {
        if (!Auth::check()) {
            return;
        }

        try {
            $cart = Cart::getCart(Auth::id(), 'customer');
            if ($cart) {
                $cart->clear();
            }
        } catch (\Throwable $e) {
            Log::error('syncClearPersistentCart failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
