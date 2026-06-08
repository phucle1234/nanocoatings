<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Providers\TelegramServiceProvider;

class UserApiController extends Controller
{
    public function updateProfile(Request $request): JsonResponse
    {

        $payload = $request->json()->all();
        if (empty($payload)) {
            return response()->json(['success' => false, 'message' => 'Empty payload'], 422);
        }
        $user = User::where('user_name', $request->username)
            ->where('role', '=', 'customer')
            ->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $updateData = [];

        if ($request->has('fullname')) {
            $updateData['name'] = $request->fullname;
        }
        if ($request->has('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        if ($request->has('email')) {
            $emailExists = User::where('email', $request->email)
                ->where('id', '!=', $user->id)
                ->exists();

            if ($emailExists) {
                return response()->json(['success' => false, 'message' => 'Email already exists'], 422);
            }
            $updateData['email'] = $request->email;
        }

        if ($request->has('phone')) {
            $updateData['phone'] = $request->phone;
        }
        if ($request->has('address')) {
            $updateData['address'] = $request->address;
        }


        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data'    => [
                'user_name'    => $user->user_name,
                'role' => $user->role,
                'fullname'  => $request->fullname,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
            ],
        ]);
    }
    public function updateStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'role'  => ['required', 'string', 'max:20', 'in:customer,dealer,member'],
            'status'  => ['required', 'string', 'max:20'],
        ]);

        $query = User::query()
            ->where('user_name', $request->username);
        match ($validated['role']) {
            'dealer' => $query->where('role', 'dealer'),
            'customer' => $query->where('role', 'customer')->where('type', 'customer_info'),
            'member' => $query->where('role', 'customer')->where('type', 'customer_account'),
        };

        $user = $query->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found'], 404);
        }

        $updateData = [];

        $updateData['status'] = $validated['status'] == '1' ? 'active' : 'N';
        if (empty($updateData)) {
            return response()->json(['success' => false, 'message' => 'No data to update'], 422);
        }

        $user->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data'    => [
                'user_name'    => $user->user_name,
                'status' => $validated['status'],
            ],
        ]);
    }
    public function CustomerCreateAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_no' => 'required|string',
            'fullname'    => 'required|string',
            'email'       => 'required|email',
            'phone'       => 'required|string',
            'address'     => 'required|string',
            'city_code'   => 'required|string',
            'city_name'   => 'required|string',
            'country'     => 'required|string',
            'source_code' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }
        $idNPP = null;
        if ($request->source_code) {
            $idNPP = User::where('user_name', $request->source_code)->where('role', 'dealer')->first();
            if (!$idNPP) {
                return response()->json([
                    'success' => false,
                    'message' => 'NPP cha không tồn tại'
                ], 404);
            }
        }
        if ($request->customer_no) {
            $userCustomerNo = User::where('code', $request->customer_no)->where('role', 'customer')->first();
            if ($userCustomerNo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer no already exists'
                ], 422);
            }
        }

        $username = Str::random(20);
        $user = User::create([
            'code' => $request->customer_no,
            'parent_code' => $request->source_code,
            'user_name' => 'customer_no_login_' . $username,
            'parent_id' => $idNPP->id,
            'type' => 'customer_info',
            'role' => 'customer',
            'name' => $request->fullname,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city_code' => $request->city_code,
            'city_name' => $request->city_name,
            'country' => $request->country,
            'zalo' => $request->zalo,
            'facebook' => $request->facebook,
            'vehicle' => $request->vehicle,
            'license_plate' => $request->license_plate,
            'status' => 'active',
            'is_active' => '1',
            'is_admin' => '0',
        ]);
        $telegram = new TelegramServiceProvider();
        $telegram->sendMessage('Tạo khách hàng thành công: ' . $user->user_name . ' - ' . $user->email . ' - ' . $user->phone . ' - ' . $user->address . ' - ' . $user->address);
        return response()->json([
            'success' => true,
            'message' => 'Account created successfully',
            'data' => [
                'id' => $user->id,
                'status' => $user->status
            ],
        ], 200);
    }
    public function customerUpdateAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_no' => 'required|string',
            'source_code' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }
        $user = User::where('code', $request->customer_no)->where('type', 'customer_info')->where('role', 'customer')->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }
        $updateData = [];

        if ($request->fullname) {
            $updateData['name'] = $request->fullname;
        }
        if ($request->email) {
            $updateData['email'] = $request->email;
            $checkEmail = User::where('email', $request->email)->where('id', '!=', $user->id)->first();
            if ($checkEmail && $checkEmail->id != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email already exists'
                ], 422);
            }
        }
        if ($request->phone) {
            $updateData['phone'] = $request->phone;
            $checkPhone = User::where('phone', $request->phone)->where('id', '!=', $user->id)->first();
            if ($checkPhone && $checkPhone->id != $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Phone already exists'
                ], 422);
            }
        }
        if ($request->address) {
            $updateData['address'] = $request->address;
        }
        if ($request->city_code) {
            $updateData['city_code'] = $request->city_code;
        }
        if ($request->city_name) {
            $updateData['city_name'] = $request->city_name;
        }
        if ($request->country) {
            $updateData['country'] = $request->country;
        }
        if ($request->zalo) {
            $updateData['zalo'] = $request->zalo;
        }
        if ($request->facebook) {
            $updateData['facebook'] = $request->facebook;
        }
        if ($request->vehicle) {
            $updateData['vehicle'] = $request->vehicle;
        }
        if ($request->license_plate) {
            $updateData['license_plate'] = $request->license_plate;
        }
        if (empty($updateData)) {
            return response()->json([
                'success' => false,
                'message' => 'No data to update'
            ], 422);
        }
        $user->update($updateData);
        return response()->json([
            'success' => true,
            'message' => 'Account created successfully',
            'data' => [
                'id' => $user->id,
                'status' => $user->status
            ],
        ], 200);
    }
}
