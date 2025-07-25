<?php

use App\Http\Controllers\AssetHistoryController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\AssetTypeController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\ITController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\AssetHistory;
use App\Models\AssetDisplay;
use App\Models\AssetType;
use App\Models\Department;
use App\Models\Location;
use App\Models\Employee;


Route::bind('location', function ($value) {
    return Location::where('location', $value)->firstOrFail();
});

Route::bind('history', function ($value) {
    return AssetHistory::where('id', $value)->firstOrFail();
});

Route::bind('department', function ($value) {
    return \App\Models\Department::where('department', $value)->firstOrFail();
});
Route::bind('type', function ($value) {
    return AssetType::where('type', $value)->firstOrFail();
});
Route::bind('employee', function ($value) {
    return Employee::where('file_no', $value)->firstOrFail();
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


// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

Route::middleware(['auth'])->group(function () {
    Route::get('/admin', function () {
        return view('admin-dashboard'); 
    });

    Route::get('/it', function () {
        return view('it-dashboard'); 
    });
});


Route::get('/admin', [AssetController::class, 'index'])
    ->middleware(['auth', 'role:admin'])
    ->name('admin.dashboard');


Route::middleware(['auth', 'role:admin'])->get('/admin/query', [AssetController::class, 'advancedQuery'])->name('assets.query');
Route::middleware(['auth', 'role:admin'])->get('/history/query', [AssetHistoryController::class, 'advancedQuery'])->name('history.query');
Route::middleware(['auth', 'role:admin'])->get('/history/query/export', [AssetHistoryController::class, 'exportQuery'])->name('history.query.export');


Route::middleware(['auth'])->group(function () {
    Route::get('/admin/create-asset', [AssetController::class, 'create'])->name('assets.create');
    Route::post('/admin/create-asset', [AssetController::class, 'store'])->name('assets.store');
    Route::get('/admin/edit-asset/{asset}', [AssetController::class, 'edit'])->name('assets.edit');
    Route::put('/admin/edit-asset/{asset}', [AssetController::class, 'update'])->name('assets.update');
    Route::delete('/admin/delete-asset/{asset}', [AssetController::class, 'destroy'])->name('assets.destroy');

});
// routes/web.php
Route::post('/settings/save-columns', function (Illuminate\Http\Request $request) {
    $table = $request->input('table', 'default');
    session(['visible_columns_' . $table => $request->columns]);
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


    Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::get('/create-department', [DepartmentController::class, 'create'])->name('departments.create');
    Route::post('/create-department', [DepartmentController::class, 'store'])->name('departments.store');
    Route::get('/edit-department/{department}', [DepartmentController::class, 'edit'])->name('departments.edit');
    Route::put('/edit-department/{department}', [DepartmentController::class, 'update'])->name('departments.update');
    Route::delete('/delete-department/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
    Route::get('/departments/export', [DepartmentController::class, 'export'])->name('departments.export');

    Route::get('/types', [AssetTypeController::class, 'index'])->name('types.index');
    Route::get('/create-type', [AssetTypeController::class, 'create'])->name('types.create');
    Route::post('/create-type', [AssetTypeController::class, 'store'])->name('types.store');
    Route::get('/edit-type/{type}', [AssetTypeController::class, 'edit'])->name('types.edit');
    Route::put('/edit-type/{type}', [AssetTypeController::class, 'update'])->name('types.update');
    Route::delete('/delete-type/{type}', [AssetTypeController::class, 'destroy'])->name('types.destroy');
    Route::get('/types/export', [AssetTypeController::class, 'export'])->name('types.export');

    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/create-employee', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/create-employee', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/edit-employee/{employee}', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/edit-employee/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/delete-employee/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    Route::get('/employees/export', [EmployeeController::class, 'export'])->name('employees.export');

    Route::get('/history', [AssetHistoryController::class, 'index'])->name('history.index');
    Route::get('/create-history', [AssetHistoryController::class, 'create'])->name('history.create');
    Route::post('/create-history', [AssetHistoryController::class, 'store'])->name('history.store');
    Route::get('/edit-history/{history}', [AssetHistoryController::class, 'edit'])->name('history.edit');
    Route::put('/edit-history/{history}', [AssetHistoryController::class, 'update'])->name('history.update');
    Route::delete('/delete-history/{history}', [AssetHistoryController::class, 'destroy'])->name('history.destroy');
    Route::get('/history/export', [AssetHistoryController::class, 'export'])->name('history.export');

});

Route::get('/shift-asset/{id}', [AssetHistoryController::class, 'create'])->name('asset_history.create');
Route::post('/shift-asset', [AssetHistoryController::class, 'store'])->name('asset_history.store');

Route::get('/it', [ITController::class, 'index'])->middleware('auth')->name('it.dashboard');

Route::middleware(['auth', 'role:it'])->group(function () {
    Route::post('/it/remark/asset/{id}', [ITController::class, 'remarkAsset'])->name('it.remark.asset');
    Route::post('/it/remark/shift/{id}', [ITController::class, 'remarkShift'])->name('it.remark.shift');
});

require __DIR__.'/auth.php';
