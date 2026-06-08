<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttributeValueTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_value_id',
        'language',
        'value',
    ];

    /**
     * Quan hệ với giá trị thuộc tính sản phẩm
     */
    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(ProductAttributeValue::class, 'attribute_value_id');
    }

    /**
     * Scope để lấy bản dịch theo ngôn ngữ
     */
    public function scopeLanguage($query, $language)
    {
        return $query->where('language', $language);
    }
}
