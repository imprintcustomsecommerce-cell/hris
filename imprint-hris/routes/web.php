<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Dashboard / Home Page
Route::get('/', function () {
    return view('welcome');
});


// Employees List Page
Route::get('/employees', function () {
    $employees = DB::table('employees')
        ->orderBy('id', 'desc')
        ->get();

    $totalEmployees = DB::table('employees')->count();

    $activeEmployees = DB::table('employees')
        ->where('status', 'Active')
        ->count();

    $onLeaveEmployees = DB::table('employees')
        ->where('status', 'On Leave')
        ->count();

    $departments = DB::table('employees')
        ->whereNotNull('department')
        ->distinct()
        ->count('department');

    return view('employees.index', [
        'employees' => $employees,
        'totalEmployees' => $totalEmployees,
        'activeEmployees' => $activeEmployees,
        'onLeaveEmployees' => $onLeaveEmployees,
        'departments' => $departments,
    ]);
});


// Add Employee Page
Route::get('/employees/create', function () {
    return view('employees.create');
});


// Save Employee
Route::post('/employees', function (Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'nullable|email|max:255',
        'contact_number' => 'nullable|string|max:255',
        'birthdate' => 'nullable|date',
        'address' => 'nullable|string',

        'employee_id' => 'required|string|max:255|unique:employees,employee_id',
        'department' => 'required|string|max:255',
        'position' => 'required|string|max:255',
        'date_hired' => 'required|date',
        'employment_type' => 'nullable|string|max:255',
        'status' => 'required|string|max:255',
    ]);

    DB::table('employees')->insert([
        'name' => $request->name,
        'email' => $request->email,
        'contact_number' => $request->contact_number,
        'birthdate' => $request->birthdate,
        'address' => $request->address,

        'employee_id' => $request->employee_id,
        'department' => $request->department,
        'position' => $request->position,
        'date_hired' => $request->date_hired,
        'employment_type' => $request->employment_type,
        'status' => $request->status,

        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect('/employees')->with('success', 'Employee added successfully.');
});


// View Single Employee
Route::get('/employees/{id}', function ($id) {
    $employee = DB::table('employees')
        ->where('id', $id)
        ->first();

    if (!$employee) {
        abort(404);
    }

    return view('employees.show', [
        'employee' => $employee,
    ]);
});


// Edit Employee Page
Route::get('/employees/{id}/edit', function ($id) {
    $employee = DB::table('employees')
        ->where('id', $id)
        ->first();

    if (!$employee) {
        abort(404);
    }

    return view('employees.edit', [
        'employee' => $employee,
    ]);
});


// Update Employee
Route::put('/employees/{id}', function (Request $request, $id) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'nullable|email|max:255',
        'contact_number' => 'nullable|string|max:255',
        'birthdate' => 'nullable|date',
        'address' => 'nullable|string',

        'employee_id' => 'required|string|max:255|unique:employees,employee_id,' . $id,
        'department' => 'required|string|max:255',
        'position' => 'required|string|max:255',
        'date_hired' => 'required|date',
        'employment_type' => 'nullable|string|max:255',
        'status' => 'required|string|max:255',
    ]);

    DB::table('employees')
        ->where('id', $id)
        ->update([
            'name' => $request->name,
            'email' => $request->email,
            'contact_number' => $request->contact_number,
            'birthdate' => $request->birthdate,
            'address' => $request->address,

            'employee_id' => $request->employee_id,
            'department' => $request->department,
            'position' => $request->position,
            'date_hired' => $request->date_hired,
            'employment_type' => $request->employment_type,
            'status' => $request->status,

            'updated_at' => now(),
        ]);

    return redirect('/employees')->with('success', 'Employee updated successfully.');
});


// Delete Employee
Route::delete('/employees/{id}', function ($id) {
    DB::table('employees')
        ->where('id', $id)
        ->delete();

    return redirect('/employees')->with('success', 'Employee deleted successfully.');
});

// Attendance List Page
Route::get('/attendance', function () {
    $today = now()->toDateString();

    $attendanceRecords = DB::table('attendances')
        ->join('employees', 'attendances.employee_id', '=', 'employees.id')
        ->select(
            'attendances.*',
            'employees.name as employee_name',
            'employees.employee_id as employee_code',
            'employees.department',
            'employees.position'
        )
        ->orderBy('attendances.attendance_date', 'desc')
        ->orderBy('attendances.id', 'desc')
        ->get();

    $totalToday = DB::table('attendances')
        ->whereDate('attendance_date', $today)
        ->count();

    $presentToday = DB::table('attendances')
        ->whereDate('attendance_date', $today)
        ->where('status', 'Present')
        ->count();

    $lateToday = DB::table('attendances')
        ->whereDate('attendance_date', $today)
        ->where('status', 'Late')
        ->count();

    $absentToday = DB::table('attendances')
        ->whereDate('attendance_date', $today)
        ->where('status', 'Absent')
        ->count();

    return view('attendance.index', [
        'attendanceRecords' => $attendanceRecords,
        'totalToday' => $totalToday,
        'presentToday' => $presentToday,
        'lateToday' => $lateToday,
        'absentToday' => $absentToday,
    ]);
});


// Add Attendance Page
Route::get('/attendance/create', function () {
    $employees = DB::table('employees')
        ->where('status', 'Active')
        ->orderBy('name', 'asc')
        ->get();

    return view('attendance.create', [
        'employees' => $employees,
    ]);
});


// Save Attendance
Route::post('/attendance', function (Request $request) {
    $request->validate([
        'employee_id' => 'required|exists:employees,id',
        'attendance_date' => 'required|date',
        'time_in' => 'nullable|date_format:H:i',
        'time_out' => 'nullable|date_format:H:i',
        'status' => 'required|string|in:Present,Late,Absent,Half Day,On Leave',
        'remarks' => 'nullable|string',
    ]);

    DB::table('attendances')->insert([
        'employee_id' => $request->employee_id,
        'attendance_date' => $request->attendance_date,
        'time_in' => $request->time_in,
        'time_out' => $request->time_out,
        'status' => $request->status,
        'remarks' => $request->remarks,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect('/attendance')->with('success', 'Attendance record added successfully.');
});

// Leave List Page
Route::get('/leave', function () {
    $leaveRequests = DB::table('leaves')
        ->join('employees', 'leaves.employee_id', '=', 'employees.id')
        ->select(
            'leaves.*',
            'employees.name as employee_name',
            'employees.employee_id as employee_code',
            'employees.department',
            'employees.position'
        )
        ->orderBy('leaves.id', 'desc')
        ->get();

    $totalLeaves = DB::table('leaves')->count();

    $pendingLeaves = DB::table('leaves')
        ->where('status', 'Pending')
        ->count();

    $approvedLeaves = DB::table('leaves')
        ->where('status', 'Approved')
        ->count();

    $rejectedLeaves = DB::table('leaves')
        ->where('status', 'Rejected')
        ->count();

    return view('leave.index', [
        'leaveRequests' => $leaveRequests,
        'totalLeaves' => $totalLeaves,
        'pendingLeaves' => $pendingLeaves,
        'approvedLeaves' => $approvedLeaves,
        'rejectedLeaves' => $rejectedLeaves,
    ]);
});


// Add Leave Page
Route::get('/leave/create', function () {
    $employees = DB::table('employees')
        ->where('status', 'Active')
        ->orderBy('name', 'asc')
        ->get();

    return view('leave.create', [
        'employees' => $employees,
    ]);
});


// Save Leave Request
Route::post('/leave', function (Request $request) {
    $request->validate([
        'employee_id' => 'required|exists:employees,id',
        'leave_type' => 'required|string|max:255',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'reason' => 'nullable|string',
    ]);

    $startDate = Carbon::parse($request->start_date);
    $endDate = Carbon::parse($request->end_date);
    $totalDays = $startDate->diffInDays($endDate) + 1;

    DB::table('leaves')->insert([
        'employee_id' => $request->employee_id,
        'leave_type' => $request->leave_type,
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
        'total_days' => $totalDays,
        'reason' => $request->reason,
        'status' => 'Pending',
        'remarks' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect('/leave')->with('success', 'Leave request added successfully.');
});


// Approve Leave
Route::patch('/leave/{id}/approve', function ($id) {
    DB::table('leaves')
        ->where('id', $id)
        ->update([
            'status' => 'Approved',
            'remarks' => 'Approved by HR',
            'updated_at' => now(),
        ]);

    return redirect('/leave')->with('success', 'Leave request approved successfully.');
});


// Reject Leave
Route::patch('/leave/{id}/reject', function ($id) {
    DB::table('leaves')
        ->where('id', $id)
        ->update([
            'status' => 'Rejected',
            'remarks' => 'Rejected by HR',
            'updated_at' => now(),
        ]);

    return redirect('/leave')->with('success', 'Leave request rejected successfully.');
});


// Delete Leave
Route::delete('/leave/{id}', function ($id) {
    DB::table('leaves')
        ->where('id', $id)
        ->delete();

    return redirect('/leave')->with('success', 'Leave request deleted successfully.');
});

// Payroll List Page
Route::get('/payroll', function () {
    $payrollRecords = DB::table('payrolls')
        ->join('employees', 'payrolls.employee_id', '=', 'employees.id')
        ->select(
            'payrolls.*',
            'employees.name as employee_name',
            'employees.employee_id as employee_code',
            'employees.department',
            'employees.position'
        )
        ->orderBy('payrolls.id', 'desc')
        ->get();

    $totalPayrolls = DB::table('payrolls')->count();

    $pendingPayrolls = DB::table('payrolls')
        ->where('status', 'Pending')
        ->count();

    $paidPayrolls = DB::table('payrolls')
        ->where('status', 'Paid')
        ->count();

    $totalNetPay = DB::table('payrolls')
        ->sum('net_pay');

    return view('payroll.index', [
        'payrollRecords' => $payrollRecords,
        'totalPayrolls' => $totalPayrolls,
        'pendingPayrolls' => $pendingPayrolls,
        'paidPayrolls' => $paidPayrolls,
        'totalNetPay' => $totalNetPay,
    ]);
});


// Add Payroll Page
Route::get('/payroll/create', function () {
    $employees = DB::table('employees')
        ->where('status', 'Active')
        ->orderBy('name', 'asc')
        ->get();

    return view('payroll.create', [
        'employees' => $employees,
    ]);
});


// Save Payroll
Route::post('/payroll', function (Request $request) {
    $request->validate([
        'employee_id' => 'required|exists:employees,id',
        'payroll_month' => 'required|string|max:255',
        'payroll_year' => 'required|integer|min:2000|max:2100',
        'basic_salary' => 'required|numeric|min:0',
        'allowances' => 'nullable|numeric|min:0',
        'overtime_pay' => 'nullable|numeric|min:0',
        'deductions' => 'nullable|numeric|min:0',
        'remarks' => 'nullable|string',
    ]);

    $basicSalary = $request->basic_salary ?? 0;
    $allowances = $request->allowances ?? 0;
    $overtimePay = $request->overtime_pay ?? 0;
    $deductions = $request->deductions ?? 0;

    $grossPay = $basicSalary + $allowances + $overtimePay;
    $netPay = $grossPay - $deductions;

    DB::table('payrolls')->insert([
        'employee_id' => $request->employee_id,
        'payroll_month' => $request->payroll_month,
        'payroll_year' => $request->payroll_year,

        'basic_salary' => $basicSalary,
        'allowances' => $allowances,
        'overtime_pay' => $overtimePay,
        'deductions' => $deductions,

        'gross_pay' => $grossPay,
        'net_pay' => $netPay,

        'status' => 'Pending',
        'payment_date' => null,
        'remarks' => $request->remarks,

        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect('/payroll')->with('success', 'Payroll record added successfully.');
});


// Mark Payroll as Paid
Route::patch('/payroll/{id}/paid', function ($id) {
    DB::table('payrolls')
        ->where('id', $id)
        ->update([
            'status' => 'Paid',
            'payment_date' => now()->toDateString(),
            'updated_at' => now(),
        ]);

    return redirect('/payroll')->with('success', 'Payroll marked as paid successfully.');
});


// Delete Payroll
Route::delete('/payroll/{id}', function ($id) {
    DB::table('payrolls')
        ->where('id', $id)
        ->delete();

    return redirect('/payroll')->with('success', 'Payroll record deleted successfully.');
});