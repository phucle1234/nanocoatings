<?php

namespace App\Http\Requests;

use App\Helpers\LanguageHelper;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return backpack_auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('id');

        $rules = [
            'category_id' => 'nullable|integer|exists:product_categories,id',
            'code' => 'nullable|string|max:255',
            'categories' => 'nullable|array', // Pivot table categories
            'categories.*' => 'exists:product_categories,id',
            'sku' => 'nullable|string|max:255|unique:products,sku,' . $id,
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock_quantity' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_new' => 'boolean',
            'is_bestseller' => 'boolean',
            'sort_order' => 'integer|min:0',
            'image_urls' => 'nullable|string',
            'attribute_values' => 'nullable|array',
            'attribute_values.*' => 'exists:product_attribute_values,id',
        ];

        // Thêm validation cho các field đa ngôn ngữ
        $languages = LanguageHelper::getLanguageCodes();
        foreach ($languages as $lang) {
            $rules['name_' . $lang] = 'required|string|max:255';
            $rules['description_' . $lang] = 'nullable|string|max:5000';
            $rules['short_description_' . $lang] = 'nullable|string|max:500';
            $rules['outstanding_features_' . $lang] = 'nullable|string|max:500';
            $rules['meta_title_' . $lang] = 'nullable|string|max:250';
            $rules['meta_description_' . $lang] = 'nullable|string|max:160';
            $rules['meta_keywords_' . $lang] = 'nullable|string|max:255';
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
            'categories' => 'danh mục sản phẩm',
            'sku' => 'mã sản phẩm',
            'price' => 'giá bán',
            'sale_price' => 'giá khuyến mãi',
            'stock_quantity' => 'số lượng tồn kho',
            'min_stock_quantity' => 'số lượng tồn kho tối thiểu',
            'is_active' => 'trạng thái hoạt động',
            'is_featured' => 'sản phẩm nổi bật',
            'is_new' => 'sản phẩm mới',
            'is_bestseller' => 'sản phẩm bán chạy',
            'sort_order' => 'thứ tự sắp xếp',
            'image_urls' => 'hình ảnh sản phẩm',
            'attribute_values' => 'thuộc tính sản phẩm',
        ] + $this->translationImageUrlAttributes();
    }

    /**
     * @return array<string, string>
     */
    private function translationImageUrlAttributes(): array
    {
        $out = [];
        foreach (LanguageHelper::getLanguageCodes() as $lang) {
            $out['image_urls_' . $lang] = 'hình ảnh sản phẩm (' . $lang . ')';
        }

        return $out;
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'sku.unique' => 'Mã sản phẩm đã tồn tại',
            'price.required' => 'Giá bán không được để trống',
            'price.numeric' => 'Giá bán phải là số',
            'price.min' => 'Giá bán phải lớn hơn hoặc bằng 0',
            'sale_price.lt' => 'Giá khuyến mãi phải nhỏ hơn giá bán',
            'stock_quantity.required' => 'Số lượng tồn kho không được để trống',
            'stock_quantity.integer' => 'Số lượng tồn kho phải là số nguyên',
            'stock_quantity.min' => 'Số lượng tồn kho phải lớn hơn hoặc bằng 0',
        ];
    }
}
