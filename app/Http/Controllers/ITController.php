<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetHistory;
use App\Models\Employee;
use App\Http\Controllers\EmployeeController;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
class ITController extends Controller
{

public function index()
{
    $pendingShifts = AssetHistory::where('requires_it_remark', true)
        ->orderBy('date', 'asc')
        ->get()
        ->map(function ($shift) {
            // Current description from assets table
            $asset = Asset::where('asset_tag', $shift->asset_tag)->first();
            $shift->current_description = $asset?->description;

            // Get full name for previous owner
            $prevEmp = Employee::where('file_no', $shift->prev_owner)->first();
            $shift->prev_owner_full = $prevEmp
                ? "{$prevEmp->file_no} - {$prevEmp->first_name} {$prevEmp->last_name}"
                : $shift->prev_owner;

            // Get full name for new owner
            $newEmp = Employee::where('file_no', $shift->new_owner)->first();
            $shift->new_owner_full = $newEmp
                ? "{$newEmp->file_no} - {$newEmp->first_name} {$newEmp->last_name}"
                : $shift->new_owner;
            
            $latestShift = AssetHistory::where('asset_tag', $shift->asset_tag)
                ->orderBy('date', 'desc')
                ->first();
            $shift->is_latest_shift = $latestShift && $latestShift->id === $shift->id;
            return $shift;
        });

    $pendingAssets = Asset::where('requires_it_remark', true)
        ->orderBy('last_updated_on', 'asc')
        ->get();
    
    return view('it-dashboard', compact('pendingShifts', 'pendingAssets'));
}

public function remarkAsset(Request $request, $id)
{
    $request->validate([
        'remark' => 'required|string|max:255',
        'status' => ['required', Rule::in(['None', 'Working', 'Damaged'])],
    ]);

    $asset = Asset::findOrFail($id);
    $asset->remarks = $request->remark;
    $asset->status = $request->status;
    $asset->remarked_by = Auth::user()->name;
    $asset->requires_it_remark = false;
    $asset->save();

    return redirect()->back()->with('success', 'Asset Remark Submitted.');
}

public function remarkShift(Request $request, $id)
{
    $request->validate([
        'remark' => 'required|string|max:255',
        'status' => ['required', Rule::in(['None', 'Working', 'Damaged'])],
    ]);

    $shift = AssetHistory::findOrFail($id);
    $shift->remarks = $request->remark;
    $shift->status = $request->status;
    $shift->remarked_by = Auth::user()->name;
    $shift->requires_it_remark = false;
    $shift->save();

    // Only update the asset if override is selected (or if the shift is the latest)
    if ($request->input('override') === '1' || !$request->has('not_latest')) {
        $asset = Asset::where('asset_tag', $shift->asset_tag)->first();
        if ($asset) {
            $asset->remarks = $shift->remarks;
            $asset->status = $request->status;
            $asset->remarked_by = $shift->remarked_by;
            $asset->requires_it_remark = false;
            $asset->save();
        }
    }

    return redirect()->back()->with('success', 'Shift Remarked Submitted.');
}

}
