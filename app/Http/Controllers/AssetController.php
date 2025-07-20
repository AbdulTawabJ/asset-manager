<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\Location;
use App\Models\Employee;
use App\Models\AssetType;
use App\Http\Requests\StoreAssetRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
              ->orWhere('owner_full_name', 'like', "%{$search}%") // ✅ fix: use correct field
              ->orWhere('remarks', 'like', "%{$search}%");
        });
    }

    $assets = $query->orderBy('id', 'desc')->paginate(4)->withQueryString(); // ✅ use the filtered query

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
    if (!in_array('id', $selectedFields)) {
        $selectedFields[] = 'id';
    }

    $conditionColumns   = $request->input('condition_column', []);
    $conditionOperators = $request->input('condition_operator', []);
    $conditionValues    = $request->input('condition_value', []);
    $conditionLogics    = $request->input('condition_logic', []);
    $orderBy            = $request->input('order_by', 'serial_no');
    $orderDir           = strtolower($request->input('order_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

    $query = DB::table('asset_display')->select($selectedFields);

    $query->where(function ($q) use ($conditionColumns, $conditionOperators, $conditionValues, $conditionLogics) {
        $first = true;

        for ($i = 0; $i < count($conditionColumns); $i++) {
    $col = $conditionColumns[$i];
    $op = $conditionOperators[$i];
    $val = $conditionValues[$i];
    $logic = strtoupper($conditionLogics[$i - 1] ?? 'AND'); // ✅ shift logic

    if ($i === 0) {
        $q->where($col, $op, $val); // always start with where
    } else {
        if ($logic === 'OR') {
            $q->orWhere($col, $op, $val);
        } else {
            $q->where($col, $op, $val);
        }
    }
}

    });

    if (in_array($orderBy, array_keys($columns))) {
        $query->orderBy($orderBy, $orderDir);
    }
\DB::listen(function ($query) {
    logger('SQL Executed: ' . $query->sql);
    logger('Bindings: ' . implode(', ', $query->bindings));
});

    $assets = $query->paginate(4)->withQueryString();

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


public function export(Request $request): StreamedResponse
{
    $columns = session('visible_columns', [  // Fallback to all if none selected
        'serial_no', 'date_of_purchase', 'type', 'description', 'amount',
        'location', 'owner_full_name', 'remarks', 'remarked_by', 'last_updated_on',
    ]);

    // Build query (reusing the same logic as your index method)
    $query = DB::table('asset_display');

    if ($search = $request->input('search')) {
        $query->where(function ($q) use ($search) {
            $q->where('serial_no', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('location', 'like', "%{$search}%")
              ->orWhere('owner_full_name', 'like', "%{$search}%");
        });
    }

    if ($type = $request->input('type')) {
        $query->where('type', $type);
    }

    if ($location = $request->input('location')) {
        $query->where('location', $location);
    }

    if ($owner = $request->input('owner')) {
        $query->where('owner', $owner);
    }

    // Sorting
    $orderBy = $request->input('order_by', 'serial_no');
    $orderDir = $request->input('order_dir', 'asc');
    $query->orderBy($orderBy, $orderDir);

    // Fetch all rows (no pagination)
    $assets = $query->get();

    // Prepare streamed CSV response
    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="assets_export.csv"',
    ];

    return response()->stream(function () use ($assets, $columns) {
        $output = fopen('php://output', 'w');
        fputcsv($output, $columns); // Header row

        foreach ($assets as $asset) {
            $row = [];
            foreach ($columns as $col) {
                $row[] = $asset->$col ?? '';
            }
            fputcsv($output, $row);
        }

        fclose($output);
    }, 200, $headers);
}


public function exportQuery(Request $request)
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
    if (!in_array('id', $selectedFields)) {
        $selectedFields[] = 'id';
    }

    $conditionColumns   = $request->input('condition_column', []);
    $conditionOperators = $request->input('condition_operator', []);
    $conditionValues    = $request->input('condition_value', []);
    $conditionLogics    = $request->input('condition_logic', []);
    $orderBy            = $request->input('order_by', 'serial_no');
    $orderDir           = strtolower($request->input('order_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

    $query = DB::table('asset_display')->select($selectedFields);

    $query->where(function ($q) use ($conditionColumns, $conditionOperators, $conditionValues, $conditionLogics) {
        for ($i = 0; $i < count($conditionColumns); $i++) {
            $col = $conditionColumns[$i];
            $op  = $conditionOperators[$i];
            $val = $conditionValues[$i];
            $logic = strtoupper($conditionLogics[$i - 1] ?? 'AND');

            if ($i === 0) {
                $q->where($col, $op, $val);
            } else {
                $logic === 'OR'
                    ? $q->orWhere($col, $op, $val)
                    : $q->where($col, $op, $val);
            }
        }
    });

    if (in_array($orderBy, array_keys($columns))) {
        $query->orderBy($orderBy, $orderDir);
    }

    $rows = $query->get();

    return new StreamedResponse(function () use ($rows, $selectedFields) {
        $handle = fopen('php://output', 'w');
        fputcsv($handle, $selectedFields);

        foreach ($rows as $row) {
            $csvRow = [];
            foreach ($selectedFields as $field) {
                $csvRow[] = $row->$field ?? '';
            }
            fputcsv($handle, $csvRow);
        }

        fclose($handle);
    }, 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="advanced_query_export.csv"',
    ]);
}


}
