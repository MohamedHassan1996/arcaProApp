<?php

namespace App\Http\Requests\Category\SubCategory;

use App\Enums\Product\CategoryStatus;
use App\Enums\ResponseCode\HttpStatusCode;
use App\Helpers\ApiResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;


class UpdateSubCategoryRequest extends FormRequest
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
            'name' => ['required',
                    Rule::unique('categories', 'name')
                    ->ignore($this->route('sub_category'))
                    ->where(function ($query) {
                        return $query->where('parent_id', $this->input('categoryId')); // Only check uniqueness for main categories
                    }),
                ],            'isActive' => ['required', new Enum(CategoryStatus::class)],
            'path' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg|max:2048',
            'categoryId' => 'nullable',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            ApiResponse::error('', $validator->errors(), HttpStatusCode::UNPROCESSABLE_ENTITY)
        );
    }


}
