<?php

namespace App\Http\Controllers\langding;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Providers\TelegramServiceProvider;
use App\Services\CasuminaApi\TraceabilityService;
use Illuminate\Support\Facades\Auth;

class WarrantyController extends Controller
{
    protected $traceabilityService;
    public function __construct(TraceabilityService $traceabilityService)
    {
        $this->traceabilityService = $traceabilityService;
    }

    /**
     * Hiển thị trang đề nghị bảo hành
     */
    public function index(Request $request)
    {
        $productCode = null;
        $documentNo = null;

        $code = $request->query('code');
        if ($code) {
            $parts = explode('&', $code);
            $productCode = $parts[0] ?? null;
            $documentNo = $parts[1] ?? null;
        }

        $captchaImage = $this->_renderCaptcha();

        return view('langding.warranty', [
            'productCode' => $productCode,
            'documentNo' => $documentNo,
            'captchaImage' => $captchaImage,
        ]);
    }

    /**
     * Xử lý form đề nghị bảo hành
     */
    public function store(Request $request)
    {
        $request->validate([
            'applicant_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'invoice_number' => 'nullable|string|max:100',
            'qr_code' => 'nullable|string|max:255',
            'warranty_content' => 'required|string|max:5000',
            'captcha' => ['required', 'string', 'max:15', function ($attribute, $value, $fail) {
                if (strtoupper($value) !== strtoupper(session('warranty_captcha'))) {
                    $fail(__('messages.captcha_invalid'));
                }
            }],
        ], [
            'applicant_name.required' => __('messages.applicant_name_required'),
            'email.required' => __('messages.email_required'),
            'email.email' => __('messages.email_invalid'),
            'phone.required' => __('messages.phone_required'),
            'warranty_content.required' => __('messages.warranty_content_required'),
            'captcha.required' => __('messages.enter_captcha'),
        ]);

        if ($request->qr_code) {
            $existingWarranty = Contact::where('QRcode', $request->qr_code)
                ->where('Status', 'N')
                ->first();

            if ($existingWarranty) {
                return back()
                    ->withInput()
                    ->with('error', __('messages.warranty_already_requested'));
            }
        }

        try {
            // Generate new captcha after successful validation
            $newCaptcha = $this->_renderCaptcha();

            $GetWarranty = $this->traceabilityService->GetWarranty(
                [
                    'fullname' => $request->applicant_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'content' => $request->warranty_content,
                    'order_no' => $request->invoice_number,
                    'qrcode' => $request->qr_code
                ]
            );
            if ($GetWarranty->error_no == 1) {
                // Xử lý khi API trả về lỗi
                return back()
                    ->withInput()
                    ->with('error', __('messages.warranty_api_error'));
            }

            Contact::create([
                'Title' => 'Bảo hành',
                'Fullname' => $request->applicant_name,
                'Email'    => $request->email,
                'Phone'    => $request->phone,
                'Invoice'  => $request->invoice_number,
                'QRcode'   => $request->qr_code,
                'Content'  => $request->warranty_content,
                'Type'     => 0,
                'Date'     => now(),

                'user_id' => Auth::id(),
                'order_number'  => 'BH-' . time() . '-' . rand(1000, 9999),
                'Status' => 'N',
            ]);

            $telegram = new TelegramServiceProvider();
            $message = "📢 <b>Yêu cầu bảo hành!</b>\n";
            $message .= str_repeat("━", 25) . "\n\n";
            $message .= "👤 <b>Họ tên:</b> {$request->applicant_name}\n";
            $message .= "📧 <b>Email:</b> {$request->email}\n";
            $message .= "📞 <b>Điện thoại:</b> {$request->phone}\n";
            $message .= "👤 <b>Invoice:</b> {$request->invoice_number}\n";
            $message .= "📧 <b>QRcode:</b> {$request->qr_code}\n";
            $message .= "📞 <b>Content:</b> {$request->warranty_content}\n";
            $message .= "👤 <b>Date:</b> " . now() . "\n";
            $telegram->sendMessage($message);


            return redirect()
                ->route('warranty')
                ->with('success', __('messages.warranty_success'));
        } catch (\Exception $e) {
            Log::error('Warranty request error', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            $captchaImage = $this->_renderCaptcha();
            return back()
                ->withInput()
                ->with('error', __('messages.warranty_error'))
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
        session(['warranty_captcha' => $captchaCode]);
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
}
