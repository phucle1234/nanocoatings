<?php

namespace App\Http\Requests;

use App\Helpers\LanguageHelper;
use Illuminate\Foundation\Http\FormRequest;

class ProductAttributeRequest extends FormRequest
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
            'code' => 'required|string|max:255|unique:product_attributes,code,' . $id,
            'type' => 'required|in:text,number,select,multiselect,boolean,date,textarea',
            'vehicle_type' => 'nullable|in:oto,xe-may,xe-tai,xe-dap,xe-chuyen-dung,xe-dien,all',
            'is_required' => 'boolean',
            'is_filterable' => 'boolean',
            'is_comparable' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'options' => 'nullable|string',
        ];

        // Thêm validation cho các field đa ngôn ngữ
        $languages = LanguageHelper::getLanguageCodes();
        foreach ($languages as $lang) {
            $rules['name_' . $lang] = 'nullable|string|max:255';
            $rules['description_' . $lang] = 'nullable|string|max:500';
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
            'code' => 'mã thuộc tính',
            'type' => 'loại thuộc tính',
            'is_required' => 'bắt buộc',
            'is_filterable' => 'có thể lọc',
            'is_comparable' => 'có thể so sánh',
            'is_active' => 'trạng thái hoạt động',
            'sort_order' => 'thứ tự sắp xếp',
            'options' => 'tùy chọn',
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
            'code.required' => 'Mã thuộc tính không được để trống',
            'code.unique' => 'Mã thuộc tính đã tồn tại',
            'type.required' => 'Loại thuộc tính không được để trống',
            'type.in' => 'Loại thuộc tính không hợp lệ',
        ];
    }
}
