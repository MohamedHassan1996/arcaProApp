<?php

namespace App\Http\Controllers\Api\V1\Dashboard\User;

use App\Enums\ResponseCode\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\ChangePasswordRequest;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:api'),
        ];
    }

    /**
     * Handle password change request.
     */
    public function __invoke(ChangePasswordRequest $request)
    {
        $authUser = $request->user();

        // if (!Hash::check($request->currentPassword, $authUser->password)) {
        //     return ApiResponse::error(__('passwords.current_password'), HttpStatusCode::UNPROCESSABLE_ENTITY);
        // }

        // Update password securely
        $authUser->update([
            'password' => $request->password,
        ]);

        $authUser->tokens()->delete();

        return ApiResponse::success([], __('crud.updated'));
    }
}
