<?php
// app/Http/Controllers/LocationController.php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Location::query();

        if ($search) {
            $query->where('location', 'like', "%$search%");
        }

        $locations = $query->paginate(10);

        return view('admin-location', compact('locations'));
    }

    public function create()
    {
        return view('form-location');
    }

    public function store(Request $request)
    {
        $request->validate([
            'region' => 'required|string|not_regex:/[-\/:>0<~`]/',
            'area' => 'nullable|string|not_regex:/[-\/:>0<~`]/',
            'branch' => 'nullable|string|not_regex:/[-\/:>0<~`]/',
            'department' => 'nullable|string|not_regex:/[-\/:>0<~`]/',
        ]);

        $region = $request->input('region');
        $area = $request->input('area');
        $branch = $request->input('branch');
        $department = $request->input('department');

        // Hierarchy check
        if (!$region) {
            return back()->withErrors(['region' => 'Region is required.']);
        }
        if ($branch && !$area) {
            return back()->withErrors(['area' => 'Area is required if Branch is provided.']);
        }
        if ($department && (!$area || !$branch)) {
            return back()->withErrors(['department' => 'Area and Branch are required if Department is provided.']);
        }

        // Build location string based on level
        $parts = [$region];
        if ($area) $parts[] = $area;
        if ($branch) $parts[] = $branch;
        if ($department) $parts[] = $department;

        $locationStr = implode('-', $parts);


        Location::create(['location' => $locationStr]);

        return redirect()->route('locations.index')->with('success', 'Location added successfully.');
    }

    public function edit(Location $location)
    {
        return view('form-location', compact('location'));
    }

    public function update(Request $request, Location $location)
    {
        $request->validate([
            'region' => 'required|string|not_regex:/[-\/:>0<~`]/',
            'area' => 'nullable|string|not_regex:/[-\/:>0<~`]/',
            'branch' => 'nullable|string|not_regex:/[-\/:>0<~`]/',
            'department' => 'nullable|string|not_regex:/[-\/:>0<~`]/',
        ]);


        $region = $request->input('region');
        $area = $request->input('area');
        $branch = $request->input('branch');
        $department = $request->input('department');

        // Hierarchy check
        if (!$region) {
            return back()->withErrors(['region' => 'Region is required.']);
        }
        if ($branch && !$area) {
            return back()->withErrors(['area' => 'Area is required if Branch is provided.']);
        }
        if ($department && (!$area || !$branch)) {
            return back()->withErrors(['department' => 'Area and Branch are required if Department is provided.']);
        }
        
        // Build location string based on level
        $parts = [$region];
        if ($area) $parts[] = $area;
        if ($branch) $parts[] = $branch;
        if ($department) $parts[] = $department;
        
        $locationStr = implode('-', $parts);

        $location->update(['location' => $locationStr]);

        return redirect()->route('locations.index')->with('success', 'Location updated successfully.');
    }

    public function destroy(Location $location)
    {
        $location->delete();
        return redirect()->route('locations.index')->with('error', 'Location deleted successfully.');
    }
public function export(Request $request)
{
    $search = $request->input('search');
    $query = Location::query();

    if ($search) {
        $query->where('location', 'like', "%$search%");
    }

    $locations = $query->get();

    $columnMap = [
        'location' => 'Location Name',
    ];

    $selectedColumns = session('visible_columns_locations', array_keys($columnMap));
    $headers = array_merge(['Sr.'], array_map(fn($col) => $columnMap[$col] ?? $col, $selectedColumns));

    $csv = implode(',', $headers) . "\n";

    foreach ($locations as $index => $loc) {
        $row = [$index + 1];
        foreach ($selectedColumns as $col) {
            $row[] = str_replace(',', ' ', $loc->$col ?? '');
        }
        $csv .= implode(',', $row) . "\n";
    }

    return response($csv)
        ->header('Content-Type', 'text/csv')
        ->header('Content-Disposition', 'attachment; filename="locations.csv"');
}


}
