<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_sku',
        'quantity',
        'unit_price',
        'total_price',
        'options',
        'qrcode',
        'warranty_status',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'options' => 'array',
        'qrcode' => 'array',
    ];

    /**
     * Quan hệ với Order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Quan hệ với Product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Lấy tổng tiền (alias cho total_price)
     */
    public function getSubtotalAttribute(): ?float
    {
        return $this->total_price ? (float) $this->total_price : null;
    }

    /**
     * Lấy thông tin options dạng text
     */
    public function getOptionsTextAttribute(): string
    {
        if (empty($this->options)) {
            return '';
        }

        $options = [];
        foreach ($this->options as $key => $value) {
            $options[] = ucfirst($key) . ': ' . $value;
        }

        return implode(', ', $options);
    }

    /**
     * Lấy tên sản phẩm với options
     */
    public function getProductNameWithOptionsAttribute(): string
    {
        $name = $this->product_name;

        if ($this->options_text) {
            $name .= ' (' . $this->options_text . ')';
        }

        return $name;
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // Tự động tính total_price khi tạo/cập nhật
        static::saving(function ($orderItem) {
            if ($orderItem->quantity && $orderItem->unit_price) {
                $orderItem->total_price = $orderItem->quantity * $orderItem->unit_price;
            }
        });
    }
}
