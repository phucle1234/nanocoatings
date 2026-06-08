<?php

namespace App\Http\Controllers\langding;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Contact;
use App\Providers\TelegramServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Services\CasuminaApi\CustomerService;

class ContactController extends Controller
{
    protected $customerService;
    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }
    /**
     * Hiển thị trang liên hệ
     */
    public function index()
    {
        $captchaImage = $this->_renderCaptcha();
        return view('langding.contact', compact('captchaImage'));
    }

    /**
     * Xử lý form liên hệ
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'captcha' => [
                'required',
                'string',
                'max:15',
                function ($attribute, $value, $fail) {
                    if (strtoupper($value) !== strtoupper(session('contact_captcha'))) {
                        $fail(__('messages.captcha_invalid'));
                    }
                }
            ],
        ], [
            'name.required' => __('messages.name_required'),
            'email.required' => __('messages.email_required'),
            'email.email' => __('messages.email_invalid'),
            'phone.required' => __('messages.phone_required'),
            'subject.required' => __('messages.subject_required'),
            'message.required' => __('messages.message_required'),
            'captcha.required' => __('messages.enter_captcha'),
        ]);

        try {
            Contact::create([
                'Title' => $request->subject,
                'Fullname' => $request->name,
                'Email' => $request->email,
                'Phone' => $request->phone,
                'Content' => $request->message,
                'Type' => 1,
                'Date' => now(),
            ]);

            $result = $this->customerService->contactCustomer([
                'email' => $request->email,
                'phone' => $request->phone,
                'fullname' => $request->name,
                'message' => $request->message,
                'subject' => $request->subject,
            ]);

            $telegram = app(TelegramServiceProvider::class);

            if ($result && !empty($result->error_no)) {
                $telegram->sendMessage(
                    'Liên hệ gọi API lỗi - ' . $request->name . ' | Lỗi: ' . $result->error_no
                );
            }

            $telegramMessage = "📢 <b>Liên hệ mới!</b>\n";
            $telegramMessage .= str_repeat("━", 25) . "\n\n";
            $telegramMessage .= "👤 <b>Họ tên:</b> {$request->name}\n";
            $telegramMessage .= "📧 <b>Email:</b> {$request->email}\n";
            $telegramMessage .= "📞 <b>Điện thoại:</b> {$request->phone}\n";
            $telegramMessage .= "📝 <b>Tiêu đề:</b> {$request->subject}\n";
            $telegramMessage .= "💬 <b>Nội dung:</b> {$request->message}\n";
            $telegramMessage .= "📅 <b>Thời gian:</b> " . now()->format('d/m/Y H:i:s') . "\n";

            $telegram->sendMessage($telegramMessage);

            $this->_renderCaptcha();

            return redirect()
                ->route('contact')
                ->with('success', __('messages.contact_success'));
        } catch (\Exception $e) {
            Log::error('Contact form error', [
                'error' => $e->getMessage(),
                'email' => $request->email,
                'phone' => $request->phone,
            ]);

            $captchaImage = $this->_renderCaptcha();

            return back()
                ->withInput()
                ->with('error', __('messages.contact_error'))
                ->with('captchaImage', $captchaImage);
        }
    }

    /**
     * Refresh captcha image
     */
    public function refreshCaptcha()
    {
        return response()->json([
            'captchaImage' => $this->_renderCaptcha()
        ]);
    }

    /**
     * Generate and render captcha
     */
    private function _renderCaptcha()
    {
        $captchaCode = $this->_generateCaptcha();
        session(['contact_captcha' => $captchaCode]);
        $captchaImage = $this->_generateCaptchaImage($captchaCode);
        return $captchaImage;
    }

    /**
     * Generate random captcha code
     */
    private function _generateCaptcha()
    {
        $characters = 'ABCDEFGHIJKLMNPQRSTUVWXYZ123456789';
        $captcha = '';
        for ($i = 0; $i < 4; $i++) {
            $captcha .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $captcha;
    }

    /**
     * Generate captcha image (base64 implementation)
     */
    private function _generateCaptchaImage($code)
    {
        $width = 120;
        $height = 40;
        $image = imagecreatetruecolor($width, $height);

        // Background color (light red)
        $bgColor = imagecolorallocate($image, 237, 220, 220);
        imagefill($image, 0, 0, $bgColor);

        // Text color (black)
        $textColor = imagecolorallocate($image, 0, 0, 0);

        // Add noise
        for ($i = 0; $i < 50; $i++) {
            $noiseColor = imagecolorallocate($image, random_int(150, 200), random_int(50, 200), random_int(50, 200));
            imagesetpixel($image, random_int(0, $width), random_int(0, $height), $noiseColor);
        }

        // Add lines
        for ($i = 0; $i < 3; $i++) {
            $lineColor = imagecolorallocate($image, random_int(150, 200), random_int(80, 200), random_int(80, 200));
            imageline($image, random_int(0, $width), random_int(0, $height), random_int(0, $width), random_int(0, $height), $lineColor);
        }

        // Add text
        $fontSize = 5;
        $x = ($width - (imagefontwidth($fontSize) * strlen($code))) / 2;
        $y = ($height - imagefontheight($fontSize)) / 2;
        imagestring($image, $fontSize, $x, $y, $code, $textColor);

        ob_start();
        imagepng($image);
        $imageData = ob_get_contents();
        ob_end_clean();
        imagedestroy($image);

        return 'data:image/png;base64,' . base64_encode($imageData);
    }


    /**
     * Lưu email đăng ký từ footer
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
        ], [

            'email.required' => __('messages.email_required'),
            'email.email' => __('messages.email_invalid'),
        ]);

        $UserID = null;
        if (Auth::check()) {
            $UserID = Auth::id();
        }

        try {
            Contact::create([
                'Email' => $request->email,
                'Type' => 2,
                'Date' => now(),
                'Status' => 'pending',
                'Fullname' => 'Subscriber',
                'Phone' => 'Subscriber',
                'user_id' => $UserID
            ]);

            return back()->withInput()->with('toast_success', '' . __('messages.subscribe_success'));
        } catch (\Exception $e) {
            Log::error('subscribe form error', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return back()->withInput()->with('toast_error', '' . __('messages.subscribe_error'));
        }
    }
}
