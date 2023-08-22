<?php

namespace App\Http\Controllers;

use App\Models\User;


use App\Mail\OtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{



    // ...

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'phone_number' => 'required|unique:users',
            'email' => 'required|email|unique:users',
        ], [
            'phone_number.unique' => 'Phone number already in use.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->messages()->first()], 422);
        }

        $otp = $this->generateFourDigitNumber();

        // Create a new user
        // You'll need to define your User model and its properties
        $user = new User();
        $user->username = $request->input('username');
        $user->phone_number = $request->input('phone_number');
        $user->email = $request->input('email');
        $user->otp_code = $otp;
        $user->save();

        Mail::to($user->email)->send(new OtpMail($otp));
        // Generate a token for the user
        $token = $user->createToken('authToken')->plainTextToken;

        // Return user information and token in the response
        return response()->json([
            'success' => true,
            'message' => 'User registered successfully.',
            'token' => $token,
            'user' => $user,
        ], 200);
    }



    public function verifyOtp(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string',
            'otp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->messages()->first()], 422);
        }

        $user = User::where('phone_number', $request->phone_number)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        // Assuming you have a "otp" field in the users table
        if ($user->otp_code !== $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP.'
            ], 400);
        }

        // Clear the OTP field after successful verification
        $user->otp_code = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully.'
        ], 200);
    }

    public function generateFourDigitNumber()
    {
        return rand(1000, 9999);
    }

    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->messages()->first()], 422);
        }


        $user = User::where('phone_number', $request->phone_number)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number not found.'
            ], 404);
        }

        // Generate a new OTP and save it to the user's record
        $newOtp = $this->generateFourDigitNumber(); // Implement this function
        $user->otp_code = $newOtp;
        $user->save();
        Mail::to($user->email)->send(new OtpMail($newOtp));
        // Send the new OTP to the user (you can use your email sending logic here)

        return response()->json([
            'success' => true,
            'message' => 'New OTP sent successfully.'
        ], 200);
    }


    public function createPassword(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->messages()->first()], 422);
        }


        $user = User::where('phone_number', $request->phone_number)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ], 404);
        }

        // Set the user's password
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password created successfully.'
        ], 200);
    }



    // ...

    public function signIn(Request $request)
    {
        $credentials = $request->only('username', 'password');
       
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Sign-in successful.',
                'token' => $token,
            ], 200);
        }

        return response()->json([
            'scuccess' => false,
            'message' => 'Invalid username or password.'
        ], 401);
    }
}
