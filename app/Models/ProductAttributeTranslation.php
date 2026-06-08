<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttributeTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_id',
        'language',
        'name',
        'description',
    ];

    /**
     * Quan hệ với thuộc tính sản phẩm
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class, 'attribute_id');
    }

    /**
     * Scope để lấy bản dịch theo ngôn ngữ
     */
    public function scopeLanguage($query, $language)
    {
        return $query->where('language', $language);
    }
}
