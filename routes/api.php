<?php
  
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\TnaEntryController;
use Tests\Feature\TnaControllerTest;

Route::post('/login', [AuthController::class, 'login']);
Route::post('refresh', [AuthController::class, 'refresh']);


Route::middleware('api.token')->post('/tna-entries', [TnaEntryController::class, 'createOrUpdateTNAEntry']); 

Route::post('/test-tna', [TnaControllerTest::class, 'createOrUpdateTNAEntry']);



Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/employees', [AuthController::class, 'getEmployees']);     
    Route::post('/customers', [CustomerController::class, 'store']);
    Route::get('/cusotomers-list', [CustomerController::class, 'getCustomersList']);     


});




Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');


Route::get('/hello', function () {
    return 'Hello World';
});

