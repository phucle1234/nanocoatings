<?php
// app/Models/ProductCategoryTranslation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductCategoryTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'language',
        'name',
        'description',
        'meta_title',
        'meta_description',
        'slug',
        'image_urls',
        'link_type',
        'youtube_url',
    ];

    protected $casts = [
        'image_urls' => 'array', // Thêm cast cho array
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function scopeLanguage($query, $language)
    {
        return $query->where('language', $language);
    }
}
