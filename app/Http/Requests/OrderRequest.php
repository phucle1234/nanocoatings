<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'payment_status' => 'required|in:pending,paid,failed,refunded,cancelled',
            'notes' => 'nullable|string|max:1000',
            'shipped_at' => 'nullable|date',
            'delivered_at' => 'nullable|date|after_or_equal:shipped_at',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Trạng thái đơn hàng là bắt buộc.',
            'status.in' => 'Trạng thái đơn hàng không hợp lệ.',
            'payment_status.required' => 'Trạng thái thanh toán là bắt buộc.',
            'payment_status.in' => 'Trạng thái thanh toán không hợp lệ.',
            'notes.max' => 'Ghi chú không được vượt quá 1000 ký tự.',
            'shipped_at.date' => 'Ngày giao hàng không hợp lệ.',
            'delivered_at.date' => 'Ngày nhận hàng không hợp lệ.',
            'delivered_at.after_or_equal' => 'Ngày nhận hàng phải sau hoặc bằng ngày giao hàng.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'status' => 'trạng thái đơn hàng',
            'payment_status' => 'trạng thái thanh toán',
            'notes' => 'ghi chú',
            'shipped_at' => 'ngày giao hàng',
            'delivered_at' => 'ngày nhận hàng',
        ];
    }
}
