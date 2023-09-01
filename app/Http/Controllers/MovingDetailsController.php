<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\MovingDetails;
use GoogleMaps\GoogleMaps;
use Illuminate\Support\Facades\Validator;
class MovingDetailsController extends Controller
{
    public function storeMoveDetails(Request $request)
    {
       dd('Test');
        // Validate the incoming request data
        $validatedData = $request->validate([
            'pickup_address' => 'nullable|string',
            'dropoff_address' => 'nullable|string',
            'pickup_date' => 'nullable|string',
            'pickup_time' => 'nullable|string',
            'item_pictures' => 'nullable|string',
            'detailed_description' => 'nullable|string',
            'pickup_property_type' => 'nullable|in:apartment,condominium',
            'pickup_unit_number' => 'nullable|string',
            'pickup_bedrooms' => 'nullable|integer',
            'pickup_elevator' => 'nullable|boolean',
            'pickup_flight_of_stairs' => 'nullable|integer',
            'pickup_elevator_timing_from' => 'nullable|integer',
            'pickup_elevator_timing_to' => 'nullable|integer',
            'dropoff_elevator' => 'nullable|boolean',
            'dropoff_flight_of_stairs' => 'nullable|integer',
            'dropoff_elevator_timing_from' => 'nullable|integer',
            'dropoff_elevator_timing_to' => 'nullable|integer',
        ]);


        // Initialize Google Maps client
    //    $googleMaps = new GoogleMaps(['key' => 'YOUR_GOOGLE_MAPS_API_KEY']);

      
    //     $pickupAddress = $validatedData['pickup_address'];
    //     $pickupCoordinates = $googleMaps->geocode($pickupAddress)->first();

      
    //     $dropoffAddress = $validatedData['dropoff_address'];
    //     $dropoffCoordinates = $googleMaps->geocode($dropoffAddress)->first();

       
    //     $validatedData['pickup_latitude'] = $pickupCoordinates['geometry']['location']['lat'];
    //     $validatedData['pickup_longitude'] = $pickupCoordinates['geometry']['location']['lng'];
    //     $validatedData['dropoff_latitude'] = $dropoffCoordinates['geometry']['location']['lat'];
    //     $validatedData['dropoff_longitude'] = $dropoffCoordinates['geometry']['location']['lng'];

        // Create and store the data
        $moveDetails = MovingDetails::create($validatedData);

        if ($moveDetails) {
            return response()->json([
                'success' => true,
                'message' => 'Move details stored successfully.',
                'data' => $moveDetails
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to store move details.'
        ], 500);

    }
}
