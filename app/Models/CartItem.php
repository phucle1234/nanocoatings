<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'product_code',
        'quantity',
        'unit_price',
        'total_price',
        'options',
        'added_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'added_at' => 'datetime',
    ];

    /**
     * Quan hệ với Cart
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Quan hệ với Product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Tăng số lượng
     */
    public function incrementQuantity($quantity = 1)
    {
        $this->increment('quantity', $quantity);
        $this->updateTotalPrice();
        return $this;
    }

    /**
     * Giảm số lượng
     */
    public function decrementQuantity($quantity = 1)
    {
        $this->decrement('quantity', $quantity);
        $this->updateTotalPrice();
        return $this;
    }

    /**
     * Cập nhật số lượng
     */
    public function updateQuantity($quantity)
    {
        $this->update([
            'quantity' => $quantity,
        ]);
        $this->updateTotalPrice();
        return $this;
    }

    /**
     * Cập nhật tổng tiền
     */
    public function updateTotalPrice()
    {
        $this->update([
            'total_price' => $this->quantity * $this->unit_price
        ]);
        return $this;
    }

    /**
     * Lấy tên sản phẩm
     */
    public function getProductNameAttribute()
    {
        return $this->product ? $this->product->name : 'Sản phẩm không tồn tại';
    }

    /**
     * Lấy hình ảnh sản phẩm
     */
    public function getProductImageAttribute()
    {
        return $this->product ? $this->product->main_image : null;
    }

    /**
     * Lấy SKU sản phẩm
     */
    public function getProductSkuAttribute()
    {
        return $this->product ? $this->product->sku : '';
    }

    /**
     * Lấy options dạng text
     */
    // public function getOptionsTextAttribute()
    // {
    //     if (!$this->options || empty($this->options)) {
    //         return '';
    //     }

    //     $options = [];
    //     foreach ($this->options as $key => $value) {
    //         $options[] = ucfirst($key) . ': ' . $value;
    //     }

    //     return implode(', ', $options);
    // }

    /**
     * Kiểm tra sản phẩm còn tồn tại không
     */
    public function isProductAvailable(): bool
    {
        return $this->product && $this->product->is_active && $this->product->isInStock();
    }

    /**
     * Kiểm tra số lượng có đủ không
     */
    public function isQuantityAvailable(): bool
    {
        if (!$this->product) {
            return false;
        }

        return $this->product->stock_quantity >= $this->quantity;
    }

    /**
     * Scope: Lấy items có sản phẩm còn hoạt động
     */
    public function scopeAvailable($query)
    {
        return $query->whereHas('product', function ($q) {
            $q->where('is_active', true);
        });
    }

    /**
     * Scope: Lấy items có sản phẩm còn hàng
     */
    public function scopeInStock($query)
    {
        return $query->whereHas('product', function ($q) {
            $q->where('stock_quantity', '>', 0);
        });
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // Cập nhật cart totals khi cart item thay đổi
        static::saved(function ($cartItem) {
            $cartItem->cart->updateTotals();
        });

        static::deleted(function ($cartItem) {
            $cartItem->cart->updateTotals();
        });
    }
}
