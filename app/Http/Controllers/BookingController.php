<?php

namespace App\Http\Controllers;

use App\Actions\GetFlightPassengersNumber;
use App\Models\Booking;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Models\Flight;
use App\Models\Passenger;
use App\Models\User;
use App\Utils\Random;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;


class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $passengers = Passenger::select(['id', 'booking_id'])->with('booking')->where('document_number', $user->document_number)->get();
        $bookings = $passengers->pluck('booking');

        return [
            'data' => [
                'items' => $bookings->map(fn ($booking) => [
                    'code' => $booking->code,
                    'cost' => ($booking->from->cost + $booking->back->cost) * $booking->passengers->count(),
                    'flights' => [
                        [
                            'flight_id' => $booking->from->id,
                            'flight_code' => $booking->from->flight_code,
                            'from' => [
                                'city' => $booking->from->from_airport->city,
                                'airport' => $booking->from->from_airport->name,
                                'iata' => $booking->from->from_airport->iata,
                                'date' => $booking->date_from,
                                'time' => (new Carbon($booking->from->time_from))->format('H:i')
                            ],
                            'to' => [
                                'city' => $booking->from->to_airport->city,
                                'airport' => $booking->from->to_airport->name,
                                'iata' => $booking->from->to_airport->iata,
                                'date' => $booking->date_from,
                                'time' => (new Carbon($booking->from->time_to))->format('H:i')
                            ],
                            'cost' => $booking->from->cost,
                            'availability' => 58,
                        ],
                        [
                            'flight_id' => $booking->back->id,
                            'flight_code' => $booking->back->flight_code,
                            'from' => [
                                'city' => $booking->back->from_airport->city,
                                'airport' => $booking->back->from_airport->name,
                                'iata' => $booking->back->from_airport->iata,
                                'date' => $booking->date_back,
                                'time' => (new Carbon($booking->back->time_from))->format('H:i')
                            ],
                            'to' => [
                                'city' => $booking->back->to_airport->city,
                                'airport' => $booking->back->to_airport->name,
                                'iata' => $booking->back->to_airport->iata,
                                'date' => $booking->date_back,
                                'time' => (new Carbon($booking->back->time_to))->format('H:i')
                            ],
                            'cost' => $booking->back->cost,
                            'availability' => 58,
                        ]
                    ],
                    'passengers' => $booking->passengers
                    ->map(fn ($passenger) => [
                        'id' => $passenger->id, 
                        'first_name' => $passenger->first_name, 
                        'last_name' => $passenger->last_name, 
                        'birth_date' => $passenger->birth_date, 
                        'document_number' => $passenger->document_number, 
                        'place_from' => $passenger->place_from, 
                        'place_back' => $passenger->place_back, 
                    ])
                ])
            ]
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookingRequest $request, Random $random)
    {
        $data = $request->validated();
        $flight_from = Flight::find($data['flight_from']['id']);
        $flight_back = Flight::find($data['flight_back']['id']);
        
        $flight_from->load([
            'bookings_from' => fn ($query) => $query->withCount('passengers'),
            'bookings_back' => fn ($query) => $query->withCount('passengers')
        ]);
        $flight_back->load([
            'bookings_from' => fn ($query) => $query->withCount('passengers'),
            'bookings_back' => fn ($query) => $query->withCount('passengers')
        ]);

        $flight_from_bookings = $flight_from->get_bookings($data['flight_from']['date']);
        $flight_back_bookings = $flight_back->get_bookings($data['flight_back']['date']);
        $flight_from_num_passengers = $flight_from_bookings->pluck('passengers_count')->sum();
        $flight_back_num_passengers = $flight_back_bookings->pluck('passengers_count')->sum();
        $new_code = $random->unique_code(Booking::count());

        if ($flight_from_num_passengers + count($data['passengers']) > Flight::$NUMBER_SEATS) {
            return response([
                'error' => [
                    'code' => 422,
                    'message' => 'There are no available seats on flight_from'
                ]
            ], 422);
        }
        if ($flight_back_num_passengers + count($data['passengers']) > Flight::$NUMBER_SEATS) {
            return response([
                'error' => [
                    'code' => 422,
                    'message' => 'There are no available seats on flight_back'
                ]
            ], 422);
        }

        DB::transaction(function () use ($data, $new_code) {
            $new_booking = Booking::create([
                'flight_from' => $data['flight_from']['id'],
                'flight_back' => $data['flight_back']['id'],
                'date_from' => $data['flight_from']['date'],
                'date_back' => $data['flight_back']['date'],
                'code' => $new_code
            ]);

            Passenger::insert(collect($data['passengers'])->map(fn ($item) => [...$item, 'booking_id' => $new_booking->id])->all());
        });

        return response([
            'data' => [
                'code' => $new_code
            ]
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking)
    {
        return [
            'data' => [
                'code' => $booking->code,
                'cost' => ($booking->from->cost + $booking->back->cost) * $booking->passengers->count(),
                'flights' => [
                    [
                        'flight_id' => $booking->from->id,
                        'flight_code' => $booking->from->flight_code,
                        'from' => [
                            'city' => $booking->from->from_airport->city,
                            'airport' => $booking->from->from_airport->name,
                            'iata' => $booking->from->from_airport->iata,
                            'date' => $booking->date_from,
                            'time' => (new Carbon($booking->from->time_from))->format('H:i')
                        ],
                        'to' => [
                            'city' => $booking->from->to_airport->city,
                            'airport' => $booking->from->to_airport->name,
                            'iata' => $booking->from->to_airport->iata,
                            'date' => $booking->date_from,
                            'time' => (new Carbon($booking->from->time_to))->format('H:i')
                        ],
                        'cost' => $booking->from->cost,
                        'availability' => 56,
                    ],
                    [
                        'flight_id' => $booking->back->id,
                        'flight_code' => $booking->back->flight_code,
                        'from' => [
                            'city' => $booking->back->from_airport->city,
                            'airport' => $booking->back->from_airport->name,
                            'iata' => $booking->back->from_airport->iata,
                            'date' => $booking->date_back,
                            'time' => (new Carbon($booking->back->time_from))->format('H:i')
                        ],
                        'to' => [
                            'city' => $booking->back->to_airport->city,
                            'airport' => $booking->back->to_airport->name,
                            'iata' => $booking->back->to_airport->iata,
                            'date' => $booking->date_back,
                            'time' => (new Carbon($booking->back->time_to))->format('H:i')
                        ],
                        'cost' => $booking->back->cost,
                        'availability' => 56,
                    ]
                ],
                'passengers' => $booking->passengers
                ->map(fn ($passenger) => [
                    'id' => $passenger->id, 
                    'first_name' => $passenger->first_name, 
                    'last_name' => $passenger->last_name, 
                    'birth_date' => $passenger->birth_date, 
                    'document_number' => $passenger->document_number, 
                    'place_from' => $passenger->place_from, 
                    'place_back' => $passenger->place_back, 
                ])
            ]
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookingRequest $request, Booking $booking)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking)
    {
        //
    }


    function occupied_seats(Booking $booking) {
        return [
            'data' => [
                'occupied_from' => $booking->passengers
                ->filter(fn ($passenger) => isset($passenger->place_from))
                ->values()
                ->map(fn ($passenger) => [
                    'passenger_id' => $passenger->id,
                    'place' => $passenger->place_from,
                ]),
                'occupied_back' => $booking->passengers
                ->filter(fn ($passenger) => isset($passenger->place_back))
                ->values()
                ->map(fn ($passenger) => [
                    'passenger_id' => $passenger->id,
                    'place' => $passenger->place_back,
                ])
            ]
        ];
    }

    function select_seat(Request $request, Booking $booking) {
        $data = $request->validate([
            'passenger' => ['required', Rule::exists('passengers', 'id')],
            'seat' => ['required', 'string'],
            'type' => ['required', Rule::in(['from', 'back'])],
        ]);

        $place_type = 'place_' . $data['type'];

        $passenger_in_booking = $booking->passengers->filter(fn ($passenger) => $passenger->id == $data['passenger'])->first();

        if (!isset($passenger_in_booking)) {
            return response([
                'error' => [
                    'code' => 403,
                    'message' => 'Passenger does not apply to booking'
                ]
            ], 403);
        }

        $is_occupied = $booking->passengers->contains(
            fn ($passenger) => $passenger->$place_type == $data['seat']
        );

        if ($is_occupied) {
            return response([
                'error' => [
                    'code' => 422,
                    'message' => 'Seat is occupied'
                ]
            ], 422);
        }
        
        $passenger_in_booking->update([
            $place_type => $data['seat']
        ]);

        return [
            'id' => $passenger_in_booking->id, 
            'first_name' => $passenger_in_booking->first_name, 
            'last_name' => $passenger_in_booking->last_name, 
            'birth_date' => $passenger_in_booking->birth_date, 
            'document_number' => $passenger_in_booking->document_number, 
            'place_from' => $passenger_in_booking->place_from, 
            'place_back' => $passenger_in_booking->place_back, 
        ];
    }
}
