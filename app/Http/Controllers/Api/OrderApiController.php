<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class OrderApiController extends Controller
{
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orderNumber' => 'required|string',
            'username' => 'required|string',
            'status' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $user = User::where('user_name', $request->username)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $order = Order::where('order_number', $request->orderNumber)->where('user_id', $user->id)->where('status', '!=', $request->status)->first();

        if (!$order) {
            return response()->json([
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json($order);
    }

    public function updateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'orderNumber' => 'required|string',
            'status' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $order = Order::where('order_number', $request->orderNumber)->first();

        if (!$order) {
            return response()->json([
                'success' => 'false',
                'message' => 'Order not found'
            ], 404);
        }

        $order->status = $request->status;
        $order->save();

        return response()->json([
            'success' => 'true',
            'message' => 'Order status updated successfully',
            'data' => array('status' => $order->status, 'orderNumber' => $order->order_number)
        ]);
    }

    public function updateNPPForOrder(Request $request)
    {
        $request->validate([
            'orderNumber' => 'required|string',
            'code' => 'required|string',
        ]);

        $npp = User::where('code', $request->code)->where('role', '=', 'dealer')->where('status', '=', 'active')->first();
        if (!$npp) {
            return response()->json([
                'success' => 'false',
                'message' => 'NPP not found'
            ], 404);
        }

        $order = Order::where('order_number', $request->orderNumber)->where('status', '=', 0)->first();
        if (!$order) {
            return response()->json([
                'success' => 'false',
                'message' => 'Order not found'
            ], 404);
        }


        $order->dealer_code = $npp->code;
        $order->save();

        return response()->json([
            'success' => 'true',
            'message' => 'NPP for order updated successfully',
            'data' => $order
        ]);
    }
}
