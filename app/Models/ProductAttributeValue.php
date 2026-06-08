<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class ProductAttributeValue extends Model
{
    use HasFactory, CrudTrait;

    protected $fillable = [
        'attribute_id',
        'vehicle_type',
        'value',
        'color_code',
        'image_url',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Quan hệ với thuộc tính sản phẩm
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class, 'attribute_id');
    }

    /**
     * Quan hệ với bản dịch
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ProductAttributeValueTranslation::class, 'attribute_value_id');
    }

    /**
     * Quan hệ với bản dịch theo ngôn ngữ hiện tại
     */
    public function translation(): HasOne
    {
        $locale = config('languages.default');

        return $this->hasOne(ProductAttributeValueTranslation::class, 'attribute_value_id')
            ->where('language', $locale);
    }

    /**
     * Quan hệ với sản phẩm (many-to-many)
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_attribute_product',
            'attribute_value_id',
            'product_id'
        );
    }

    /**
     * Scope để lấy giá trị đang hoạt động
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope để sắp xếp theo thứ tự
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope để lấy giá trị theo thuộc tính
     */
    public function scopeForAttribute($query, $attributeId)
    {
        return $query->where('attribute_id', $attributeId);
    }

    /**
     * Scope để lấy giá trị theo mã thuộc tính
     */
    public function scopeForAttributeCode($query, $attributeCode)
    {
        return $query->whereHas('attribute', function ($q) use ($attributeCode) {
            $q->where('code', $attributeCode);
        });
    }

    /**
     * Lấy tên giá trị theo ngôn ngữ hiện tại
     */
    public function getLocalizedValueAttribute()
    {
        $translation = $this->translation;
        return $translation ? $translation->value : $this->value;
    }

    /**
     * Lấy tên thuộc tính
     */
    public function getAttributeNameAttribute()
    {
        return $this->attribute ? $this->attribute->name : '';
    }

    /**
     * Lấy mã thuộc tính
     */
    public function getAttributeCodeAttribute()
    {
        return $this->attribute ? $this->attribute->code : '';
    }

    /**
     * Lấy loại thuộc tính
     */
    public function getAttributeTypeAttribute()
    {
        return $this->attribute ? $this->attribute->type : '';
    }

    /**
     * Kiểm tra xem giá trị có phải là màu sắc không
     */
    public function isColor(): bool
    {
        return !empty($this->color_code);
    }

    /**
     * Kiểm tra xem giá trị có hình ảnh không
     */
    public function hasImage(): bool
    {
        return !empty($this->image_url);
    }

    /**
     * Lấy màu sắc để hiển thị
     */
    public function getDisplayColorAttribute()
    {
        if ($this->isColor()) {
            return $this->color_code;
        }

        // Nếu không có color_code, có thể tạo màu ngẫu nhiên dựa trên value
        $hash = md5($this->value);
        return '#' . substr($hash, 0, 6);
    }

    /**
     * Lấy hình ảnh để hiển thị
     */
    public function getDisplayImageAttribute()
    {
        if ($this->hasImage()) {
            return $this->image_url;
        }

        // Có thể trả về hình ảnh mặc định
        return null;
    }

    /**
     * Lấy giá trị để hiển thị (có thể là text, màu, hoặc hình ảnh)
     */
    public function getDisplayValueAttribute()
    {
        if ($this->isColor()) {
            return $this->color_code;
        }

        if ($this->hasImage()) {
            return $this->image_url;
        }

        return $this->localized_value;
    }

    /**
     * Tạo slug từ giá trị
     */
    public function getSlugAttribute()
    {
        return Str::slug($this->value);
    }

    /**
     * Lấy URL để lọc sản phẩm theo giá trị này
     */
    public function getFilterUrlAttribute()
    {
        $attributeCode = $this->attribute_code;
        $valueSlug = $this->slug;

        return route('products.filter', [
            'attribute' => $attributeCode,
            'value' => $valueSlug
        ]);
    }

    /**
     * Scope để eager load tất cả relationships cần thiết
     */
    public function scopeWithAll($query)
    {
        return $query->with([
            'attribute.translations',
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
            'attribute',
            'translation'
        ]);
    }

    /**
     * Scope để eager load relationships cho frontend
     */
    public function scopeForFrontend($query)
    {
        return $query->with([
            'attribute.translation',
            'translation'
        ]);
    }

    /**
     * Xử lý lưu translations từ các field đa ngôn ngữ
     */
    public function handleTranslations(array $data)
    {
        $languages = \App\Helpers\LanguageHelper::getLanguageCodes();

        foreach ($languages as $lang) {
            $value = $data["value_{$lang}"] ?? '';

            // Chỉ tạo translation nếu có giá trị
            if (!empty($value)) {
                try {
                    $this->translations()->updateOrCreate(
                        ['language' => $lang],
                        [
                            'value' => $value,
                        ]
                    );
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Error saving attribute value translation for language: ' . $lang, [
                        'error' => $e->getMessage(),
                        'data' => [
                            'value' => $value,
                        ]
                    ]);
                }
            }
        }
    }

    // ========================================
    // UI DISPLAY METHODS (Backpack Columns)
    // ========================================

    /**
     * Lấy tên thuộc tính đa ngôn ngữ để hiển thị trong cột
     * 
     * @return string
     */
    public function getAttributeNameMultilang(): string
    {
        if ($this->attribute) {
            $translations = $this->attribute->translations;
            if ($translations->isNotEmpty()) {
                $names = $translations->pluck('name', 'language')->toArray();
                return implode(' | ', array_filter($names));
            }
            return $this->attribute->code;
        }
        return '-';
    }

    /**
     * Lấy giá trị đã localized để hiển thị
     * 
     * @return string
     */
    public function getLocalizedValueDisplay(): string
    {
        $translation = $this->translation;
        return $translation ? $translation->value : $this->value;
    }

    /**
     * Lấy HTML hiển thị mã màu
     * 
     * @return string
     */
    public function getColorCodeHtml(): string
    {
        if ($this->color_code) {
            return sprintf(
                '<span style="display: inline-block; width: 20px; height: 20px; background-color: %s; border: 1px solid #ccc; border-radius: 3px;"></span> %s',
                e($this->color_code),
                e($this->color_code)
            );
        }
        return '<span class="text-muted">-</span>';
    }

    /**
     * Lấy HTML hiển thị hình ảnh
     * 
     * @return string
     */
    public function getImageUrlHtml(): string
    {
        if ($this->image_url) {
            return sprintf(
                '<img src="%s" style="max-width: 50px; max-height: 50px; object-fit: cover; border-radius: 4px;">',
                e($this->image_url)
            );
        }
        return '<span class="text-muted">-</span>';
    }
}
