<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderShipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'shipping_method_id',
        'tracking_number',
        'carrier',
        'status',
        'shipped_at',
        'delivered_at',
        'notes',
    ];

    protected $casts = [
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /**
     * Quan hệ với Order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Quan hệ với ShippingMethod
     */
    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    /**
     * Kiểm tra đã giao hàng chưa
     */
    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    /**
     * Kiểm tra đã ship chưa
     */
    public function isShipped(): bool
    {
        return in_array($this->status, ['shipped', 'in_transit', 'delivered']);
    }

    /**
     * Kiểm tra đang vận chuyển
     */
    public function isInTransit(): bool
    {
        return $this->status === 'in_transit';
    }

    /**
     * Kiểm tra đã trả lại
     */
    public function isReturned(): bool
    {
        return $this->status === 'returned';
    }

    /**
     * Đánh dấu đã ship
     */
    public function markAsShipped($trackingNumber = null, $carrier = null)
    {
        $this->update([
            'status' => 'shipped',
            'tracking_number' => $trackingNumber ?: $this->tracking_number,
            'carrier' => $carrier ?: $this->carrier,
            'shipped_at' => now(),
        ]);

        return $this;
    }

    /**
     * Đánh dấu đang vận chuyển
     */
    public function markAsInTransit()
    {
        $this->update([
            'status' => 'in_transit',
        ]);

        return $this;
    }

    /**
     * Đánh dấu đã giao hàng
     */
    public function markAsDelivered()
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        return $this;
    }

    /**
     * Đánh dấu đã trả lại
     */
    public function markAsReturned()
    {
        $this->update([
            'status' => 'returned',
        ]);

        return $this;
    }

    /**
     * Lấy URL tracking
     */
    public function getTrackingUrl()
    {
        if (!$this->tracking_number) {
            return null;
        }

        $urls = [
            'viettel_post' => 'https://viettelpost.vn/tra-cuu-hang-hoa?code=' . $this->tracking_number,
            'vnpost' => 'https://www.vnpost.vn/vi-vn/dinh-vi/buu-pham?key=' . $this->tracking_number,
            'ghn' => 'https://donhang.ghn.vn/?order_code=' . $this->tracking_number,
            'ghtk' => 'https://giaohangtietkiem.vn/tracking/' . $this->tracking_number,
        ];

        $carrier = strtolower($this->carrier ?? '');

        return $urls[$carrier] ?? null;
    }

    /**
     * Lấy trạng thái dạng text
     */
    public function getStatusTextAttribute()
    {
        $statuses = [
            'pending' => 'Chờ giao hàng',
            'shipped' => 'Đã giao hàng',
            'in_transit' => 'Đang vận chuyển',
            'delivered' => 'Đã giao thành công',
            'returned' => 'Đã trả lại',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Lấy màu sắc cho trạng thái
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'shipped' => 'info',
            'in_transit' => 'primary',
            'delivered' => 'success',
            'returned' => 'danger',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Scope: Lấy shipment đã giao
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Scope: Lấy shipment đang vận chuyển
     */
    public function scopeInTransit($query)
    {
        return $query->where('status', 'in_transit');
    }

    /**
     * Scope: Lấy shipment đã ship
     */
    public function scopeShipped($query)
    {
        return $query->where('status', 'shipped');
    }

    /**
     * Scope: Lấy shipment theo carrier
     */
    public function scopeByCarrier($query, $carrier)
    {
        return $query->where('carrier', $carrier);
    }

    /**
     * Tạo shipment mới
     */
    public static function createForOrder($orderId, $shippingMethodId, $trackingNumber = null, $carrier = null)
    {
        return static::create([
            'order_id' => $orderId,
            'shipping_method_id' => $shippingMethodId,
            'tracking_number' => $trackingNumber,
            'carrier' => $carrier,
            'status' => 'pending',
        ]);
    }

    /**
     * Lấy thời gian giao hàng ước tính
     */
    public function getEstimatedDelivery()
    {
        if (!$this->shippingMethod || !$this->shipped_at) {
            return null;
        }

        return $this->shipped_at->addDays($this->shippingMethod->estimated_days);
    }

    /**
     * Kiểm tra có trễ giao hàng không
     */
    public function isDelayed()
    {
        if (!$this->isShipped() || $this->isDelivered()) {
            return false;
        }

        $estimatedDelivery = $this->getEstimatedDelivery();

        return $estimatedDelivery && $estimatedDelivery->isPast();
    }
}
