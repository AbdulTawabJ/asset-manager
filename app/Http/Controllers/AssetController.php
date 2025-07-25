<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\Location;
use App\Models\Employee;
use App\Models\AssetType;
use App\Models\AssetDisplay;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreAssetRequest;
use Symfony\Component\HttpFoundation\StreamedResponse;


class AssetController extends Controller
{
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
    $search = $request->input('search');
$searchColumn = $request->input('search_column', 'all');
$query = AssetDisplay::query();

if ($search) {
    $query->where(function ($q) use ($search, $searchColumn) {
        if ($searchColumn === 'all') {
            $q->where('asset_tag', 'like', "%{$search}%")
              ->orWhere('serial', 'like', "%{$search}%")
              ->orWhere('status', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('type', 'like', "%{$search}%")
              ->orWhere('amount', 'like', "%{$search}%")
              ->orWhere('location', 'like', "%{$search}%")
              ->orWhere('owner_full_name', 'like', "%{$search}%")
              ->orWhere('remarks', 'like', "%{$search}%")
              ->orWhere('remarked_by', 'like', "%{$search}%");
        } else {
            $q->where($searchColumn, 'like', "%{$search}%");
        }
    });
}


    $assets = $query->orderBy('id', 'desc')->paginate(10)->withQueryString(); // ✅ use the filtered query
    return view('admin-dashboard', compact('assets'));
}


// Keep this in AssetController
public function advancedQuery(Request $request)
{
    
    $columns = [
        'asset_tag' => 'string',
        'serial' => 'string',
        'status' => 'string',
        'date_of_purchase' => 'date',
        'date_of_issue' => 'date',
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
    $orderBy            = $request->input('order_by', 'asset_tag');
    $orderDir           = strtolower($request->input('order_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

    $query = DB::table('asset_display')->select($selectedFields);

    $query->where(function ($q) use ($conditionColumns, $conditionOperators, $conditionValues, $conditionLogics) {
        $first = true;

        $map = ['region' => 1, 'area' => 2, 'branch' => 3, 'department' => 4]; // 1-based index for split_part

for ($i = 0; $i < count($conditionColumns); $i++) {
    $col = $conditionColumns[$i];
    $op = $conditionOperators[$i];
    $val = $conditionValues[$i];
    $logic = strtoupper($conditionLogics[$i - 1] ?? 'AND');

    $clause = function ($query) use ($col, $op, $val, $map) {
        if (isset($map[$col])) {
            $query->whereRaw("SUBSTRING_INDEX(SUBSTRING_INDEX(location, '-', {$map[$col]}), '-', -1) $op ?", [$val]);
        } else {
            $query->where($col, $op, $val);
        }
    };

    if ($i === 0) {
        $q->where(fn($query) => $clause($query));
    } else {
        if ($logic === 'OR') {
            $q->orWhere(fn($query) => $clause($query));
        } else {
            $q->where(fn($query) => $clause($query));
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

    $assets = $query->paginate(20)->withQueryString();
    session()->flash('success', 'Query Executed.');
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

    $remark = trim($data['remarks'] ?? '');
    $requiresIT = $request->boolean('requires_it_remark');


    if ($requiresIT) {
        $data['remarks'] = 'Pending';
        $data['remarked_by'] = null;
        $data['requires_it_remark'] = true; 
    } elseif ($remark === '' || $remark === 'Remark Inapt') {
        $data['remarks'] = 'Remark Inapt';
        $data['remarked_by'] = null;
        $data['requires_it_remark'] = false; 
    } elseif ($remark === 'Pending') {
        $data['remarked_by'] = null;
        $data['requires_it_remark'] = true; 
    } else {
        $data['remarks'] = $remark;
        $data['remarked_by'] = Auth::user()->name;
        $data['requires_it_remark'] = false; 
    }

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

    $remark = trim($data['remarks'] ?? '');
    $requiresIT = $request->boolean('requires_it_remark');

    if ($requiresIT) {
        $data['remarks'] = 'Pending';
        $data['remarked_by'] = null;
        $data['requires_it_remark'] = true; 
    } elseif ($remark === '' || $remark === 'Remark Inapt') {
        $data['remarks'] = 'Remark Inapt';
        $data['remarked_by'] = null;
        $data['requires_it_remark'] = false; 
    } elseif ($remark === 'Pending') {
        $data['remarked_by'] = null;
        $data['requires_it_remark'] = true; 
    } else {
        $data['remarks'] = $remark;
        $data['remarked_by'] = Auth::user()->name;
        $data['requires_it_remark'] = false; 
    }

    $asset->update($data);

    return redirect('/admin')->with('success', 'Asset updated successfully.');
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Asset $asset)
{
    $asset->delete();

    return redirect('/admin')->with('error', 'Asset deleted successfully.');
}



public function export(Request $request)
{
    $columnMap = [
        'asset_tag' => 'Asset Tag',
        'status' => 'Status',
        'serial' => 'Serial',
        'date_of_purchase' => 'Date of Addition',
        'date_of_issue' => 'Date of Issue',
        'type' => 'Type',
        'description' => 'Description',
        'amount' => 'Amount',
        'location' => 'Location',
        'owner_full_name' => 'Owner',
        'remarks' => 'Remarks',
        'remarked_by' => 'Remarked By',
        'last_updated_on' => 'Updated On',
    ];

    $visibleColumns = session('visible_columns_assets', array_keys($columnMap));
    $headers = array_merge(['Sr.'], array_map(fn($col) => $columnMap[$col] ?? $col, $visibleColumns));

    $query = DB::table('asset_display');
    $search = $request->get('search');
$searchColumn = $request->get('search_column', 'all');

if ($search) {
    $query->where(function ($q) use ($search, $searchColumn) {
        if ($searchColumn === 'all') {
            $q->where('asset_tag', 'like', "%$search%")
              ->orWhere('serial', 'like', "%{$search}%")
              ->orWhere('status', 'like', "%$search%")
              ->orWhere('type', 'like', "%$search%")
              ->orWhere('description', 'like', "%$search%")
              ->orWhere('amount', 'like', "%$search%")
              ->orWhere('location', 'like', "%$search%")
              ->orWhere('owner_full_name', 'like', "%$search%")
              ->orWhere('remarks', 'like', "%$search%")
              ->orWhere('remarked_by', 'like', "%$search%");
        } else {
            $q->where($searchColumn, 'like', "%$search%");
        }
    });
}


    $assets = $query->get();

    $csv = implode(',', $headers) . "\n";
    foreach ($assets as $index => $asset) {
        $row = [$index + 1];
        foreach ($visibleColumns as $col) {
            $row[] = str_replace(',', ' ', $asset->$col ?? '');
        }
        $csv .= implode(',', $row) . "\n";
    }
    return response($csv)
        ->header('Content-Type', 'text/csv')
        ->header('Content-Disposition', 'attachment; filename="assets_export.csv"');
}


public function exportQuery(Request $request)
{
    $columns = [
        'asset_tag' => 'string',
        'serial' => 'string',
        'status' => 'string',
        'date_of_purchase' => 'date',
        'date_of_issue' => 'date',
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
    $orderBy            = $request->input('order_by', 'asset_tag');
    $orderDir           = strtolower($request->input('order_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

    $query = DB::table('asset_display')->select($selectedFields);

    $query->where(function ($q) use ($conditionColumns, $conditionOperators, $conditionValues, $conditionLogics) {
        $map = ['region' => 1, 'area' => 2, 'branch' => 3, 'department' => 4];

        for ($i = 0; $i < count($conditionColumns); $i++) {
            $col = $conditionColumns[$i];
            $op = $conditionOperators[$i];
            $val = $conditionValues[$i];
            $logic = strtoupper($conditionLogics[$i - 1] ?? 'AND');

            $clause = function ($query) use ($col, $op, $val, $map) {
                if (isset($map[$col])) {
                    $query->whereRaw("SUBSTRING_INDEX(SUBSTRING_INDEX(location, '-', {$map[$col]}), '-', -1) $op ?", [$val]);
                } else {
                    $query->where($col, $op, $val);
                }
            };

            if ($i === 0) {
                $q->where(fn($query) => $clause($query));
            } else {
                if ($logic === 'OR') {
                    $q->orWhere(fn($query) => $clause($query));
                } else {
                    $q->where(fn($query) => $clause($query));
                }
            }
        }
    });

    if (in_array($orderBy, array_keys($columns))) {
        $query->orderBy($orderBy, $orderDir);
    }

    $rows = $query->get();
$selectedParts = $request->input('location_parts', ['region','area','branch','department']);
$exportFields = array_filter($selectedFields, fn($f) => $f !== 'id'); // ✅ Strip ID for CSV

return new StreamedResponse(function () use ($rows, $exportFields, $selectedParts) {
    $handle = fopen('php://output', 'w');
    fputcsv($handle, array_merge(['Sr.'], $exportFields));

    foreach ($rows as $index => $row) {
        $csvRow = [$index + 1];
        foreach ($exportFields as $field) {
            if ($field === 'location') {
                $parts = explode('-', $row->location ?? '');
                $map = ['region' => 0, 'area' => 1, 'branch' => 2, 'department' => 3];
                $displayParts = [];
                foreach ($selectedParts as $part) {
                    $idx = $map[$part];
                    $displayParts[] = $parts[$idx] ?? '';
                }
                $csvRow[] = implode('-', $displayParts);
            } else {
                $csvRow[] = $row->$field ?? '';
            }
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
