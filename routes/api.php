<?php
 
 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\TnaEntryController;


Route::post('/login', [AuthController::class, 'login']);
Route::post('refresh', [AuthController::class, 'refresh']);


Route::get('/tna-entries', [TnaEntryController::class, 'index']);
Route::get('/tna-entries/{id}', [TnaEntryController::class, 'show']);
Route::post('/tna-entries', [TnaEntryController::class, 'store']);
Route::put('/tna-entries/{id}', [TnaEntryController::class, 'update']);
Route::delete('/tna-entries/{id}', [TnaEntryController::class, 'destroy']);



Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/employees', [AuthController::class, 'getEmployees']);     
    Route::post('/customers', [CustomerController::class, 'store']);

});




Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');


Route::get('/hello', function () {
    return 'Hello World';
});

