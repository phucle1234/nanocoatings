<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'language',
        'name',
        'description',
        'short_description',
        'outstanding_features',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'slug',
        'features',
        'specifications',
        'image_urls',
        'text_search',
    ];

    protected $casts = [
        'specifications' => 'array',
        'image_urls' => 'array',
    ];

    /**
     * Quan hệ với sản phẩm
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Scope để lấy bản dịch theo ngôn ngữ
     */
    public function scopeLanguage($query, $language)
    {
        return $query->where('language', $language);
    }
}
