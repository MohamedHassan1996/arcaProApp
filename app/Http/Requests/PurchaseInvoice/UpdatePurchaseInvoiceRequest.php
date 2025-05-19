<?php

namespace App\Http\Requests\PurchaseInvoice;

use App\Enums\Order\DiscountType;
use App\Helpers\ApiResponse;
use Illuminate\Validation\Rules\Enum;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Enums\Stock\InStockStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePurchaseInvoiceRequest extends FormRequest
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
            'supplierInvoicNumber' => ["nullable", "string", "max:255"],
            'supplierId' => ["nullable", "integer"],
            "status" => ["required", new Enum(InStockStatus::class)],
            "note" => ["nullable", "string", "max:255"],
            "purchaseInvoiceItems" => ["required", "array", "min:1"],
            'discountType' => ["required", new Enum(DiscountType::class)],
            'discount' => ["nullable", "numeric", "min:0"],
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
