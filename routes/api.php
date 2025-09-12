<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('refresh', [AuthController::class, 'refresh']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/employees', [AuthController::class, 'getEmployees']);
    // Other protected routes
});




Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');


Route::get('/hello', function () {
    return 'Hello World';
});

