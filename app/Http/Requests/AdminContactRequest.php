<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return backpack_auth()->check();
    }

    public function rules(): array
    {
        return [
            'user_id' => 'nullable|integer|exists:users,id',
            'order_number' => 'nullable|string|max:255',

            'Title' => 'nullable|string|max:100',
            'Fullname' => 'required|string|max:255',
            'Phone' => 'required|string|max:20',
            'Email' => 'nullable|email|max:255',
            'Content' => 'nullable|string|max:5000',
            'Invoice' => 'nullable|string|max:100',
            'QRcode' => 'nullable|string|max:255',
            'Status' => 'nullable|string|max:100',

            'Type' => 'required|integer|in:0,1,2',
            'Date' => 'nullable|date',
        ];
    }

    public function attributes(): array
    {
        return [
            'user_id' => 'người dùng',
            'order_number' => 'mã đơn',
            'Title' => 'tiêu đề',
            'Fullname' => 'họ tên',
            'Phone' => 'điện thoại',
            'Email' => 'email',
            'Content' => 'nội dung',
            'Invoice' => 'hóa đơn',
            'QRcode' => 'QR code',
            'Status' => 'trạng thái',
            'Type' => 'loại',
            'Date' => 'ngày',
        ];
    }
}

