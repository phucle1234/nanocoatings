<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use App\Traits\HasSlugGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class ProductCategory extends Model
{
    use CrudTrait;
    use HasFactory, HasSlugGenerator;

    protected $fillable = [
        'parent_id',
        'code',
        'image',
        'image_urls',
        'icon',
        'is_active',
        'is_featured',
        'sort_order',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'image_urls' => 'array',
    ];


    /**
     * Override boot method để xử lý translations
     */
    protected static function boot()
    {
        parent::boot();

        // Eager load translations globally
        static::addGlobalScope('withTranslations', function ($builder) {
            $builder->with('translations');
        });

        // Sync image from image_urls on create/update
        static::creating(function ($model) {
            if ($model->isDirty('image_urls')) {
                $model->syncImageFromUrls();
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('image_urls')) {
                $model->syncImageFromUrls();
            }
        });
    }


    /**
     * Sync image from image_urls array
     */
    public function syncImageFromUrls()
    {
        if (!empty($this->image_urls) && is_array($this->image_urls)) {
            $this->image = $this->image_urls[0] ?? null;
        }
    }

    /**
     * Xử lý lưu translations từ các field đa ngôn ngữ
     */
    public function handleTranslations(array $data)
    {
        $languages = array_keys(config('languages.supported'));

        foreach ($languages as $lang) {
            $name = $data['name_' . $lang] ?? '';
            if ($name === '') {
                continue;
            }

            $slugInput = trim($data['slug_' . $lang] ?? '');
            $baseSlug = $slugInput !== '' ? Str::slug($slugInput) : Str::slug($name);

            $linkType = $data['link_type_' . $lang] ?? 'detail';
            if (!in_array($linkType, ['detail', 'youtube'], true)) {
                $linkType = 'detail';
            }

            $translationData = [
                'name' => $name,
                'description' => $data['description_' . $lang] ?? '',
                'slug' => $this->generateUniqueSlug($baseSlug, $lang),
                'meta_title' => $data['meta_title_' . $lang] ?? '',
                'meta_description' => $data['meta_description_' . $lang] ?? '',
                'image_urls' => $this->processImageUrls($data['image_urls_' . $lang] ?? ''),
                'link_type' => $linkType,
                'youtube_url' => $linkType === 'youtube' ? trim($data['youtube_url_' . $lang] ?? '') : null,
            ];

            $this->translations()->updateOrCreate(
                ['language' => $lang],
                $translationData
            );
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
    /**
     * Generate a unique slug for a given base string and language.
     */
    private function generateUniqueSlug(string $baseSlug, string $language): string
    {
        $slug = Str::slug($baseSlug) ?: 'category';
        $originalSlug = $slug;
        $counter = 1;

        while ($this->isSlugTaken($slug, $language)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if a slug is already taken for a given language (any other category).
     */
    private function isSlugTaken(string $slug, string $language): bool
    {
        return ProductCategoryTranslation::query()
            ->where('slug', $slug)
            ->where('language', $language)
            ->where('category_id', '!=', $this->id)
            ->exists();
    }

    /**
     * Quan hệ với danh mục cha
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    /**
     * Quan hệ với danh mục con
     */
    public function children(): HasMany
    {
        return $this->hasMany(ProductCategory::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Quan hệ với tất cả danh mục con (đệ quy)
     */
    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    /**
     * Quan hệ với sản phẩm (backward compatibility)
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    /**
     * Quan hệ many-to-many với sản phẩm
     */
    public function productsManyToMany()
    {
        return $this->belongsToMany(Product::class, 'product_product_category', 'product_category_id', 'product_id')
            ->withPivot('is_primary', 'sort_order')
            ->withTimestamps()
            ->orderBy('product_product_category.sort_order');
    }

    /**
     * Quan hệ với sản phẩm chính (primary category)
     */
    public function primaryProducts()
    {
        return $this->belongsToMany(Product::class, 'product_product_category', 'product_category_id', 'product_id')
            ->withPivot('is_primary', 'sort_order')
            ->withTimestamps()
            ->wherePivot('is_primary', true);
    }

    /**
     * Quan hệ với sản phẩm phụ (secondary category)
     */
    public function secondaryProducts()
    {
        return $this->belongsToMany(Product::class, 'product_product_category', 'product_category_id', 'product_id')
            ->withPivot('is_primary', 'sort_order')
            ->withTimestamps()
            ->wherePivot('is_primary', false)
            ->orderBy('product_product_category.sort_order');
    }

    /**
     * Lấy tất cả sản phẩm (pivot table)
     */
    public function getAllProducts()
    {
        return $this->productsManyToMany()->get();
    }

    /**
     * Đếm số sản phẩm (pivot table)
     */
    public function getProductsCount()
    {
        return $this->productsManyToMany()->count();
    }

    /**
     * Quan hệ với bản dịch
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ProductCategoryTranslation::class, 'category_id');
    }

    /**
     * Quan hệ với bản dịch theo ngôn ngữ hiện tại
     */
    public function translation(): HasOne
    {
        $locale = config('languages.default');
        return $this->hasOne(ProductCategoryTranslation::class, 'category_id')
            ->where('language', $locale);
    }

    /**
     * Scope để lấy danh mục đang hoạt động
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope để lấy danh mục nổi bật
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope để lấy danh mục gốc (không có parent)
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope để sắp xếp theo thứ tự
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope để load parent relationships cho getPath()
     */
    public function scopeWithParentPath($query, $depth = 5)
    {
        $with = [];
        for ($i = 1; $i <= $depth; $i++) {
            $with[] = str_repeat('parent.', $i - 1) . 'parent';
        }
        return $query->with($with);
    }

    /**
     * Lấy tên danh mục theo ngôn ngữ hiện tại
     */
    public function getNameAttribute()
    {
        $translation = $this->translation;
        return $translation ? $translation->name : $this->code;
    }

    /**
     * Lấy mô tả danh mục theo ngôn ngữ hiện tại
     */
    public function getDescriptionAttribute()
    {
        $translation = $this->translation;
        return $translation ? $translation->description : null;
    }

    /**
     * Lấy slug theo ngôn ngữ hiện tại
     */
    public function getLocalizedSlugAttribute()
    {
        $translation = $this->translation;
        return $translation ? $translation->slug : null;
    }

    /**
     * Kiểm tra xem danh mục có phải là danh mục gốc không
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Kiểm tra xem danh mục có danh mục con không (tối ưu)
     */
    public function hasChildren(): bool
    {
        // Sử dụng relationship đã load nếu có
        if ($this->relationLoaded('children')) {
            return $this->children->count() > 0;
        }

        return $this->children()->count() > 0;
    }

    /**
     * Get the category path (breadcrumb).
     *
     * @return array
     */
    public function getPath(): array
    {
        // Using a recursive query to get all parents in a single query
        $path = collect([$this]);
        $current = $this;

        // Load all necessary parent relationships
        $parents = static::whereIn('id', function ($query) use ($current) {
            $query->select('parent_id')
                ->from('product_categories')
                ->where('id', $current->id)
                ->whereNotNull('parent_id');
        })->with('translations')->get()->keyBy('id');

        // Build the path from the loaded parents
        while ($current->parent_id && isset($parents[$current->parent_id])) {
            $current = $parents[$current->parent_id];
            $path->prepend($current);
        }

        return $path->toArray();
    }
    public function npps()
    {
        return $this->belongsToMany(
            \App\Models\User::class,
            'npp_product_categories',
            'category_id',
            'user_id'
        )->withTimestamps();
    }
    /**
     * Scope để eager load tất cả relationships cần thiết
     */
    public function scopeWithAll($query)
    {
        return $query->with([
            'parent.parent.parent.parent.parent.translations', // Load sâu 5 cấp parent
            'children.translations',
            'translations',
            'products.translations'
        ]);
    }

    /**
     * Scope để eager load relationships cơ bản
     */
    public function scopeWithBasic($query)
    {
        return $query->with([
            'parent.parent.parent', // Load sâu 3 cấp parent
            'children',
            'translation'
        ]);
    }

    /**
     * Scope để eager load relationships cho frontend
     */
    public function scopeForFrontend($query)
    {
        return $query->with([
            'parent.parent.translation', // Load sâu 2 cấp parent
            'children' => function ($query) {
                $query->with('translation')
                    ->where('is_active', true)
                    ->orderBy('sort_order');
            },
            'translation',
            'products' => function ($query) {
                $query->with('translation')
                    ->where('is_active', true)
                    ->orderBy('sort_order');
            }
        ]);
    }
    public function getTranslatedName()
    {
        $translations = $this->translations;
        if ($translations->isNotEmpty()) {
            $names = $translations->pluck('name', 'language')->toArray();
            return implode(' | ', array_filter($names));
        }
        return $this->code;
    }

    public function getParentTranslatedName()
    {
        if ($this->parent) {
            $parentTranslations = $this->parent->translations;
            if ($parentTranslations->isNotEmpty()) {
                $names = $parentTranslations->pluck('name', 'language')->toArray();
                return implode(' | ', array_filter($names));
            }
            return $this->parent->code;
        }
        return '-';
    }
    public function getTranslatedSlug()
    {
        $translations = $this->translations;
        if ($translations->isNotEmpty()) {
            $slugs = $translations->pluck('slug', 'language')->toArray();
            return implode(' | ', array_filter($slugs));
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
        $translation = $this->translations()->where('language', app()->getLocale())->first();
        $imageUrls = $translation?->image_urls ?? $this->image_urls;

        if (!empty($imageUrls) && is_array($imageUrls)) {
            $firstImage = $imageUrls[0];
            return sprintf(
                '<img src="%s" style="max-width: 50px; max-height: 50px; object-fit: cover; border-radius: 4px;">',
                e($firstImage)
            );
        }

        if ($this->image) {
            return sprintf(
                '<img src="%s" style="max-width: 50px; max-height: 50px; object-fit: cover; border-radius: 4px;">',
                e($this->image)
            );
        }

        return '<span class="text-muted">Chưa có hình</span>';
    }

    /**
     * Lấy HTML hiển thị hình ảnh lớn (300px) cho Show
     * 
     * @return string
     */
    public function getImageLargeHtml(): string
    {
        $translation = $this->translations()->where('language', app()->getLocale())->first();
        $imageUrls = $translation?->image_urls ?? $this->image_urls;

        if (!empty($imageUrls) && is_array($imageUrls)) {
            $firstImage = $imageUrls[0];
            return sprintf(
                '<img src="%s" style="max-width: 300px; max-height: 300px; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">',
                e($firstImage)
            );
        }

        if ($this->image) {
            return sprintf(
                '<img src="%s" style="max-width: 300px; max-height: 300px; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">',
                e($this->image)
            );
        }

        return '<span class="text-muted">Chưa có hình ảnh</span>';
    }
}
