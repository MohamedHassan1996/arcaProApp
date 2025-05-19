<?php

namespace App\Http\Requests\User;

use App\Enums\ResponseCode\HttpStatusCode;
use App\Enums\User\UserStatus;
use App\Helpers\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;


class CreateUserRequest extends FormRequest
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
            'username' => ['required', 'unique:users,username'],
            'name' => 'required|string',
            'email'=> ['nullable'],
            'phone' => 'nullable',
            'address' => 'nullable',
            'isActive' => ['required', new Enum(UserStatus::class)],
            'password'=> [
                'required','string',
                Password::min(8)->mixedCase()->numbers(),
            ],
            'roleId'=> ['required', 'numeric', 'exists:roles,id'],
            'avatar' => ["sometimes", "nullable","image", "mimes:jpeg,jpg,png,gif,svg", "max:5148"],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        /*throw new HttpResponseException(response()->json([
            'message' => $validator->errors()
        ], 422));*/

        throw new HttpResponseException(
            ApiResponse::error('', $validator->errors(), HttpStatusCode::UNPROCESSABLE_ENTITY)
        );
    }

    public function messages()
    {
        return [
            'username.unique' => __('validation.custom.username.unique'),
        ];
    }

}
