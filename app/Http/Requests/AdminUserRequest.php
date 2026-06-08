<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return backpack_auth()->check();
    }

    public function rules(): array
    {
        $id = $this->route('id');

        $rules = [
            'code'                => 'nullable|string|max:255',
            'parent_code'         => 'nullable|string|max:255',
            'name'                => 'required|string|max:255',
            'user_name'           => 'required|string|max:255',
            // Admin CRUD: KHÔNG kiểm tra unique email (theo yêu cầu).
            'email'               => ['required', 'email', 'max:255'],
            'role'                => 'required|in:admin,customer,dealer',
            'address'             => 'nullable|string|max:2000',
            'latitude'            => 'nullable|numeric|between:-90,90',
            'longitude'           => 'nullable|numeric|between:-180,180',
            'phone'               => 'nullable|string|max:50',
            'city_code'           => 'nullable|string|max:50',
            'type'                => 'nullable|in:customer_account,customer_info',
            'productCategories'   => 'nullable|array',
            'productCategories.*' => 'integer|exists:product_categories,id',
        ];

        if ($id) {
            $rules['password'] = ['nullable', 'string', 'min:6'];
        } else {
            $rules['password'] = ['required', 'string', 'min:6'];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'code'             => 'mã',
            'parent_code'      => 'mã cha',
            'name'             => 'tên',
            'user_name'        => 'tên đăng nhập',
            'email'            => 'email',
            'role'             => 'vai trò',
            'address'          => 'địa chỉ',
            'latitude'         => 'vĩ độ',
            'longitude'        => 'kinh độ',
            'phone'            => 'điện thoại',
            'city_code'        => 'mã thành phố',
            'type'             => 'loại',
            'password'         => 'mật khẩu',
            'productCategories' => 'danh mục NPP',
        ];
    }
}

