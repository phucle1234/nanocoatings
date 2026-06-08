<?php

namespace App\Http\Controllers\Dealer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\User;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CartItem;
use Illuminate\Http\Request;
use App\Services\CasuminaApi\ProductService;
use App\Services\CasuminaApi\CustomerService;
use App\Services\CasuminaApi\CartService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Services\CasuminaApi\WarrantyService;
use App\Models\Province;

class LoanOrderController extends Controller
{
    public function __construct(
        protected ProductService $productService,
        protected CustomerService $customerService,
        protected CartService $cartService,
        protected WarrantyService $warrantyService,
    ) {}

    public function index(Request $request)
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'order-parent';
        $sidebarChildActive = 'loan-order';
        $user = Auth::user();

        $locale = app()->getLocale();
        $productListBuyDealer = collect($this->productService->getProductListBuyDealer($user->parent_code ?? $user->code))
            // ->where('type', 0)
            ->values()
            ->all();

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
                    return $item->source_code === ($user->parent_code ?? $user->code) && $item->type === "2";
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
            return view('dealer.layout.loan-order._table-product', compact('list'))->render();
        }

        return view('dealer.layout.loan-order.index', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'list'));
    }
    public function productDetail(string $id)
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'order-parent';
        $sidebarChildActive = 'loan-order';
        $user = Auth::user();

        if (!is_numeric($id)) {
            return redirect()->route('dealer.loan-order')->with('toast_error', 'Sản phẩm không tồn tại.');
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
                    return $item->source_code === ($user->parent_code ?? $user->code) && $item->type === "2";
                });
                if ($dealerPrice) {
                    $product->price = $dealerPrice->price;
                } else {
                    $product->price = 0;
                }
            } else {
                $product->price = 0;
            }

            return view('dealer.layout.loan-order.product-detail', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'product'));
        } catch (\Exception $e) {
            return redirect()->route('dealer.loan-order')->with('toast_error', 'Sản phẩm không tồn tại.');
        }
    }
    public function partner()
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'order-parent';
        $sidebarChildActive = 'loan-order';
        $user = Auth::user();
        $cart = Cart::where('user_id', $user->id)
            ->where('type', 'dealer_loan')
            ->where('is_checked_out', false)
            ->first();
        if (!$cart || $cart?->items->isEmpty()) {
            return redirect()->route('dealer.loan-order')->with('toast_error', 'Giỏ hàng trống!.');
        }
        $dealerPartnerCodeSelected = $cart->dealer_code;
        $provinces = Province::where('country_id', 1)->active()->ordered()->get();
        $dealerPartner = null;
        $provinceSelected = null;
        if ($dealerPartnerCodeSelected) {
            $dealerPartner = User::where('code', $dealerPartnerCodeSelected)->whereNull('parent_code')->first();
            $provinceSelected = $dealerPartner?->city_code;
        }

        return view('dealer.layout.loan-order.partner', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'cart', 'provinces', 'dealerPartner', 'provinceSelected', 'dealerPartnerCodeSelected'));
    }
    public function qr()
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'order-parent';
        $sidebarChildActive = 'loan-order';
        $user = Auth::user();
        $cart = Cart::with([
            'items.product.translations',
            'items.product.categories.translations',
        ])
            ->where('user_id', $user->id)
            ->where('type', 'dealer_loan')
            ->where('is_checked_out', false)
            ->first();
        if (!$cart || $cart?->items->isEmpty()) {
            return redirect()->route('dealer.loan-order')->with('toast_error', 'Giỏ hàng trống!');
        }
        $dealerPartner = User::where('code', $cart->dealer_code)->whereNull('parent_code')->first();
        foreach ($cart->items as $item) {
            $rawOptions = $item->options;
            $qrcodeCurrent = [];
            if (!empty($rawOptions)) {
                $qrcodeCurrent = array_map('trim', explode(',', $rawOptions));
            }
            $item->qrcodes = $qrcodeCurrent;
        }
        return view('dealer.layout.loan-order.qr', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'cart', 'dealerPartner'));
    }
    public function confirm()
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'order-parent';
        $sidebarChildActive = 'loan-order';
        $user = Auth::user();
        return view('dealer.layout.loan-order.confirm', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive'));
    }

    // Ajax
    public function loadCart(Request $request)
    {
        $user = Auth::user();
        // $type index or checkout
        $type = $request->input('type', 'index');
        $cart = Cart::with([
            'items.product.translations',
            'items.product.categories.translations',
        ])
            ->where('user_id', $user->id)
            ->where('type', 'dealer_loan')
            ->where('is_checked_out', false)
            ->first();

        // Kiểm tra và cập nhật lại giá từ API cho từng sản phẩm trong giỏ hàng
        if ($cart && $cart->items->isNotEmpty()) {
            $priceChanged = 0;
            $hasErrorItem = 0;
            foreach ($cart->items as $cartItem) {
                $priceResult = $this->productService->getProductPriceBuyDealer($cartItem->product_code, $user->parent_code ?? $user->code);
                if (!empty($priceResult)) {
                    // $stockResult = $this->productService->getProductStock($user->code, $cartItem->product_code);
                    // if ($stockResult && $stockResult->error_no == '' && $stockResult->quantity > 0) {
                    //     if ($stockResult->quantity < $cartItem->quantity) {
                    //         $cartItem->error = 'Kho hàng chỉ còn ' . $stockResult->quantity . ' sản phẩm.';
                    //         $hasErrorItem++;
                    //     }
                    // } else {
                    //     $cartItem->error = 'Sản phẩm tạm hết hàng.';
                    //     $hasErrorItem++;
                    // }
                    $newPrice = 0;
                    $oldPrice = (float) $cartItem->unit_price;
                    $dealerPrice = collect($priceResult)->first(function ($item) use ($user) {
                        return $item->source_code === ($user->parent_code ?? $user->code) && $item->type === "2";
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
                        if ($newPrice == 0) {
                            $cartItem->error = 'Sản phẩm đã thay đổi giá = 0';
                            $hasErrorItem++;
                        }

                        $priceChanged++;
                    }
                } else {
                    $cartItem->error = 'Không tìm thấy giá sản phẩm.';
                    $hasErrorItem++;
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
                'html'      => view('dealer.layout.loan-order._table-cart', compact('cart', 'type', 'hasErrorItem'))->render(),
                'isEmpty'   => false,
                'hasErrorItem' => $hasErrorItem,
                'redirect'  => route('dealer.loan-order'),
            ]);
        } else {
            return response()->json([
                'status'    => 1,
                'html'      => view('dealer.layout.loan-order._table-cart-empty')->render(),
                'isEmpty'   => true,
                'redirect'  => route('dealer.loan-order'),
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

            // Gán lại giá sản phẩm
            $apiResult = $this->productService->getProductPriceBuyDealer($product->sku, $user->parent_code ?? $user->code);
            if (!empty($apiResult)) {
                $dealerPrice = collect($apiResult)->first(function ($item) use ($user) {
                    return $item->source_code === ($user->parent_code ?? $user->code) && $item->type === "2";
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
                    'message' => 'Không thể mua sản phẩm có giá = 0',
                ]);
            }
            $cart = Cart::getOrCreateCart($user->id, 'dealer_loan');
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

            $cart->updateItemQuantity($item->product_id, $quantity, $item->options);

            return response()->json([
                'status' => 1,
                'message' => 'Đã cập nhật sản phẩm trong giỏ hàng.',
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
            $cart->removeItem($item->product_id, $item->options);

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

    public function dealersByCityCode(Request $request)
    {
        $cityCode = $request->input('city_code', '');
        if (!$cityCode) {
            return response()->json([
                'status' => 0,
                'data' => [],
            ]);
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
            ->whereNull('parent_code')
            ->orderBy('name')
            ->get();

        if ($dealers->isEmpty()) {
            return response()->json([
                'status' => 0,
                'data' => [],
            ]);
        }
        return response()->json([
            'status' => 1,
            'data' => $dealers,
        ]);
    }

    public function partnerSubmit(Request $request)
    {
        try {
            $user = Auth::user();

            $cart = Cart::where('user_id', $user->id)
                ->where('type', 'dealer_loan')
                ->where('is_checked_out', false)
                ->first();

            if (!$cart || $cart->items->isEmpty()) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Giỏ hàng trống, không thể thanh toán.',
                ]);
            }
            $validator = Validator::make($request->all(), [
                'city_code' => ['required', 'string', 'max:255'],
                'dealer_partner_code' => ['required', 'string', 'max:255'],
            ], [], [
                'city_code'     => 'Tỉnh / Thành phố',
                'dealer_partner_code'   => 'Nhà phân phối',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 99,
                    'message' => 'Invalid data!',
                    'errors' => $validator->errors()
                ]);
            }
            $cart->update([
                'dealer_code' => $request->input('dealer_partner_code'),
            ]);
            return response()->json([
                'status' => 1,
                'message' => 'Chọn NPP thành công.',
                'url' => route('dealer.loan-order-qr'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Đã xảy ra lỗi không xác định.',
            ]);
        }
    }

    public function qrCertification(Request $request)
    {
        try {
            $itemId = $request->input('itemId');
            $qrcode = $request->input('qrcode');
            $itemInfo = CartItem::find($itemId);
            if (!$itemInfo) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Yêu cầu không hợp lệ.',
                ]);
            }
            $rawOptions = $itemInfo->options;
            $qrcodeCurrent = [];

            if (!empty($rawOptions)) {
                $qrcodeCurrent = array_map('trim', explode(',', $rawOptions));
            }
            if (!in_array($qrcode, $qrcodeCurrent)) {
                $qrcodeCurrent[] = $qrcode;
                $newOptionsString = implode(',', array_filter($qrcodeCurrent));
                $itemInfo->options = $newOptionsString;
                $itemInfo->save();
            }
            return response()->json([
                'status' => 1,
                'message' => 'Nhập mã QR thành công.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Lỗi không xác định từ server.',
            ]);
        }
    }

    public function confirmSubmit(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();

            $cart = Cart::where('user_id', $user->id)
                ->where('type', 'dealer_loan')
                ->where('is_checked_out', false)
                ->first();

            if (!$cart || $cart->items->isEmpty()) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Giỏ hàng trống, không thể thanh toán.',
                ]);
            }
            // $result = $this->cartService->checkoutLoan(
            //     $user->user_name,
            //     $cart->dealer_code,
            //     $cart->items->map(function ($item) {
            //         $rawOptions = $item->options;
            //         $qrcodeArray = !empty($rawOptions)
            //             ? array_values(array_filter(array_map('trim', explode(',', $rawOptions))))
            //             : [];
            //         return (object) [
            //             'item_no'  => $item->product_code,
            //             'qrcode'   => $qrcodeArray, // Bây giờ là ['ID1', 'ID2'] thay vì "ID1,ID2"
            //             'quantity' => $item->quantity,
            //             'price'    => $item->unit_price,
            //         ];
            //     })->toArray()
            // );
            $result = $cart->items->flatMap(function ($item) {
                $rawOptions = $item->options;
                $qrcodeArray = !empty($rawOptions)
                    ? array_values(array_filter(array_map('trim', explode(',', $rawOptions))))
                    : [null];
                return collect($qrcodeArray)->map(function ($qr) use ($item) {
                    return (object) [
                        'item_no'  => $item->product_code,
                        'qrcode'   => $qr,
                        'quantity' => 1,
                        'price'    => (int)$item->unit_price,
                    ];
                });
            })->values()->toArray();
            if (isset($result->error_no) && $result->error_no === "") {
                $order = Order::create([
                    'user_id'         => $customerInfo->id,
                    'order_number'    => $result->order_no,
                    'status'          => 0,
                    'payment_method'  => null,
                    'payment_status'  => 'pending',
                    'subtotal'        => $cart->total_amount,
                    'tax_amount'      => 0,
                    'shipping_amount' => 0,
                    'discount_amount' => 0,
                    'total_amount'    => $cart->total_amount,
                    'address'         => [
                        'name'     => $customerInfo->name,
                        'phone'    => $customerInfo->phone,
                        'email'    => $customerInfo->email,
                        'city_code'     => $customerInfo->city_code,
                        'address'  => $customerInfo->address,
                    ],
                    'type'            => 'dealer_sale',
                    'dealer_code' => $user->code,
                ]);

                $cart->load('items.product');
                foreach ($cart->items as $cartItem) {
                    OrderItem::create([
                        'order_id'     => $order->id,
                        'product_id'   => $cartItem->product_id,
                        'product_name' => $cartItem->product->name ?? $cartItem->product_code,
                        'product_sku'  => $cartItem->product_code,
                        'qrcode'       => $cartItem->options ? [$cartItem->options] : null, // Lưu mã QR code vào trường qrcode của cart_item
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
                    'url' => route('dealer.sale-confirm', ['id' => $order->id]),
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
