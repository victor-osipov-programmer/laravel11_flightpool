<?php

use App\Http\Controllers\AirportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [UserController::class, 'store']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/airport', [AirportController::class, 'index']);
Route::get('/flight', [FlightController::class, 'index']);
Route::post('/booking', [BookingController::class, 'store']);
Route::get('/booking/{booking:code}', [BookingController::class, 'show']);
Route::get('/booking/{booking:code}/seat', [BookingController::class, 'occupied_seats']);
Route::patch('/booking/{booking:code}/seat', [BookingController::class, 'select_seat']);
Route::middleware('auth')->group(function () {
    Route::get('/user/booking', [BookingController::class, 'index']);
    Route::get('/user', [UserController::class, 'show']);
});
