<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\SentMailLog;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerificationMail;
use App\Services\CasuminaApi\CustomerService;

class ForgotPasswordController extends Controller
{
    public function __construct(
        protected CustomerService $customerService
    ) {}
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => __('auth.forgot_password_email_required'),
            'email.email' => __('auth.forgot_password_email_invalid'),
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withInput()->with('error', __('auth.forgot_password_email_not_found'));
        }
        $result = $this->customerService->resetPassword([
            'username' => $user->user_name,
            'email' => $user->email,
        ]);

        if ($result && isset($result->error_no) && $result->error_no === "") {
            $newPassword = $result->new_password;
            $user->password = Hash::make($newPassword);
            $user->save();

            return back()->withInput()->with('toast_success', __('auth.forgot_password_success'));
        } else {
            return back()->withInput()->with('toast_error', __('auth.forgot_password_error'));
        }
        // Tạo mật khẩu mới và cập nhật


        // Ghi log gửi mail
        // SentMailLog::create([
        //     'user_id' => $user->id,
        //     'type' => 'reset_password',
        //     'email' => $user->email,
        //     'token' => $newPassword,
        // ]);

        // // Gửi email mật khẩu mới
        // Mail::to($user->email)->send(new EmailVerificationMail($user, null, 'reset_password'));
    }
}
