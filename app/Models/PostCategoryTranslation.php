<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostCategoryTranslation extends Model
{
    protected $table = 'postcategory_translations';

    protected $fillable = [
        'postcategory_id',
        'language',
        'name',
        'description',
        'slug',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image',
        'image_urls',
        'url',
    ];

    protected $casts = [
        'image_urls' => 'array',
    ];

    public function postcategory()
    {
        return $this->belongsTo(PostCategory::class);
    }
}
