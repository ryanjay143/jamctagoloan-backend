<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ListOfMemberController;
use App\Http\Controllers\EditMemberController;
use App\Http\Controllers\TithesController;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();

    
});


Route::apiResource('list-of-member', ListOfMemberController::class);
Route::apiResource('tithes', TithesController::class);
Route::post('expenses', [TithesController::class, 'create']);

Route::post('edit-member/{id}', [EditMemberController::class, 'edit']);



