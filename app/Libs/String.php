<?php

namespace App;

use App\Models\Flight;



class Libs {
    function getFlightPassengersNumber(Flight $flight) {
        return array_sum([...$flight->bookings_from->pluck('passengers_count'), ...$flight->bookings_back->pluck('passengers_count')]);
    }
}