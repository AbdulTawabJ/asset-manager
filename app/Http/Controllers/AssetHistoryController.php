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
    public function create($asset_tag)
    {
        $asset = Asset::where('asset_tag', $asset_tag)->firstOrFail();
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

        if ($search) {
            $query->where('asset_tag', 'like', "%$search%")
                ->orWhere('description', 'like', "%$search%")
                ->orWhere('prev_owner', 'like', "%$search%")
                ->orWhere('new_owner', 'like', "%$search%")
                ->orWhere('prev_location', 'like', "%$search%")
                ->orWhere('new_location', 'like', "%$search%")
                ->orWhere('remarks', 'like', "%$search%")
                ->orWhere('status', 'like', "%$search%");
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
        $query = AssetHistory::query();

        if ($search) {
            $query->where('asset_tag', 'like', "%$search%")
                ->orWhere('description', 'like', "%$search%")
                ->orWhere('prev_owner', 'like', "%$search%")
                ->orWhere('new_owner', 'like', "%$search%")
                ->orWhere('prev_location', 'like', "%$search%")
                ->orWhere('new_location', 'like', "%$search%")
                ->orWhere('remarks', 'like', "%$search%")
                ->orWhere('status', 'like', "%$search%");
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

        $selectedColumns = session('visible_columns_history', array_keys($columnMap));
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
    if (!in_array('id', $selectedFields)) {
        $selectedFields[] = 'id'; // assuming your asset_history table has an 'id' PK
    }

    $query = DB::table('asset_history')->select($selectedFields);

    $conditionColumns   = $request->input('condition_column', []);
    $conditionOperators = $request->input('condition_operator', []);
    $conditionValues    = $request->input('condition_value', []);
    $conditionLogics    = $request->input('condition_logic', []);
    $orderBy            = $request->input('order_by', 'date');
    $orderDir           = strtolower($request->input('order_dir', 'desc')) === 'desc' ? 'desc' : 'asc';

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
        fputcsv($handle, array_merge(['Sr.'], $selectedFields));
        foreach ($rows as $index => $row) {
            $csvRow = [$index + 1];
            foreach ($selectedFields as $field) {
                $csvRow[] = $row->$field ?? '';
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

    $history = $query->paginate(20)->appends($request->except('page'));

    return view('history.query', compact('history', 'columns', 'columnTypes', 'operators'));

}


}
