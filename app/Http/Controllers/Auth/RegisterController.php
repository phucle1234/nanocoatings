<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Providers\TelegramServiceProvider;
use App\Mail\EmailVerificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\SentMailLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\CasuminaApi\CustomerService;


class RegisterController extends Controller
{

    public function __construct(
        protected CustomerService $customerService
    ) {}

    public function showRegistrationForm(?string $token = null)
    {
        $sponsor = null;
        // if ($token) {
        //     $sponsor = User::where('TokenID', $token)->first();
        // }
        // if (!$sponsor) {
        //     $sponsor = User::find(1);
        // }
        return view('auth.register', [
            'sponsor' => $sponsor,
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->where(fn($query) => $query->where('role', 'customer'))],
            'name' => ['required', 'string', 'max:255'],
            // 'password' => ['required', 'confirmed:password_confirmation', Password::defaults()],
            'phone' => ['required', 'string', 'max:15', 'unique:users,phone'],
            'address' => ['', 'string', 'max:255'],
            'gender' => ['required', 'in:0,1,2,3'],
            'terms' => ['accepted'],
        ], [
            'email.unique' => __('auth.register_email_unique'),
            'phone.unique' => __('auth.register_phone_unique'),
        ], [
            'email'    => __('auth.attr_email'),
            'name'     => __('auth.attr_name'),
            // 'password' => __('auth.attr_password'),
            'phone'    => __('auth.attr_phone'),
            'terms'    => __('auth.attr_terms'),
        ]);
        try {

            $rawUtc = $request->input('terms_accepted_at_utc');
            try {
                $vietnamTime = $rawUtc
                    ? Carbon::parse($rawUtc)->timezone('Asia/Ho_Chi_Minh')
                    : Carbon::now('Asia/Ho_Chi_Minh');
            } catch (\Exception $e) {
                Log::warning('Register - terms_accepted_at_utc không parse được', [
                    'email' => $request->email,
                    'raw_value' => $rawUtc,
                    'error' => $e->getMessage(),
                ]);
                $vietnamTime = Carbon::now('Asia/Ho_Chi_Minh');
            }
            $result = $this->customerService->createCustomer([
                'loginname' => $request->email,
                'fullname' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'gender' => $request->gender,
                'address' => $request->address,
                'policy_date' => $vietnamTime,

            ]);

            if ($result && isset($result[0]->error_no) && $result[0]->error_no === "") {
                $user = User::create([
                    'code' => $result[0]->customer_no,
                    'user_name' => $request->email,
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'gender' => $request->gender,
                    'password' => Hash::make($result[0]->password),
                    'role' => "customer",
                    'status' => "active",
                    'is_active' => '1',
                    'is_admin' => '0',
                    'type' => 'customer_account',
                ]);

                $telegram = new TelegramServiceProvider();
                $message = "🎉 <b>Đăng ký mới thành công!</b>\n\n";
                $message .= "👥 <b>UserName:</b> {$result[0]->loginname}\n";
                $message .= "📧 <b>Email:</b> {$request->email}\n";
                $message .= "📞 <b>Phone:</b> {$request->phone}\n";
                $message .= "📍 <b>Address:</b> {$request->address}\n";
                $message .= "👤 <b>Giới tính:</b> {$request->gender}\n";
                $message .= "👤 <b>Thời gian Terms:</b> {$vietnamTime}\n";
                $message .= "⏰ <b>Thời gian:</b> " . now()->format('d/m/Y H:i:s');
                $telegram->sendMessage($message);
                return redirect()->route('login')->with('toast_success', __('auth.register_success'));
            } else {
                // Xác định nguồn lỗi để hiển thị đúng thông báo
                if ($result === null) {
                    $errorSource = '[API] Không nhận được phản hồi (null) — có thể timeout, HTTP error hoặc network';
                    $errorDisplay = 'Hệ thống đang bận, vui lòng thử lại sau.';
                } elseif (!isset($result[0])) {
                    $errorSource = '[API] Phản hồi không đúng cấu trúc mảng: ' . json_encode($result);
                    $errorDisplay = 'Phản hồi API không hợp lệ.';
                } elseif (!isset($result[0]->error_no)) {
                    $errorSource = '[API] Thiếu trường error_no trong phần tử đầu tiên: ' . json_encode($result[0]);
                    $errorDisplay = 'Phản hồi API thiếu thông tin lỗi.';
                } else {
                    $errorSource = '[API] error_no = "' . $result[0]->error_no . '" | Full: ' . json_encode($result[0]);
                    $errorDisplay = $result[0]->error_no;
                }

                Log::warning('Register - API error', [
                    'email' => $request->email,
                    'source' => $errorSource,
                    'result' => $result,
                ]);

                $telegram = new TelegramServiceProvider();
                $message = "❌ <b>Đăng ký Lỗi</b>\n\n";
                $message .= "👥 <b>UserName:</b> {$request->email}\n";
                $message .= "🔍 <b>Nguồn lỗi:</b> " . $errorSource . "\n";
                $message .= "⏰ <b>Thời gian:</b> " . now()->format('d/m/Y H:i:s');
                $telegram->sendMessage($message);

                return redirect()->back()->withInput()->with('toast_error', $errorDisplay);
            }
        } catch (\Exception $e) {
            $telegram = new TelegramServiceProvider();
            $message = "🎉 <b>Đăng ký Lỗi</b>\n\n";
            $message .= "👥 <b>UserName:</b> {$request->email}\n";
            $message .= "⏰ <b>Thời gian:</b> " . now()->format('d/m/Y H:i:s');
            $message .= "🔍 <b>Lỗi:</b> " . $e->getMessage();
            $telegram->sendMessage($message);
            return redirect()->back()->with('toast_error', __('auth.register_error'));
        }


        // Tạo log gửi mail xác thực và gửi email
        // $token = Str::random(64);
        // SentMailLog::create([
        //     'user_id' => $user->id,
        //     'type' => 'register',
        //     'email' => $user->email,
        //     'token' => $token,
        // ]);
        // // Gửi email kích hoạt
        // $verificationUrl = route('email_verify', ['token' => $token]);
        // Mail::to($user->email)->send(new EmailVerificationMail($user, $verificationUrl, 'register'));


        # Cài đặt package cho Telegram Bot API (nếu cần)
        # composer require irazasyed/telegram-bot-sdk



    }

    public function previewEmail()
    {
        $user = User::where('id', 7)->first();

        $verificationUrl = url('/email-verify/preview-token-example');

        return new EmailVerificationMail($user, $verificationUrl, 'register');
    }
}
