<?php

namespace App\Http\Controllers\langding;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CasuminaApi\WarrantyService;
use Illuminate\Support\Facades\Validator;
use Random\Randomizer;

class TraceabilityController extends Controller
{
    public function __construct(
        protected WarrantyService $warrantyService,
    ) {}

    /**
     * Hiển thị trang trung tâm truy nguyên sản phẩm
     */
    public function index(Request $request)
    {
        $captchaImage = $this->_renderCaptcha();
        return view('langding.traceability.index', compact('captchaImage'));
    }

    /**
     * Xử lý form kiểm tra mã sản phẩm
     */
    // public function check(Request $request)
    // {
    //     $request->validate([
    //         'product_code' => 'required|string|max:50',
    //         'captcha' => 'required|string|max:10',
    //     ]);

    //     $sessionCaptcha = session('traceability_captcha');
    //     if (strtoupper($request->captcha) !== strtoupper($sessionCaptcha)) {
    //         return back()->withInput()->with('error', __('messages.captcha_invalid'))->with('captchaCode', $sessionCaptcha);
    //     }

    //     $productCode = $request->product_code;

    //     // Generate new captcha after successful check
    //     $newCaptcha = $this->generateCaptcha();
    //     session(['traceability_captcha' => $newCaptcha]);

    //     $GetTrace = $this->traceabilityService->GetTraceability(['qrcode' => $productCode]);


    //     if ($GetTrace->error_no != '' || $GetTrace->item_no == '' || $GetTrace->item_name == '' || $GetTrace->order_no == '' || $GetTrace->status == 0) {
    //         return back()->withInput()
    //             ->with('error', __('messages.product_not_found'))
    //             ->with('captchaCode', $newCaptcha);
    //     }

    //     return back()
    //         ->with('traceability_data', (array) $GetTrace)
    //         ->with('product_code', $productCode)
    //         ->with('captchaCode', $newCaptcha);
    // }

    public function check(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'trace_code' => ['required', 'string', 'max:255'],
                'captcha' => ['required', 'string', 'max:15', function ($attribute, $value, $fail) {
                    if (strtoupper($value) !== strtoupper(session('traceability_captcha'))) {
                        $fail(__('messages.captcha_invalid'));
                    }
                },],
            ], [], [
                'trace_code' => __('messages.enter_product_code'),
                'captcha'    => __('messages.enter_captcha'),
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 99,
                    'message' => 'Invalid data!',
                    'errors' => $validator->errors()
                ]);
            }
            $traceInfo = $this->warrantyService->getWarrantyInfo($request->trace_code);
            $captchaImage = $this->_renderCaptcha();
            if (!$traceInfo || $traceInfo->error_no != '') {
                return response()->json([
                    'status' => 0,
                    'message' => __('messages.product_not_found'),
                    'captchaImage' => $captchaImage,
                ]);
            }
            $html = view('langding.traceability._trace-product', compact('traceInfo'))->render();
            if ($traceInfo?->type == 2) {
                $html = view('langding.traceability._trace-order', compact('traceInfo'))->render();
            }
            return response()->json([
                'status' => 1,
                'html' => $html,
                'captchaImage' => $captchaImage,
            ]);
        } catch (\Exception $e) {
            $captchaImage = $this->_renderCaptcha();
            return response()->json([
                'status' => 0,
                'message' => __('messages.error_occurred'),
                'captchaImage' => $captchaImage,
            ]);
        }
    }

    public function refreshCaptcha()
    {
        return response()->json([
            'captchaImage' => $this->_renderCaptcha()
        ]);
    }

    /**
     * Generate new captcha code
     */
    private function _renderCaptcha()
    {
        $captchaCode = $this->_generateCaptcha();
        session(['traceability_captcha' => $captchaCode]);
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
     * Generate captcha image (simple base64 implementation)
     */
    private function _generateCaptchaImage($code)
    {
        // Simple implementation - you can enhance this later
        $width = 120;
        $height = 40;
        $image = imagecreatetruecolor($width, $height);

        // Background color (dark red)
        $bgColor = imagecolorallocate($image, 237, 220, 220);
        imagefill($image, 0, 0, $bgColor);

        // Text color (white)
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

        return 'data:image/png;base64,' . base64_encode($imageData);
    }
}
