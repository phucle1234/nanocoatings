<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use App\Traits\HasSlugGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class PostCategory extends Model
{
    use CrudTrait, HasSlugGenerator;

    protected $table = 'postcategories';

    protected $fillable = [
        'parent_id',
        'image',
        'icon',
        'is_active',
        'is_featured',
        'is_banner',
        'sort_order',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_banner' => 'boolean',
        'sort_order' => 'integer',
    ];



    protected static function boot()
    {
        parent::boot();

        // Auto eager load translations khi query
        static::addGlobalScope('withTranslations', function ($builder) {
            $builder->with('translations');
        });

        // Cascade delete: xóa cha → xóa con + bài viết
        static::deleting(function (PostCategory $category) {
            // 1. Đệ quy xóa các danh mục con
            foreach ($category->children as $child) {
                $child->delete(); // gọi lại event deleting cho mỗi con
            }

            // 2. Xóa bài viết thuộc danh mục này (chỉ bài viết primary)
            $postIds = $category->posts()
                ->wherePivot('is_primary', true)
                ->pluck('posts.id')
                ->toArray();

            if (!empty($postIds)) {
                // Xóa translations
                \App\Models\PostTranslation::whereIn('post_id', $postIds)->delete();
                // Xóa pivot
                \Illuminate\Support\Facades\DB::table('post_postcategory')
                    ->whereIn('post_id', $postIds)->delete();
                // Xóa file đính kèm
                $fileIds = \App\Models\Post::whereIn('id', $postIds)
                    ->pluck('document_file_id')->filter()->toArray();
                \App\Models\Post::whereIn('id', $postIds)->delete();
                if (!empty($fileIds)) {
                    \App\Models\UploadedFile::whereIn('id', $fileIds)->delete();
                }
            }

            // 3. Xóa translations của chính category này
            $category->translations()->delete();

            // 4. Xóa pivot post_postcategory còn lại (bài viết non-primary)
            \Illuminate\Support\Facades\DB::table('post_postcategory')
                ->where('postcategory_id', $category->id)->delete();
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
                'name' => $data['name_' . $lang] ?? '',
                'description' => $data['description_' . $lang] ?? '',
                // Ưu tiên dùng slug được chỉ định, nếu không có thì tự động tạo từ name
                'slug' => $data['slug_' . $lang] ?? Str::slug($data['name_' . $lang] ?? ''),
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

            if (!empty($translationData['name'])) {
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
            return null;
        }

        // Nếu là string (từ textarea), chuyển thành array
        if (is_string($imageUrls)) {
            $urls = array_filter(array_map('trim', explode("\n", $imageUrls)));
            return !empty($urls) ? $urls : null;
        }

        // Nếu đã là array, trả về như cũ
        return is_array($imageUrls) ? $imageUrls : null;
    }

    public function translations()
    {
        return $this->hasMany(PostCategoryTranslation::class, 'postcategory_id');
    }

    public function translation()
    {
        return $this->hasOne(PostCategoryTranslation::class, 'postcategory_id')->where('language', app()->getLocale());
    }

    public function parent()
    {
        return $this->belongsTo(PostCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(PostCategory::class, 'parent_id');
    }

    // Thay đổi relationship này
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_postcategory', 'postcategory_id', 'post_id')
            ->withPivot('is_primary', 'sort_order')
            ->withTimestamps();
    }

    public function getNameAttribute()
    {
        $currentLocale = app()->getLocale();
        $translation = $this->translations()
            ->where('language', $currentLocale)
            ->first();

        return $translation ? $translation->name : 'N/A';
    }

    /**
     * Hiển thị link quản lý bài viết thuộc danh mục hiện tại trên backend Phần danh mục tin tức.
     */
    public function getManagePostsLinkHtml(): string
    {
        $postCount = $this->posts()->count();

        // Backward compatibility: một số bài viết vẫn lưu postcategory_id trực tiếp
        if ($postCount === 0) {
            $postCount = Post::where('postcategory_id', $this->id)->count();
        }

        $viewUrl = backpack_url('post?category_id=' . $this->id);
        $createUrl = backpack_url('post/create?category_id=' . $this->id);

        $viewLabel = sprintf('<i class="la la-newspaper"></i> Xem (%d)', $postCount);
        $createLabel = '<i class="la la-plus"></i> Tạo tin mới';

        // Nút "Xem"
        if ($postCount === 0) {
            $viewButton = sprintf(
                '<a href="%s" class="btn btn-sm btn-outline-secondary disabled" aria-disabled="true">%s</a>',
                e($viewUrl),
                $viewLabel
            );
        } else {
            $viewButton = sprintf(
                '<a href="%s" class="btn btn-sm btn-outline-primary" target="_blank">%s</a>',
                e($viewUrl),
                $viewLabel
            );
        }

        // ✅ Nút "Tạo tin mới" - luôn hiện
        $createButton = sprintf(
            '<a href="%s" class="btn btn-sm btn-success" target="_blank">%s</a>',
            e($createUrl),
            $createLabel
        );

        // ✅ Trả về cả 2 nút
        return $viewButton . ' ' . $createButton;
    }
}
