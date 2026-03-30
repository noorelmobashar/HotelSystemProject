<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\ReceptionistController;
use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function (Request $request) {
    if ($request->user()) {
        return redirect()->route('dashboard');
    }

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/create', [ReservationController::class, 'create'])->name('reservations.create');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');

    Route::middleware('role:admin')->group(function () {
        Route::get('/managers', [ManagerController::class, 'index'])->name('managers.index');
        Route::post('/managers', [ManagerController::class, 'store'])->name('managers.store');
        Route::put('/managers/{manager}', [ManagerController::class, 'update'])->name('managers.update');
        Route::delete('/managers/{manager}', [ManagerController::class, 'destroy'])->name('managers.destroy');
    });

    Route::middleware('role:admin|manager')->group(function () {
        Route::get('/receptionists', [ReceptionistController::class, 'index'])->name('receptionists.index');
        Route::post('/receptionists', [ReceptionistController::class, 'store'])->name('receptionists.store');
        Route::put('/receptionists/{receptionist}', [ReceptionistController::class, 'update'])->name('receptionists.update');
        Route::delete('/receptionists/{receptionist}', [ReceptionistController::class, 'destroy'])->name('receptionists.destroy');
    });
});

//add the route in the gruop  depend on the role
//admin
//manager
//receptionist
//client


// Route::middleware(['auth', 'role:admin'])->group(function () {
// });


// Route::middleware(['auth', 'role:manager'])->group(function () {
// });

// Route::middleware(['auth', 'role:manager|admin'])->group(function () {
// });

// Route::middleware(['auth', 'role:receptionist'])->group(function () {
// });



require __DIR__.'/auth.php';
