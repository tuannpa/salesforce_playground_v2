<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\CampaignController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::controller(ContactController::class)->group(function () {
        Route::get('contacts', 'getList');
        Route::patch('contacts/{id}', 'update');
    });

    Route::controller(HomeController::class)->group(function () {
        Route::post('salesforce/connect', 'connectSFDCAccount');
        Route::post('salesforce/logout', 'logoutSalesforceAccount');
    });

    Route::controller(LeadController::class)->group(function () {
        Route::get('leads', 'getList');
        Route::post('leads/export', 'exportData');
        Route::post('leads/export/{exportId}/result', 'getExportResult');
    });

    Route::controller(CampaignController::class)->group(function () {
        Route::get('campaigns', 'getList');
        Route::get('campaigns/{campaignId}/chart', 'getChartData');
    });
});
