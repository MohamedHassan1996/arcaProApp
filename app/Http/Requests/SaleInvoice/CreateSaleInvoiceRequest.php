<?php

namespace App\Http\Requests\SaleInvoice;

use App\Helpers\ApiResponse;
use Illuminate\Validation\Rules\Enum;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Enums\Stock\OutStockStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CreateSaleInvoiceRequest extends FormRequest
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
            "invoiceDate" => ["required", "date"],
            'clientId' => ["nullable", "integer"],
            "status" => ["required", new Enum(OutStockStatus::class)],
            "note" => ["nullable", "string", "max:255"],
            "saleInvoiceItems" => ["required", "array", "min:1"],
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
