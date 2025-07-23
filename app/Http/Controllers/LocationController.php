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
