<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Passenger extends Model
{
    /** @use HasFactory<\Database\Factories\PassengerFactory> */
    use HasFactory;


    protected $guarded = [];


    function booking() {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}
