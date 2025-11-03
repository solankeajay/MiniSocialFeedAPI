<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReactionController;
use App\Models\Post;
use Dom\Comment;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::controller(AuthController::class)->prefix('/auth')->group(function () {
    Route::post('/signup', 'register');
    Route::post('/login', 'login');
});


Route::middleware('auth:sanctum')->group(function () {

    Route::controller(AuthController::class)->prefix('/auth')->group(function () {
        Route::post('/logout', 'logout');
        Route::get('/me', 'getLoginUser');
    });

    Route::prefix('/posts')->group(function () {

        Route::controller(PostController::class)->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
            Route::get('/{postId}', 'show');
            Route::delete('/{postId}', 'destroy');
        });


        Route::controller(CommentController::class)->group(function () {
            Route::get('/{postId}/comments', 'index');
            Route::post('/{postId}/comment', 'store');
        });

        Route::controller(ReactionController::class)->group(function () {
            Route::post('/{postId}/like', 'postReaction');
            Route::post('/{postId}/dislike', 'postReaction');
        });
    });

    Route::controller(PostController::class)->group(function () {
        Route::get('/feed', 'postFeed');
    });

});
