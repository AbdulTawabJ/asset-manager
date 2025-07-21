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
        $request->validate(['location' => 'required|string|unique:locations,location']);
        Location::create($request->only('location'));
        return redirect()->route('locations.index')->with('success', 'Location added successfully.');
    }

    public function edit(Location $location)
    {
        return view('form-location', compact('location'));
    }

    public function update(Request $request, Location $location)
    {
        $request->validate([
    'location' => 'required|string|unique:locations,location,' . $location->location . ',location'
]);

        $location->update($request->only('location'));
        return redirect()->route('locations.index')->with('success', 'Location updated successfully.');
    }

    public function destroy(Location $location)
    {
        $location->delete();
        return redirect()->route('locations.index')->with('success', 'Location deleted successfully.');
    }
public function export(Request $request)
{
    $search = $request->input('search');

    $query = \App\Models\Location::query();

    if ($search) {
        $query->where('location', 'like', "%$search%");
    }

    $locations = $query->get();

    $allColumns = [
        'location' => 'Location Name',
        // Add more in the future here
    ];

    // Use page-specific session key
    $selectedColumns = session('visible_columns_locations', array_keys($allColumns));

    $headers = array_map(fn($col) => $allColumns[$col] ?? $col, $selectedColumns);

    $csv = implode(',', $headers) . "\n";

    foreach ($locations as $loc) {
        $row = [];
        foreach ($selectedColumns as $col) {
            $value = str_replace('"', '""', $loc->$col ?? '');
            $row[] = "\"$value\"";
        }
        $csv .= implode(',', $row) . "\n";
    }

    return response($csv)
        ->header('Content-Type', 'text/csv')
        ->header('Content-Disposition', 'attachment; filename="locations.csv"');
}

}
