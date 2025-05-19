<?php

namespace App\Http\Requests\Product;

use App\Enums\Product\HasSubUnit;
use App\Helpers\ApiResponse;
use App\Enums\Product\ProductStatus;
use Illuminate\Validation\Rules\Enum;
use App\Enums\ResponseCode\HttpStatusCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateProductRequest extends FormRequest
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
            "productMedia" => ["nullable", "array"],
            'serialNumber' => ["nullable", "string", "max:14"],
            "categoryId" => ["nullable"],
            "subCategoryId" => ["nullable"],
            'minQuantity' => ["required", "numeric"],
            "name" => ["required", "unique:products,name", "max:255"],
            "status" => ["required", new Enum(ProductStatus::class)],
            "hasSubUnit" => ["required", new Enum(HasSubUnit::class)],
            'subUnitConversionRate' => ['required_if:hasSubUnit,' . HasSubUnit::YES->value],
            // "price" => ["required", "numeric"],
            // 'subUnitPrice' => ['required_if:hasSubUnit,' . HasSubUnit::YES->value, "numeric"],
            'quantity' => ['nullable', "numeric"],
            'cost' => ['nullable', "numeric"],
            'description' => ['nullable', "string"],
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
