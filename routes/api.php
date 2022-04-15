<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
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


//User
Route::group(['prefix' => 'user'], function () {
    Route::post('register', [UserController::class, 'register']);
    Route::post('login', [UserController::class, 'login']);
    Route::post('logout', [UserController::class, 'logout']);
    Route::middleware('auth:api')->get('profile', [UserController::class, 'profile']);
});


//Post
Route::post('post', [PostController::class, 'create'])->middleware('auth:api');
Route::put('post', [PostController::class, 'update'])->middleware('auth:api');
Route::delete('post', [PostController::class, 'destroy'])->middleware('auth:api');
Route::get('post', [PostController::class, 'get']);

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('details', 'API\UserController@details');
});
