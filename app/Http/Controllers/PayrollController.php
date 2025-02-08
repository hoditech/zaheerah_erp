<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\User;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    /**
     * Display a listing of payrolls.
     */
    public function index()
    {
        $payrolls = Payroll::with('user')->latest()->get();

        return response()->json([
            'payrolls' => $payrolls,
        ]);
    }

    /**
     * Store a new payroll entry.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'base_salary' => 'nullable|numeric|min:0',
            'overtime_pay' => 'nullable|numeric|min:0',
            'bonuses' => 'nullable|numeric|min:0',
            'commission' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:bank_transfer,cash,mobile_money',
            'payment_status' => 'required|in:pending,paid',
            'payment_date' => 'required|date',
        ]);

        $employee = User::find($request->user_id);

        $netSalary = ($request->base_salary + $request->overtime_pay + $request->bonuses + $request->commission)
            - ($request->deductions + $request->tax);

        $payroll = Payroll::create([
            'user_id' => $request->user_id,
            'base_salary' => $request->base_salary ?? 0,
            'overtime_pay' => $request->overtime_pay ?? 0,
            'bonuses' => $request->bonuses ?? 0,
            'deductions' => $request->deductions ?? 0,
            'tax' => $request->tax ?? 0,
            'commission' => $request->commission ?? 0,
            'net_salary' => $netSalary,
            'payment_method' => $request->payment_method,
            'payment_status' => $request->payment_status,
            'payment_date' => $request->payment_date,
        ]);

        // Log the activity
        log_activity('Create Payroll', 'Created payroll for employee: ' . $employee->name);

        return response()->json([
            'message' => 'Payroll record added successfully',
        ]);
    }

    /**
     * Update a payroll entry.
     */
    public function update(Request $request, Payroll $payroll)
    {
        $request->validate([
            'base_salary' => 'nullable|numeric|min:0',
            'overtime_pay' => 'nullable|numeric|min:0',
            'bonuses' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:bank_transfer,cash,mobile_money',
            'payment_status' => 'required|in:pending,paid',
            'payment_date' => 'nullable|date',
        ]);

        $employee = $payroll->user;

        $netSalary = ($request->base_salary + $request->overtime_pay + $request->bonuses + $request->commission)
            - ($request->deductions + $request->tax);

        $payroll->update([
            'base_salary' => $request->base_salary,
            'overtime_pay' => $request->overtime_pay,
            'bonuses' => $request->bonuses,
            'deductions' => $request->deductions,
            'tax' => $request->tax,
            'commission' => $request->commission,
            'net_salary' => $netSalary,
            'payment_method' => $request->payment_method,
            'payment_status' => $request->payment_status,
            'payment_date' => $request->payment_date,
        ]);

        // Log the activity
        log_activity('Update Payroll', 'Updated payroll for employee: ' . $employee->name);

        return response()->json([
            'message' => 'Payroll updated successfully.',
        ]);
    }

    /**
     * Mark a payroll as paid.
     */
    public function markAsPaid(Payroll $payroll)
    {
        $payroll->update([
            'payment_status' => 'paid',
            'payment_date' => now(),
        ]);

        // Log the activity
        log_activity('Mark Payroll Paid', 'Marked payroll for employee: ' . $payroll->user->name . ' as paid.');

        return response()->json([
            'message' => 'Payroll marked as paid.',
        ]);
    }

    /**
     * Delete a payroll entry.
     */
    public function destroy(Payroll $payroll)
    {
        $employee = $payroll->user;

        // Log the activity
        log_activity('Delete Payroll', 'Deleted payroll for employee: ' . $employee->name);

        $payroll->delete();

        return response()->json([
            'message' => 'Payroll deleted successfully.',
        ]);
    }
}