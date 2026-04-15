<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/projector', function () {
    return view('projector'); // Paghimo og obs.blade.php
});