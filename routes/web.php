<?php
/*
 * GorKa Team
 * Copyright (c) 2024  Vlad Horpynych <19dynamo27@gmail.com>, Pavel Karpushevskiy <pkarpushevskiy@gmail.com>
 */

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*
 * Main routes
 */
Route::group(['as' => 'main.', 'middleware' => []], function () {
    Route::get('/', function () {
        return redirect()->route('dashboard.index');
    })->name('index');
});

/*
 * Dashboard routes
 */
Route::group(['prefix' => 'dashboard', 'as' => 'dashboard.', 'middleware' => ['auth', 'verified']], function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
});


/*
 * Profile routes
 */
Route::group(['prefix' => 'profile', 'as' => 'profile.', 'middleware' => ['auth']], function () {
    Route::get('/', [ProfileController::class, 'edit'])->name('edit');
    Route::patch('/', [ProfileController::class, 'update'])->name('update');
    Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
});


/*
 * Authentication & verification routes
 */
Route::group(['middleware' => ['guest']], function () {
    Route::group(['as' => 'auth.'], function () {
        Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
        Route::post('register', [RegisteredUserController::class, 'store']);
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store']);
    });

    Route::group(['as' => 'password.'], function () {
        Route::get('reset-password', function () {
            return redirect()->route('auth.login');
        });

        Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('request');
        Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('email');
        Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('reset');
        Route::post('reset-password', [NewPasswordController::class, 'store'])->name('store');
    });
});

Route::group(['middleware' => ['auth']], function () {
    Route::group(['as' => 'auth.'], function () {
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    });

    Route::group(['as' => 'verification.'], function () {
        Route::get('verify-email', EmailVerificationPromptController::class)->name('notice');
        Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verify');
        Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('send');
    });

    Route::group(['as' => 'password.'], function () {
        Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('confirm');
        Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
        Route::put('password', [PasswordController::class, 'update'])->name('update');
    });
});


/*
 * Users routes
 */
Route::group(['prefix' => 'users', 'as' => 'users.', 'middleware' => ['auth']], function () { // OLD: check_user_role:super-admin,manager
    Route::get('/', [UsersController::class, 'index'])->name('index');
//    Route::get('/{user}', [UsersController::class, 'show'])->name('show');
    Route::post('/', [UsersController::class, 'store'])->name('store');
    Route::put('/{user}', [UsersController::class, 'update'])->name('update');
    Route::post('/{user}/download', [UsersController::class, 'download'])->name('download');
    Route::delete('/{user}', [UsersController::class, 'destroy'])->name('destroy');
    Route::post('/{user}/restore', [UsersController::class, 'restore'])->name('restore');
});
