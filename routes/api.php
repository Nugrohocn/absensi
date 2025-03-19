<?php

use App\Http\Controllers\AbsensiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('clock-in', [AbsensiController::class, 'clockIn']);
Route::put('clock-out', [AbsensiController::class, 'clockOut']);
