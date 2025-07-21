<?php

use App\Http\Controllers\AssetHistoryController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\ITController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\AssetDisplay;
use App\Models\Location;


Route::bind('location', function ($value) {
    return Location::where('location', $value)->firstOrFail();
});

Route::redirect('/', '/login');


// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');


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


Route::get('/admin', [AssetController::class, 'index'])
    ->middleware(['auth', 'role:admin'])
    ->name('admin.dashboard');



Route::middleware(['auth', 'role:admin'])->get('/admin/query', [AssetController::class, 'advancedQuery'])->name('assets.query');


Route::middleware(['auth'])->group(function () {
    Route::get('/admin/create-asset', [AssetController::class, 'create'])->name('assets.create');
    Route::post('/admin/create-asset', [AssetController::class, 'store'])->name('assets.store');
    Route::get('/admin/edit-asset/{asset}', [AssetController::class, 'edit'])->name('assets.edit');
    Route::put('/admin/edit-asset/{asset}', [AssetController::class, 'update'])->name('assets.update');
    Route::delete('/admin/delete-asset/{asset}', [AssetController::class, 'destroy'])->name('assets.destroy');

});
// routes/web.php
Route::post('/settings/save-columns', function (Illuminate\Http\Request $request) {
    session(['visible_columns' => $request->columns]);
    return response()->json(['status' => 'stored']);
})->middleware(['auth'])->name('settings.save-columns');

Route::get('/assets/query/export', [AssetController::class, 'exportQuery'])->name('assets.query.export');
Route::get('/admin/export', [AssetController::class, 'export'])->name('admin.export');


Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');
    Route::get('/create-location', [LocationController::class, 'create'])->name('locations.create');
    Route::post('/create-location', [LocationController::class, 'store'])->name('locations.store');
    Route::get('/edit-location/{location}', [LocationController::class, 'edit'])->name('locations.edit');
    Route::put('/edit-location/{location}', [LocationController::class, 'update'])->name('locations.update');
    Route::delete('/delete-location/{location}', [LocationController::class, 'destroy'])->name('locations.destroy');
    Route::get('/locations/export', [LocationController::class, 'export'])->name('locations.export');

});

Route::get('/shift-asset/{serial_no}', [AssetHistoryController::class, 'create'])->name('asset_history.create');
Route::post('/shift-asset', [AssetHistoryController::class, 'store'])->name('asset_history.store');

Route::get('/it', [ITController::class, 'index'])->middleware('auth')->name('it.dashboard');

Route::middleware(['auth', 'role:it'])->group(function () {
    Route::post('/it/remark/asset/{id}', [ITController::class, 'remarkAsset'])->name('it.remark.asset');
    Route::post('/it/remark/shift/{id}', [ITController::class, 'remarkShift'])->name('it.remark.shift');
});

require __DIR__.'/auth.php';
