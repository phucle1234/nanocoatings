<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'minimum_amount',
        'maximum_discount',
        'usage_limit',
        'used_count',
        'user_limit',
        'is_active',
        'starts_at',
        'expires_at',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'maximum_discount' => 'decimal:2',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'user_limit' => 'integer',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Quan hệ với OrderCoupons
     */
    public function orderCoupons(): HasMany
    {
        return $this->hasMany(OrderCoupon::class);
    }

    /**
     * Kiểm tra coupon có hợp lệ không
     */
    public function isValid(): bool
    {
        return $this->is_active &&
            $this->isNotExpired() &&
            $this->isStarted() &&
            $this->hasUsageLeft();
    }

    /**
     * Kiểm tra coupon chưa hết hạn
     */
    public function isNotExpired(): bool
    {
        return !$this->expires_at || $this->expires_at->isFuture();
    }

    /**
     * Kiểm tra coupon đã bắt đầu
     */
    public function isStarted(): bool
    {
        return !$this->starts_at || $this->starts_at->isPast();
    }

    /**
     * Kiểm tra còn lượt sử dụng
     */
    public function hasUsageLeft(): bool
    {
        return !$this->usage_limit || $this->used_count < $this->usage_limit;
    }

    /**
     * Kiểm tra có thể sử dụng cho đơn hàng
     */
    public function canUseForOrder($orderAmount, $userId = null): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        // Kiểm tra số tiền tối thiểu
        if ($this->minimum_amount && $orderAmount < $this->minimum_amount) {
            return false;
        }

        // Kiểm tra giới hạn user (nếu có)
        if ($userId && $this->user_limit) {
            $userUsageCount = $this->orderCoupons()
                ->whereHas('order', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->count();

            if ($userUsageCount >= $this->user_limit) {
                return false;
            }
        }

        return true;
    }

    /**
     * Tính số tiền giảm giá
     */
    public function calculateDiscount($orderAmount): float
    {
        if (!$this->canUseForOrder($orderAmount)) {
            return 0;
        }

        $discount = 0;

        if ($this->type === 'fixed') {
            $discount = $this->value;
        } elseif ($this->type === 'percentage') {
            $discount = ($orderAmount * $this->value) / 100;
        }

        // Áp dụng giới hạn tối đa
        if ($this->maximum_discount && $discount > $this->maximum_discount) {
            $discount = $this->maximum_discount;
        }

        // Không được giảm nhiều hơn giá trị đơn hàng
        return min($discount, $orderAmount);
    }

    /**
     * Sử dụng coupon
     */
    public function use()
    {
        $this->increment('used_count');
        return $this;
    }

    /**
     * Hoàn lại lượt sử dụng
     */
    public function refund()
    {
        $this->decrement('used_count');
        return $this;
    }

    /**
     * Scope: Lấy coupon đang hoạt động
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Lấy coupon chưa hết hạn
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope: Lấy coupon đã bắt đầu
     */
    public function scopeStarted($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('starts_at')
                ->orWhere('starts_at', '<=', now());
        });
    }

    /**
     * Scope: Lấy coupon còn lượt sử dụng
     */
    public function scopeHasUsageLeft($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('usage_limit')
                ->orWhereRaw('used_count < usage_limit');
        });
    }

    /**
     * Scope: Lấy coupon hợp lệ
     */
    public function scopeValid($query)
    {
        return $query->active()
            ->notExpired()
            ->started()
            ->hasUsageLeft();
    }

    /**
     * Tìm coupon theo code
     */
    public static function findByCode($code)
    {
        return static::where('code', $code)->first();
    }

    /**
     * Tạo coupon mặc định
     */
    public static function createDefaultCoupons()
    {
        $coupons = [
            [
                'code' => 'WELCOME10',
                'name' => 'Chào mừng khách hàng mới',
                'description' => 'Giảm 10% cho đơn hàng đầu tiên',
                'type' => 'percentage',
                'value' => 10,
                'minimum_amount' => 200000,
                'maximum_discount' => 50000,
                'usage_limit' => 1000,
                'user_limit' => 1,
                'is_active' => true,
                'starts_at' => now(),
                'expires_at' => now()->addMonths(3),
            ],
            [
                'code' => 'SAVE50K',
                'name' => 'Tiết kiệm 50k',
                'description' => 'Giảm 50,000đ cho đơn hàng trên 500k',
                'type' => 'fixed',
                'value' => 50000,
                'minimum_amount' => 500000,
                'usage_limit' => 500,
                'user_limit' => 2,
                'is_active' => true,
                'starts_at' => now(),
                'expires_at' => now()->addMonths(1),
            ],
            [
                'code' => 'VIP20',
                'name' => 'Khách hàng VIP',
                'description' => 'Giảm 20% cho khách hàng VIP',
                'type' => 'percentage',
                'value' => 20,
                'minimum_amount' => 1000000,
                'maximum_discount' => 200000,
                'usage_limit' => 100,
                'user_limit' => 5,
                'is_active' => true,
                'starts_at' => now(),
                'expires_at' => now()->addYear(),
            ]
        ];

        foreach ($coupons as $coupon) {
            static::firstOrCreate(
                ['code' => $coupon['code']],
                $coupon
            );
        }
    }

    /**
     * Lấy loại coupon dạng text
     */
    public function getTypeTextAttribute()
    {
        return $this->type === 'fixed' ? 'Giảm giá cố định' : 'Giảm giá phần trăm';
    }

    /**
     * Lấy giá trị hiển thị
     */
    public function getDisplayValueAttribute()
    {
        if ($this->type === 'fixed') {
            return number_format($this->value) . 'đ';
        }

        return $this->value . '%';
    }
}
