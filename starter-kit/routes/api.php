<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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

/*FIXME - Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
*/

//REVIEW -  -    Route::post('register', [AuthController::class, 'register']);

Route::group(['prefix' => 'vtiger'], function () {
  Route::post('login', [AuthController::class, 'login']);
  Route::post('logout', [AuthController::class, 'logout']);
  Route::post('nav-items', [AuthController::class, 'fetchModules']);
  Route::post('add-card', [AuthController::class, 'addCard']);
  Route::post('add-stripe-card', [AuthController::class, 'addStripeCard']);
  Route::post('delete-card', [AuthController::class, 'deleteStripeCard']);
  //Route::post('invoices', [AuthController::class, 'fetchInvoicesPost']);
  Route::get('invoices', [AuthController::class, 'fetchInvoices']);
  Route::get('locations', [AuthController::class, 'fetchLocationsWithChargers']);
  Route::get('creditcards', [AuthController::class, 'fetchCreditCards']);
  Route::get('products', [AuthController::class, 'fetchProducts']);
  Route::get('services', [AuthController::class, 'fetchServices']);
  Route::get('plan-info', [AuthController::class, 'fetchCurrentPlan']);
  Route::get('getstripekey', [AuthController::class, 'getStripeKey']);
  Route::get('createpaymentintent', [AuthController::class, 'createPaymentIntent']);
  Route::get('createsetupintent', [AuthController::class, 'createSetupIntent']);
  
  Route::group(['middleware' => 'auth:vtiger'], function () {
    Route::get('user', [AuthController::class, 'user']);
  });
});
