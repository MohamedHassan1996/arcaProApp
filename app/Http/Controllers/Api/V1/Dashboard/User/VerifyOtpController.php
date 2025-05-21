<?php

namespace App\Http\Controllers\Api\V1\Dashboard\User;

use App\Enums\ResponseCode\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerifyOtpController extends Controller //implements HasMiddleware
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function __invoke(Request $request)
    {
        $data = $request->all();

        $otp = Otp::where('otp', $data['otp'])->where('email', $data['email'])->where('type', $data['type'])->first();

        if(!$otp) {
            return ApiResponse::error(__('auth.invalid_otp'), [], HttpStatusCode::UNAUTHORIZED);
        }

        if (!$otp->created_at || $otp->created_at->lt(now()->subMinutes(5))) {
            $otp->delete();
            return ApiResponse::error(__('auth.invalid_otp'), [], HttpStatusCode::UNAUTHORIZED);
        }

        return ApiResponse::success(__('auth.valid_otp'));

    }
}
