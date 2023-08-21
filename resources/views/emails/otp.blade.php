@component('mail::message')
# OTP for User Registration

Your OTP is: {{ $otp }}

Thanks,
{{ config('app.name') }}
@endcomponent
