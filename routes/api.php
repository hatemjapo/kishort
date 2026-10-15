<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ShortLinkController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/links/{id}', [ShortLinkController::class, 'show']);
    Route::put('/links/{id}', [ShortLinkController::class, 'update']);
    Route::delete('/links/{id}', [ShortLinkController::class, 'destroy']);
    Route::get('/links', [ShortLinkController::class, 'index']);

    Route::post('/links', [ShortLinkController::class, 'store']);

});
