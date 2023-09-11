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
            'pickup_address' => 'required|array|between:1,3',
            'dropoff_address' => 'required|array|between:1,3',
            'pickup_date' => 'required|string',
            'pickup_time' => 'required|string',
            'detailed_description' => 'required|string',
            'number_of_items' => 'required|string',
            'heavey_weight_items' => 'boolean',
            'pickup_property_type' => ['required', 'in:apartment,condominium'],
            'pickup_unit_number' => 'nullable|string',
            'pickup_elevator' => 'boolean',
            // 'pickup_flight_of_stairs' => 'null',
            // 'dropoff_flight_of_stairs' => 'required',
            'pickup_elevator_timing_from' => 'required',
            'pickup_elevator_timing_to' => 'required',
            'dropoff_property_type' => ['required', 'in:apartment,condominium'],
            'dropoff_unit_number' => 'required|integer',
            'dropoff_elevator' => 'boolean',
            'dropoff_elevator_timing_from' => 'required|string',
            'dropoff_elevator_timing_to' => 'required|string',
            'pickup_latitude' => 'required',
            'pickup_longitude' => 'required',
            'dropoff_latitude' => 'required',
            'dropoff_longitude' => 'required',
            'item_pictures.*' => 'required', // Adjust the file types and size as needed
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->messages()->first()], 422);
        }
        // Create an array to store all the delivery details
        $deliveries = [];
        // foreach ($request->pickup_address as $key => $pickupAddress) {
            // Create a new DeliveryDetail instance for each set of pickup and dropoff addresses
            $delivery = new DeliveryDetail();
            $delivery->pickup_address = json_encode($request->pickup_address);
            $delivery->dropoff_address = json_encode($request->dropoff_address);
            $delivery->pickup_date = $request->pickup_date;
            $delivery->pickup_time = $request->pickup_time;
            $delivery->detailed_description = $request->detailed_description;
            $delivery->number_of_items = $request->number_of_items;
            $delivery->heavey_weight_items = $request->heavey_weight_items;
            $delivery->pickup_property_type = $request->pickup_property_type;
            if ($delivery->pickup_property_type === 'apartment' || $delivery->pickup_property_type === 'condominium') {
                $delivery->pickup_unit_number = $request->pickup_unit_number;
            }
            $delivery->pickup_elevator = $request->pickup_elevator;
            // dd($request->pickup_elevator);
            if ($request->pickup_elevator == 0) {
                $delivery->pickup_flight_of_stairs = $request->pickup_flight_of_stairs;
            } else {
                $delivery->pickup_elevator_timing_from = $request->pickup_elevator_timing_from;
                $delivery->pickup_elevator_timing_to = $request->pickup_elevator_timing_to;
            }
            $delivery->dropoff_property_type = $request->dropoff_property_type;
            if ($delivery->dropoff_property_type === 'apartment' || $delivery->dropoff_property_type === 'condominium') {
                $delivery->dropoff_unit_number = $request->dropoff_unit_number;
            }
            $delivery->dropoff_elevator = $request->dropoff_elevator;
            if ($request->dropoff_elevator == 0) {
                $delivery->dropoff_flight_of_stairs = $request->dropoff_flight_of_stairs;
            } else {
                $delivery->dropoff_elevator_timing_from = $request->dropoff_elevator_timing_from;
                $delivery->dropoff_elevator_timing_to = $request->dropoff_elevator_timing_to;
            }

            $delivery->pickup_latitude = json_encode($request->pickup_latitude);
            $delivery->pickup_longitude = json_encode($request->pickup_longitude);
            $delivery->dropoff_latitude = json_encode($request->dropoff_latitude);
            $delivery->dropoff_longitude = json_encode($request->dropoff_longitude);

            // Save the DeliveryDetail record to the database
            if (!$delivery->save()) {
                // Handle database save failure
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to store move details.',
                ], 200);
            }

            // Handle item pictures
            if ($request->hasFile('item_pictures')) {
                $itemPictures = [];
                foreach ($request->file('item_pictures') as $file) {
                    // Check if the file is an image
                   
                    if ($file->isValid() && in_array($file->getClientOriginalExtension(), ['jpeg', 'png', 'jpg', 'gif'])) {
                        $fileName = time() . '_' . $file->getClientOriginalName();
                        $file->move(public_path('deliveryImages'), $fileName);

                        // Create a new DeliveryItemPicture record and associate it with the delivery
                        $itemPicture = new DeliveryItemPicture([
                            'item_picture_path' => $fileName,
                        ]);

                        $delivery->itemPictures()->save($itemPicture);

                        $itemPictures[] = $itemPicture;
                    } else {
                        // Handle invalid image file
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid image file(s). Please upload valid images (jpeg, png, jpg, gif).',
                        ], 400);
                    }
                }

                // Attach uploaded picture file names to the delivery instance
                $delivery->itemPictures()->saveMany($itemPictures);
            }

            $deliveries[] = $delivery;
        // }

        return response()->json([
            'success' => true,
            'message' => 'Move details stored successfully.',
            'data' => $deliveries,
        ], 200);
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
