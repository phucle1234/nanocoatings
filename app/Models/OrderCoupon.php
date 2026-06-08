<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderCoupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'coupon_id',
        'discount_amount',
    ];

    protected $casts = [
        'discount_amount' => 'decimal:2',
    ];

    /**
     * Quan hệ với Order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Quan hệ với Coupon
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Lấy mã coupon
     */
    public function getCouponCodeAttribute()
    {
        return $this->coupon ? $this->coupon->code : '';
    }

    /**
     * Lấy tên coupon
     */
    public function getCouponNameAttribute()
    {
        return $this->coupon ? $this->coupon->name : '';
    }

    /**
     * Lấy loại coupon
     */
    public function getCouponTypeAttribute()
    {
        return $this->coupon ? $this->coupon->type : '';
    }

    /**
     * Lấy giá trị coupon
     */
    public function getCouponValueAttribute()
    {
        return $this->coupon ? $this->coupon->value : 0;
    }

    /**
     * Tạo order coupon
     */
    public static function createForOrder($orderId, $couponId, $discountAmount)
    {
        return static::create([
            'order_id' => $orderId,
            'coupon_id' => $couponId,
            'discount_amount' => $discountAmount,
        ]);
    }

    /**
     * Scope: Lấy theo order
     */
    public function scopeForOrder($query, $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    /**
     * Scope: Lấy theo coupon
     */
    public function scopeForCoupon($query, $couponId)
    {
        return $query->where('coupon_id', $couponId);
    }
}
