<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\StreamController;
use App\Http\Controllers\Api\VideoController;
use App\Http\Controllers\Api\V1\SubscriptionController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});


Route::middleware('auth:sanctum')->group(function () {

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

});


Route::middleware(['auth:sanctum', 'admin'])->group(function () {

    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

});


Route::prefix('stream')->group(function () {
    Route::get('/streams', [StreamController::class, 'index']);
    Route::get('/streams/{stream}', [StreamController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/streams', [StreamController::class, 'store']);

        Route::middleware('stream.owner')->group(function () {
            Route::put('/streams/{stream}', [StreamController::class, 'update']);
            Route::patch('/streams/{stream}/end', [StreamController::class, 'end']);
        });
    });
});


Route::prefix('video')->group(function () {

    Route::get('/videos', [VideoController::class, 'index']);
    Route::get('/videos/{video}', [VideoController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/videos', [VideoController::class, 'store']);

        Route::middleware('video.owner')->group(function () {
            Route::delete('/videos/{video}', [VideoController::class, 'destroy']);
        });
    });
});


Route::prefix('subscrip')->group(function () {
    Route::get('/users/{user}/followers', [SubscriptionController::class, 'followers']);
    Route::get('/users/{user}/following', [SubscriptionController::class, 'following']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/subscriptions/follow', [SubscriptionController::class, 'follow']);
        Route::delete('/subscriptions/{user}/unfollow', [SubscriptionController::class, 'unfollow']);
    });
});
