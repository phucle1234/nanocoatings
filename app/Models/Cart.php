<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'total_amount',
        'item_count',
        'is_checked_out',
        'expires_at',
        'type',
        'dealer_code',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'item_count' => 'integer',
        'is_checked_out' => 'boolean',
        'expires_at' => 'datetime',
        'type' => 'string',
        'dealer_code' => 'string',
    ];

    /**
     * Quan hệ với User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Quan hệ với CartItems
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Lấy giỏ hàng theo user hoặc session
     */
    public static function getCart($userId = null, $type = 'customer', $sessionId = null)
    {
        if ($userId) {
            return static::where('user_id', $userId)->where('type', $type)
                ->where('is_checked_out', false)
                ->first();
        }

        if ($sessionId) {
            return static::where('session_id', $sessionId)
                ->where('type', $type)
                ->where('is_checked_out', false)
                ->first();
        }

        return null;
    }

    /**
     * Tạo hoặc lấy giỏ hàng
     */
    public static function getOrCreateCart($userId = null, $type = 'customer', $sessionId = null)
    {
        $cart = static::getCart($userId, $type, $sessionId);

        if (!$cart) {
            $cart = static::create([
                'user_id' => $userId,
                'session_id' => $sessionId,
                'total_amount' => 0,
                'item_count' => 0,
                'is_checked_out' => false,
                'expires_at' => now()->addDays(7), // 7 ngày
                'type' => $type,
            ]);
        }

        return $cart;
    }

    /**
     * Thêm sản phẩm vào giỏ hàng
     */
    public function addItem($productId, $quantity = 1, $options = null)
    {
        $product = Product::find($productId);
        if (!$product) {
            throw new \Exception('Sản phẩm không tồn tại');
        }

        $existingItem = $this->items()
            ->where('cart_id', $this->id)
            ->where('product_id', $productId)
            ->where('options', $options)
            ->first();

        if ($existingItem) {
            $existingItem->increment('quantity', $quantity);
            $existingItem->update([
                'total_price' => $existingItem->quantity * $existingItem->unit_price
            ]);
        } else {
            $this->items()->create([
                'product_id' => $productId,
                'product_code' => $product->code,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'total_price' => $product->price * $quantity,
                'options' => $options,
            ]);
        }

        $this->updateTotals();
        return $this;
    }

    public function addItemDealer($productInfo, $quantity = 1, $options = null)
    {
        $existingItem = $this->items()
            ->where('cart_id', $this->id)
            ->where('product_id', $productInfo->id)
            ->where('options', $options)
            ->first();

        if ($existingItem) {
            $existingItem->increment('quantity', $quantity);
            $existingItem->update([
                'total_price' => $existingItem->quantity * $existingItem->unit_price
            ]);
        } else {
            $this->items()->create([
                'product_id' => $productInfo->id,
                'product_code' => $productInfo->code,
                'quantity' => $quantity,
                'unit_price' => $productInfo->price,
                'total_price' => $productInfo->price * $quantity,
                'options' => $options,
            ]);
        }

        $this->updateTotals();
        return $this;
    }

    /**
     * Cập nhật số lượng sản phẩm
     */
    public function updateItemQuantity($productId, $quantity, $options = null)
    {
        $item = $this->items()
            ->where('product_id', $productId)
            ->where('options', $options)
            ->first();

        if ($item) {
            if ($quantity <= 0) {
                $item->delete();
            } else {
                $item->update([
                    'quantity' => $quantity,
                    'total_price' => $quantity * $item->unit_price
                ]);
            }
            $this->updateTotals();
        }

        return $this;
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng
     */
    public function removeItem($productId, $options = null)
    {
        $this->items()
            ->where('product_id', $productId)
            ->where('options', $options)
            ->delete();

        $this->updateTotals();

        if ($this->fresh()->item_count === 0) {
            $this->delete();
            return null;
        }
        return $this;
    }

    /**
     * Cập nhật tổng tiền và số lượng
     */
    public function updateTotals()
    {
        $this->update([
            'total_amount' => $this->items()->sum('total_price'),
            'item_count' => $this->items()->sum('quantity'),
        ]);
    }

    /**
     * Kiểm tra giỏ hàng có rỗng không
     */
    public function isEmpty(): bool
    {
        return $this->item_count <= 0;
    }

    /**
     * Xóa tất cả sản phẩm trong giỏ hàng
     */
    public function clear()
    {
        $this->items()->delete();
        $this->updateTotals();

        if ($this->fresh()->item_count === 0) {
            $this->delete();
            return null;
        }

        return $this;
    }

    /**
     * Đánh dấu đã checkout
     */
    public function markAsCheckedOut()
    {
        $this->update(['is_checked_out' => true]);
        return $this;
    }

    /**
     * Scope: Lấy giỏ hàng chưa checkout
     */
    public function scopeActive($query)
    {
        return $query->where('is_checked_out', false);
    }

    /**
     * Scope: Lấy giỏ hàng hết hạn
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Xóa giỏ hàng hết hạn
     */
    public static function cleanupExpired()
    {
        return static::expired()->delete();
    }

    /**
     * Lấy giỏ hàng với eager loading
     */
    public function scopeWithItems($query)
    {
        return $query->with(['items.product.translations']);
    }
}
