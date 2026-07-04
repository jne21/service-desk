<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\TicketController;
use App\Http\Controllers\Admin\UserController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {

    // admin area
    // users
    // tickets
    Route::get('/tickets', [TicketController::class, 'index'])
        ->name('tickets.index');

    Route::get('/tickets/create', [TicketController::class, 'create'])
        ->name('tickets.create');

    Route::post('/tickets', [TicketController::class, 'store'])
        ->name('tickets.store');
        
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])
        ->name('tickets.show');
    
    Route::patch('/tickets/{ticket}', [TicketController::class, 'update'])
        ->name('tickets.update');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', function () {
        return 'Admin area';
    })->name('admin.index');

    Route::get('/admin/users', [UserController::class, 'index'])
        ->name('admin.users.index');

    Route::get('/admin/users/create', [UserController::class, 'create'])
        ->name('admin.users.create');

    Route::get('/admin/users/store', [UserController::class, 'store'])
        ->name('admin.users.store');

    Route::get('/admin/users/show', [UserController::class, 'show'])
        ->name('admin.users.show');

    Route::get('/admin/users/update', [UserController::class, 'update'])
        ->name('admin.users.update');

});

require __DIR__.'/auth.php';
