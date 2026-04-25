<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ListOfMemberController;
use App\Http\Controllers\EditMemberController;
use App\Http\Controllers\TithesController;
use App\Http\Controllers\Api\PlaylistController;
use App\Http\Controllers\Api\ObsSyncController;
use App\Http\Controllers\Api\ObsStateController;
use App\Http\Controllers\Api\BackgroundVideoController;
use App\Http\Controllers\Api\PptPresentationController;

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
Route::post('/playlists/fetch-song-resources', [PlaylistController::class, 'fetchSongResources']);

Route::get('/obs-state', [ObsStateController::class, 'show']);
Route::get('/obs-state/stream', [ObsStateController::class, 'stream']);
Route::post('/obs-state', [ObsStateController::class, 'update']);

Route::post('/background-videos/upload', [BackgroundVideoController::class, 'upload']);
Route::post('/background-videos/delete', [BackgroundVideoController::class, 'delete']);

Route::get('/ppt-presentations', [PptPresentationController::class, 'index']);
Route::post('/ppt-presentations/sync', [PptPresentationController::class, 'sync']);
