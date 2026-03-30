<?php

use App\Http\Controllers\FloorController;
use App\Http\Controllers\ProfileController;
<<<<<<< HEAD
use Illuminate\Http\Request;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManagerController;
=======
>>>>>>> 5b13735357bcb58abbb9d87ab730d608b1cd554c
use App\Http\Controllers\ReceptionistController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReservationPaymentController;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function (Request $request) {
    if ($request->user()) {
        return redirect()->route('dashboard');
    }

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::controller(ReservationController::class)->group(function () {
        Route::get('/reservations', 'index')->name('reservations.index');
        Route::get('/reservations/create', 'create')->name('reservations.create');
        Route::get('/reservations/rooms/{roomId}', 'showRoomReservation')->name('reservations.rooms.show');
    });

    Route::controller(ReservationPaymentController::class)->group(function () {
        Route::post('/reservations/rooms/{roomId}/checkout', 'checkout')->name('reservations.rooms.checkout');
        Route::get('/reservations/checkout/success', 'checkoutSuccess')->name('reservations.checkout.success');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/managers', [ManagerController::class, 'index'])->name('managers.index');
        Route::post('/managers', [ManagerController::class, 'store'])->name('managers.store');
        Route::put('/managers/{manager}', [ManagerController::class, 'update'])->name('managers.update');
        Route::delete('/managers/{manager}', [ManagerController::class, 'destroy'])->name('managers.destroy');
    });

    Route::middleware('role:admin|manager')->group(function () {
        Route::resource('floors', FloorController::class)->except('show');
        Route::get('/receptionists', [ReceptionistController::class, 'index'])->name('receptionists.index');
        Route::post('/receptionists', [ReceptionistController::class, 'store'])->name('receptionists.store');
        Route::put('/receptionists/{receptionist}', [ReceptionistController::class, 'update'])->name('receptionists.update');
        Route::delete('/receptionists/{receptionist}', [ReceptionistController::class, 'destroy'])->name('receptionists.destroy');
    });
});

require __DIR__.'/auth.php';
