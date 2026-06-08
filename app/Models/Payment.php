<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_method',
        'payment_gateway',
        'transaction_id',
        'amount',
        'status',
        'gateway_response',
        'failure_reason',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
    ];

    /**
     * Quan hệ với Order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Kiểm tra thanh toán đã hoàn thành
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Kiểm tra thanh toán đã thất bại
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Kiểm tra thanh toán đang xử lý
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Kiểm tra có thể hoàn tiền không
     */
    public function canRefund(): bool
    {
        return $this->isCompleted() && $this->amount > 0;
    }

    /**
     * Đánh dấu thanh toán thành công
     */
    public function markAsCompleted($transactionId = null)
    {
        $this->update([
            'status' => 'completed',
            'transaction_id' => $transactionId ?: $this->transaction_id,
            'paid_at' => now(),
        ]);

        return $this;
    }

    /**
     * Đánh dấu thanh toán thất bại
     */
    public function markAsFailed($reason = null)
    {
        $this->update([
            'status' => 'failed',
            'failure_reason' => $reason,
        ]);

        return $this;
    }

    /**
     * Đánh dấu đang xử lý
     */
    public function markAsProcessing()
    {
        $this->update([
            'status' => 'processing',
        ]);

        return $this;
    }

    /**
     * Lưu response từ gateway
     */
    public function setGatewayResponse($response)
    {
        $this->update([
            'gateway_response' => $response,
        ]);

        return $this;
    }

    /**
     * Lấy thông tin từ gateway response
     */
    public function getGatewayData($key = null)
    {
        if (!$this->gateway_response) {
            return null;
        }

        if ($key) {
            return data_get($this->gateway_response, $key);
        }

        return $this->gateway_response;
    }

    /**
     * Scope: Lấy thanh toán đã hoàn thành
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: Lấy thanh toán thất bại
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope: Lấy thanh toán đang xử lý
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope: Lấy thanh toán theo phương thức
     */
    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope: Lấy thanh toán theo gateway
     */
    public function scopeByGateway($query, $gateway)
    {
        return $query->where('payment_gateway', $gateway);
    }

    /**
     * Tạo thanh toán mới
     */
    public static function createForOrder($orderId, $method, $gateway, $amount)
    {
        return static::create([
            'order_id' => $orderId,
            'payment_method' => $method,
            'payment_gateway' => $gateway,
            'amount' => $amount,
            'status' => 'pending',
        ]);
    }

    /**
     * Lấy trạng thái dạng text
     */
    public function getStatusTextAttribute()
    {
        $statuses = [
            'pending' => 'Chờ thanh toán',
            'processing' => 'Đang xử lý',
            'completed' => 'Đã thanh toán',
            'failed' => 'Thất bại',
            'cancelled' => 'Đã hủy',
            'refunded' => 'Đã hoàn tiền',
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
            'processing' => 'info',
            'completed' => 'success',
            'failed' => 'danger',
            'cancelled' => 'secondary',
            'refunded' => 'primary',
        ];

        return $colors[$this->status] ?? 'secondary';
    }
}
