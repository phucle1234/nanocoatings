<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
{
    public function authorize()
    {
        return backpack_auth()->check();
    }

    public function rules(): array
    {
        $id = $this->route('id');
        $languages = array_keys(config('languages.supported'));

        $rules = [
            'slug' => 'nullable|string|max:255|unique:posts,slug,' . $id,
            'postcategory_id' => 'nullable|exists:postcategories,id',
            'icon' => 'nullable|string|max:255',
            'status' => 'required|in:draft,published,archived',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer|min:0',
            'view_count' => 'integer|min:0',
            'published_at' => 'nullable|date',
        ];

        // Validation rules cho từng ngôn ngữ
        foreach ($languages as $lang) {
            $rules['title_' . $lang] = 'nullable|string|max:255';
            $rules['slug_' . $lang] = 'nullable|string|max:255';
            $rules['content_' . $lang] = 'nullable|string';
            $rules['excerpt_' . $lang] = 'nullable|string';
            $rules['meta_title_' . $lang] = 'nullable|string|max:255';
            $rules['meta_description_' . $lang] = 'nullable|string';
            $rules['meta_keywords_' . $lang] = 'nullable|string';
            $rules['canonical_url_' . $lang] = 'nullable|url';
            $rules['og_title_' . $lang] = 'nullable|string|max:255';
            $rules['og_description_' . $lang] = 'nullable|string';
            $rules['og_image_' . $lang] = 'nullable|url';
            $rules['image_urls_' . $lang] = 'nullable|string';
        }

        return $rules;
    }

    public function attributes(): array
    {
        $languages = config('languages.supported');
        $attributes = [
            'slug' => 'Slug',
            'postcategory_id' => 'Danh mục',
            'icon' => 'Icon',
            'status' => 'Trạng thái',
            'is_active' => 'Kích hoạt',
            'is_featured' => 'Nổi bật',
            'sort_order' => 'Thứ tự',
            'view_count' => 'Lượt xem',
            'published_at' => 'Ngày xuất bản',
        ];

        // Attributes cho từng ngôn ngữ
        foreach ($languages as $lang => $langName) {
            $attributes['title_' . $lang] = 'Tiêu đề (' . $langName . ')';
            $attributes['slug_' . $lang] = 'Slug (' . $langName . ')';
            $attributes['content_' . $lang] = 'Nội dung (' . $langName . ')';
            $attributes['excerpt_' . $lang] = 'Tóm tắt (' . $langName . ')';
            $attributes['meta_title_' . $lang] = 'Meta Title (' . $langName . ')';
            $attributes['meta_description_' . $lang] = 'Meta Description (' . $langName . ')';
            $attributes['meta_keywords_' . $lang] = 'Meta Keywords (' . $langName . ')';
            $attributes['canonical_url_' . $lang] = 'Canonical URL (' . $langName . ')';
            $attributes['og_title_' . $lang] = 'OG Title (' . $langName . ')';
            $attributes['og_description_' . $lang] = 'OG Description (' . $langName . ')';
            $attributes['og_image_' . $lang] = 'OG Image (' . $langName . ')';
            $attributes['image_urls_' . $lang] = 'Hình ảnh (' . $langName . ')';
        }

        return $attributes;
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Trạng thái là bắt buộc.',
            'status.in' => 'Trạng thái phải là: bản nháp, đã xuất bản, hoặc đã lưu trữ.',
            'postcategory_id.exists' => 'Danh mục được chọn không tồn tại.',
            'published_at.date' => 'Ngày xuất bản phải là định dạng ngày hợp lệ.',
        ];
    }
}
