<?php

namespace App\Http\Requests\Order;

use App\Enums\Order\DiscountType;
use App\Enums\Order\OrderStatus;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class CreateOrderRequest extends FormRequest
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
            'discount' => 'numeric',
            'discountType' => ['required', new Enum(DiscountType::class)],
            'clientId' => 'required',
            'clientPhoneId' => 'nullable',
            'clientEmailId' => 'nullable',
            'clientAddressId' => 'nullable',
            'status' => ['required',new Enum(OrderStatus::class)],
            'orderItems' => 'required|array',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => $validator->errors()
        ], 401));
    }

}
