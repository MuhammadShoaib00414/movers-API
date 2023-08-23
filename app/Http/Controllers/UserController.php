<?php

namespace App\Http\Controllers;

use Exception;


use App\Models\User;
use App\Mail\OtpMail;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Twilio\Rest\Client;

class UserController extends Controller
{

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

        $message  = $request->input('username') . " Your verification code is: $otp. Please enter this code to verify your account.";
        $this->sendSMS($request, $otp, $message);

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
        $checkphone = User::where('phone_number', '=', $request->phone_number)->first();

        if (!$checkphone) {
            return response()->json([
                'message' => 'This phone is not valid.'
            ], 422);
        }



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
            'otp' => 'required|digits:4', // Assuming OTP is 6 digits
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

        // Check if OTP exists in the user record
        if (!$user->otp_code) {
            return response()->json([
                'success' => false,
                'message' => 'No OTP code found for this user.'
            ], 400);
        }

        // Compare the provided OTP with the stored OTP
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

        // Generate a new OTP
        $newOtp = $this->generateFourDigitNumber(); // Implement this function

        // Update the user's OTP in the database
        $user->otp_code = $newOtp;
        $user->save();

        // Send the new OTP to the user via email
        Mail::to($user->email)->send(new OtpMail($newOtp));

        // Send the new OTP to the user via SMS
        $message = $user->username . " Your verification code is: $newOtp. Please enter this code to verify your account.";
        $this->sendSMS($request, $newOtp, $message);

        return response()->json([
            'success' => true,
            'message' => 'New OTP sent successfully.'
        ], 200);
    }



    public function createPassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string',
            'password' => 'required|min:8',
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



    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->messages()->first()], 422);
        }

        $user = User::where('phone_number', $request->input('phone_number'))->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number not found.',
            ], 404);
        }

        // Generate a new OTP
        $newOtp = $this->generateFourDigitNumber(); // Implement this function

        Mail::to($user->email)->send(new OtpMail($newOtp));
        // Send the OTP to the user's phone number via SMS
        $message = $user->username . " Your verification code is: $newOtp. Please enter this code to verify your account.";
        $this->sendSMS($request, $newOtp, $message);

        // Update the user's password
        $user->password = Hash::make($newOtp);

        if ($user->save()) {
            return response()->json([
                'success' => true,
                'message' => 'OTP sent to the provided phone number.',
            ], 200);
        }
    }



    public function editProfile(Request $request)
    {
        $user_id = $request->id;
    
        $validator = Validator::make($request->all(), [
            'profile_image' => 'required',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone_number' => 'required',
            'email' => 'required|email',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['message' => $validator->messages()->first()], 422);
        }
    
        $user = User::find($user_id);
    
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }
    
        // Assuming you have a file input named 'profile_image' in your form
        if ($request->hasFile('profile_image')) {
            $profileImagePath = $request->file('profile_image')->store('profiles', 'public');
            // Store the relative path in the database
            $user->profile_image = $profileImagePath;
        }
    
        // Update user profile information based on the request
        $user->update([
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
            'phone_number' => $request->input('phone_number'),
            'email' => $request->input('email'),
        ]);
    
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
        ], 200);
    }
    

    function sendSMS($arg1, $arg2, $arg3)
    {

        try {
            $account_sid =  getenv('TWILIO_ACCOUNT_SID');
            $auth_token = getenv('TWILIO_AUTH_TOKEN');
            $twilio_number = getenv("TWILIO_FROM");

            $client = new Client($account_sid, $auth_token);
            $client->messages->create($arg1->phone_number, [
                'from' => $twilio_number,
                'body' => $arg3
            ]);

            // dd('SMS Sent Successfully.');
        } catch (Exception $e) {
            // dd("Error: " . $e->getMessage());
            return response()->json([
                'message' => 'This Number is not valid.',
            ], 422);
        }
    }
}
