<?php
  
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InspectionReportController;
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
    // Route::post('/customers', [CustomerController::class, 'store']);
    // Route::get('/customer-list-list', [CustomerController::class, 'getCustomersList']);   
    // Route::post('/search-customer-list', [CustomerController::class, 'searchCustomer']); 
    // Route::post('/getsingle-customer/{id}', [CustomerController::class, 'getsingleCustomer']); 
 

    Route::prefix('customers')->group(function () {
        Route::post('/getsingle-customer/{id}', [CustomerController::class, 'getsingleCustomer']); 
        Route::post('/', [CustomerController::class, 'store']);
        Route::get('/list', [CustomerController::class, 'getCustomersList']);
        Route::post('/search', [CustomerController::class, 'searchCustomer']);
        Route::post('/{id}', [CustomerController::class, 'getsingleCustomer']);
    });



   Route::prefix('items')->group(function () {

     Route::post('/', [ItemMasterController::class, 'save']); // Create item
     Route::put('/{itemID}', [ItemMasterController::class, 'update']);
     Route::post('/search-items', [ItemMasterController::class, 'searchItems']); 
     Route::get('{id}', [ItemMasterController::class, 'show']); // Get single item
     Route::get('/', [ItemMasterController::class, 'getItemsList']);   
    });


     Route::prefix('installbase')->group(function () {
        Route::get('customers_search', [InstallBaseController::class, 'searchCustomers']); 
      ///  Route::post('get-items', [InstallBaseController::class, 'searchItems']);
         Route::post('items/search', [InstallBaseController::class, 'searchItems']);
        Route::get('/', [InstallBaseController::class, 'getInstallBase']);  
        Route::post('/', [InstallBaseController::class, 'save']); // Create item
        Route::put('/{id}', [InstallBaseController::class, 'update']); // update the records
        Route::post('/search', [InstallBaseController::class, 'searchInstallBase']); 
        Route::get('{id}', [InstallBaseController::class, 'show']); // Get single item  

         Route::get('item/{id}', [InstallBaseController::class, 'getItems']); //  
    });

    Route::prefix('inspection-report')->group(function () {
        // Route::post('search', [InspectionReportController::class, 'searchInstallbase']);
        Route::post('search', [InspectionReportController::class, 'searchInspection']);
        Route::get('{id}', [InspectionReportController::class, 'showInspectionFetch']);  

        Route::post('/save', [InspectionReportController::class, 'save']); // Create Inspetion Report
        Route::put('/{id}', [InspectionReportController::class, 'update']); // update Inspection Report 

      //  Route::prefix('inspection-report')->group(function () {
            Route::post('/save_inspection', [InspectionReportController::class, 'saveInspection']); // Create Isnpection Report 
            Route::post('/inspection-images/{id}', [InspectionReportController::class, 'showInspectionImages']); // Get the inspection images 
            Route::post('/{id}', [InspectionReportController::class, 'delete']); // delete Inspection Report
      //  });
        

    });




}); 


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');


Route::get('/hello', function () {
    return 'Hello World';
});

