<?php

use App\Http\Controllers\ProfileController;
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
