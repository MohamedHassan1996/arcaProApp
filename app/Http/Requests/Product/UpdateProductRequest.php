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

class UpdateProductRequest extends FormRequest
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
    {//categoryId, name, description, price, status
        return [
            "name" => ["required", "unique:products,name,{$this->route('product')}", "max:255"],
            'serialNumber' => ["nullable", "string", "max:14"],
            "categoryId" => ["nullable"],
            "subCategoryId" => ["nullable"],
            "description" => ["nullable", "string", "max:255"],
            "status" => ["required", new Enum(ProductStatus::class)],
            'minQuantity' => ["required", "numeric"],
            "hasSubUnit" => ["required", new Enum(HasSubUnit::class)],
            'subUnitConversionRate' => ['required_if:hasSubUnit,' . HasSubUnit::YES->value],
            // "price" => ["required", "numeric"],
            // 'subUnitPrice' => ['required_if:hasSubUnit,' . HasSubUnit::YES->value, "numeric"],
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
