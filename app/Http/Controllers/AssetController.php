<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\Location;
use App\Models\Employee;
use App\Models\AssetType;
use App\Http\Requests\StoreAssetRequest;
use Illuminate\Support\Facades\DB;
use App\Models\AssetDisplay;


class AssetController extends Controller
{
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    $search = $request->input('search');

    $query = AssetDisplay::query();

    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('serial_no', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('type', 'like', "%{$search}%")
              ->orWhere('location', 'like', "%{$search}%")
              ->orWhere('owner', 'like', "%{$search}%")
              ->orWhere('remarks', 'like', "%{$search}%");
        });
    }

$assets = AssetDisplay::paginate(4);

    return view('admin-dashboard', compact('assets'));
}

// Keep this in AssetController

public function advancedQuery(Request $request)
{
    $columns = [
        'serial_no' => 'string',
        'date_of_purchase' => 'date',
        'type' => 'string',
        'description' => 'string',
        'amount' => 'numeric',
        'location' => 'string',
        'owner_full_name' => 'string',
        'remarks' => 'string',
        'remarked_by' => 'string',
        'last_updated_on' => 'datetime',
    ];

    $selectedFields = $request->input('fields', array_keys($columns));

    // Ensure ID is always selected for edit/delete functionality
    if (!in_array('id', $selectedFields)) {
        $selectedFields[] = 'id';
    }

    $conditionColumn = $request->input('condition_column');
    $operator = $request->input('condition_operator');
    $value = $request->input('condition_value');
    $orderBy = $request->input('order_by', 'serial_no');
    $orderDir = $request->input('order_dir', 'asc');

    // Base query
    $query = DB::table('asset_display')->select($selectedFields);

    // Add condition only if all parts are provided
    if ($conditionColumn && $operator && $value !== null) {
        if (in_array($operator, ['=', '!=', '<', '<=', '>', '>=', 'LIKE'])) {
            $query->where($conditionColumn, $operator, $value);
        } elseif (strtolower($operator) === 'in') {
            $query->whereIn($conditionColumn, explode(',', $value));
        }
    }

    // Order results
    if (in_array($orderBy, array_keys($columns))) {
        $query->orderBy($orderBy, $orderDir === 'desc' ? 'desc' : 'asc');
    }

    $assets = $query->paginate(10)->withQueryString();

    return view('assets.query', compact('assets'));
}

public function create()
{
    return view('assets.form', [
        'types' => AssetType::all(),
        'locations' => Location::all(),
        'employees' => Employee::all()
    ]);
}


public function store(StoreAssetRequest $request)
{
    $data = $request->validated();
    $data['remarked_by'] = auth()->user()->full_name ?? auth()->user()->email;

    Asset::create($data);

    return redirect('/admin')->with('success', 'Asset added successfully.');
}


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }
    public function edit(Asset $asset)
{
    return view('assets.form', [
        'asset' => $asset,
        'types' => AssetType::all(),
        'locations' => Location::all(),
        'employees' => Employee::all()
    ]);
}



    /**
     * Update the specified resource in storage.
     */
    public function update(StoreAssetRequest $request, Asset $asset)
{
    $data = $request->validated();
    $data['remarked_by'] = auth()->user()->full_name ?? auth()->user()->email;

    $asset->update($data);

    return redirect('/admin')->with('success', 'Asset updated successfully.');
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Asset $asset)
{
    $asset->delete();

    return redirect('/admin')->with('success', 'Asset deleted successfully.');
}

}
