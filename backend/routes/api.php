<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\StreamController;
use App\Http\Controllers\Api\VideoController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\ReactionController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\Admin\StatisticController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CommentController;




Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware(['auth:sanctum','user.banned'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});


Route::prefix('/profile')->middleware(['auth:sanctum','user.banned'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'showMyProfile']);
    Route::get('/profile/{user}', [ProfileController::class, 'show']);

    Route::middleware('profile.owner')->group(function () {
        Route::put('/profile', [ProfileController::class, 'update']);
    });
});


Route::prefix('subscrip')->group(function () {
    Route::get('/users/{user}/followers', [SubscriptionController::class, 'followers']);
    Route::get('/users/{user}/following', [SubscriptionController::class, 'following']);

    Route::middleware(['auth:sanctum','user.banned'])->group(function () {
        Route::post('/subscriptions/follow', [SubscriptionController::class, 'follow']);
        Route::delete('/subscriptions/{user}/unfollow', [SubscriptionController::class, 'unfollow']);
    });
});


Route::prefix('stream')->group(function () {
    Route::get('/streams', [StreamController::class, 'index']);
    Route::get('/streams/{stream}', [StreamController::class, 'show']);
    Route::get('/streams/category/{category}', [StreamController::class, 'filterByCategory']);

    Route::middleware(['auth:sanctum','user.banned'])->group(function () {

        Route::middleware('stream.status.live')->group(function () {
              Route::post('/streams', [StreamController::class, 'store']);
        });

        Route::middleware('stream.owner.live')->group(function () {
            Route::get('/streams/{stream}/edit', [StreamController::class, 'edit']);
            Route::put('/streams/{stream}', [StreamController::class, 'update']);
            Route::patch('/streams/{stream}/end', [StreamController::class, 'end']);
        });
    });
});


Route::prefix('video')->group(function () {

    Route::get('/videos', [VideoController::class, 'index']);
    Route::get('/videos/{video}', [VideoController::class, 'show']);
    Route::get('/videos/category/{category}', [VideoController::class, 'filterByCategory']);

    Route::middleware(['auth:sanctum','user.banned'])->group(function () {

        Route::post('/videos', [VideoController::class, 'store']);

        Route::middleware('video.owner')->group(function () {
            Route::delete('/videos/{video}', [VideoController::class, 'destroy']);
        });
    });
});


Route::prefix('reaction')->group(function () {
    Route::middleware(['auth:sanctum','user.banned'])->group(function () {
        Route::post('/reactions', [ReactionController::class, 'store']);
    });
});

Route::prefix('comments')->group(function () {
    Route::middleware(['auth:sanctum', 'user.banned'])->group(function () {
        Route::post('/', [CommentController::class, 'store']);
        Route::put('/{comment}', [CommentController::class, 'update']);
        Route::delete('/{comment}', [CommentController::class, 'destroy']);
    });
});

Route::prefix('messages')->group(function () {
    Route::middleware(['auth:sanctum','user.banned'])->group(function () {
        Route::post('/messages', [MessageController::class, 'store']);
        Route::get('/conversations', [MessageController::class, 'conversations']);
        Route::get('/messages/{user}', [MessageController::class, 'messages']);

    });
});

Route::prefix('notification')->middleware(['auth:sanctum','user.banned'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
});


Route::middleware('auth:sanctum')->group(function () {

    Route::get('/categories', [CategoryController::class, 'index']);

    Route::prefix('admin')->middleware('admin')->group(function (){

        Route::get('/statistics', [StatisticController::class, 'dashboard']);

        Route::get('/users', [UserController::class, 'index']);
        Route::patch('/users/{user}/ban', [UserController::class, 'ban']);
        Route::patch('/users/{user}/unban', [UserController::class, 'unban']);

        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    });

});
