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
use Illuminate\Validation\Rule;

class SaleCartController extends Controller
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
        $sidebarActive = 'casumina-parent';
        $sidebarChildActive = 'sale-cart';
        $user = Auth::user();

        $locale = app()->getLocale();
        $productListBuyDealer = collect($this->productService->getProductListBuyDealer($user->parent_code ?? $user->code))
            ->where('type', 0)
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
                    return $item->source_code === ($user->parent_code ?? $user->code) && $item->type === "0";
                });
                // dd($dealerPrice);
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
            return view('dealer.layout.sale-cart._table-product', compact('list'))->render();
        }

        return view('dealer.layout.sale-cart.index', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'list'));
    }

    public function productDetail(string $id)
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'casumina-parent';
        $sidebarChildActive = 'sale-cart';
        $user = Auth::user();
        if (!is_numeric($id)) {
            return redirect()->route('dealer.sale-cart')->with('toast_error', 'Sản phẩm không tồn tại.');
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
                    return $item->source_code === ($user->parent_code ?? $user->code) && $item->type === "0";
                });
                if ($dealerPrice) {
                    $product->price = $dealerPrice->price;
                } else {
                    $product->price = 0;
                }
            } else {
                $product->price = 0;
            }

            return view('dealer.layout.sale-cart.product-detail', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'product'));
        } catch (\Exception $e) {
            return redirect()->route('dealer.sale-cart')->with('toast_error', 'Sản phẩm không tồn tại.');
        }
    }

    public function checkout(Request $request)
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'casumina-parent';
        $sidebarChildActive = 'sale-cart';
        $user = User::find(Auth::id());
        $cart = Cart::where('user_id', $user->id)
            ->where('type', 'dealer_sale')
            ->where('is_checked_out', false)
            ->first();
        if (!$cart || $cart?->items->isEmpty()) {
            return redirect()->route('dealer.sale-cart')->with('toast_error', 'Giỏ hàng trống, không thể thanh toán.');
        }
        $query = User::query()->where('role', 'customer')->where('type', 'customer_info')->where('parent_code', $user->parent_code ?? $user->code);

        if ($request->keyword) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('phone', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('email', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('code', 'LIKE', '%' . $keyword . '%');
            });
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(7)->onEachSide(1)->withQueryString();
        $provainces = Province::where('country_id', 1)->active()->ordered()->get();
        if ($request->ajax()) {
            return view('dealer.layout.sale-cart._table-customer', compact('customers'))->render();
        }
        return view('dealer.layout.sale-cart.checkout', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'customers', 'provainces', 'cart'));
    }

    public function confirm($id)
    {
        try {
            $headerWhite = 'header-secondary';
            $sidebarActive = 'casumina-parent';
            $sidebarChildActive = 'sale-cart';
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
            return view('dealer.layout.sale-cart.confirm', compact('user', 'headerWhite', 'sidebarActive', 'sidebarChildActive', 'order', 'dealer', 'recipient'));
        } catch (\Exception $e) {
            return redirect()->route('dealer.sale-cart')->with('toast_error', 'Đơn hàng không tồn tại.');
        }
    }

    // Ajax
    public function productByQRCode(Request $request)
    {
        try {
            $user = Auth::user();
            $qrcode = $request->input('qrcode');

            // Xử lý lấy mã sản phẩm và giá từ qrcode trên sản phẩm
            $getProductInfo = $this->productService->getProductByQrcode($user->parent_code ?? $user->code, $qrcode);

            if (!$getProductInfo || $getProductInfo->error_no != '') {
                return response()->json([
                    'status' => 0,
                    'message' => $getProductInfo->error_no ?: 'Không tìm thấy sản phẩm trên hệ thống.',
                ]);
            }

            if ($getProductInfo->status != 0) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Sản phẩm này đã bán ở đơn hàng ' . $getProductInfo->order_no . '. Vui lòng kiểm tra lại.',
                ]);
            }
            // Kiểm tra sản phẩm
            $product = Product::where('sku', $getProductInfo->item_no)->first();

            if (!$product) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Sản phẩm không tồn tại. Hoặc chưa được đồng bộ.',
                ]);
            }

            return response()->json([
                'status' => 1,
                'message' => 'Tìm thấy thông tin sản phẩm.',
                'productId' => $product->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Đã xảy ra lỗi khi lấy thông tin sản phẩm.',
                'serverMessage' => $e->getMessage(),
            ]);
        }
    }

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
            ->where('type', 'dealer_sale')
            ->where('is_checked_out', false)
            ->first();

        // Kiểm tra và cập nhật lại giá từ API cho từng sản phẩm trong giỏ hàng
        if ($cart && $cart->items->isNotEmpty()) {
            $priceChanged = 0;
            $hasErrorItem = 0;
            foreach ($cart->items as $cartItem) {
                $priceResult = $this->productService->getProductPriceBuyDealer($cartItem->product_code, $user->parent_code ?? $user->code);
                if (!empty($priceResult)) {
                    $stockResult = $this->productService->getProductStock($user->code, $cartItem->product_code);
                    if ($stockResult && $stockResult->error_no == '' && $stockResult->quantity > 0) {
                        if ($stockResult->quantity < $cartItem->quantity) {
                            $cartItem->error = 'Kho hàng chỉ còn ' . $stockResult->quantity . ' sản phẩm.';
                            $hasErrorItem++;
                        }
                    } else {
                        $cartItem->error = 'Sản phẩm tạm hết hàng.';
                        $hasErrorItem++;
                    }
                    $newPrice = 0;
                    $oldPrice = (float) $cartItem->unit_price;
                    $dealerPrice = collect($priceResult)->first(function ($item) use ($user) {
                        return $item->source_code === ($user->parent_code ?? $user->code) && $item->type === "0";
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
                'html'      => view('dealer.layout.sale-cart._table-cart', compact('cart', 'type', 'hasErrorItem'))->render(),
                'isEmpty'   => false,
                'hasErrorItem' => $hasErrorItem,
                'redirect'  => route('dealer.sale-cart'),
            ]);
        } else {
            return response()->json([
                'status'    => 1,
                'html'      => view('dealer.layout.sale-cart._table-cart-empty')->render(),
                'isEmpty'   => true,
                'redirect'  => route('dealer.sale-cart'),
            ]);
        }
    }

    public function addToCart(Request $request)
    {
        try {
            $user = Auth::user();

            $productId = $request->input('productId');
            $quantity = max(1, (int) $request->input('quantity', 1));
            $qrcode = $request->input('qrcode', null);

            // Kiểm tra sản phẩm
            $product = Product::find($productId);

            if (!$product) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Sản phẩm không tồn tại.',
                ]);
            }

            // Gán lại giá sản phẩm theo Nhà phân phối bán cho khách
            $apiResult = $this->productService->getProductPriceBuyDealer($product->sku, $user->parent_code ?? $user->code);
            if (!empty($apiResult)) {
                $dealerPrice = collect($apiResult)->first(function ($item) use ($user) {
                    return $item->source_code === ($user->parent_code ?? $user->code) && $item->type === "0";
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
            if ($qrcode) {
                $existsInCart = CartItem::where('options', $qrcode)
                    ->whereHas('cart', function ($query) {
                        $query->where('is_checked_out', false);
                    })
                    ->exists();
                if ($existsInCart) {
                    return response()->json([
                        'status' => 0,
                        'message' => 'Sản phẩm này đã tồn tại trong giỏ hàng.',
                    ]);
                }
                $existsInOrderItem = OrderItem::where('qrcode', $qrcode)
                    ->whereHas('order', function ($query) {
                        $query->where('status', '!=', -1);
                    })
                    ->exists();
                if ($existsInOrderItem) {
                    return response()->json([
                        'status' => 0,
                        'message' => 'Sản phẩm này đã bán ở đơn hàng khác.',
                    ]);
                }
            }
            $cart = Cart::getOrCreateCart($user->id, 'dealer_sale');
            $cart->addItemDealer($product, $quantity, $qrcode);

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

    public function checkoutInfo(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();

            $cart = Cart::where('user_id', $user->id)
                ->where('type', 'dealer_sale')
                ->where('is_checked_out', false)
                ->first();

            if (!$cart || $cart->items->isEmpty()) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Giỏ hàng trống, không thể thanh toán.',
                ]);
            }
            $customerInfo = null;
            $customerCode = $request->input('customer_code');
            $hasCreateNew = $request->input('has_create_new');
            if (!$customerCode && !$hasCreateNew) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Chưa chọn khách hàng cho đơn hàng này.',
                ]);
            }

            if ($customerCode) {
                $customerInfo = User::where('code', $customerCode)
                    ->where('parent_code', $user->parent_code ?? $user->code)
                    ->where('role', 'customer')
                    ->where('type', 'customer_info')
                    ->first();
            } else {
                $validator = Validator::make($request->all(), [
                    'name' => ['required', 'string', 'max:255'],
                    'gender' => ['required', 'string', 'max:15'],
                    'phone' => ['required', 'string', 'max:15', Rule::unique('users', 'phone')->where(function ($query) {
                        return $query->where('role', 'customer')->where('type', 'customer_info');
                    }),],
                    'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')->where(function ($query) {
                        return $query->where('role', 'customer')->where('type', 'customer_info');
                    }),],
                    'city' => ['required', 'string', 'max:255'],
                    'address' => ['required', 'string', 'max:255'],
                ], [], [
                    'name'     => 'Tên',
                    'gender'   => 'Giới tính',
                    'phone'    => 'Số điện thoại',
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
                $resultCreate = $this->customerService->createCustomerByDealer($user->parent_code ?? $user->code, [
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'gender' => $request->input('gender'),
                    'phone' => $request->input('phone'),
                    'zalo' => $request->input('zalo'),
                    'facebook' => $request->input('facebook'),
                    'vehicle' => $request->input('vehicle'),
                    'license_plate' => $request->input('license_plate'),
                    'city' => $request->input('city'),
                    'address' => $request->input('address'),
                ]);
                if (!$resultCreate) {
                    return response()->json([
                        'status' => 0,
                        'message' => 'Không tạo được thông tin khách hàng mới.',
                    ]);
                } else {
                    $customerInfoExist = User::where('code', $resultCreate->customer_no)
                        ->where('parent_code', $user->parent_code ?? $user->code)
                        ->where('role', 'customer')
                        ->where('type', 'customer_info')
                        ->first();
                    if (!$customerInfoExist) {
                        // Tạo khách hàng cho web từ thông tin resultCreate
                        $customerInfo = User::updateOrCreate([
                            'code'        => $resultCreate->customer_no,
                            'parent_code' => $user->parent_code ?? $user->code,
                        ], [
                            'parent_code' => $user->parent_code ?? $user->code,
                            'code' => $resultCreate->customer_no,
                            'role'          => 'customer',
                            'type'          => 'customer_info',
                            'status'        => 'active',
                            'is_active'     => '1',
                            'is_admin'      => '0',
                            'name' => $resultCreate->customer_name,
                            'email' => $resultCreate->email,
                            'gender' => $resultCreate->gender,
                            'phone' => $resultCreate->phone,
                            'zalo' => $resultCreate->zalo,
                            'facebook' => $resultCreate->facebook,
                            'vehicle' => $resultCreate->vehicle,
                            'license_plate' => $resultCreate->license_plate,
                            'city_code' => $resultCreate->city_code,
                            'address' => $resultCreate->address,
                        ]);
                    } else {
                        $customerInfo = $customerInfoExist;
                    }
                }
            }
            if (!$customerInfo) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Chưa chọn khách hàng cho đơn hàng này.',
                ]);
            } else {
                $hasErrorItem = 0;
                foreach ($cart->items as $cartItem) {
                    $priceResult = $this->productService->getProductPriceBuyDealer($cartItem->product_code, $user->parent_code ?? $user->code);
                    if (!empty($priceResult)) {
                        $stockResult = $this->productService->getProductStock($user->code, $cartItem->product_code);
                        if ($stockResult && $stockResult->error_no == '' && $stockResult->quantity > 0) {
                            if ($stockResult->quantity < $cartItem->quantity) {
                                // $cartItem->error = 'Kho hàng chỉ còn ' . $stockResult->quantity . ' sản phẩm.';
                                $hasErrorItem++;
                            }
                        } else {
                            // $cartItem->error = 'Sản phẩm tạm hết hàng.';
                            $hasErrorItem++;
                        }
                        $newPrice = 0;
                        $oldPrice = (float) $cartItem->unit_price;
                        $dealerPrice = collect($priceResult)->first(function ($item) use ($user) {
                            return $item->source_code === ($user->parent_code ?? $user->code) && $item->type === "0";
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
                                // $cartItem->error = 'Sản phẩm đã thay đổi giá = 0';
                                $hasErrorItem++;
                            }
                        }
                    } else {
                        $hasErrorItem++;
                    }
                }
                if ($hasErrorItem > 0) {
                    return response()->json([
                        'status' => 0,
                        'message' => 'Có sản phẩm hết hàng hoặc giá = 0. Vui lòng tải lại trình duyệt và kiểm tra lại giỏ hàng.',
                    ]);
                }
                $result = $this->cartService->checkoutSale(
                    $user->user_name,
                    $customerInfo->code,
                    $cart->items->map(function ($item) {
                        return (object) [
                            'item_no'  => $item->product_code,
                            'qrcode' => $item->options,
                            'quantity' => $item->quantity,
                            'price'    => (int)$item->unit_price,
                        ];
                    })->toArray()
                );
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
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 0,
                'message' => 'Đã xảy ra lỗi khi đặt hàng.',
            ]);
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
                'message' => 'Nhập mã QR thành công.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Lỗi không xác định từ server.',
            ]);
        }
    }
}
