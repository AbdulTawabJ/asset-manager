<?php
// app/Http/Controllers/AssetHistoryController.php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Employee;
use App\Models\Location;
use Illuminate\Http\Request;
use App\Models\AssetHistory;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetHistoryController extends Controller

{
    public function create($id)
    {
        $asset = Asset::findOrFail($id);
        $employees = Employee::all();
        $locations = Location::all();

        return view('assets.shift-form', compact('asset', 'employees', 'locations'));
    }

    public function store(Request $request)
{
    $data = $request->validate([
        'asset_tag' => 'required|exists:assets,asset_tag',
        'description' => 'nullable|string',
        'prev_location' => 'nullable|string',
        'new_location' => 'required|string',
        'prev_owner' => 'nullable|string',
        'new_owner' => 'required|string',
        'remarks' => 'nullable|string',
        'status' => ['required', Rule::in(['None', 'Working', 'Damaged'])],
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
    
    $asset = Asset::where('asset_tag', $data['asset_tag'])->first();
    if ($asset) {
        $asset->update([
            'owner' => $data['new_owner'],
            'location' => $data['new_location'],
            'remarks' => $data['remarks'],
            'remarked_by' => $data['remarked_by'],
            'date_of_issue' => now(),
            'status' => $data['status'],
        ]);
    }

    return redirect('/admin')->with('success', 'Asset shift recorded.');
}

 // NEW: List view
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = AssetHistory::query();

        $searchColumn = $request->input('search_column', 'all');
if ($search) {
    if ($searchColumn === 'all') {
        $query->where(function ($q) use ($search) {
            $q->where('asset_tag', 'like', "%$search%")
              ->orWhere('description', 'like', "%$search%")
              ->orWhere('prev_owner', 'like', "%$search%")
              ->orWhere('new_owner', 'like', "%$search%")
              ->orWhere('prev_location', 'like', "%$search%")
              ->orWhere('new_location', 'like', "%$search%")
              ->orWhere('remarks', 'like', "%$search%")
              ->orWhere('status', 'like', "%$search%");
        });
    } else {
        $query->where($searchColumn, 'like', "%$search%");
    }
}


        $history = $query->orderBy('date', 'desc')->paginate(15);
        return view('admin-history', compact('history'));
    }

    // NEW: Edit form
    public function edit(AssetHistory $history)
    {
        $employees = Employee::all();
        $locations = Location::all();
        return view('form-history', compact('history', 'employees', 'locations'));
    }

    // NEW: Update logic
    public function update(Request $request, AssetHistory $history)
    {
        $data = $request->validate([
            'description' => 'nullable|string',
            'prev_location' => 'nullable|string',
            'new_location' => 'required|string',
            'prev_owner' => 'nullable|string',
            'new_owner' => 'required|string',
            'remarks' => 'nullable|string',
            'remarked_by' => 'nullable|string',
            'requires_it_remark' => 'boolean',
            'date' => 'nullable|date',
            'status' => ['required', Rule::in(['None', 'Working', 'Damaged'])],
        ]);

        $history->update($data);

        return redirect()->route('history.index')->with('success', 'History entry updated.');
    }

    // NEW: Delete
    public function destroy(AssetHistory $history)
    {
        $history->delete();
        return redirect()->route('history.index')->with('error', 'History entry deleted.');
    }

    // NEW: Export to CSV
    public function export(Request $request)
    {
        $search = $request->input('search');
        $searchColumn = $request->input('search_column', 'all');
        $query = AssetHistory::query();
if ($search) {
    if ($searchColumn === 'all') {
        $query->where(function ($q) use ($search) {
            $q->where('asset_tag', 'like', "%$search%")
              ->orWhere('description', 'like', "%$search%")
              ->orWhere('prev_owner', 'like', "%$search%")
              ->orWhere('new_owner', 'like', "%$search%")
              ->orWhere('prev_location', 'like', "%$search%")
              ->orWhere('new_location', 'like', "%$search%")
              ->orWhere('remarks', 'like', "%$search%")
              ->orWhere('status', 'like', "%$search%");
        });
    } else {
        $query->where($searchColumn, 'like', "%$search%");
    }
}


        $records = $query->get();

        $columnMap = [
            'asset_tag' => 'Asset Tag',
            'description' => 'Description',
            'prev_location' => 'Previous Location',
            'new_location' => 'New Location',
            'prev_owner' => 'Previous Owner',
            'new_owner' => 'New Owner',
            'remarks' => 'Remarks',
            'remarked_by' => 'Remarked By',
            'requires_it_remark' => 'Requires IT Remark',
            'date' => 'Date',
            'status' => 'Status',
        ];

        $selectedColumns = session('visible_columns_asset_history', array_keys($columnMap));
        $headers = array_merge(['Sr.'], array_map(fn($col) => $columnMap[$col] ?? $col, $selectedColumns));

        $csv = implode(',', $headers) . "\n";

        foreach ($records as $index => $record) {
            $row = [$index + 1];
            foreach ($selectedColumns as $col) {
                $row[] = str_replace(',', ' ', $record->$col ?? '');
            }
            $csv .= implode(',', $row) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="asset_history.csv"');
    }


public function exportQuery(Request $request)
{
    $columns = [
        'asset_tag' => 'string',
        'description' => 'string',
        'prev_location' => 'string',
        'new_location' => 'string',
        'prev_owner' => 'string',
        'new_owner' => 'string',
        'remarks' => 'string',
        'remarked_by' => 'string',
        'date' => 'date',
        'status' => 'string',
    ];

    $selectedFields = $request->input('fields', array_keys($columns));
    $includeId = false;
    if (!in_array('id', $selectedFields)) {
        $selectedFields[] = 'id'; // Required for internal reference, excluded from output
    } else {
        $includeId = true;
    }

    $query = DB::table('asset_history')->select($selectedFields);

    $conditionColumns   = $request->input('condition_column', []);
    $conditionOperators = $request->input('condition_operator', []);
    $conditionValues    = $request->input('condition_value', []);
    $conditionLogics    = $request->input('condition_logic', []);
    $orderBy            = $request->input('order_by', 'date');
    $orderDir           = strtolower($request->input('order_dir', 'desc')) === 'desc' ? 'desc' : 'asc';

    // Map for location parts
    $map = [
        'prev_region'      => ['column' => 'prev_location', 'index' => 1],
        'prev_area'        => ['column' => 'prev_location', 'index' => 2],
        'prev_branch'      => ['column' => 'prev_location', 'index' => 3],
        'prev_department'  => ['column' => 'prev_location', 'index' => 4],
        'new_region'       => ['column' => 'new_location', 'index' => 1],
        'new_area'         => ['column' => 'new_location', 'index' => 2],
        'new_branch'       => ['column' => 'new_location', 'index' => 3],
        'new_department'   => ['column' => 'new_location', 'index' => 4],
    ];

    $query->where(function ($q) use ($conditionColumns, $conditionOperators, $conditionValues, $conditionLogics, $map) {
        for ($i = 0; $i < count($conditionColumns); $i++) {
            $col = $conditionColumns[$i];
            $op  = $conditionOperators[$i];
            $val = $conditionValues[$i];
            $logic = strtoupper($conditionLogics[$i - 1] ?? 'AND');

            $clause = function ($query) use ($col, $op, $val, $map) {
                if (isset($map[$col])) {
                    $loc = $map[$col]['column'];
                    $idx = $map[$col]['index'];
                    $query->whereRaw("SUBSTRING_INDEX(SUBSTRING_INDEX($loc, '-', {$idx}), '-', -1) $op ?", [$val]);
                } else {
                    $query->where($col, $op, $val);
                }
            };

            if ($i === 0) {
                $q->where($clause);
            } else {
                $logic === 'OR'
                    ? $q->orWhere($clause)
                    : $q->where($clause);
            }
        }
    });

    if (in_array($orderBy, array_keys($columns))) {
        $query->orderBy($orderBy, $orderDir);
    }

    $rows = $query->get();

    return new StreamedResponse(function () use ($rows, $selectedFields, $includeId) {
        $handle = fopen('php://output', 'w');

        // Header
        $headers = ['Sr.'];
        foreach ($selectedFields as $field) {
            if ($field !== 'id') {
                $headers[] = $field;
            }
        }
        fputcsv($handle, $headers);

        // Rows
        foreach ($rows as $index => $row) {
            $csvRow = [$index + 1];
            foreach ($selectedFields as $field) {
                if ($field !== 'id') {
                    $csvRow[] = $row->$field ?? '';
                }
            }
            fputcsv($handle, $csvRow);
        }

        fclose($handle);
    }, 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="asset_history_advanced_query.csv"',
    ]);
}

public function advancedQuery(Request $request)
{
    $columns = [
        'asset_tag' => 'Tag',
        'description' => 'Description',
        'prev_location' => 'Previous Location',
        'new_location' => 'New Location',
        'prev_owner' => 'Previous Owner',
        'new_owner' => 'New Owner',
        'remarks' => 'Remarks',
        'remarked_by' => 'Remarked By',
        'date' => 'Date',
        'status' => 'Status',
    ];

    $columnTypes = [
        'asset_tag' => 'string',
        'description' => 'text',
        'prev_location' => 'string',
        'new_location' => 'string',
        'prev_owner' => 'string',
        'new_owner' => 'string',
        'remarks' => 'text',
        'remarked_by' => 'string',
        'date' => 'date',
        'status' => 'string',
    ];

    $operators = [
        'string' => ['=', '!=', 'LIKE', 'NOT LIKE'],
        'numeric' => ['=', '!=', '<', '<=', '>', '>='],
        'date' => ['=', '!=', '<', '<=', '>', '>='],
        'datetime' => ['=', '!=', '<', '<=', '>', '>='],
        'text' => ['LIKE', 'NOT LIKE'],
    ];

    $selectedFields = $request->input('fields', array_keys($columns));
    if (!in_array('id', $selectedFields)) {
        $selectedFields[] = 'id'; // required for actions
    }

    $query = DB::table('asset_history')->select($selectedFields);

    $conditionColumns   = $request->input('condition_column', []);
    $conditionOperators = $request->input('condition_operator', []);
    $conditionValues    = $request->input('condition_value', []);
    $conditionLogics    = $request->input('condition_logic', []);
    $orderBy            = $request->input('order_by', 'date');
    $orderDir           = strtolower($request->input('order_dir', 'desc')) === 'desc' ? 'desc' : 'asc';

    // Location part mapping
    $map = [
        'prev_region'      => ['column' => 'prev_location', 'index' => 1],
        'prev_area'        => ['column' => 'prev_location', 'index' => 2],
        'prev_branch'      => ['column' => 'prev_location', 'index' => 3],
        'prev_department'  => ['column' => 'prev_location', 'index' => 4],
        'new_region'       => ['column' => 'new_location', 'index' => 1],
        'new_area'         => ['column' => 'new_location', 'index' => 2],
        'new_branch'       => ['column' => 'new_location', 'index' => 3],
        'new_department'   => ['column' => 'new_location', 'index' => 4],
    ];

    // Apply filters
    $query->where(function ($q) use ($conditionColumns, $conditionOperators, $conditionValues, $conditionLogics, $map) {
        for ($i = 0; $i < count($conditionColumns); $i++) {
            $col   = $conditionColumns[$i];
            $op    = $conditionOperators[$i];
            $val   = $conditionValues[$i];
            $logic = strtoupper($conditionLogics[$i - 1] ?? 'AND');

            $clause = function ($subQ) use ($col, $op, $val, $map) {
                if (isset($map[$col])) {
                    $loc = $map[$col]['column'];
                    $idx = $map[$col]['index'];
                    $subQ->whereRaw("SUBSTRING_INDEX(SUBSTRING_INDEX($loc, '-', {$idx}), '-', -1) $op ?", [$val]);
                } else {
                    $subQ->where($col, $op, $val);
                }
            };

            if ($i === 0) {
                $q->where($clause);
            } else {
                $logic === 'OR'
                    ? $q->orWhere($clause)
                    : $q->where($clause);
            }
        }
    });

    if (in_array($orderBy, array_keys($columns))) {
        $query->orderBy($orderBy, $orderDir);
    }

    $history = $query->paginate(20)->appends($request->except('page'));

    return view('history.query', compact('history', 'columns', 'columnTypes', 'operators'));
}

// AssetHistoryController.php

public function reportForm()
{
    $locations = \App\Models\Location::pluck('location');

    $parsed = [
        'regions' => [],
        'branches' => [],
        'offices' => [],
        'departments' => []
    ];

    foreach ($locations as $loc) {
        $parts = explode('-', $loc);

        if (!empty($parts[0])) $parsed['regions'][] = $parts[0];
        if (!empty($parts[1])) $parsed['branches'][] = $parts[0] . '-' . $parts[1];
        if (!empty($parts[2])) $parsed['offices'][] = $parts[0] . '-' . $parts[1] . '-' . $parts[2];
        if (!empty($parts[3])) $parsed['departments'][] = $loc;
    }

    $parsed = array_map('array_unique', $parsed);

    return view('history.report', [
        'owners' => \App\Models\Employee::pluck('file_no'),
        'locations' => $parsed,
    ]);
}



public function generateReport(Request $request)
{
    $filters = $request->only([
        'asset_tag',
        'prev_location', 'new_location',
        'prev_owner', 'new_owner',
        'date_start', 'date_end',
        'status',
    ]);

    $query = \App\Models\AssetHistory::query();

    if (!empty($filters['asset_tag'])) {
        $query->where('asset_tag', 'like', '%' . $filters['asset_tag'] . '%');
    }

    // Previous Location
$prevParts = [];
if (!empty($request->prev_region))    $prevParts[] = $request->prev_region;
if (!empty($request->prev_branch))    $prevParts[] = explode('-', $request->prev_branch)[1] ?? '';
if (!empty($request->prev_office))    $prevParts[] = explode('-', $request->prev_office)[2] ?? '';
if (!empty($request->prev_location))  $prevParts[] = explode('-', $request->prev_location)[3] ?? '';

if (!empty($prevParts)) {
    $prevLoc = implode('-', array_filter($prevParts));
    $query->where('prev_location', 'like', $prevLoc . '%');
}

// New Location
$newParts = [];
if (!empty($request->new_region))    $newParts[] = $request->new_region;
if (!empty($request->new_branch))    $newParts[] = explode('-', $request->new_branch)[1] ?? '';
if (!empty($request->new_office))    $newParts[] = explode('-', $request->new_office)[2] ?? '';
if (!empty($request->new_location))  $newParts[] = explode('-', $request->new_location)[3] ?? '';

if (!empty($newParts)) {
    $newLoc = implode('-', array_filter($newParts));
    $query->where('new_location', 'like', $newLoc . '%');
}


    if (!empty($filters['prev_owner'])) {
        $query->where('prev_owner', $filters['prev_owner']);
    }

    if (!empty($filters['new_owner'])) {
        $query->where('new_owner', $filters['new_owner']);
    }

    if (!empty($filters['status'])) {
        $query->where('status', $filters['status']);
    }

    if (!empty($filters['date_start'])) {
        $query->whereDate('date', '>=', $filters['date_start']);
    }

    if (!empty($filters['date_end'])) {
        $query->whereDate('date', '<=', $filters['date_end']);
    }

    $records = $query->get();

    return view('history.generated-report', [
        'records' => $records,
        'filters' => $filters,
    ]);
}


public function exportReport(Request $request)
{
    $filters = $request->only([
        'asset_tag',
        'prev_location', 'new_location',
        'prev_owner', 'new_owner',
        'date_start', 'date_end',
        'status',
    ]);

    $query = \App\Models\AssetHistory::query();

    if (!empty($filters['asset_tag'])) {
        $query->where('asset_tag', 'like', '%' . $filters['asset_tag'] . '%');
    }

    // Previous Location
$prevParts = [];
if (!empty($request->prev_region))    $prevParts[] = $request->prev_region;
if (!empty($request->prev_branch))    $prevParts[] = explode('-', $request->prev_branch)[1] ?? '';
if (!empty($request->prev_office))    $prevParts[] = explode('-', $request->prev_office)[2] ?? '';
if (!empty($request->prev_location))  $prevParts[] = explode('-', $request->prev_location)[3] ?? '';

if (!empty($prevParts)) {
    $prevLoc = implode('-', array_filter($prevParts));
    $query->where('prev_location', 'like', $prevLoc . '%');
}

// New Location
$newParts = [];
if (!empty($request->new_region))    $newParts[] = $request->new_region;
if (!empty($request->new_branch))    $newParts[] = explode('-', $request->new_branch)[1] ?? '';
if (!empty($request->new_office))    $newParts[] = explode('-', $request->new_office)[2] ?? '';
if (!empty($request->new_location))  $newParts[] = explode('-', $request->new_location)[3] ?? '';

if (!empty($newParts)) {
    $newLoc = implode('-', array_filter($newParts));
    $query->where('new_location', 'like', $newLoc . '%');
}


    if (!empty($filters['prev_owner'])) {
        $query->where('prev_owner', $filters['prev_owner']);
    }

    if (!empty($filters['new_owner'])) {
        $query->where('new_owner', $filters['new_owner']);
    }

    if (!empty($filters['status'])) {
        $query->where('status', $filters['status']);
    }

    if (!empty($filters['date_start'])) {
        $query->whereDate('date', '>=', $filters['date_start']);
    }

    if (!empty($filters['date_end'])) {
        $query->whereDate('date', '<=', $filters['date_end']);
    }

    $records = $query->get();

    $columns = [
        'asset_tag',
        'description',
        'prev_location',
        'new_location',
        'prev_owner',
        'new_owner',
        'date',
        'status',
    ];

    $filename = 'asset_history_report_' . now()->format('Ymd_His') . '.csv';

    return response()->streamDownload(function () use ($records, $columns) {
    $file = fopen('php://output', 'w');

    // Add custom "Sr #" column header manually
    fputcsv($file, array_merge(['Sr #'], array_map(fn($col) => ucwords(str_replace('_', ' ', $col)), $columns)));

    $count = 1;
    foreach ($records as $row) {
        $data = [$count++]; // Start with Sr #
        foreach ($columns as $col) {
            $data[] = $row->$col ?? '';
        }
        fputcsv($file, $data);
    }

    fclose($file);
}, $filename, [
    'Content-Type' => 'text/csv',
    'Content-Disposition' => "attachment; filename=\"$filename\"",
]);

}



}
