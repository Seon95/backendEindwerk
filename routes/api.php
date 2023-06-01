<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// public routes

// new user registeration
Route::post('/register', [AuthController::class, 'register']);
// user login and token creation
Route::post('/login', [AuthController::class, 'login']);
// get all users
Route::get('/users', [AuthController::class, 'index']);
// get specified user by id
Route::get('/users/{id}', [AuthController::class, 'show']);
// search user by name
Route::get('/users/search/{name}', [AuthController::class, 'search']);

// protected routes

Route::group(['middleware' => ['auth:sanctum']], function () {
    // items routes
    Route::post('/users/items/{id}', [ItemsController::class, 'item_post'])->middleware('check_user_ownership');
    Route::put('/users/{id}/items/{item_id}', [ItemsController::class, 'update'])->middleware('check_user_ownership');
    Route::delete('/users/{id}/items/{item_id}', [ItemsController::class, 'destroy'])->middleware('check_user_ownership');
    // user routes
    Route::delete('/users/{id}', [AuthController::class, 'destroy'])->middleware('check_user_ownership');
    Route::post('/users/{id}/pic', [AuthController::class, 'storeImage'])->middleware('check_user_ownership');
    Route::put('/users/{id}/pass', [AuthController::class, 'updatePassword'])->middleware('check_user_ownership');
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
