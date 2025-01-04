<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the employees.
     */
    public function index()
    {
        $employees = Employee::with('category')->get();

        return Inertia::render('HR/Index', [
            'employees' => $employees,
            'categories' => Category::all(),
        ]);
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        $categories = Category::all();

        return Inertia::render('HR/Create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'nullable|string|max:15',
            'role' => 'required|string|max:50',
            'category_id' => 'required|exists:categories,id',
        ]);

        Employee::create($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Employee added successfully.');
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(Employee $employee)
    {
        
        $categories = Category::all();

        return Inertia::render('HR/Edit', [
            'employee' => $employee,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $employee->id,
            'phone' => 'nullable|string|max:15',
            'role' => 'required|string|max:50',
            'category_id' => 'required|exists:categories,id',
        ]);

        $employee->update($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();

        return back()->with( 'success', 'Employee deleted successfully.');
    }
}
