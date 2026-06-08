<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'config',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Kiểm tra phương thức có khả dụng không
     */
    public function isAvailable(): bool
    {
        return $this->is_active;
    }

    /**
     * Lấy cấu hình
     */
    public function getConfig($key = null, $default = null)
    {
        if (!$this->config) {
            return $default;
        }

        if ($key) {
            return data_get($this->config, $key, $default);
        }

        return $this->config;
    }

    /**
     * Cập nhật cấu hình
     */
    public function updateConfig($key, $value)
    {
        $config = $this->config ?: [];
        data_set($config, $key, $value);

        $this->update(['config' => $config]);

        return $this;
    }

    /**
     * Lấy tất cả cấu hình
     */
    public function getAllConfig()
    {
        return $this->config ?: [];
    }

    /**
     * Scope: Lấy phương thức đang hoạt động
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Sắp xếp theo thứ tự
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Lấy phương thức thanh toán khả dụng
     */
    public static function getAvailable()
    {
        return static::active()->ordered()->get();
    }

    /**
     * Tìm phương thức theo code
     */
    public static function findByCode($code)
    {
        return static::where('code', $code)->first();
    }

    /**
     * Kiểm tra phương thức có tồn tại không
     */
    public static function exists($code): bool
    {
        return static::where('code', $code)->exists();
    }

    /**
     * Tạo phương thức thanh toán mặc định
     */
    public static function createDefaultMethods()
    {
        $methods = [
            [
                'name' => 'Tiền mặt khi nhận hàng',
                'code' => 'cod',
                'description' => 'Thanh toán bằng tiền mặt khi nhận hàng',
                'is_active' => true,
                'sort_order' => 1,
                'config' => [
                    'fee' => 0,
                    'description' => 'Không mất phí thanh toán'
                ]
            ],
            [
                'name' => 'Chuyển khoản ngân hàng',
                'code' => 'bank_transfer',
                'description' => 'Chuyển khoản qua ngân hàng',
                'is_active' => true,
                'sort_order' => 2,
                'config' => [
                    'fee' => 0,
                    'bank_info' => [
                        'account_name' => 'CÔNG TY TNHH CASUMINA',
                        'account_number' => '1234567890',
                        'bank_name' => 'Vietcombank',
                        'branch' => 'Chi nhánh Hà Nội'
                    ]
                ]
            ],
            [
                'name' => 'Ví điện tử MoMo',
                'code' => 'momo',
                'description' => 'Thanh toán qua ví điện tử MoMo',
                'is_active' => true,
                'sort_order' => 3,
                'config' => [
                    'fee' => 0,
                    'partner_code' => '',
                    'access_key' => '',
                    'secret_key' => ''
                ]
            ],
            [
                'name' => 'Thẻ tín dụng/Ghi nợ',
                'code' => 'credit_card',
                'description' => 'Thanh toán bằng thẻ tín dụng hoặc ghi nợ',
                'is_active' => false,
                'sort_order' => 4,
                'config' => [
                    'fee' => 2.5,
                    'gateway' => 'vnpay'
                ]
            ]
        ];

        foreach ($methods as $method) {
            static::firstOrCreate(
                ['code' => $method['code']],
                $method
            );
        }
    }

    /**
     * Lấy phí thanh toán
     */
    public function getFee()
    {
        return $this->getConfig('fee', 0);
    }

    /**
     * Tính phí thanh toán
     */
    public function calculateFee($amount)
    {
        $fee = $this->getFee();

        if (is_numeric($fee)) {
            return $fee;
        }

        return 0;
    }

    /**
     * Lấy thông tin ngân hàng (cho bank transfer)
     */
    public function getBankInfo()
    {
        return $this->getConfig('bank_info', []);
    }

    /**
     * Kiểm tra có cần thông tin ngân hàng không
     */
    public function requiresBankInfo(): bool
    {
        return $this->code === 'bank_transfer' && !empty($this->getBankInfo());
    }
}
