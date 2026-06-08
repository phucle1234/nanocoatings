<?php

namespace App\Traits;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

trait HasSlugGenerator
{
    /**
     * Thêm slug generator - xử lý cả đơn lẻ và đa ngôn ngữ
     * 
     * @param string|null $sourceField Field nguồn để tạo slug (cho đơn lẻ)
     * @param string|null $targetField Field đích để lưu slug (cho đơn lẻ)
     * @param bool $isMultilang Có phải đa ngôn ngữ không
     * @return void
     */
    protected function addSlugGenerator($sourceField = null, $targetField = null, $isMultilang = false)
    {
        // Sử dụng addSingleSlugGenerator cho tất cả trường hợp vì nó tạo slug đúng hơn
        $this->addSingleSlugGenerator($sourceField, $targetField);
    }

    /**
     * Thêm slug generator cho field đơn lẻ
     * 
     * @param string $sourceField Field nguồn để tạo slug
     * @param string $targetField Field đích để lưu slug
     * @return void
     */
    protected function addSingleSlugGenerator($sourceField, $targetField)
    {
        // Thêm JavaScript để tự động tạo slug (sử dụng view thay vì custom_html)
        $this->crud->addField([
            'name' => 'slug_generator',
            'type' => 'view',
            'view' => 'vendor.backpack.crud.fields.slug_generator',
            'data' => [
                'source_field' => $sourceField,
                'target_field' => $targetField,
                'languages' => array_keys(config('languages.supported')) // Truyền languages từ config
            ]
        ]);
    }

    /**
     * Lấy giá trị translation cho field
     * 
     * @param string $lang Mã ngôn ngữ
     * @param string $field Tên field
     * @return string
     */
    public function getTranslationValue($lang, $field)
    {
        $entry = $this->crud->getCurrentEntry();
        if (!$entry) {
            return '';
        }

        $translation = $entry->translations()->where('language', $lang)->first();
        if (!$translation) {
            return '';
        }

        $value = $translation->{$field};

        // Handle array fields like image_urls
        if ($field === 'image_urls' && is_array($value)) {
            return implode("\n", $value);
        }

        return $value ?? '';
    }
}

