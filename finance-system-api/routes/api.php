<?php

use App\Http\Controllers\api\ChartOfAccountController;
use App\Http\Controllers\api\CoaCategoryController;
use App\Http\Controllers\api\ProfitLossController;
use App\Http\Controllers\api\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('api')->group(function () {
    Route::apiResource('coa-categories', CoaCategoryController::class);
    Route::apiResource('chart-of-accounts', ChartOfAccountController::class);
    Route::apiResource('transactions', TransactionController::class);
    
    // Special route untuk Profit & Loss Report
    Route::get('/profit-loss/{year_month}', [ProfitLossController::class, 'show']);
    Route::get('/profit-loss-export/{year_month}', [ProfitLossController::class, 'exportSingle']);
    Route::get('/profit-loss-export', [ProfitLossController::class, 'export']);
});