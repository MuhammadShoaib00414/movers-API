<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'pickup_address',
        'dropoff_address',
        'pickup_date',
        'pickup_time',
        'item_pictures',
        'detailed_description',
        'number_of_items',
        'heavey_weight_items',
        'pickup_property_type',
        'pickup_unit_number',
        'pickup_elevator',
        'pickup_elevator_timing_from',
        'pickup_elevator_timing_to',
        'dropoff_property_type',
        'dropoff_unit_number',
        'dropoff_elevator',
        'dropoff_elevator_timing_from',
        'dropoff_elevator_timing_to',
        'pickup_latitude',
        'pickup_longitude',
        'dropoff_latitude',
        'dropoff_longitude',
        'pickup_flight_of_stairs'
    ];
    

    // Add other properties if necessary, such as timestamps
    public $timestamps = true;

    
    public function itemPictures()
    {
        return $this->hasMany(DeliveryItemPicture::class);
    }
    
}