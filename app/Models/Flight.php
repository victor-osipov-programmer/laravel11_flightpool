<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    /** @use HasFactory<\Database\Factories\FlightFactory> */
    use HasFactory;

    protected $guarded = [];

    static public $NUMBER_SEATS = 25;

    function to_airport() {
        return $this->belongsTo(Airport::class, 'to_id');
    }
    function from_airport() {
        return $this->belongsTo(Airport::class, 'from_id');
    }

    function bookings_from() {
        return $this->hasMany(Booking::class, 'flight_from');
    }
    function bookings_back() {
        return $this->hasMany(Booking::class, 'flight_back');
    }

    function get_bookings(string $date) {
        return Booking::withCount('passengers')
        ->where(function ($query) use ($date) {
            $query->where('flight_from', $this->id)->where('date_from', $date);
        })
        ->orWhere(function ($query) use ($date) {
            $query->where('flight_back', $this->id)->where('date_back', $date);
        })
        ->get();
    }
}
