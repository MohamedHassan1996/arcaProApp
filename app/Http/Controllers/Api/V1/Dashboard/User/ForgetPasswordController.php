<?php

namespace App\Http\Controllers\Api\V1\Dashboard\User;

use App\Enums\ResponseCode\HttpStatusCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Mail\SendOtpMail;
use App\Models\Employee;
use App\Models\Otp;
use App\Models\User;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ForgetPasswordController extends Controller //implements HasMiddleware
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function __invoke(Request $request)
    {
        try{
            $data = $request->all();

            DB::beginTransaction();

            $operatorEmail = DB::connection('proMaintenances')->table('emails')->where('email', $data['email'])->first();

            if(!$operatorEmail) {
                return ApiResponse::error(__('auth.email_not_found'), [], HttpStatusCode::UNAUTHORIZED);
            }

            $user = User::where('operator_guid', $operatorEmail->employee_guid)->first();


            if(!$user || !$operatorEmail) {
                return ApiResponse::error(__('auth.email_not_found'), [], HttpStatusCode::UNAUTHORIZED);
            }

            $otp = Otp::where('email', $data['email'])->first();

            if($otp) {
                $otp->delete();
            }


            $otp = Otp::create([
                'email' => $data['email'],
                'otp' => mt_rand(1000, 9999),
                'type' => 1
            ]);

            Mail::to($data['email'])->send(new SendOtpMail($otp->otp));

            DB::commit();

            return ApiResponse::success(__('auth.email_sent'));

        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }
}
