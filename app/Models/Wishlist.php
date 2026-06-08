<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wishlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
    ];

    /**
     * Quan hệ với User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Quan hệ với Product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Thêm sản phẩm vào wishlist
     */
    public static function addToWishlist($userId, $productId)
    {
        // Kiểm tra sản phẩm có tồn tại không
        if (!Product::find($productId)) {
            throw new \Exception('Sản phẩm không tồn tại');
        }

        // Kiểm tra đã có trong wishlist chưa
        if (static::isInWishlist($userId, $productId)) {
            throw new \Exception('Sản phẩm đã có trong danh sách yêu thích');
        }

        return static::create([
            'user_id' => $userId,
            'product_id' => $productId,
        ]);
    }

    /**
     * Xóa sản phẩm khỏi wishlist
     */
    public static function removeFromWishlist($userId, $productId)
    {
        return static::where('user_id', $userId)
            ->where('product_id', $productId)
            ->delete();
    }

    /**
     * Kiểm tra sản phẩm có trong wishlist không
     */
    public static function isInWishlist($userId, $productId): bool
    {
        return static::where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }

    /**
     * Lấy wishlist của user
     */
    public static function getUserWishlist($userId, $limit = null)
    {
        $query = static::where('user_id', $userId)
            ->with(['product.translations'])
            ->orderBy('created_at', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Lấy số lượng sản phẩm trong wishlist
     */
    public static function getWishlistCount($userId): int
    {
        return static::where('user_id', $userId)->count();
    }

    /**
     * Xóa tất cả sản phẩm khỏi wishlist
     */
    public static function clearWishlist($userId)
    {
        return static::where('user_id', $userId)->delete();
    }

    /**
     * Lấy sản phẩm phổ biến trong wishlist
     */
    public static function getPopularWishlistProducts($limit = 10)
    {
        return static::selectRaw('product_id, COUNT(*) as wishlist_count')
            ->with(['product.translations'])
            ->groupBy('product_id')
            ->orderBy('wishlist_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Scope: Lấy theo user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Lấy theo sản phẩm
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope: Lấy sản phẩm còn hoạt động
     */
    public function scopeActiveProducts($query)
    {
        return $query->whereHas('product', function ($q) {
            $q->where('is_active', true);
        });
    }

    /**
     * Toggle wishlist (thêm nếu chưa có, xóa nếu đã có)
     */
    public static function toggleWishlist($userId, $productId)
    {
        if (static::isInWishlist($userId, $productId)) {
            static::removeFromWishlist($userId, $productId);
            return false; // Đã xóa
        } else {
            static::addToWishlist($userId, $productId);
            return true; // Đã thêm
        }
    }

    /**
     * Lấy wishlist với thông tin sản phẩm đầy đủ
     */
    public function scopeWithProductDetails($query)
    {
        return $query->with([
            'product' => function ($q) {
                $q->with(['translations', 'category.translations']);
            }
        ]);
    }

    /**
     * Lấy sản phẩm trong wishlist có giá khuyến mãi
     */
    public static function getWishlistProductsOnSale($userId)
    {
        return static::byUser($userId)
            ->whereHas('product', function ($q) {
                $q->whereNotNull('sale_price')
                    ->where('sale_price', '>', 0)
                    ->whereColumn('sale_price', '<', 'price');
            })
            ->with(['product.translations'])
            ->get();
    }
}
