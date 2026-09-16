<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShortLinkController;

Route::get('/s/{code}', [ShortLinkController::class, 'redirect']);

Route::get('/{any?}', function () {
    return view('welcome');
})->where('any', '.*');
