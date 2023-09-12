<?php

namespace App\Http\Controllers;

use Exception;


use App\Models\User;
use App\Mail\OtpMail;
use App\Models\MovingDetails;
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
            'user_type' => 'required|in:user',
        ], [
            'phone_number.unique' => 'Phone number already in use.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->messages()->first()], 422);
        }

        $otp = $this->generateFourDigitNumber();

        $message = $request->input('username') . " Your verification code is: $otp. Please enter this code to verify your account.";
        $this->sendSMS($request, $otp, $message);

        // Create a new user
        // You'll need to define your User model and its properties
        $user = new User();
        $user->username = $request->input('username');
        $user->phone_number = $request->input('phone_number');
        $user->email = $request->input('email');
        $user->user_type = $request->input('user_type');
        $user->otp_code = $otp;
        $user->save();

        if ($request->hasFile('profile_image')) {
            $filename = 'users/' . $user->id . '/profile_image';
            $image = $request->file('profile_image');
            $path = $image->store($filename, 'public');
            $user->profile_image = 'storage/' . $path; // Added a '/' after 'storage'
        }
        
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

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Sign-in successful.',
                'token' => $token,
                'user' => $user
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
            $account_sid = getenv('TWILIO_ACCOUNT_SID');
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

    public function storeMoveDetails(Request $request)
    {
        // Validation rules for the request data
        $validator = Validator::make($request->all(), [
            'pickup_address' => 'required|string',
            'dropoff_address' => 'required|string',
            'pickup_date' => 'required|date',
            'pickup_time' => 'required',
            'item_pictures.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'detailed_description' => 'required|string',
            'pickup_property_type' => 'required|string|in:apartment,condominium',
            'pickup_bedrooms' => 'integer|nullable',
            'pickup_unit_number' => 'string|nullable',
            'pickup_elevator' => 'required|boolean',
            'pickup_flight_of_stairs' => 'integer|nullable',
            'pickup_elevator_timing_from' => 'nullable',
            'pickup_elevator_timing_to' => 'nullable',
            'dropoff_elevator' => 'boolean|nullable',
            'dropoff_flight_of_stairs' => 'integer|nullable',
            'dropoff_elevator_timing_from' => 'nullable',
            'dropoff_elevator_timing_to' => 'nullable',
            'pickup_latitude' => 'numeric|nullable',
            'pickup_longitude' => 'numeric|nullable',
            'dropoff_latitude' => 'numeric|nullable',
            'dropoff_longitude' => 'numeric|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input.',
                'errors' => $validator->errors(),
            ], 400);
        }

        // Retrieve the validated data
        $validatedData = $validator->validated();

        // Create a new MovingDetails instance with the validated data
        $delivery = new MovingDetails($validatedData);

        // Handle conditional logic based on 'pickup_property_type' and 'pickup_elevator'
        if ($delivery->pickup_property_type === 'apartment' || $delivery->pickup_property_type === 'condominium') {
            // Handle fields related to apartments and condominiums
            $delivery->pickup_bedrooms = $validatedData['pickup_bedrooms'];
            $delivery->pickup_unit_number = $validatedData['pickup_unit_number'];
        }

        if (!$delivery->pickup_elevator) {
            // Handle fields when there is no elevator
            $delivery->pickup_flight_of_stairs = $validatedData['pickup_flight_of_stairs'];
        } else {
            // Handle fields when there is an elevator
            $delivery->pickup_elevator_timing_from = $validatedData['pickup_elevator_timing_from'];
            $delivery->pickup_elevator_timing_to = $validatedData['pickup_elevator_timing_to'];
        }

        // Ensure the 'delivery' folder exists
        $deliveryFolder = public_path('delivery');
        if (!is_dir($deliveryFolder)) {
            mkdir($deliveryFolder, 0777, true);
        }
       
        // Upload item pictures to the 'delivery' folder
        $uploadedPictures = [];
        foreach ($request->file('item_pictures') as $file) {
          
            // Check if the file is an image
            if ($file->isValid() && in_array($file->getClientOriginalExtension(), ['jpeg', 'png', 'jpg', 'gif'])) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->move($deliveryFolder, $fileName);
                $uploadedPictures[] = $fileName;
            } else {
                // Handle invalid image file
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid image file(s). Please upload valid images (jpeg, png, jpg, gif).'
                ], 400);
            }
        }

        // Attach uploaded picture file names to the delivery instance
        $delivery->item_pictures = json_encode($uploadedPictures);

        // Save the delivery record to the database
        if ($delivery->save()) {
            return response()->json([
                'success' => true,
                'message' => 'Move details stored successfully.',
                'data' => $delivery
            ], 200);
        } else {
            // Handle database save failure
            return response()->json([
                'success' => false,
                'message' => 'Failed to store move details.'
            ], 500);
        }

    }
}