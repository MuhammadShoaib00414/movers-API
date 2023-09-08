<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryItemPicture extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_detail_id',
        'item_picture_path',
    ];

   
    public function deliveryDetail()
{
    return $this->belongsTo(DeliveryDetail::class);
}

}