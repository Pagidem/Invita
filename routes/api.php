<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GuestController;
use App\Http\Controllers\Api\GuestRsvpController;
use App\Http\Controllers\Api\MesaController;
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


    Route::get('/guests/export-template', [GuestController::class, 'exportTemplate']);
    Route::post('/guests/import', [GuestController::class, 'import']);
    Route::get('/guests/by-mesa', [GuestController::class, 'byMesa']);
    Route::post('/guests/{guest}/assign-mesa', [GuestController::class, 'assignMesa']);
    Route::delete('/guests/{guest}/mesa', [GuestController::class, 'removeMesaAssignment']);
    Route::put('/mesas/{mesa}', [GuestController::class, 'updateMesa']);
    Route::apiResource('guests', GuestController::class);

    Route::get('/mesas', [MesaController::class, 'index']);

    Route::post(
    '/mesas/{mesa}/assign-guests',
    [GuestController::class, 'assignGuestsToMesa']
);

});


Route::get('/rsvp/{token}', [GuestRsvpController::class, 'show']);
Route::post('/rsvp/{token}', [GuestRsvpController::class, 'confirm']);

Route::post('/short-link', [ShortLinkController::class, 'store']);

