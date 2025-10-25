<?php

use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\IdleController;

Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('users.index');
    }

    return view('welcome');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/auth/status', function () {
    return response()->json([
        'authenticated' => auth()->check(),
        'role' => auth()->user()?->role ?? null,
    ]);
})->name('auth.status');

Route::middleware('auth')->group(function () {
    Route::resource('users', UserController::class);
    Route::get('users/{user}/avatar/download', [UserController::class, 'downloadAvatar'])->name('users.avatar.download');
    Route::post('idle/event', [IdleController::class, 'event'])->name('idle.event');
});

Route::middleware(['auth', AdminMiddleware::class])->prefix('admin')->group(function () {
    Route::get('dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::match(['get','post'], 'settings', [AdminController::class, 'settings'])->name('admin.settings');
});
