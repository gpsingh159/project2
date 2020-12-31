<?php

use App\Http\Controllers\API\v1\User\AuthController;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group([
    'namespace' => 'API\v1\User',
    'prefix' => 'v1',

], function () {
    Route::post('/user/signup', [AuthController::class, 'signup']);
    Route::post('/user/login', [AuthController::class, 'login']);
});

Route::group([
    'namespace' => 'API\v1\User',
    'prefix' => 'v1',
    'middleware' =>'auth:api',

], function () {
    Route::post('/user/me', [AuthController::class, 'me']);
    Route::post('/user/logout', [AuthController::class, 'logout']);
});