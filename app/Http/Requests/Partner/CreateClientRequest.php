<?php

namespace App\Http\Requests\Partner;

use App\Enums\IsMain;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Enums\Client\ClientType;
use App\Helpers\ApiResponse;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;


class CreateClientRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'type'=>['required',new Enum(ClientType::class)],
            'taxNumber' => 'nullable|string|max:255',
            'note' => 'nullable|string',
            'contacts'=>'nullable|array',//phone ,is_main , country_code
            'contacts.*.phone'=>'required',
            'contacts.*.isMain'=>['required',new Enum(IsMain::class)],
            'contacts.*.countryCode'=>'nullable|string|max:255',
            'contacts.*.name'=>['nullable'],
            'contacts.*.email'=>['nullable', 'email'],
            'addresses'=>'nullable|array',//address ,is_main
            'addresses.*.address'=>'required|string|max:255',
            'addresses.*.isMain'=>['required',new Enum(IsMain::class)],
            'addresses.*.city'=>'nullable|string|max:255',
            'addresses.*.state'=>'nullable|string|max:255',
            'addresses.*.zipCode'=>'nullable|string|max:255',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::error('', $validator->errors(), HttpStatusCode::UNPROCESSABLE_ENTITY)
        );
    }

}
