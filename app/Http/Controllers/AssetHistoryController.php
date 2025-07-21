<?php
// app/Http/Controllers/AssetHistoryController.php

namespace App\Http\Controllers;


use App\Models\Asset;
use App\Models\Employee;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Models\AssetHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AssetHistoryController extends Controller
{
    public function create($serial_no)
    {
        $asset = Asset::where('serial_no', $serial_no)->firstOrFail();
        $employees = Employee::all();
        $locations = Location::all();

        return view('assets.shift-form', compact('asset', 'employees', 'locations'));
    }

    public function store(Request $request)
{
    $data = $request->validate([
        'serial_no' => 'required|exists:assets,serial_no',
        'description' => 'nullable|string',
        'prev_location' => 'nullable|string',
        'new_location' => 'required|string',
        'prev_owner' => 'nullable|string',
        'new_owner' => 'required|string',
        'remarks' => 'nullable|string',
    ]);

    $remark = trim($data['remarks'] ?? '');
    $requiresIT = $request->boolean('requires_it_remark');

    if ($requiresIT || strcasecmp($remark, 'Pending') === 0) {
        $data['remarks'] = 'Pending';
        $data['remarked_by'] = null;
        $data['requires_it_remark'] = true;
    } elseif ($remark === '' || strcasecmp($remark, 'Remark Inapt') === 0) {
        $data['remarks'] = 'Remark Inapt';
        $data['remarked_by'] = null;
        $data['requires_it_remark'] = false;
    } else {
        $data['remarks'] = $remark;
        $data['remarked_by'] = Auth::user()->name;
        $data['requires_it_remark'] = false;
    }

    DB::table('asset_history')->insert($data);
    $asset = Asset::where('serial_no', $data['serial_no'])->first();
    if ($asset) {
        $asset->update([
            'owner' => $data['new_owner'],
            'location' => $data['new_location'],
            'remarks' => $data['remarks'],
            'remarked_by' => $data['remarked_by'],
        ]);
    }

    return redirect('/admin')->with('success', 'Asset shift recorded.');
}

}
