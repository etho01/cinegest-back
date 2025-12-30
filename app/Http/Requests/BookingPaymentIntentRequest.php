<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingPaymentIntentRequest extends FormRequest
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
            'sessionId' => 'required|integer|exists:sessions,id',
            'items' => 'required|array|min:1',
            'items.*.priceId' => 'required|integer',
            'items.*.priceName' => 'required|string',
            'items.*.priceAmount' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'totalAmount' => 'required|numeric|min:0',
            'userId' => 'required|integer|exists:users,id',
        ];
    }
}
