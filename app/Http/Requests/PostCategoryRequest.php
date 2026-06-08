<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostCategoryRequest extends FormRequest
{
    public function authorize()
    {
        return backpack_auth()->check();
    }

    public function rules()
    {
        $rules = [
            'parent_id' => 'nullable|exists:postcategories,id',
            'image' => 'nullable|string',
            'icon' => 'nullable|string',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer|min:0',
        ];

        // Thêm rules cho các field đa ngôn ngữ
        $languages = array_keys(config('languages.supported'));
        foreach ($languages as $lang) {
            $rules['name_' . $lang] = 'nullable|string|max:255';
            $rules['description_' . $lang] = 'nullable|string';
            $rules['meta_title_' . $lang] = 'nullable|string|max:255';
            $rules['meta_description_' . $lang] = 'nullable|string|max:500';
            $rules['meta_keywords_' . $lang] = 'nullable|string|max:500';
            $rules['canonical_url_' . $lang] = 'nullable|url';
            $rules['og_title_' . $lang] = 'nullable|string|max:255';
            $rules['og_description_' . $lang] = 'nullable|string|max:500';
            $rules['og_image_' . $lang] = 'nullable|string';
            $rules['image_urls_' . $lang] = 'nullable|string';
        }

        return $rules;
    }

    public function attributes()
    {
        $attributes = [
            'slug' => 'slug danh mục',
            'parent_id' => 'danh mục cha',
            'image' => 'hình ảnh',
            'icon' => 'icon',
            'is_active' => 'trạng thái hoạt động',
            'is_featured' => 'danh mục nổi bật',
            'sort_order' => 'thứ tự sắp xếp',
        ];

        // Thêm attributes cho các field đa ngôn ngữ
        $languages = array_keys(config('languages.supported'));
        foreach ($languages as $lang) {
            $attributes['name_' . $lang] = 'tên danh mục (' . $lang . ')';
            $attributes['description_' . $lang] = 'mô tả (' . $lang . ')';
            $attributes['meta_title_' . $lang] = 'meta title (' . $lang . ')';
            $attributes['meta_description_' . $lang] = 'meta description (' . $lang . ')';
            $attributes['meta_keywords_' . $lang] = 'meta keywords (' . $lang . ')';
            $attributes['canonical_url_' . $lang] = 'canonical url (' . $lang . ')';
            $attributes['og_title_' . $lang] = 'og title (' . $lang . ')';
            $attributes['og_description_' . $lang] = 'og description (' . $lang . ')';
            $attributes['og_image_' . $lang] = 'og image (' . $lang . ')';
            $attributes['image_urls_' . $lang] = 'hình ảnh (' . $lang . ')';
        }

        return $attributes;
    }
}
