<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'cost',
        'free_shipping_threshold',
        'estimated_days',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'free_shipping_threshold' => 'decimal:2',
        'estimated_days' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Quan hệ với OrderShipments
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(OrderShipment::class);
    }

    /**
     * Kiểm tra phương thức có khả dụng không
     */
    public function isAvailable(): bool
    {
        return $this->is_active;
    }

    /**
     * Tính phí vận chuyển
     */
    public function calculateShipping($orderAmount = 0)
    {
        // Kiểm tra có miễn phí ship không
        if ($this->free_shipping_threshold && $orderAmount >= $this->free_shipping_threshold) {
            return 0;
        }

        return $this->cost;
    }

    /**
     * Kiểm tra có miễn phí ship không
     */
    public function isFreeShipping($orderAmount = 0): bool
    {
        return $this->free_shipping_threshold && $orderAmount >= $this->free_shipping_threshold;
    }

    /**
     * Lấy thời gian ước tính
     */
    public function getEstimatedDelivery()
    {
        if (!$this->estimated_days) {
            return null;
        }

        return now()->addDays($this->estimated_days);
    }

    /**
     * Lấy mô tả thời gian giao hàng
     */
    public function getDeliveryTimeDescription()
    {
        if (!$this->estimated_days) {
            return 'Liên hệ để biết thời gian giao hàng';
        }

        if ($this->estimated_days == 1) {
            return 'Giao hàng trong ngày';
        }

        if ($this->estimated_days <= 3) {
            return "Giao hàng trong {$this->estimated_days} ngày";
        }

        return "Giao hàng trong {$this->estimated_days} ngày làm việc";
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
     * Lấy phương thức vận chuyển khả dụng
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
     * Tạo phương thức vận chuyển mặc định
     */
    public static function createDefaultMethods()
    {
        $methods = [
            [
                'name' => 'Giao hàng tiêu chuẩn',
                'code' => 'standard',
                'description' => 'Giao hàng tiêu chuẩn trong nội thành',
                'cost' => 30000,
                'free_shipping_threshold' => 500000,
                'estimated_days' => 2,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Giao hàng nhanh',
                'code' => 'express',
                'description' => 'Giao hàng nhanh trong 24h',
                'cost' => 50000,
                'free_shipping_threshold' => 1000000,
                'estimated_days' => 1,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Giao hàng miễn phí',
                'code' => 'free',
                'description' => 'Giao hàng miễn phí cho đơn hàng trên 500k',
                'cost' => 0,
                'free_shipping_threshold' => 500000,
                'estimated_days' => 3,
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Nhận tại cửa hàng',
                'code' => 'pickup',
                'description' => 'Khách hàng đến nhận tại cửa hàng',
                'cost' => 0,
                'free_shipping_threshold' => null,
                'estimated_days' => 0,
                'is_active' => true,
                'sort_order' => 4,
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
     * Lấy phương thức vận chuyển phù hợp
     */
    public static function getSuitableMethods($orderAmount = 0)
    {
        return static::active()
            ->ordered()
            ->get()
            ->map(function ($method) use ($orderAmount) {
                $method->calculated_cost = $method->calculateShipping($orderAmount);
                $method->is_free = $method->isFreeShipping($orderAmount);
                return $method;
            });
    }

    /**
     * Lấy phương thức miễn phí
     */
    public static function getFreeShippingMethods($orderAmount = 0)
    {
        return static::active()
            ->whereNotNull('free_shipping_threshold')
            ->where('free_shipping_threshold', '<=', $orderAmount)
            ->ordered()
            ->get();
    }

    /**
     * Lấy phương thức rẻ nhất
     */
    public static function getCheapestMethod($orderAmount = 0)
    {
        $methods = static::getSuitableMethods($orderAmount);

        return $methods->sortBy('calculated_cost')->first();
    }
}
