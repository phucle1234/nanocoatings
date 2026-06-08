<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Province;
use App\Services\CasuminaApi\CustomerService;
use App\Providers\TelegramServiceProvider;

class ProfileController extends Controller
{
    protected $customerService;
    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }
    public function infomation()
    {
        $headerWhite = 'header-secondary';
        $user = Auth::user();
        $sidebarActive = 'profile';
        $cities = Province::active()->ordered()->get();
        return view('customer.layout.profile', compact('user', 'headerWhite', 'sidebarActive', 'cities'));
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'gender' => 'required|in:0,1,2,3',
            'city_code' => 'nullable|string|exists:npp_provinces,code',
        ]);

        $result = $this->customerService->updateCustomer([
            'loginname' => Auth::user()->user_name,
            'email' => Auth::user()->email,
            'phone' => $validated['phone'],
            'fullname' => $validated['name'],
            'address' => $validated['address'],
            'gender' => $validated['gender'],
            'city_code' => $validated['city_code'],
            'password' => '123',
        ]);

        if ($result &&  $result->error_no === "") {

            User::where('id', Auth::user()->id)->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'gender' => $validated['gender'],
                'city_code' => $validated['city_code'],
            ]);

            return redirect()->route('customer.profile')->withInput()->with('toast_success', __('auth.profile_update_success'));
        } else {
            $telegram = new TelegramServiceProvider();
            $telegram->sendMessage('Cập nhật thông tin ' . Auth::user()->user_name . ' Lỗi: ' . $result->error_no);
            return redirect()->route('customer.profile')->withInput()->with('toast_error', $result->error_no);
        }
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required',
        ]);

        $result = $this->customerService->updateCustomer([
            'loginname' => Auth::user()->user_name,
            'email' => Auth::user()->email,
            'phone' => Auth::user()->phone,
            'fullname' => Auth::user()->name,
            'address' => Auth::user()->address,
            'gender' => Auth::user()->gender,
            'city_code' => Auth::user()->city_code,
            'password' => $validated['password'],
        ]);

        if ($result &&  $result->error_no === "") {
            User::where('id', Auth::user()->id)->update(['password' => bcrypt($validated['password'])]);
            return redirect()->route('customer.profile')->withInput()->with('toast_success', __('auth.password_update_success'));
        } else {
            $telegram = new TelegramServiceProvider();
            $telegram->sendMessage('Cập nhật mật khẩu ' . Auth::user()->user_name . ' Lỗi: ' . $result->error_no);
            return redirect()->route('customer.profile')->withInput()->with('toast_error', $result->error_no);
        }
    }
}
