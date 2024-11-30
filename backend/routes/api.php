<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DraftController;


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

Route::middleware(['api', 'web'])->post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/request-password-reset', [AuthController::class, 'requestPasswordReset']);
Route::put('/user/password-with-token', [AuthController::class, 'resetPasswordWithToken']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::get('/types', [TransactionController::class, 'getTypesEnumOptions']);
    Route::get('/parties', [TransactionController::class, 'getParties']);
    Route::get('/users', [TransactionController::class, 'getUsers']);
    Route::get('/payment-methods', [TransactionController::class, 'getPaymentMethodsEnumOptions']);
    Route::get('/tags', [TransactionController::class, 'getTagsEnumOptions']);
    Route::get('/recurrence-types', [TransactionController::class, 'getRecurrenceTypesEnumOptions']);
    Route::get('/drafts', [DraftController::class, 'index']);
    Route::get('/drafts/all', [DraftController::class, 'all']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::post('/drafts', [DraftController::class, 'store']);
    Route::post('/parties', [TransactionController::class, 'postParty']);
    Route::post('/update-transaction/{id}', [TransactionController::class, 'update']);
    Route::post('/update-draft/{id}', [DraftController::class, 'update']);
    Route::post('/populate-month', [DraftController::class, 'populateMonth']);
    Route::patch('/transactions/{id}', [TransactionController::class, 'patch']);
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);
});
