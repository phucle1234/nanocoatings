<?php

namespace App\Http\Controllers\Dealer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Services\CasuminaApi\WarrantyService;
use App\Models\OrderItem;
use App\Models\Order;
use Illuminate\Support\Facades\Validator;

class WarrantyController extends Controller
{

    public function __construct(
        protected WarrantyService $warrantyService,
    ) {}

    public function index()
    {
        $headerWhite = 'header-secondary';
        $sidebarActive = 'warranty';
        $user = Auth::user();
        return view('dealer.layout.warranty.index', compact('user', 'headerWhite', 'sidebarActive'));
    }

    public function search(Request $request)
    {
        try {
            $user = Auth::user();
            $qrcode = $request->input('qrcode');
            $warrantyInfo = $this->warrantyService->getWarrantyInfo($qrcode);

            if (!$warrantyInfo || $warrantyInfo->error_no != '') {
                return response()->json([
                    'status' => 0,
                    'message' => 'Không tìm thấy thông tin bảo hành.',
                ]);
            }
            $html = view('dealer.layout.warranty._warranty-product', compact('user', 'warrantyInfo'))->render();
            if ($warrantyInfo?->type == 2) {
                $html = view('dealer.layout.warranty._warranty-order', compact('user', 'warrantyInfo'))->render();
            }
            return response()->json([
                'status' => 1,
                'message' => 'Có thông tin bảo hành.',
                'html' => $html,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Lỗi không xác định từ server.',
            ]);
        }
    }

    public function certification(Request $request)
    {
        try {
            $orderCode = $request->input('orderCode');
            $qrcode = $request->input('qrcode');
            $warrantyInfo = $this->warrantyService->activateWarranty($orderCode, $qrcode);

            if (!$warrantyInfo || $warrantyInfo->error_no != '') {
                return response()->json([
                    'status' => 0,
                    'message' => $warrantyInfo->error_no ?: 'Kích hoạt bảo hành thất bại.',
                ]);
            }
            $orderInfo = Order::query()->where('order_number', $warrantyInfo->order_no)->first();
            if ($orderInfo) {
                $orderItem = OrderItem::query()->where('order_id', $orderInfo->id)->where('product_sku', $warrantyInfo->item_no)->first();
                if ($orderItem) {
                    $qrcodeCurrent = $orderItem->qrcode ?? [];
                    if (!in_array($qrcode, $qrcodeCurrent)) {
                        $qrcodeCurrent[] = $qrcode;
                        $orderItem->qrcode = !empty($qrcodeCurrent) ? $qrcodeCurrent : null;
                        $orderItem->save();
                    }
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

    public function requestWarranty(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fullname' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'string', 'max:15'],
                'email' => ['required', 'string', 'email', 'max:255'],
                'order_no' => ['required_if:type,order', 'nullable', 'string', 'max:255'],
                'qrcode' => ['required', 'string', 'max:255'],
                'content' => ['required', 'string', 'max:1000'],
                'type' => ['required', 'in:order,product'],
            ], [], [
                'fullname' => 'Họ tên khách hàng',
                'phone'    => 'Số điện thoại',
                'email'    => 'Email',
                'order_no' => 'Số hóa đơn',
                'qrcode'  => 'QR code',
                'content' => 'Nội dung bảo hành',
                'type' => 'Loại bảo hành',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 99,
                    'message' => 'Invalid data!',
                    'errors' => $validator->errors()
                ]);
            }
            $user = Auth::user();
            $data = [
                'fullname' => $request->input('fullname'),
                'phone' => $request->input('phone'),
                'email' => $request->input('email'),
                'order_no' => $request->input('order_no'),
                'qrcode' => $request->input('qrcode'),
                'content' => $request->input('content'),
            ];
            $warrantyInfo = $this->warrantyService->requestWarranty($user->user_name, $data);
            if (!$warrantyInfo || $warrantyInfo->error_no != '') {
                return response()->json([
                    'status' => 0,
                    'message' => $warrantyInfo->error_no ?: 'Yêu cầu bảo hành thất bại.',
                ]);
            }
            return response()->json([
                'status' => 1,
                'message' => 'Yêu cầu bảo hành thành công.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Lỗi không xác định từ server.',
            ]);
        }
    }
}
