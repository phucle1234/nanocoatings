<?php

namespace App\Http\Requests;

use App\Helpers\LanguageHelper;
use Illuminate\Foundation\Http\FormRequest;

class ProductAttributeValueRequest extends FormRequest
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
        $rules = [
            'attribute_id' => 'required|exists:product_attributes,id',
            'value' => 'required|string|max:255',
            'color_code' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'image_url' => 'nullable|url|max:500',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ];

        // Thêm validation cho các field đa ngôn ngữ
        $languages = LanguageHelper::getLanguageCodes();
        foreach ($languages as $lang) {
            $rules['value_' . $lang] = 'nullable|string|max:255';
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
            'attribute_id' => 'thuộc tính',
            'value' => 'giá trị',
            'color_code' => 'mã màu',
            'image_url' => 'URL hình ảnh',
            'is_active' => 'trạng thái hoạt động',
            'sort_order' => 'thứ tự sắp xếp',
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
            'attribute_id.required' => 'Thuộc tính không được để trống',
            'attribute_id.exists' => 'Thuộc tính không tồn tại',
            'value.required' => 'Giá trị không được để trống',
            'color_code.regex' => 'Mã màu phải có định dạng #RRGGBB (VD: #FF0000)',
            'image_url.url' => 'URL hình ảnh không hợp lệ',
        ];
    }
}
