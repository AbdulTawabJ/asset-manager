<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Employee::query();

        if ($search) {
            $query->where('file_no', 'like', "%$search%")
                  ->orWhere('first_name', 'like', "%$search%")
                  ->orWhere('middle_name', 'like', "%$search%")
                  ->orWhere('last_name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('department', 'like', "%$search%");
        }

        $employees = $query->paginate(10);
        return view('admin-employee', compact('employees'));
    }

public function create()
{
    $departments = Department::all();
    return view('form-employee', compact('departments'));
}


    // public function create()
    // {
    //     return view('form-employee');
    // }

    public function store(Request $request)
    {
        $request->validate([
            'file_no' => 'required|string|unique:employees,file_no',
            'first_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:employees,email',
            'department' => 'required|string',
        ]);

        Employee::create($request->only([
            'file_no', 'first_name', 'middle_name', 'last_name', 'email', 'department'
        ]));

        return redirect()->route('employees.index')->with('success', 'Employee added successfully.');
    }

    public function edit(Employee $employee)
    {
        $departments = Department::all();
        return view('form-employee', compact('employee', 'departments'));
    }
    

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'file_no' => 'required|string|unique:employees,file_no,' . $employee->file_no . ',file_no',
            'first_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'last_name' => 'required|string',
            'email' => 'required|email|unique:employees,email,' . $employee->file_no . ',file_no',
            'department' => 'required|string',
        ]);

        $employee->update($request->only([
            'file_no', 'first_name', 'middle_name', 'last_name', 'email', 'department'
        ]));

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }
    

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('employees.index')->with('error', 'Employee deleted successfully.');
    }

    public function export(Request $request)
    {
        $search = $request->input('search');
        $query = Employee::query();

        if ($search) {
            $query->where('file_no', 'like', "%$search%")
                  ->orWhere('first_name', 'like', "%$search%")
                  ->orWhere('middle_name', 'like', "%$search%")
                  ->orWhere('last_name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('department', 'like', "%$search%");
        }

        $employees = $query->get();

        $columnMap = [
            'file_no' => 'File No',
            'first_name' => 'First Name',
            'middle_name' => 'Middle Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'department' => 'Department',
        ];

        $selectedColumns = session('visible_columns_employees', array_keys($columnMap));
        $headers = array_merge(['Sr.'], array_map(fn($col) => $columnMap[$col] ?? $col, $selectedColumns));

        $csv = implode(',', $headers) . "\n";

        foreach ($employees as $index => $emp) {
            $row = [$index + 1];
            foreach ($selectedColumns as $col) {
                $row[] = str_replace(',', ' ', $emp->$col ?? '');
            }
            $csv .= implode(',', $row) . "\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="employees.csv"');
    }
}
