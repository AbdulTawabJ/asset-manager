<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Models\AssetDisplay;
use App\Http\Controllers\AssetController;


Route::redirect('/', '/login');


// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

use Illuminate\Support\Facades\Auth;

Route::get('/dashboard', function () {
    $user = Auth::user();

    if (!$user) {
        return redirect('/login');
    }

    if ($user->role === 'admin') {
        return redirect('/admin');
    } elseif ($user->role === 'it') {
        return redirect('/it');
    }

    abort(403, 'Unauthorized');
})->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/admin', function () {
        return view('admin-dashboard'); // create this blade file
    });

    Route::get('/it', function () {
        return view('it-dashboard'); // create this blade file
    });
});

Route::middleware(['auth'])->get('/admin', function () {
    if (auth()->user()->role !== 'admin') {
        abort(403, 'Unauthorized');
    }

    $assets = AssetDisplay::paginate(10);

    return view('admin-dashboard', compact('assets'));
});


Route::middleware(['auth'])->group(function () {
    Route::get('/admin/create-asset', [AssetController::class, 'create'])->name('assets.create');
    Route::post('/admin/create-asset', [AssetController::class, 'store'])->name('assets.store');
});


require __DIR__.'/auth.php';
