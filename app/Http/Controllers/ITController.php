<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetHistory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class ITController extends Controller
{

public function index()
{
    $pendingShifts = AssetHistory::where('requires_it_remark', true)
    ->orderByDesc('date')
    ->get();
    $pendingAssets  = Asset::where('requires_it_remark', true)
    ->orderByDesc('last_updated_on')
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

    return redirect()->back()->with('success', 'Asset remark updated.');
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

    $asset = Asset::where('asset_tag', $shift->asset_tag)->first();
    if ($asset) {
        $asset->remarks = $shift->remarks;
        $asset->status = $request->status;
        $asset->remarked_by = $shift->remarked_by;
        $asset->requires_it_remark = false; // ensure consistency
        $asset->save();
    }

    return redirect()->back()->with('success', 'Shift remark updated.');
}

}
