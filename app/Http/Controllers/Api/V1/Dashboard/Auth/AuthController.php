<?php

namespace App\Http\Controllers\Api\V1\Dashboard\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\AuthService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use OpenApi\Annotations as OA;

class AuthController extends Controller //implements HasMiddleware
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    // 🔹 Ensure middleware() is defined AFTER the constructor
    // public static function middleware(): array
    // {
    //     return [
    //         new Middleware('auth:api', except: ['login'])
    //     ];
    // }

    /*
    ** login method
    */
/**
 * @OA\Post(
 *     path="/auth/login",
 *     summary="Authenticate user and generate JWT token",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 @OA\Property(property="email", type="string", example="admin"),
 *                 @OA\Property(property="password", type="string", example="MaNs123456")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Login successful",
 *         @OA\JsonContent(
 *             @OA\Property(property="profile", type="object",
 *                 @OA\Property(property="name", type="string", example="مستر محمد عبده"),
 *                 @OA\Property(property="username", type="string", example="Admin"),
 *                 @OA\Property(property="phone", type="string", example="1234567890"),
 *                 @OA\Property(property="email", type="string", example="admin@admin.com")
 *             ),
 *             @OA\Property(property="role", type="string", example="مدير عام"),
 *             @OA\Property(property="permissions", type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="permissionName", type="string", example="all_users"),
 *                     @OA\Property(property="access", type="boolean", example=true)
 *                 )
 *             ),
 *             @OA\Property(property="tokenDetails", type="object",
 *                 @OA\Property(property="token", type="string", example="21|2ceHorzqQmn3..."),
 *                 @OA\Property(property="expiresIn", type="integer", example=600)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Invalid credentials"
 *     )
 * )
 */

    public function login(LoginRequest $loginReq)
    {
        return $this->authService->login($loginReq->validated());
    }

    /*
    ** logout method
    */

   /**
 * @OA\Post(
 *     path="/auth/logout",
 *     summary="Logout user",
 *     security={{"bearerAuth":{}}},
 *     tags={"Authentication"},
 *     @OA\Response(
 *         response=200,
 *         description="Logout successful",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="You have logged out")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized"
 *     )
 * )
 */
    public function logout()
    {
        return $this->authService->logout();
    }
}
