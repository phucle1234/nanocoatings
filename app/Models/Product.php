<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Support\Facades\Log;

class Product extends Model
{
    use HasFactory, CrudTrait;

    protected $fillable = [
        'category_id', // Giữ lại để backward compatibility
        'code', // SKU gốc (có thể trùng nhau)
        'sku', // SKU tự tạo từ ID (duy nhất)
        'price',
        'sale_price',
        'stock_quantity',
        'min_stock_quantity',
        'is_active',
        'is_featured',
        'is_new',
        'is_bestseller',
        'view_count',
        'sort_order',
        'image_urls',
        'document_file_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'min_stock_quantity' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_new' => 'boolean',
        'is_bestseller' => 'boolean',
        'view_count' => 'integer',
        'sort_order' => 'integer',
        'image_urls' => 'array',
    ];

    /**
     * Quan hệ với danh mục sản phẩm chính (backward compatibility)
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * Quan hệ many-to-many với danh mục sản phẩm
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class, 'product_product_category', 'product_id', 'product_category_id')
            ->withPivot('is_primary', 'sort_order')
            ->withTimestamps()
            ->orderBy('product_product_category.sort_order');
    }

    /**
     * Quan hệ với danh mục chính
     */
    public function primaryCategory(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class, 'product_product_category', 'product_id', 'product_category_id')
            ->withPivot('is_primary', 'sort_order')
            ->withTimestamps()
            ->wherePivot('is_primary', true);
    }

    /**
     * Quan hệ với danh mục phụ
     */
    public function secondaryCategories(): BelongsToMany
    {
        return $this->belongsToMany(ProductCategory::class, 'product_product_category', 'product_id', 'product_category_id')
            ->withPivot('is_primary', 'sort_order')
            ->withTimestamps()
            ->wherePivot('is_primary', false)
            ->orderBy('product_product_category.sort_order');
    }

    /**
     * Quan hệ với bản dịch (lazy loading để tối ưu performance)
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class, 'product_id');
    }

    /**
     * Quan hệ với bản dịch theo ngôn ngữ hiện tại (lazy loading)
     */
    public function translation(): HasOne
    {
        $locale = config('languages.default');
        return $this->hasOne(ProductTranslation::class, 'product_id')
            ->where('language', $locale);
    }

    /**
     * Quan hệ với thuộc tính sản phẩm (EAV) - lazy loading (tạm comment để tối ưu)
     */
    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductAttributeValue::class,
            'product_attribute_product',
            'product_id',
            'attribute_value_id'
        )->withPivot('show_detail');
    }

    /**
     * Quan hệ với Vehicle Fitments (thay thế cho EAV pattern)
     */
    public function vehicleFitments(): HasMany
    {
        return $this->hasMany(ProductVehicleFitment::class);
    }

    /**
     * Quan hệ với UploadedFile (document)
     */
    public function documentFile(): BelongsTo
    {
        return $this->belongsTo(UploadedFile::class, 'document_file_id');
    }

    /**
     * Get unique manufacturers for this product
     * NOTE: Đổi tên method để không conflict với accessor
     */
    public function getVehicleManufacturers()
    {
        return $this->vehicleFitments()
            ->select('manufacturer')
            ->distinct()
            ->whereNotNull('manufacturer')
            ->orderBy('manufacturer')
            ->pluck('manufacturer')
            ->toArray();
    }

    /**
     * Get unique models for this product
     * NOTE: Đổi tên method để không conflict với accessor
     */
    public function getVehicleModels()
    {
        return $this->vehicleFitments()
            ->select('model')
            ->distinct()
            ->whereNotNull('model')
            ->orderBy('model')
            ->pluck('model')
            ->toArray();
    }

    /**
     * Get unique years for this product
     * NOTE: Đổi tên method để không conflict với accessor
     */
    public function getVehicleYears()
    {
        return $this->vehicleFitments()
            ->select('year')
            ->distinct()
            ->whereNotNull('year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
    }
    /**
     * Scope để lấy sản phẩm nổi bật
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope để lấy sản phẩm mới
     */
    public function scopeNew($query)
    {
        return $query->where('is_new', true);
    }

    /**
     * Scope để lấy sản phẩm bán chạy
     */
    public function scopeBestseller($query)
    {
        return $query->where('is_bestseller', true);
    }

    /**
     * Scope để lấy sản phẩm còn hàng
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    /**
     * Scope để lấy sản phẩm hết hàng
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('stock_quantity', '<=', 0);
    }

    /**
     * Scope để lấy sản phẩm có giá khuyến mãi
     */
    public function scopeOnSale($query)
    {
        return $query->whereNotNull('sale_price')
            ->where('sale_price', '>', 0)
            ->whereColumn('sale_price', '<', 'price');
    }

    /**
     * Scope để sắp xếp theo thứ tự
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope để sắp xếp theo giá
     */
    public function scopeOrderByPrice($query, $direction = 'asc')
    {
        return $query->orderBy('price', $direction);
    }

    /**
     * Scope để sắp xếp theo lượt xem
     */
    public function scopeOrderByViews($query, $direction = 'desc')
    {
        return $query->orderBy('view_count', $direction);
    }

    /**
     * Scope để eager load tất cả relationships cần thiết
     */
    public function scopeWithAll($query)
    {
        return $query->with([
            'category.translations',
            'translations',
            'attributeValues.attribute.translations',
            'attributeValues.translations'
        ]);
    }

    /**
     * Scope để eager load relationships cơ bản
     */
    public function scopeWithBasic($query)
    {
        return $query->with([
            'category',
            'translation',
            'attributeValues.attribute'
        ]);
    }

    /**
     * Scope để eager load relationships cho frontend
     */
    public function scopeForFrontend($query)
    {
        return $query->with([
            'category.translation',
            'translation',
            'attributeValues' => function ($query) {
                $query->with(['attribute.translation', 'translation'])
                    ->where('is_active', true)
                    ->orderBy('sort_order');
            }
        ]);
    }

    /**
     * Lấy tên sản phẩm theo ngôn ngữ hiện tại
     * TẠM THỜI COMMENT ĐỂ TRÁNH MEMORY ISSUES
     */
    // public function getNameAttribute()
    // {
    //     $translation = $this->translation;
    //     return $translation ? $translation->name : $this->sku;
    // }

    /**
     * Lấy mô tả sản phẩm theo ngôn ngữ hiện tại
     * TẠM THỜI COMMENT ĐỂ TRÁNH MEMORY ISSUES
     */
    // public function getDescriptionAttribute()
    // {
    //     $translation = $this->translation;
    //     return $translation ? $translation->description : null;
    // }

    /**
     * Lấy slug theo ngôn ngữ hiện tại
     * TẠM THỜI COMMENT ĐỂ TRÁNH MEMORY ISSUES
     */
    // public function getLocalizedSlugAttribute()
    // {
    //     $translation = $this->translation;
    //     return $translation ? $translation->slug : $this->sku;
    // }

    /**
     * Lấy giá cuối cùng (sale_price nếu có, không thì price)
     */
    public function getFinalPriceAttribute()
    {
        return $this->sale_price && $this->sale_price > 0 ? $this->sale_price : $this->price;
    }

    /**
     * Lấy phần trăm giảm giá
     */
    public function getDiscountPercentageAttribute()
    {
        if (!$this->sale_price || $this->sale_price <= 0) {
            return 0;
        }

        return round((($this->price - $this->sale_price) / $this->price) * 100);
    }

    /**
     * Kiểm tra xem sản phẩm có đang giảm giá không
     */
    public function isOnSale(): bool
    {
        return $this->sale_price && $this->sale_price > 0 && $this->sale_price < $this->price;
    }

    /**
     * Kiểm tra xem sản phẩm còn hàng không
     */
    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    /**
     * Kiểm tra xem sản phẩm sắp hết hàng không
     */
    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->min_stock_quantity;
    }

    /**
     * Tăng lượt xem
     */
    public function incrementViews(): void
    {
        $this->increment('view_count');
    }

    /**
     * Lấy hình ảnh chính
     */
    public function getMainImageAttribute()
    {
        $images = $this->image_urls;
        return is_array($images) && count($images) > 0 ? $images[0] : null;
    }

    /**
     * Lấy tất cả hình ảnh
     */
    public function getAllImagesAttribute()
    {
        return $this->image_urls ?: [];
    }

    /**
     * Lấy giá trị thuộc tính theo mã thuộc tính (tạm comment để tối ưu)
     */
    // public function getAttributeValue($attributeCode)
    // {
    //     // Kiểm tra xem model đã tồn tại chưa
    //     if (!$this->exists) {
    //         return null;
    //     }

    //     $productId = $this->getKey(); // Sử dụng getKey() thay vì $this->id

    //     // Sử dụng query trực tiếp
    //     $attributeValue = ProductAttributeValue::whereHas('products', function ($query) use ($productId) {
    //         $query->where('product_id', $productId);
    //     })
    //         ->whereHas('attribute', function ($query) use ($attributeCode) {
    //             $query->where('code', $attributeCode);
    //         })
    //         ->first();

    //     return $attributeValue ? $attributeValue->localized_value : null;
    // }

    /**
     * Các method EAV đã được comment để tối ưu performance cho admin panel
     * Có thể uncomment khi cần sử dụng EAV features
     */

    /**
     * Xử lý lưu translations từ các field đa ngôn ngữ
     */
    public function handleTranslations(array $data)
    {
        // Kiểm tra xem model đã tồn tại chưa
        if (!$this->exists) {
            return;
        }

        $productId = $this->getKey();
        $languages = \App\Helpers\LanguageHelper::getLanguageCodes();
        $defaultLanguage = \App\Helpers\LanguageHelper::getDefaultLanguage();

        // Lấy data từ ngôn ngữ mặc định để làm fallback
        $defaultName = $data["name_{$defaultLanguage}"] ?? '';
        $defaultDescription = $data["description_{$defaultLanguage}"] ?? '';

        foreach ($languages as $lang) {
            $name = $data["name_{$lang}"] ?? '';
            $description = $data["description_{$lang}"] ?? '';
            $shortDescription = $data["short_description_{$lang}"] ?? '';
            $features = $data["features_{$lang}"] ?? '';
            $metaTitle = $data["meta_title_{$lang}"] ?? '';
            $metaDescription = $data["meta_description_{$lang}"] ?? '';
            $metaKeywords = $data["meta_keywords_{$lang}"] ?? '';
            $imageUrls = $this->processTranslationImageUrls($data["image_urls_{$lang}"] ?? '');

            // Nếu không có name cho ngôn ngữ này, dùng name từ ngôn ngữ mặc định
            if (empty($name) && !empty($defaultName)) {
                $name = $defaultName;
                $description = $description ?: $defaultDescription; // Cũng copy description nếu trống
            }

            // Tạo translation nếu có tên (hoặc đã fallback từ default language)
            if (!empty($name)) {
                try {
                    // Tạo slug từ tên nếu chưa có
                    $slug = $this->generateSlug($name, $lang);

                    $this->translations()->updateOrCreate(
                        ['language' => $lang],
                        [
                            'name' => $name,
                            'description' => $description,
                            'short_description' => $shortDescription,
                            'features' => $features,
                            'meta_title' => $metaTitle,
                            'meta_description' => $metaDescription,
                            'meta_keywords' => $metaKeywords,
                            'slug' => $slug,
                            'image_urls' => $imageUrls,
                        ]
                    );
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Error saving product translation for language: ' . $lang, [
                        'error' => $e->getMessage(),
                        'product_id' => $productId,
                        'data' => [
                            'name' => $name,
                            'description' => $description,
                        ]
                    ]);
                }
            }
        }
    }

    /**
     * Chuẩn hoá danh sách URL ảnh từ field CRUD (textarea / nhiều dòng).
     */
    protected function processTranslationImageUrls($imageUrls): array
    {
        if ($imageUrls === null || $imageUrls === '') {
            return [];
        }

        if (is_string($imageUrls)) {
            return array_values(array_filter(array_map('trim', explode("\n", $imageUrls))));
        }

        return is_array($imageUrls) ? $imageUrls : [];
    }

    /**
     * Tạo slug từ tên sản phẩm
     * Thêm language code để tránh conflict khi các translation có tên giống nhau
     */
    protected function generateSlug($name, $language)
    {
        $slug = \Illuminate\Support\Str::slug($name);

        // Thêm language code để tránh duplicate slug giữa các ngôn ngữ
        // Ví dụ: "toyota-vios" (vi) và "toyota-vios" (en) → "toyota-vios-vi" và "toyota-vios-en"
        $slugWithLang = $slug . '-' . $language;

        // Đảm bảo slug là duy nhất trong cùng ngôn ngữ
        $counter = 1;
        $originalSlug = $slugWithLang;

        while ($this->translations()
            ->where('slug', $slugWithLang)
            ->where('product_id', '!=', $this->exists ? $this->getKey() : 0)
            ->exists()
        ) {
            $slugWithLang = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slugWithLang;
    }


    /**
     * Lấy danh mục chính từ pivot table
     */
    public function getPrimaryCategoryFromPivot()
    {
        return $this->primaryCategory()->first();
    }


    /**
     * Lấy danh mục chính (ưu tiên pivot, fallback category_id cũ)
     */
    public function getMainCategory()
    {
        // Thử lấy từ pivot table trước
        $primaryCategory = $this->getPrimaryCategoryFromPivot();
        if ($primaryCategory) {
            return $primaryCategory;
        }

        // Fallback về category_id cũ
        if ($this->category_id) {
            return ProductCategory::find($this->category_id);
        }

        return null;
    }

    /**
     * Lấy tất cả danh mục (ưu tiên pivot table, fallback category_id cũ)
     */
    public function getAllCategories()
    {
        // Ưu tiên pivot table
        $categories = $this->categories()->get();
        if ($categories->isNotEmpty()) {
            return $categories;
        }

        // Fallback về category_id cũ
        if ($this->category_id) {
            return collect([ProductCategory::find($this->category_id)]);
        }

        return collect();
    }

    /**
     * Kiểm tra sản phẩm có thuộc danh mục không
     */
    public function belongsToCategory($categoryId)
    {
        // Kiểm tra trong pivot table
        if ($this->categories()->where('product_categories.id', $categoryId)->exists()) {
            return true;
        }

        // Fallback về category_id cũ
        return $this->category_id == $categoryId;
    }

    /**
     * Sync categories với pivot table
     */
    public function syncCategories(array $categoryIds, $primaryCategoryId = null)
    {
        // Xóa tất cả relationships cũ
        $this->categories()->detach();

        if (empty($categoryIds)) {
            return;
        }

        // Đảm bảo có primary category
        if (!$primaryCategoryId && !empty($categoryIds)) {
            $primaryCategoryId = $categoryIds[0];
        }

        // Attach categories với pivot data
        $syncData = [];
        foreach ($categoryIds as $index => $categoryId) {
            $syncData[$categoryId] = [
                'is_primary' => $categoryId == $primaryCategoryId,
                'sort_order' => $index,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $this->categories()->attach($syncData);

        // Cập nhật category_id cũ để backward compatibility
        $this->update(['category_id' => $primaryCategoryId]);
    }

    /**
     * Lấy category IDs từ pivot table
     */
    public function getCategoryIdsFromPivot()
    {
        return $this->categories()->pluck('product_categories.id')->toArray();
    }




    /**
     * Boot method để clear cache khi model được update
     */
    protected static function boot()
    {
        parent::boot();

        // Tự động tạo SKU từ ID khi tạo sản phẩm mới
        static::creating(function ($product) {
            // Nếu chưa có SKU, tạo tạm thời
            if (empty($product->sku)) {
                $product->sku = 'TEMP-' . time() . '-' . rand(1000, 9999);
            }
        });

        // Cập nhật SKU từ ID sau khi tạo
        static::created(function ($product) {
            if (strpos($product->sku, 'TEMP-') === 0) {
                $product->sku = 'PROD-' . $product->id;
                $product->saveQuietly(); // Dùng saveQuietly để tránh trigger events
            }
        });

        // Clear cache khi product được update (tạm comment để tối ưu)
        // static::updated(function ($product) {
        //     $product->clearAttributesCache();
        // });

        // Clear cache khi product được deleted (tạm comment để tối ưu)
        // static::deleted(function ($product) {
        //     $product->clearAttributesCache();
        // });
    }

    // ========================================
    // UI DISPLAY METHODS (Backpack Columns)
    // ========================================

    /**
     * Lấy tên các danh mục bằng tiếng Việt để hiển thị
     * 
     * @return string
     */
    public function getCategoryNamesVi(): string
    {
        try {
            $categories = $this->categories()->with('translations')->get();
            $categoryNames = [];

            foreach ($categories as $category) {
                $translation = $category->translations()->where('language', 'vi')->first();
                $categoryNames[] = $translation ? $translation->name : $category->code;
            }

            return empty($categoryNames) ? 'Chưa phân loại' : implode(', ', $categoryNames);
        } catch (\Exception $e) {
            Log::error('Error getting category names: ' . $e->getMessage());
            return 'Chưa phân loại';
        }
    }

    /**
     * Lấy tên sản phẩm tiếng Việt để hiển thị trong admin
     */
    public function getNameVi(): string
    {
        try {
            $translation = $this->relationLoaded('translations')
                ? $this->translations->firstWhere('language', 'vi')
                : $this->translations()->where('language', 'vi')->first();

            if ($translation && !empty($translation->name)) {
                return $translation->name;
            }

            return $this->sku ?? 'Chưa có tên';
        } catch (\Exception $e) {
            Log::error('Error getting product name (vi): ' . $e->getMessage());
            return $this->sku ?? 'Chưa có tên';
        }
    }
    public function getAttributeValueManufacturer(): string
    {
        return $this->getAttributeValueByCode('manufacturer');
    }

    public function getAttributeValueModel(): string
    {
        return $this->getAttributeValueByCode('model');
    }

    /**
     * Lấy giá trị attribute theo code (dùng chung cho nhiều cột)
     */
    protected function getAttributeValueByCode(string $attributeCode): string
    {
        try {
            $locale = app()->getLocale();

            $attributeValue = $this->attributeValues()
                ->whereHas('attribute', function ($query) use ($attributeCode) {
                    $query->where('code', $attributeCode);
                })
                ->with(['translation' => function ($query) use ($locale) {
                    $query->where('language', $locale);
                }])
                ->first();

            if (!$attributeValue) {
                return '';
            }

            // Sử dụng accessor localized_value hoặc lấy trực tiếp từ translation
            return $attributeValue->localized_value ?? $attributeValue->value ?? '';
        } catch (\Exception $e) {
            Log::error('Error fetching attribute ' . $attributeCode . ': ' . $e->getMessage());
            return '';
        }
    }
    /**
     * Lấy ngày tạo đã format
     * 
     * @return string
     */
    public function getFormattedCreatedAt(): string
    {
        return $this->created_at
            ? $this->created_at->format('d/m/Y H:i')
            : 'N/A';
    }

    /**
     * Lấy HTML hiển thị hình ảnh
     * 
     * @return string
     */
    public function getImageUrlsHtml(): string
    {
        $images = $this->image_urls;

        if (!is_array($images) || count($images) === 0) {
            return '<span class="text-muted">Chưa có hình ảnh</span>';
        }

        $html = '';
        foreach ($images as $image) {
            $html .= sprintf(
                '<img src="%s" style="max-width: 150px; max-height: 150px; object-fit: cover; border-radius: 8px; margin: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">',
                e($image)
            );
        }

        return $html;
    }

    /**
     * Lấy mô tả sản phẩm
     * 
     * @return string
     */
    public function getDescriptionDisplay(): string
    {
        $translation = $this->translation;
        return $translation ? $translation->description : 'Chưa có mô tả';
    }

    /**
     * Lấy mô tả ngắn
     * 
     * @return string
     */
    public function getShortDescriptionDisplay(): string
    {
        $translation = $this->translation;
        return $translation ? $translation->short_description : 'Chưa có mô tả ngắn';
    }

    /**
     * Lấy tính năng nổi bật
     * 
     * @return string
     */
    public function getOutstandingFeaturesDisplay(): string
    {
        $translation = $this->translation;
        return $translation ? $translation->outstanding_features : 'Chưa có tính năng nổi bật';
    }

    /**
     * Lấy meta title
     * 
     * @return string
     */
    public function getMetaTitleDisplay(): string
    {
        $translation = $this->translation;
        return $translation ? $translation->meta_title : 'Chưa có meta title';
    }

    /**
     * Lấy meta description
     * 
     * @return string
     */
    public function getMetaDescriptionDisplay(): string
    {
        $translation = $this->translation;
        return $translation ? $translation->meta_description : 'Chưa có meta description';
    }

    // ========================================
    // DASHBOARD STATISTICS METHODS
    // ========================================

    /**
     * Lấy tổng số sản phẩm
     */
    public static function getTotalCount()
    {
        return self::count();
    }

    /**
     * Lấy số sản phẩm hoạt động
     */
    public static function getActiveCount()
    {
        return self::where('is_active', true)->count();
    }

    /**
     * Lấy số sản phẩm nổi bật
     */
    public static function getFeaturedCount()
    {
        return self::where('is_featured', true)->count();
    }

    /**
     * Lấy số sản phẩm mới
     */
    public static function getNewCount()
    {
        return self::where('is_new', true)->count();
    }

    /**
     * Lấy số sản phẩm bán chạy
     */
    public static function getBestsellerCount()
    {
        return self::where('is_bestseller', true)->count();
    }

    /**
     * Lấy số sản phẩm sắp hết hàng
     */
    public static function getLowStockCount()
    {
        return self::whereColumn('stock_quantity', '<=', 'min_stock_quantity')->count();
    }

    /**
     * Lấy số sản phẩm được tạo trong ngày
     */
    public static function getTodayCount()
    {
        return self::whereDate('created_at', today())->count();
    }

    /**
     * Lấy số sản phẩm được tạo trong tuần
     */
    public static function getThisWeekCount()
    {
        return self::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
    }

    /**
     * Lấy số sản phẩm được tạo trong tháng
     */
    public static function getThisMonthCount()
    {
        return self::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)->count();
    }

    /**
     * Lấy thống kê sản phẩm theo thời gian
     */
    public static function getStatsByPeriod($period = 'month')
    {
        $data = [];

        switch ($period) {
            case 'day':
                for ($i = 23; $i >= 0; $i--) {
                    $date = now()->subHours($i);
                    $count = self::whereBetween('created_at', [
                        $date->copy()->startOfHour(),
                        $date->copy()->endOfHour()
                    ])->count();
                    $data[] = [
                        'label' => $date->format('H:i'),
                        'value' => $count
                    ];
                }
                break;

            case 'week':
                for ($i = 6; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $count = self::whereDate('created_at', $date)->count();
                    $data[] = [
                        'label' => $date->format('d/m'),
                        'value' => $count
                    ];
                }
                break;

            case 'month':
                for ($i = 29; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $count = self::whereDate('created_at', $date)->count();
                    $data[] = [
                        'label' => $date->format('d/m'),
                        'value' => $count
                    ];
                }
                break;

            case 'year':
                for ($i = 11; $i >= 0; $i--) {
                    $date = now()->subMonths($i);
                    $count = self::whereMonth('created_at', $date->month)
                        ->whereYear('created_at', $date->year)->count();
                    $data[] = [
                        'label' => $date->format('M Y'),
                        'value' => $count
                    ];
                }
                break;
        }

        return $data;
    }

    /**
     * Lấy top sản phẩm có lượt xem cao nhất
     */
    public static function getTopViewedProducts($limit = 10)
    {
        return self::orderBy('view_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Lấy sản phẩm có giá cao nhất
     */
    public static function getMostExpensiveProducts($limit = 10)
    {
        return self::orderBy('price', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Lấy sản phẩm có giá thấp nhất
     */
    public static function getCheapestProducts($limit = 10)
    {
        return self::where('price', '>', 0)
            ->orderBy('price', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Lấy specifications từ attributes có show_detail = 'Y'
     * 
     * @param string $locale
     * @return array
     */
    public function getSpecificationsFromAttributes($locale = 'vi'): array
    {
        $specifications = [];

        // Lấy các attribute values có show_detail = 'Y'
        $attributeValues = $this->attributeValues()
            ->wherePivot('show_detail', 'Y')
            ->with(['attribute.translations' => function ($q) use ($locale) {
                $q->where('language', $locale);
            }, 'translations' => function ($q) use ($locale) {
                $q->where('language', $locale);
            }])
            ->get();

        foreach ($attributeValues as $attributeValue) {
            $attribute = $attributeValue->attribute;
            $attributeTranslation = $attribute->translations->where('language', $locale)->first();
            $valueTranslation = $attributeValue->translations->where('language', $locale)->first();

            $attributeName = $attributeTranslation ? $attributeTranslation->name : $attribute->code;
            $valueName = $valueTranslation ? $valueTranslation->value : $attributeValue->value;

            if (!empty($attributeName) && !empty($valueName)) {
                $specifications[$attributeName] = $valueName;
            }
        }

        return $specifications;
    }

    /**
     * Có giá bán hiển thị (không thuộc trường hợp "Liên hệ").
     */
    public function hasDisplayablePrice(): bool
    {
        $price = (float) ($this->price ?? 0);
        $sale = (float) ($this->sale_price ?? 0);
        if ($price > 0 && $sale > 0 && $sale < $price) {
            return true;
        }

        return $price > 0;
    }

    /**
     * Khuyến mãi hợp lệ (giá gốc > 0 và giá sale thấp hơn giá gốc).
     */
    public function hasValidSalePromotion(): bool
    {
        $price = (float) ($this->price ?? 0);
        $sale = (float) ($this->sale_price ?? 0);

        return $price > 0 && $sale > 0 && $sale < $price;
    }

    /**
     * Chuỗi giá hiển thị (đã format) hoặc nhãn Liên hệ.
     */
    public function priceDisplayLabel(): string
    {
        if (!$this->hasDisplayablePrice()) {
            return __('messages.contact');
        }
        $price = (float) ($this->price ?? 0);
        $sale = (float) ($this->sale_price ?? 0);
        $amount = $this->hasValidSalePromotion() ? $sale : $price;

        return number_format($amount, 0, ',', '.') . 'đ';
    }
}
