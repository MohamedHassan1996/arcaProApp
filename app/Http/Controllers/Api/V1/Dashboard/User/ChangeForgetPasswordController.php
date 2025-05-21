<?php

namespace App\Http\Controllers\Api\V1\Dashboard\User;

use App\Enums\ResponseCode\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChangeForgetPasswordController extends Controller //implements HasMiddleware
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function __invoke(Request $request)
    {
        $data = $request->all();

        $operatorEmail = DB::connection('proMaintenances')->table('emails')->where('email', $data['email'])->first();

        $otp = Otp::where('email', $data['email'])->where('type', 1)->first();

        if(!$otp) {
            return ApiResponse::error(__('auth.invalid_otp'), [], HttpStatusCode::UNAUTHORIZED);
        }

        $user = User::where('operator_guid', $operatorEmail->employee_guid)->first();

        $user->update([
            'password' => $data['password']
        ]);


        $otp->delete();

        // $otp = Otp::where('otp', $data['otp'])->where('email', $data['email'])->where('type', $data['type'])->first();

        // if(!$otp) {
        //     return ApiResponse::error(__('auth.invalid_otp'), [], HttpStatusCode::UNAUTHORIZED);
        // }

        // if (!$otp->created_at || $otp->created_at->lt(now()->subMinutes(5))) {
        //     $otp->delete();
        //     return ApiResponse::error(__('auth.invalid_otp'), [], HttpStatusCode::UNAUTHORIZED);
        // }

        return ApiResponse::success(__('auth.password_changed'));

    }
}
