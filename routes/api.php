<?php

use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::middleware('api-key')->group(function () {
    Route::post('/transaction/cancel-dso-order/{dsoORder}', [TransactionController::class, 'cancelDsoOrder']);
    Route::resource('order', OrderController::class);
    Route::get('update-order', [OrderController::class, 'updateOrder']);
    Route::get('detail-order', [OrderController::class, 'detailOrder']);
});
