<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostTranslation extends Model
{
    protected $table = 'post_translations';

    protected $fillable = [
        'post_id',
        'language',
        'title',
        'slug',
        'content',
        'excerpt',
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

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
