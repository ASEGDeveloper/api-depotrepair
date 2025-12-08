<?php

use App\Http\Controllers\InspectionReportController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/send-test-mail', function () {
    try {
        $message = "Laravel mail test working!";

        Mail::to("h.hariy2k@gmail.com")->send(new TestMail($message));

        return "Test email sent successfully!";
    } catch (\Exception $e) {
        return "Mail sending failed: " . $e->getMessage();
    }
});


Route::get('/test-download-pdf/{id}', [InspectionReportController::class, 'WebdownloadReport']);
