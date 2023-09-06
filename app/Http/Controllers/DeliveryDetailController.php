<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DeliveryDetail;
use App\Models\DeliveryItemPicture;
use Illuminate\Support\Facades\Validator;

class DeliveryDetailController extends Controller
{   

    public function storeDeliveryDetails(Request $request)
{
    
    // Validation rules for the request data
    $validator = Validator::make($request->all(), [
        'pickup_address' => 'required|json',
        'dropoff_address' => 'required|json',
        'pickup_date' => 'required|date',
        'pickup_time' => 'required',
        'item_pictures.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
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
        return response()->json(['message' => $validator->messages()->first()], 422);
    }

    // Retrieve the validated data
    $validatedData = $validator->validated();

    // Create a new DeliveryDetail instance with the validated data
    $delivery = new DeliveryDetail($validatedData);
   
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
    // $delivery->item_pictures = json_encode($uploadedPictures);
    $delivery->save();
    // Upload item pictures to the 'delivery' folder and associate them with the delivery
    $uploadedPictures = [];
    foreach ($request->file('item_pictures') as $file) {
      
        // Check if the file is an image
        if ($file->isValid() && in_array($file->getClientOriginalExtension(), ['jpeg', 'png', 'jpg', 'gif'])) {
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move($deliveryFolder, $fileName);
            $uploadedPictures[] = $fileName;

            // Create a new DeliveryItemPicture instance and associate it with the delivery
            $itemPicture = new DeliveryItemPicture([
                'item_picture_path' => $fileName,
                'delivery_detail_id' => $delivery->id 
            ]);
            // dd($);
            $itemPicture->save();
            // $delivery->itemPictures()->save($itemPicture);
        } else {
            // Handle invalid image file
            $delivery->delete(); // Rollback the delivery_detail record
            return response()->json([
                'success' => false,
                'message' => 'Invalid image file(s). Please upload valid images (jpeg, png, jpg, gif).'
            ], 400);
        }
    }

    // Attach uploaded picture file names to the delivery instance
    

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
        ], 200);
    }
}


    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DeliveryDetail  $deliveryDetail
     * @return \Illuminate\Http\Response
     */
    public function show(DeliveryDetail $deliveryDetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DeliveryDetail  $deliveryDetail
     * @return \Illuminate\Http\Response
     */
    public function edit(DeliveryDetail $deliveryDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DeliveryDetail  $deliveryDetail
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DeliveryDetail $deliveryDetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DeliveryDetail  $deliveryDetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeliveryDetail $deliveryDetail)
    {
        //
    }
}
