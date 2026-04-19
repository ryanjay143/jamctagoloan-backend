<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ObsSyncController;

Route::get('/', function () {
    return view('welcome');
});
