<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return backpack_auth()->check();
    }

    public function rules(): array
    {
        $id = $this->route('id');

        $rules = [
            'code'         => 'nullable|string|max:255',
            'parent_code'  => 'nullable|string|max:255',
            'name'         => 'required|string|max:255',
            'user_name'    => 'required|string|max:255',
            'email'        => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($id),
            ],
            'role'         => 'required|in:admin,customer,dealer',
            'address'      => 'nullable|string|max:2000',
            'latitude'     => 'nullable|numeric|between:-90,90',
            'longitude'    => 'nullable|numeric|between:-180,180',
            'phone'        => 'nullable|string|max:50',
            'city_code'    => 'nullable|string|max:50',
            'type'         => 'nullable|in:customer_account,customer_info',
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
            'code'        => __('auth.attr_code'),
            'parent_code' => __('auth.attr_parent_code'),
            'name'        => __('auth.attr_name'),
            'user_name'   => __('auth.attr_user_name'),
            'email'       => __('auth.attr_email'),
            'role'        => __('auth.attr_role'),
            'address'     => __('auth.attr_address'),
            'latitude'    => __('auth.attr_latitude'),
            'longitude'   => __('auth.attr_longitude'),
            'phone'       => __('auth.attr_phone'),
            'city_code'   => __('auth.attr_city_code'),
            'type'        => __('auth.attr_type'),
            'password'    => __('auth.attr_password'),
            'productCategories' => __('auth.attr_product_categories'),
        ];
    }
}
