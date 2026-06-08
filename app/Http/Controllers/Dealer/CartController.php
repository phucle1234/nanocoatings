<?php

namespace App\Http\Controllers\Dealer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\CasuminaApi\ProductService;
use App\Services\CasuminaApi\CartService;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Province;

class CartController extends Controller
{

    public function __construct(
        protected ProductService $productService,
        protected CartService $cartService
    ) {}
    public function cart(Request $request)
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'order-parent';
        $sidebarChildActive = 'cart';
        $user = Auth::user();

        $locale = app()->getLocale();
        $productListBuyDealer = $this->productService->getProductListBuyDealer($user->parent_code ?? $user->code);
        $products = Product::query()->whereIn('code', collect($productListBuyDealer)->pluck('item_no')->toArray());
        $products->where('is_active', true)->with([
            'translations' => function ($q) use ($locale) {
                $q->where('language', $locale);
            },
            'category.translations' => function ($q) use ($locale) {
                $q->where('language', $locale);
            }
        ]);
        if ($request->keyword) {
            $keyword = $request->keyword;
            $products->where(function ($q) use ($keyword, $locale) {
                $q->whereHas('translations', function ($subQ) use ($keyword, $locale) {
                    $subQ->where('language', $locale)
                        ->where(function ($nameQ) use ($keyword) {
                            $nameQ->where('name', 'LIKE', '%' . $keyword . '%')
                                ->orWhere('short_description', 'LIKE', '%' . $keyword . '%');
                        });
                });
            })->orWhere('code', 'LIKE', '%' . $keyword . '%');
        }
        $list = $products->orderBy('sort_order')->paginate(7)->onEachSide(1)->withQueryString();
        // Gán lại giá sản phẩm theo Nhà phân phối
        foreach ($list as $product) {
            $apiResult = $this->productService->getProductPriceBuyDealer($product->sku, $user->parent_code ?? $user->code);
            if (!empty($apiResult)) {
                $dealerPrice = collect($apiResult)->first(function ($item) use ($user) {
                    return $item->source_code === ($user->parent_code ?? $user->code) && $item->type === "1";
                });
                if ($dealerPrice) {
                    $product->price = $dealerPrice->price;
                } else {
                    $product->price = 0;
                }
            } else {
                $product->price = 0;
            }
        }
        if ($request->ajax()) {
            return view('dealer.layout.cart._table-product', compact('list'))->render();
        }

        return view('dealer.layout.cart.index', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'list'));
    }

    public function productDetail(string $id)
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'order-parent';
        $sidebarChildActive = 'cart';
        $user = Auth::user();

        if (!is_numeric($id)) {
            return redirect()->route('dealer.cart')->with('toast_error', 'Sản phẩm không tồn tại.');
        }
        try {
            $product = Product::where('is_active', true)
                ->where('id', $id)
                ->with([
                    'translations' => function ($q) {
                        $q->where('language', app()->getLocale());
                    },
                    'category.translations' => function ($q) {
                        $q->where('language', app()->getLocale());
                    },
                    'attributeValues' => function ($q) {
                        $q->with([
                            'translations' => function ($tq) {
                                $tq->where('language', app()->getLocale());
                            },
                            'attribute.translations' => function ($aq) {
                                $aq->where('language', app()->getLocale());
                            }
                        ]);
                    }
                ])
                ->firstOrFail();

            // Gán lại giá sản phẩm theo Nhà phân phối
            $apiResult = $this->productService->getProductPriceBuyDealer($product->sku, $user->parent_code ?? $user->code);
            if (!empty($apiResult)) {
                $dealerPrice = collect($apiResult)->first(function ($item) use ($user) {
                    return $item->source_code === ($user->parent_code ?? $user->code) && $item->type === "1";
                });
                if ($dealerPrice) {
                    $product->price = $dealerPrice->price;
                } else {
                    $product->price = 0;
                }
            } else {
                $product->price = 0;
            }

            return view('dealer.layout.cart.product-detail', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'product'));
        } catch (\Exception $e) {
            return redirect()->route('dealer.cart')->with('toast_error', 'Sản phẩm không tồn tại.');
        }
    }

    public function checkout()
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'order-parent';
        $sidebarChildActive = 'cart';
        $user = Auth::user();

        $cart = Cart::with([
            'items.product.translations',
            'items.product.categories.translations',
        ])
            ->where('user_id', $user->id)
            ->where('type', 'dealer_buy')
            ->where('is_checked_out', false)
            ->first();
        $provainces = Province::where('country_id', 1)->active()->ordered()->get();

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('dealer.cart')->with('toast_error', 'Giỏ hàng của bạn đang trống.');
        }
        return view('dealer.layout.cart.checkout', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'provainces'));
    }

    public function confirm($id)
    {
        try {
            $headerWhite = 'header-secondary';
            $sidebarActive = 'order-parent';
            $sidebarChildActive = 'cart';
            $user = Auth::user();
            $order = Order::with([
                'items.product.translations',
                'items.product.primaryCategory.translations',
            ])->where('user_id', $user->id)->where('type', 'dealer_buy')
                ->findOrFail($id);

            return view('dealer.layout.cart.confirm', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'order'));
        } catch (\Exception $e) {
            return redirect()->route('dealer.cart')->with('toast_error', 'Đơn hàng không tồn tại.');
        }
    }
    // Ajax
    public function loadCart(Request $request)
    {
        $user = Auth::user();

        $cart = Cart::with([
            'items.product.translations',
            'items.product.categories.translations',
        ])
            ->where('user_id', $user->id)
            ->where('type', 'dealer_buy')
            ->where('is_checked_out', false)
            ->first();


        // Kiểm tra và cập nhật lại giá từ API cho từng sản phẩm trong giỏ hàng
        if ($cart && $cart->items->isNotEmpty()) {
            $priceChanged = 0;

            foreach ($cart->items as $cartItem) {
                $apiResult = $this->productService->getProductPriceBuyDealer($cartItem->product_code, $user->parent_code ?? $user->code);
                if (!empty($apiResult)) {
                    $newPrice = 0;
                    $oldPrice = (float) $cartItem->unit_price;
                    $dealerPrice = collect($apiResult)->first(function ($item) use ($user) {
                        return $item->source_code === ($user->parent_code ?? $user->code) && $item->type === "1";
                    });

                    if ($dealerPrice) {
                        $newPrice = $dealerPrice->price;
                    }

                    if ($newPrice !== $oldPrice) {
                        // Cập nhật unit_price và total_price trong cart_item
                        CartItem::query()
                            ->where('id', $cartItem->id)
                            ->update([
                                'unit_price'  => $newPrice,
                                'total_price' => $newPrice * $cartItem->quantity,
                            ]);

                        // Cập nhật in-memory để view hiển thị đúng
                        $cartItem->unit_price  = $newPrice;
                        $cartItem->total_price = $newPrice * $cartItem->quantity;
                        $cartItem->product->price = $newPrice;

                        $priceChanged++;
                    }
                }
            }

            // Tính lại tổng tiền giỏ hàng nếu có thay đổi giá
            if ($priceChanged > 0) {
                $newTotal = $cart->items->sum('total_price');
                Cart::query()
                    ->where('id', $cart->id)
                    ->update(['total_amount' => $newTotal]);
                $cart->total_amount = $newTotal;
            }

            return response()->json([
                'status'    => 1,
                'html'      => view('dealer.layout.cart._table-cart', compact('cart'))->render(),
                'isEmpty'   => false,

            ]);
        } else {
            return response()->json([
                'status'    => 1,
                'html'      => view('dealer.layout.cart._table-cart-empty')->render(),
                'isEmpty'   => true,
            ]);
        }
    }

    public function addToCart(Request $request)
    {
        try {
            $user = Auth::user();

            $productId = $request->input('productId');
            $quantity = max(1, (int) $request->input('quantity', 1));

            // Kiểm tra sản phẩm
            $product = Product::find($productId);

            if (!$product) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Sản phẩm không tồn tại.',
                ]);
            }

            // Gán lại giá sản phẩm theo Nhà phân phối
            $apiResult = $this->productService->getProductPriceBuyDealer($product->sku, $user->parent_code ?? $user->code);
            if (!empty($apiResult)) {
                $dealerPrice = collect($apiResult)->first(function ($item) use ($user) {
                    return $item->source_code === ($user->parent_code ?? $user->code) && $item->type === "1";
                });
                if ($dealerPrice) {
                    $product->price = $dealerPrice->price;
                } else {
                    $product->price = 0;
                }
            } else {
                $product->price = 0;
            }

            if ($product->price == 0) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Không thể mua sản phẩm có giá < 0',
                ]);
            }

            $cart = Cart::getOrCreateCart($user->id, 'dealer_buy');
            $cart->addItemDealer($product, $quantity);

            return response()->json([
                'status' => 1,
                'message' => 'Đã thêm sản phẩm vào giỏ hàng.',
                'cartTotalItems' => $cart->fresh()->item_count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Đã xảy ra lỗi khi thêm sản phẩm vào giỏ hàng.',
            ]);
        }
    }

    public function updateToCart(Request $request)
    {
        try {
            $user = Auth::user();

            $itemId = $request->input('itemId');
            $quantity = max(1, (int) $request->input('quantity', 1));

            $item = CartItem::find($itemId);
            $cart = Cart::find($item->cart_id);

            if (!$item || $cart->user_id != $user->id) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Cập nhật sản phẩm lỗi.',
                ]);
            }

            $cart->updateItemQuantity($item->product_id, $quantity);

            return response()->json([
                'status' => 1,
                'message' => 'Đã cập nhật sản phẩm trong giỏ hàng.',
                'subtotal' => number_format($item->fresh()->total_price, 0, ',', '.'),
                'total_amount' => number_format($cart->fresh()->total_amount, 0, ',', '.'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Đã xảy ra lỗi khi cập nhật sản phẩm trong giỏ hàng.',
            ]);
        }
    }

    public function deleteToCart(Request $request)
    {
        try {
            $user = Auth::user();

            $itemId = $request->input('itemId');
            $item = CartItem::find($itemId);
            $cart = Cart::find($item->cart_id);
            if (!$item || $cart->user_id != $user->id) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Xóa sản phẩm lỗi.',
                ]);
            }
            $cart->removeItem($item->product_id);

            return response()->json([
                'status' => 1,
                'message' => 'Đã xóa sản phẩm khỏi giỏ hàng.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Đã xảy ra lỗi khi xóa sản phẩm khỏi giỏ hàng.',
            ]);
        }
    }

    public function checkoutInfo(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();

            $cart = Cart::where('user_id', $user->id)
                ->where('type', 'dealer_buy')
                ->where('is_checked_out', false)
                ->first();

            if (!$cart || $cart->items->isEmpty()) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Giỏ hàng trống, không thể thanh toán.',
                ]);
            }
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'string', 'max:15'],
                'email' => ['required', 'string', 'email', 'max:255'],
                'city' => ['required', 'string', 'max:255'],
                // 'district' => ['required', 'string', 'max:255'],
                // 'ward' => ['required', 'string', 'max:255'],
                'address' => ['required', 'string', 'max:255'],
            ], [], [
                'name'     => 'Tên người nhận',
                'phone'    => 'Số điện thoại',
                'email'    => 'Email đặt hàng',
                'city'     => 'Tỉnh / Thành phố',
                'address'  => 'Địa chỉ chi tiết',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 99,
                    'message' => 'Invalid data!',
                    'errors' => $validator->errors()
                ]);
            }
            $result = $this->cartService->checkoutBuy(
                $user->user_name,
                [
                    'fullname' => $request->input('name'),
                    'email' => $request->input('email'),
                    'phone' => $request->input('phone'),
                    'address' => $request->input('address'),
                    'city_code' => $request->input('city'),
                    'note' => $request->input('note'),
                ],
                $cart->items->map(function ($item) {
                    return (object) [
                        'item_no'  => $item->product_code,
                        'quantity' => $item->quantity,
                        'price'    => (int)$item->unit_price,
                    ];
                })->toArray()
            );
            if (isset($result->error_no) && $result->error_no === "") {
                $order = Order::create([
                    'user_id'         => $user->id,
                    'order_number'    => $result->order_no,
                    'status'          => 0,
                    'payment_method'  => null,
                    'payment_status'  => 'pending',
                    'subtotal'        => $cart->total_amount,
                    'tax_amount'      => 0,
                    'shipping_amount' => 0,
                    'discount_amount' => 0,
                    'total_amount'    => $cart->total_amount,
                    'notes'           => $request->input('note'),
                    'address'         => [
                        'name'     => $request->input('name'),
                        'phone'    => $request->input('phone'),
                        'email'    => $request->input('email'),
                        'city'     => $request->input('city'),
                        'address'  => $request->input('address'),
                    ],
                    'type'            => 'dealer_buy',
                ]);

                $cart->load('items.product');
                foreach ($cart->items as $cartItem) {
                    OrderItem::create([
                        'order_id'     => $order->id,
                        'product_id'   => $cartItem->product_id,
                        'product_name' => $cartItem->product->name ?? $cartItem->product_code,
                        'product_sku'  => $cartItem->product->sku ?? $cartItem->product_code ?? '',
                        'quantity'     => $cartItem->quantity,
                        'unit_price'   => $cartItem->unit_price,
                        'total_price'  => $cartItem->total_price,
                    ]);
                }
                $cart->is_checked_out = true;
                $cart->save();

                DB::commit();

                return response()->json([
                    'status' => 1,
                    'message' => 'Đặt hàng thành công.',
                    'url' => route('dealer.cart-confirm', ['id' => $order->id]),
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'status' => 0,
                    'message' => 'Lỗi: ' . ($result->error_no ?? 'Lỗi hệ thống'),
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'message' => 'Đã xảy ra lỗi khi đặt hàng.',
            ]);
        }
    }
}
