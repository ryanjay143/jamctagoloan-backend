<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ListOfMemberController;
use App\Http\Controllers\EditMemberController;
use App\Http\Controllers\TithesController;
use App\Http\Controllers\Api\PlaylistController;
use App\Http\Controllers\Api\ObsSyncController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();

    
});


Route::apiResource('list-of-member', ListOfMemberController::class);
Route::apiResource('tithes', TithesController::class);
Route::post('expenses', [TithesController::class, 'create']);

Route::post('edit-member/{id}', [EditMemberController::class, 'edit']);

Route::get('/playlists', [PlaylistController::class, 'index']);
Route::post('/playlists/sync', [PlaylistController::class, 'sync']);
Route::post('/playlists/upload', [PlaylistController::class, 'upload']);

Route::post('/obs/update', [ObsSyncController::class, 'update']);
