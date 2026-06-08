<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use App\Traits\HasSlugGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Post extends Model
{
    use CrudTrait, HasSlugGenerator;

    protected $fillable = [
        // Xóa 'postcategory_id' khỏi đây
        'icon',
        'post_type',
        'section_type',
        'status',
        'is_featured',
        'is_active',
        'sort_order',
        'view_count',
        'published_at',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'document_file_id',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'view_count' => 'integer',
        'published_at' => 'datetime',
    ];


    protected static function boot()
    {
        parent::boot();

        // Eager load translations globally
        static::addGlobalScope('withTranslations', function ($builder) {
            $builder->with('translations');
        });
    }


    /**
     * Lấy image_urls từ translation hiện tại
     * 
     * @return array
     */
    public function getImageUrlsAttribute(): array
    {
        $currentLocale = app()->getLocale();
        $translation = $this->translations()->where('language', $currentLocale)->first();

        if ($translation && !empty($translation->image_urls)) {
            return is_array($translation->image_urls) ? $translation->image_urls : [];
        }

        return [];
    }

    public function handleTranslations(array $data)
    {
        $languages = array_keys(config('languages.supported'));

        foreach ($languages as $lang) {
            $translationData = [
                'title' => $data['title_' . $lang] ?? '',
                'slug' => Str::slug($data['title_' . $lang] ?? ''),
                'content' => $data['content_' . $lang] ?? '',
                'excerpt' => $data['excerpt_' . $lang] ?? '',
                'meta_title' => $data['meta_title_' . $lang] ?? '',
                'meta_description' => $data['meta_description_' . $lang] ?? '',
                'meta_keywords' => $data['meta_keywords_' . $lang] ?? '',
                'canonical_url' => $data['canonical_url_' . $lang] ?? '',
                'og_title' => $data['og_title_' . $lang] ?? '',
                'og_description' => $data['og_description_' . $lang] ?? '',
                'og_image' => $data['og_image_' . $lang] ?? '',
                'image_urls' => $this->processImageUrls($data['image_urls_' . $lang] ?? ''),
                'url' => $data['url_' . $lang] ?? '',
            ];

            if (!empty($translationData['title'])) {
                $this->translations()->updateOrCreate(
                    ['language' => $lang],
                    $translationData
                );
            }
        }
    }

    private function processImageUrls($imageUrls)
    {
        if (empty($imageUrls)) {
            return [];
        }

        if (is_string($imageUrls)) {
            return array_filter(array_map('trim', explode("\n", $imageUrls)));
        }

        return is_array($imageUrls) ? $imageUrls : [];
    }

    public function translations()
    {
        return $this->hasMany(PostTranslation::class);
    }

    public function translation()
    {
        return $this->hasOne(PostTranslation::class)->where('language', app()->getLocale());
    }

    // Thay đổi relationship này
    public function postcategories()
    {
        return $this->belongsToMany(PostCategory::class, 'post_postcategory', 'post_id', 'postcategory_id')
            ->withPivot('is_primary', 'sort_order')
            ->withTimestamps();
    }

    // Thêm relationship để lấy danh mục chính
    public function primaryCategory()
    {
        return $this->belongsToMany(PostCategory::class, 'post_postcategory', 'post_id', 'postcategory_id')
            ->wherePivot('is_primary', true)
            ->withPivot('is_primary', 'sort_order')
            ->withTimestamps();
    }

    // Giữ lại relationship cũ để backward compatibility (tạm thời)
    public function postcategory()
    {
        return $this->primaryCategory();
    }

    /**
     * Quan hệ với UploadedFile (document)
     */
    public function documentFile(): BelongsTo
    {
        return $this->belongsTo(UploadedFile::class, 'document_file_id');
    }

    public function getNameAttribute()
    {
        $currentLocale = app()->getLocale();
        $translation = $this->translations()
            ->where('language', $currentLocale)
            ->first();

        return $translation ? $translation->title : 'N/A';
    }

    public function getTitleAttribute()
    {
        return $this->getNameAttribute();
    }

    // ========================================
    // UI DISPLAY METHODS (Backpack Columns)
    // ========================================

    /**
     * Lấy tiêu đề theo locale hiện tại để hiển thị
     * 
     * @return string
     */
    public function getTitleDisplay(): string
    {
        $currentLocale = app()->getLocale();
        $translation = $this->translations()->where('language', $currentLocale)->first();
        return $translation ? $translation->title : 'N/A';
    }

    /**
     * Lấy tên các danh mục để hiển thị
     * 
     * @return string
     */
    public function getCategoryNamesDisplay(): string
    {
        $categories = $this->postcategories;
        if ($categories->count() > 0) {
            $currentLocale = app()->getLocale();
            $categoryNames = $categories->map(function ($category) use ($currentLocale) {
                $translation = $category->translations()->where('language', $currentLocale)->first();
                return $translation ? $translation->name : $category->slug;
            })->toArray();
            return implode(', ', $categoryNames);
        }
        return '-';
    }

    /**
     * Lấy HTML hiển thị hình ảnh thumbnail (50px)
     * 
     * @return string
     */
    public function getImageThumbnailHtml(): string
    {
        $imageUrls = $this->image_urls;
        if (!empty($imageUrls) && is_array($imageUrls)) {
            $firstImage = $imageUrls[0];
            return sprintf(
                '<img src="%s" style="max-width: 50px; max-height: 50px;" />',
                e($firstImage)
            );
        }
        return '-';
    }

    /**
     * Lấy HTML hiển thị hình ảnh lớn hơn (100px) cho Show
     * 
     * @return string
     */
    public function getImageMediumHtml(): string
    {
        $imageUrls = $this->image_urls;
        if (!empty($imageUrls) && is_array($imageUrls)) {
            $firstImage = $imageUrls[0];
            return sprintf(
                '<img src="%s" style="max-width: 100px; max-height: 100px;" />',
                e($firstImage)
            );
        }
        return '-';
    }

    /**
     * Lấy HTML hiển thị trạng thái với badge
     * 
     * @return string
     */
    public function getStatusBadgeHtml(): string
    {
        $statusLabels = [
            'draft' => '<span class="badge badge-secondary">Bản nháp</span>',
            'published' => '<span class="badge badge-success">Đã xuất bản</span>',
            'archived' => '<span class="badge badge-warning">Đã lưu trữ</span>',
        ];
        return $statusLabels[$this->status] ?? e($this->status);
    }

    /**
     * Lấy tiêu đề theo ngôn ngữ cụ thể
     * 
     * @param string $lang
     * @return string
     */
    public function getTranslationTitle(string $lang): string
    {
        $translation = $this->translations()->where('language', $lang)->first();
        return $translation ? $translation->title : '-';
    }

    /**
     * Lấy tóm tắt theo ngôn ngữ cụ thể (giới hạn 100 ký tự)
     * 
     * @param string $lang
     * @return string
     */
    public function getTranslationExcerpt(string $lang): string
    {
        $translation = $this->translations()->where('language', $lang)->first();
        return $translation ? \Illuminate\Support\Str::limit($translation->excerpt, 100) : '-';
    }

    /**
     * Lấy slug theo ngôn ngữ cụ thể
     * 
     * @param string $lang
     * @return string
     */
    public function getTranslationSlug(string $lang): string
    {
        $translation = $this->translations()->where('language', $lang)->first();
        return $translation ? $translation->slug : '-';
    }
}
