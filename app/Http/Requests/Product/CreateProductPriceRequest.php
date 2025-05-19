<?php

namespace App\Http\Requests\Product;

use App\Enums\IsActive;
use App\Helpers\ApiResponse;
use Illuminate\Validation\Rules\Enum;
use App\Enums\ResponseCode\HttpStatusCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateProductPriceRequest extends FormRequest
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
            "productId" => ["required"],
            "isActive" => ["required", new Enum(IsActive::class)],
            'startAt' => ["nullable"],
            'cost' => ["nullable", "numeric"],
            'unitPrice' => ["nullable", "numeric"],
            'subUnitPrice' => ["nullable", "numeric"],
        ];

    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::error('', $validator->errors(), HttpStatusCode::UNPROCESSABLE_ENTITY)
        );
    }

    public function messages()
    {
        return [
            'name.unique' => __('validation.products.name.unique'),
        ];
    }

}
