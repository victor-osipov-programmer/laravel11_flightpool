<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory;

    protected $guarded = [];

    function passengers() {
        return $this->hasMany(Passenger::class, 'booking_id');
    }

    function from() {
        return $this->belongsTo(Flight::class, 'flight_from');
    }
    function back() {
        return $this->belongsTo(Flight::class, 'flight_back');
    }

    // function flights() {
    //     return $this->hasMany(Flight::class, '')
    // }
}
