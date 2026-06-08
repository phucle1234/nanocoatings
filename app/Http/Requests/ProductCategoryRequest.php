<?php

namespace App\Http\Requests;

use App\Helpers\LanguageHelper;
use Illuminate\Foundation\Http\FormRequest;

class ProductCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // only allow updates if the user is logged in
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = $this->route('id');

        $rules = [
            'code' => 'required|string|max:255|unique:product_categories,code,' . $id,
            'parent_id' => 'nullable|exists:product_categories,id|not_in:' . $id,
            'image' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
        ];

        // Thêm validation cho các field đa ngôn ngữ
        $languages = LanguageHelper::getLanguageCodes();
        foreach ($languages as $lang) {
            $rules['name_' . $lang] = 'nullable|string|max:255';
            $rules['description_' . $lang] = 'nullable|string';
            $rules['meta_title_' . $lang] = 'nullable|string|max:255';
            $rules['meta_description_' . $lang] = 'nullable|string|max:500';
            $rules['slug_' . $lang] = 'nullable|string|max:255';
            $rules['image_urls_' . $lang] = 'nullable|string';
        }

        return $rules;
    }

    /**
     * Get the validation attributes that apply to the request.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'code' => 'mã danh mục',
            'parent_id' => 'danh mục cha',
            'slug' => 'slug',
            'image' => 'hình ảnh',
            'icon' => 'icon',
            'is_active' => 'trạng thái hoạt động',
            'is_featured' => 'danh mục nổi bật',
            'sort_order' => 'thứ tự sắp xếp',
            'meta_title' => 'meta title',
            'meta_description' => 'meta description',
            'meta_keywords' => 'meta keywords',
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'code.required' => 'Mã danh mục không được để trống',
            'code.unique' => 'Mã danh mục đã tồn tại',
            'parent_id.exists' => 'Danh mục cha không tồn tại',
            'parent_id.not_in' => 'Danh mục không thể chọn chính nó làm danh mục cha',
            'slug.unique' => 'Slug đã tồn tại',
        ];
    }
}
