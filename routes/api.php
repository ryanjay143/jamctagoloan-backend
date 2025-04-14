<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ListOfMemberController;
use App\Http\Controllers\EditMemberController;



Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();

    
});


Route::apiResource('list-of-member', ListOfMemberController::class);

Route::post('edit-member/{id}', [EditMemberController::class, 'edit']);



