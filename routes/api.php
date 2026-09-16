<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GuestController;
use App\Http\Controllers\Api\GuestRsvpController;
use App\Http\Controllers\ShortLinkController;

/*Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');*/

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function(Request $request){
        return response()->json(
            $request->user()
        );
    });

    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    Route::post('/logout',[AuthController::class, 'logout']);


    Route::apiResource('guests', GuestController::class);

    
});


Route::get('/rsvp/{token}', [GuestRsvpController::class, 'show']);
Route::post('/rsvp/{token}', [GuestRsvpController::class, 'confirm']);

Route::post('/short-link', [ShortLinkController::class, 'store']);

