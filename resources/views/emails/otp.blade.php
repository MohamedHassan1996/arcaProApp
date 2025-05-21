@component('mail::message')
# Password Reset OTP

We received a request to reset your password.
Use the OTP code below to continue:

## {{ $otp }}

If you did not request a password reset, please ignore this email.

Thanks,
{{ config('app.name') }}
@endcomponent
