<?php

namespace App\Services\Auth;

use App\Enums\ResponseCode\HttpStatusCode;
use App\Enums\User\UserStatus;
use App\Helpers\ApiResponse;
use App\Http\Resources\User\LoggedInUserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\UserRolePremission\UserPermissionService;

class AuthService
{
    protected $userPermissionService;

    public function __construct(UserPermissionService $userPermissionService)
    {
        $this->userPermissionService = $userPermissionService;
    }

    public function login(array $data)
    {
        try {
            $user = User::where('username', $data['username'])->first();

            if (!$user || !Hash::check($data['password'], $user->password)) {
                return ApiResponse::error(__('auth.failed'), [], HttpStatusCode::UNAUTHORIZED);
            }

            if ($user->is_active == UserStatus::INACTIVE) {
                return ApiResponse::error(__('auth.inactive_account'), [], HttpStatusCode::UNAUTHORIZED);
            }

            // // Revoke old tokens (optional)
            //$user->tokens()->delete();

            // Generate a new token (DO NOT return it directly)
            $token = $user->createToken('auth_token')->plainTextToken;

            return ApiResponse::success([
                'profile' => new LoggedInUserResource($user),
                'role' => $user->roles->first()->name,
                'permissions' => $this->userPermissionService->getUserPermissions($user),
                'tokenDetails' => [
                    'token' => $token,
                    'expiresIn' => null
                ],
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function logout()
    {
        $user = auth()->user();

        if ($user) {
            $user->tokens()->delete(); // Revoke all tokens
        }

        return ApiResponse::success([], __('auth.logged_out'));
    }
}
