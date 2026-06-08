<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductInventory extends Model
{
    use HasFactory;

    protected $table = 'product_inventory';

    protected $fillable = [
        'product_id',
        'quantity',
        'reserved_quantity',
        'location',
        'reorder_point',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'reorder_point' => 'integer',
    ];

    /**
     * Quan hệ với Product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Quan hệ với InventoryMovements
     */
    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'product_id', 'product_id');
    }

    /**
     * Kiểm tra có hàng không
     */
    public function isAvailable(): bool
    {
        return $this->available_quantity > 0;
    }

    /**
     * Kiểm tra có đủ hàng không
     */
    public function hasStock($quantity): bool
    {
        return $this->available_quantity >= $quantity;
    }

    /**
     * Kiểm tra cần nhập hàng
     */
    public function needsReorder(): bool
    {
        return $this->quantity <= $this->reorder_point;
    }

    /**
     * Reserve hàng
     */
    public function reserve($quantity, $referenceType = null, $referenceId = null)
    {
        if (!$this->hasStock($quantity)) {
            throw new \Exception('Không đủ hàng để reserve');
        }

        $this->increment('reserved_quantity', $quantity);

        // Log movement
        InventoryMovement::create([
            'product_id' => $this->product_id,
            'type' => 'reserved',
            'quantity' => $quantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => "Reserve {$quantity} items",
        ]);

        return $this;
    }

    /**
     * Release hàng đã reserve
     */
    public function release($quantity, $referenceType = null, $referenceId = null)
    {
        $this->decrement('reserved_quantity', $quantity);

        // Log movement
        InventoryMovement::create([
            'product_id' => $this->product_id,
            'type' => 'unreserved',
            'quantity' => $quantity,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => "Release {$quantity} items",
        ]);

        return $this;
    }

    /**
     * Nhập hàng
     */
    public function stockIn($quantity, $notes = null, $userId = null)
    {
        $this->increment('quantity', $quantity);

        // Log movement
        InventoryMovement::create([
            'product_id' => $this->product_id,
            'type' => 'in',
            'quantity' => $quantity,
            'notes' => $notes ?: "Stock in {$quantity} items",
            'created_by' => $userId,
        ]);

        return $this;
    }

    /**
     * Xuất hàng
     */
    public function stockOut($quantity, $notes = null, $userId = null)
    {
        if (!$this->hasStock($quantity)) {
            throw new \Exception('Không đủ hàng để xuất');
        }

        $this->decrement('quantity', $quantity);

        // Log movement
        InventoryMovement::create([
            'product_id' => $this->product_id,
            'type' => 'out',
            'quantity' => $quantity,
            'notes' => $notes ?: "Stock out {$quantity} items",
            'created_by' => $userId,
        ]);

        return $this;
    }

    /**
     * Điều chỉnh tồn kho
     */
    public function adjust($newQuantity, $notes = null, $userId = null)
    {
        $difference = $newQuantity - $this->quantity;

        $this->update(['quantity' => $newQuantity]);

        // Log movement
        InventoryMovement::create([
            'product_id' => $this->product_id,
            'type' => 'adjustment',
            'quantity' => $difference,
            'notes' => $notes ?: "Adjust inventory to {$newQuantity}",
            'created_by' => $userId,
        ]);

        return $this;
    }

    /**
     * Lấy tồn kho theo location
     */
    public static function getByLocation($productId, $location = null)
    {
        $query = static::where('product_id', $productId);

        if ($location) {
            $query->where('location', $location);
        }

        return $query->first();
    }

    /**
     * Lấy tổng tồn kho của sản phẩm
     */
    public static function getTotalStock($productId)
    {
        return static::where('product_id', $productId)
            ->sum('quantity');
    }

    /**
     * Lấy tổng hàng reserve của sản phẩm
     */
    public static function getTotalReserved($productId)
    {
        return static::where('product_id', $productId)
            ->sum('reserved_quantity');
    }

    /**
     * Lấy tổng hàng available của sản phẩm
     */
    public static function getTotalAvailable($productId)
    {
        return static::getTotalStock($productId) - static::getTotalReserved($productId);
    }

    /**
     * Scope: Lấy sản phẩm có hàng
     */
    public function scopeInStock($query)
    {
        return $query->whereRaw('quantity > reserved_quantity');
    }

    /**
     * Scope: Lấy sản phẩm hết hàng
     */
    public function scopeOutOfStock($query)
    {
        return $query->whereRaw('quantity <= reserved_quantity');
    }

    /**
     * Scope: Lấy sản phẩm cần nhập hàng
     */
    public function scopeNeedsReorder($query)
    {
        return $query->whereRaw('quantity <= reorder_point');
    }

    /**
     * Scope: Lấy theo location
     */
    public function scopeByLocation($query, $location)
    {
        return $query->where('location', $location);
    }

    /**
     * Tạo inventory cho sản phẩm
     */
    public static function createForProduct($productId, $quantity = 0, $location = null, $reorderPoint = 10)
    {
        return static::create([
            'product_id' => $productId,
            'quantity' => $quantity,
            'reserved_quantity' => 0,
            'location' => $location,
            'reorder_point' => $reorderPoint,
        ]);
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        // Tự động tạo inventory khi tạo sản phẩm mới
        static::created(function ($inventory) {
            if ($inventory->quantity > 0) {
                InventoryMovement::create([
                    'product_id' => $inventory->product_id,
                    'type' => 'in',
                    'quantity' => $inventory->quantity,
                    'notes' => 'Initial stock',
                ]);
            }
        });
    }
}
