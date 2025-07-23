<?php
// app/Http/Controllers/DepartmentController.php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Department::query();

        if ($search) {
            $query->where('department', 'like', "%$search%");
        }

        $departments = $query->paginate(10);

        return view('admin-department', compact('departments'));
    }

    public function create()
    {
        return view('form-department');
    }

    public function store(Request $request)
    {
        $request->validate(['department' => 'required|string|unique:departments,department']);
        Department::create($request->only('department'));
        return redirect()->route('departments.index')->with('success', 'Department added successfully.');
    }

    public function edit(Department $department)
    {
        return view('form-department', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'department' => 'required|string|unique:departments,department,' . $department->department . ',department'
        ]);

        $department->update($request->only('department'));
        return redirect()->route('departments.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return redirect()->route('departments.index')->with('error', 'Department deleted successfully.');
    }

    public function export(Request $request)
{
    $search = $request->input('search');
    $query = \App\Models\Department::query();

    if ($search) {
        $query->where('department', 'like', "%$search%");
    }

    $departments = $query->get();

    $columnMap = [
        'department' => 'Department Name',
    ];

    $selectedColumns = session('visible_columns_departments', array_keys($columnMap));
    $headers = array_merge(['Sr.'], array_map(fn($col) => $columnMap[$col] ?? $col, $selectedColumns));

    $csv = implode(',', $headers) . "\n";

    foreach ($departments as $index => $dept) {
        $row = [$index + 1];
        foreach ($selectedColumns as $col) {
            $row[] = str_replace(',', ' ', $dept->$col ?? '');
        }
        $csv .= implode(',', $row) . "\n";
    }

    return response($csv)
        ->header('Content-Type', 'text/csv')
        ->header('Content-Disposition', 'attachment; filename="departments.csv"');
}

}
