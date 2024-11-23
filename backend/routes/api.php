<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;


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
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::put('/transactions/{id}', [TransactionController::class, 'update']);
    Route::patch('/transactions/{id}', [TransactionController::class, 'patch']);
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy']);
});
