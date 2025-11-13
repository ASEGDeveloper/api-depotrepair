<?php
  
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InstallBaseController;
use App\Http\Controllers\ItemMasterController;
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
    Route::post('/search-cusotomers', [CustomerController::class, 'searchCustomer']); 
   Route::post('/getsingle-customer/{id}', [CustomerController::class, 'getsingleCustomer']);

   Route::prefix('items')->group(function () {

     Route::post('/', [ItemMasterController::class, 'save']); // Create item
     Route::put('/{itemID}', [ItemMasterController::class, 'update']);
     Route::post('/search-items', [ItemMasterController::class, 'searchItems']); 
     Route::get('{id}', [ItemMasterController::class, 'show']); // Get single item
     Route::get('/', [ItemMasterController::class, 'getItemsList']);  

    });


     Route::prefix('installbase')->group(function () {

     Route::get('/', [InstallBaseController::class, 'getInstallBase']);  
     Route::post('/', [InstallBaseController::class, 'save']); // Create item
     Route::put('/{id}', [InstallBaseController::class, 'update']); // update the records
     Route::post('/search', [InstallBaseController::class, 'searchInstallBase']); 
     Route::get('{id}', [InstallBaseController::class, 'show']); // Get single item
     

    });



}); 


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');


Route::get('/hello', function () {
    return 'Hello World';
});

