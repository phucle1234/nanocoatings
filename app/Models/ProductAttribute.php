<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Backpack\CRUD\app\Models\Traits\CrudTrait;

class ProductAttribute extends Model
{
    use HasFactory, CrudTrait;

    protected $fillable = [
        'code',
        'type',
        'is_required',
        'is_filterable',
        'is_comparable',
        'is_active',
        'sort_order',
        'options',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_filterable' => 'boolean',
        'is_comparable' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        // 'options' => 'array', // Comment out để tránh xung đột với textarea field
    ];

    /**
     * Quan hệ với bản dịch
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ProductAttributeTranslation::class, 'attribute_id');
    }

    /**
     * Quan hệ với bản dịch theo ngôn ngữ hiện tại
     */
    public function translation(): HasOne
    {
        $locale = config('languages.default');
        return $this->hasOne(ProductAttributeTranslation::class, 'attribute_id')
            ->where('language', $locale);
    }

    /**
     * Quan hệ với các giá trị thuộc tính
     */
    public function values(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class, 'attribute_id');
    }

    /**
     * Quan hệ với các giá trị thuộc tính đang hoạt động
     */
    public function activeValues(): HasMany
    {
        return $this->hasMany(ProductAttributeValue::class, 'attribute_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    /**
     * Scope để lấy thuộc tính đang hoạt động
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope để lấy thuộc tính có thể lọc
     */
    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', true);
    }

    /**
     * Scope để lấy thuộc tính có thể so sánh
     */
    public function scopeComparable($query)
    {
        return $query->where('is_comparable', true);
    }

    /**
     * Scope để lấy thuộc tính bắt buộc
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope để sắp xếp theo thứ tự
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope để lấy thuộc tính theo loại
     */
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Lấy tên thuộc tính theo ngôn ngữ hiện tại
     */
    public function getNameAttribute()
    {
        $translation = $this->translation;
        return $translation ? $translation->name : $this->code;
    }

    /**
     * Lấy mô tả thuộc tính theo ngôn ngữ hiện tại
     */
    public function getDescriptionAttribute()
    {
        $translation = $this->translation;
        return $translation ? $translation->description : null;
    }

    /**
     * Kiểm tra xem thuộc tính có phải là select/multiselect không
     */
    public function isSelectType(): bool
    {
        return in_array($this->type, ['select', 'multiselect']);
    }

    /**
     * Kiểm tra xem thuộc tính có phải là multiselect không
     */
    public function isMultiSelectType(): bool
    {
        return $this->type === 'multiselect';
    }

    /**
     * Lấy các tùy chọn cho thuộc tính select/multiselect
     */
    public function getSelectOptions(): array
    {
        if (!$this->isSelectType()) {
            return [];
        }

        return $this->options ?: [];
    }

    /**
     * Lấy giá trị mặc định cho thuộc tính
     */
    public function getDefaultValue()
    {
        switch ($this->type) {
            case 'boolean':
                return false;
            case 'number':
                return 0;
            case 'date':
                return null;
            case 'select':
            case 'multiselect':
                return $this->isMultiSelectType() ? [] : null;
            default:
                return '';
        }
    }

    /**
     * Validate giá trị thuộc tính
     */
    public function validateValue($value): bool
    {
        switch ($this->type) {
            case 'text':
                return is_string($value);
            case 'number':
                return is_numeric($value);
            case 'boolean':
                return is_bool($value);
            case 'date':
                return $value === null || strtotime($value) !== false;
            case 'select':
                return in_array($value, $this->getSelectOptions());
            case 'multiselect':
                return is_array($value) && empty(array_diff($value, $this->getSelectOptions()));
            default:
                return true;
        }
    }

    /**
     * Scope để eager load tất cả relationships cần thiết
     */
    public function scopeWithAll($query)
    {
        return $query->with([
            'translations',
            'values.translations'
        ]);
    }

    /**
     * Scope để eager load relationships cơ bản
     */
    public function scopeWithBasic($query)
    {
        return $query->with([
            'translation',
            'activeValues'
        ]);
    }

    /**
     * Scope để eager load relationships cho frontend
     */
    public function scopeForFrontend($query)
    {
        return $query->with([
            'translation',
            'activeValues' => function ($query) {
                $query->with('translation')
                    ->orderBy('sort_order');
            }
        ]);
    }

    /**
     * Xử lý lưu translations từ các field đa ngôn ngữ
     */
    public function handleTranslations(array $data)
    {
        $languages = \App\Helpers\LanguageHelper::getLanguageCodes();

        foreach ($languages as $lang) {
            $name = $data["name_{$lang}"] ?? '';
            $description = $data["description_{$lang}"] ?? '';

            // Chỉ tạo translation nếu có ít nhất tên
            if (!empty($name)) {
                try {
                    $this->translations()->updateOrCreate(
                        ['language' => $lang],
                        [
                            'name' => $name,
                            'description' => $description,
                        ]
                    );
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Error saving attribute translation for language: ' . $lang, [
                        'error' => $e->getMessage(),
                        'data' => [
                            'name' => $name,
                            'description' => $description,
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
     * Lấy tên thuộc tính đa ngôn ngữ để hiển thị
     * 
     * @return string
     */
    public function getTranslatedName(): string
    {
        $translations = $this->translations;
        if ($translations->isNotEmpty()) {
            $names = $translations->pluck('name', 'language')->toArray();
            return implode(' | ', array_filter($names));
        }
        return $this->code;
    }

    /**
     * Lấy HTML hiển thị options
     * 
     * @return string
     */
    public function getOptionsHtml(): string
    {
        if ($this->options) {
            $options = json_decode($this->options, true);
            if (is_array($options)) {
                return '<ul><li>' . implode('</li><li>', array_map('e', $options)) . '</li></ul>';
            }
        }
        return '<span class="text-muted">Không có tùy chọn</span>';
    }
}
