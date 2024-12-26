<?php

namespace App\Http\Controllers;

use App\Actions\GetFlightPassengersNumber;
use App\Models\Flight;
use App\Http\Requests\StoreFlightRequest;
use App\Http\Requests\UpdateFlightRequest;
use App\Models\Airport;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;




class FlightController extends Controller
{
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = $request->validate([
            'from' => ['required', Rule::exists('airports', 'iata')],
            'to' => ['required', Rule::exists('airports', 'iata')],
            'date1' => ['required', 'date_format:Y-m-d'],
            'date2' => ['nullable', 'date_format:Y-m-d'],
            'passengers' => ['nullable', 'integer', 'min:1', 'max:8'],
        ]);
        $from_airport = Airport::where('iata', $data['from'])->first();
        $to_airport = Airport::where('iata', $data['to'])->first();

        $flights_to = Flight::where('from_id', $from_airport->id)
        ->where('to_id', $to_airport->id)
        ->get();

        $flights_back = Flight::where('from_id', $to_airport->id)
        ->where('to_id', $from_airport->id)
        ->get();

        return [
            'data' => [
                'flights_to' => $flights_to
                ->values()
                ->map(fn ($flight) => [
                    'flight_id' => $flight->id,
                    'flight_code' => $flight->flight_code,
                    'from' => [
                        'city' => $flight->from_airport->city,
                        'airport' => $flight->from_airport->name,
                        'iata' => $flight->from_airport->iata,
                        'date' => $data['date1'],
                        'time' => (new Carbon($flight->time_from))->format('H:i')
                    ],
                    'to' => [
                        'city' => $flight->to_airport->city,
                        'airport' => $flight->to_airport->name,
                        'iata' => $flight->to_airport->iata,
                        'date' => $data['date1'],
                        'time' => (new Carbon($flight->time_to))->format('H:i')
                    ],
                    'cost' => $flight->cost,
                    'availability' => 156,
                ]),
                'flights_back' => $flights_back
                ->values()
                ->map(fn ($flight) => [
                    'flight_id' => $flight->id,
                    'flight_code' => $flight->flight_code,
                    'from' => [
                        'city' => $flight->from_airport->city,
                        'airport' => $flight->from_airport->name,
                        'iata' => $flight->from_airport->iata,
                        'date' => $data['date2'],
                        'time' => (new Carbon($flight->time_from))->format('H:i')
                    ],
                    'to' => [
                        'city' => $flight->to_airport->city,
                        'airport' => $flight->to_airport->name,
                        'iata' => $flight->to_airport->iata,
                        'date' => $data['date2'],
                        'time' => (new Carbon($flight->time_to))->format('H:i')
                    ],
                    'cost' => $flight->cost,
                    'availability' => 156,
                ])
            ]
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFlightRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Flight $flight)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFlightRequest $request, Flight $flight)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Flight $flight)
    {
        //
    }
}
