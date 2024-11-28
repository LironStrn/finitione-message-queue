<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QueueController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/queue/instant', [QueueController::class, 'insertInstant']);
Route::post('/queue/delayed', [QueueController::class, 'insertDelayed']);
Route::get('/queue/status', [QueueController::class, 'getQueueStatus']);
