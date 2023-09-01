<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovingDetails extends Model
{
    protected $fillable = [
        'pickup_address', 'dropoff_address', 'pickup_date', 'pickup_time',
        'item_pictures', 'detailed_description', 'pickup_property_type',
        'pickup_unit_number', 'pickup_bedrooms', 'pickup_elevator',
        'pickup_flight_of_stairs', 'pickup_elevator_timing_from',
        'pickup_elevator_timing_to', 'dropoff_elevator',
        'dropoff_flight_of_stairs', 'dropoff_elevator_timing_from',
        'dropoff_elevator_timing_to','pickup_latitude','pickup_longitude','dropoff_latitude','dropoff_longitude'
    ];

    // Add any additional methods or relationships here
}
