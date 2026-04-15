<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ObsSyncController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/projector', function () {
    return view('projector');
});

// Kini nga duha gamiton sa JS sulod sa projector view
Route::get('/obs-latest', [ObsSyncController::class, 'latest']);
Route::get('/obs-stream', [ObsSyncController::class, 'stream']);
