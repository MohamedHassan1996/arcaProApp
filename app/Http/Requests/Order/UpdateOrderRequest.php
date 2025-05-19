<?php

namespace App\Http\Requests\Order;

use App\Enums\Order\OrderStatus;
use App\Enums\Order\DiscountType;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateOrderRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'orderId' => 'required',
            'discount' => 'numeric',
            'discountType' => ['required', new Enum(DiscountType::class)],
            'price' => 'required|numeric',
            'clientPhoneId' => 'nullable',
            'clientEmailId' => 'nullable',
            'clientAddressId' => 'nullable',
            'clientId' => 'required',
            'status' => ['required',new Enum(OrderStatus::class)],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => $validator->errors()
        ], 401));
    }

}
