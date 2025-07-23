<?php

namespace App\Http\Controllers;

use App\Models\AssetType;
use Illuminate\Http\Request;

class AssetTypeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = AssetType::query();

        if ($search) {
            $query->where('type', 'like', "%$search%");
        }

        $types = $query->paginate(10);

        return view('admin-type', compact('types'));
    }

    public function create()
    {
        return view('form-type');
    }

    public function store(Request $request)
    {
        $request->validate(['type' => 'required|string|unique:asset_types,type']);
        AssetType::create($request->only('type'));
        return redirect()->route('types.index')->with('success', 'Type added successfully.');
    }

    public function edit(AssetType $type)
    {
        return view('form-type', compact('type'));
    }

    public function update(Request $request, AssetType $type)
    {
        $request->validate([
            'type' => 'required|string|unique:asset_types,type,' . $type->type . ',type'
        ]);

        $type->update($request->only('type'));
        return redirect()->route('types.index')->with('success', 'Type updated successfully.');
    }

    public function destroy(AssetType $type)
    {
        $type->delete();
        return redirect()->route('types.index')->with('error', 'Type deleted successfully.');
    }

    public function export(Request $request)
    {
        $search = $request->input('search');
        $query = AssetType::query();

        if ($search) {
            $query->where('type', 'like', "%$search%");
        }

        $types = $query->get();

        $columnMap = [
            'type' => 'Type',
        ];

        $selectedColumns = session('visible_columns_types', array_keys($columnMap));
        $headers = array_merge(['Sr.'], array_map(fn($col) => $columnMap[$col] ?? $col, $selectedColumns));

        $csv = implode(',', $headers) . "\n";

        foreach ($types as $index => $type) {
            $row = [$index + 1];
            foreach ($selectedColumns as $col) {
                $row[] = str_replace(',', ' ', $type->$col ?? '');
            }
            $csv .= implode(',', $row) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="types.csv"');
    }
}
