<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', function (Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    });
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
})->middleware('auth');

/*
|--------------------------------------------------------------------------
| Protected HRIS Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    /*
    | Dashboard / Home Page
    */
    Route::get('/', function () {
        $today = now()->toDateString();

        $stats = [
            'totalEmployees'   => DB::table('employees')->count(),
            'activeEmployees'  => DB::table('employees')->where('status', 'Active')->count(),
            'onLeaveEmployees' => DB::table('employees')->where('status', 'On Leave')->count(),
            'departments'      => DB::table('departments')->count(),
            'presentToday'     => DB::table('attendances')->whereDate('attendance_date', $today)->where('status', 'Present')->count(),
            'lateToday'        => DB::table('attendances')->whereDate('attendance_date', $today)->where('status', 'Late')->count(),
            'pendingLeaves'    => DB::table('leaves')->where('status', 'Pending')->count(),
            'pendingPayrolls'  => DB::table('payrolls')->where('status', 'Pending')->count(),
            'totalNetPay'      => DB::table('payrolls')->where('status', 'Paid')->sum('net_pay'),
        ];

        $recentEmployees = DB::table('employees')->orderBy('id', 'desc')->limit(5)->get();

        $pendingLeaveList = DB::table('leaves')
            ->join('employees', 'leaves.employee_id', '=', 'employees.id')
            ->where('leaves.status', 'Pending')
            ->select('leaves.*', 'employees.name as employee_name')
            ->orderBy('leaves.id', 'desc')
            ->limit(5)
            ->get();

        return view('welcome', [
            'stats' => $stats,
            'recentEmployees' => $recentEmployees,
            'pendingLeaveList' => $pendingLeaveList,
        ]);
    });

    /*
    | Departments
    */
    Route::get('/departments', function () {
        $departments = DB::table('departments')
            ->leftJoin('employees', 'departments.name', '=', 'employees.department')
            ->select('departments.*', DB::raw('COUNT(employees.id) as employee_count'))
            ->groupBy('departments.id', 'departments.name', 'departments.description', 'departments.created_at', 'departments.updated_at')
            ->orderBy('departments.name')
            ->get();

        return view('departments.index', ['departments' => $departments]);
    });

    Route::get('/departments/create', function () {
        return view('departments.create');
    })->middleware('role:Admin,HR');

    Route::post('/departments', function (Request $request) {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'description' => 'nullable|string',
        ]);

        DB::table('departments')->insert([
            'name' => $request->name,
            'description' => $request->description,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect('/departments')->with('success', 'Department added successfully.');
    })->middleware('role:Admin,HR');

    Route::get('/departments/{id}/edit', function ($id) {
        $department = DB::table('departments')->where('id', $id)->first();
        abort_if(! $department, 404);
        return view('departments.edit', ['department' => $department]);
    })->middleware('role:Admin,HR');

    Route::put('/departments/{id}', function (Request $request, $id) {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $id,
            'description' => 'nullable|string',
        ]);

        DB::table('departments')->where('id', $id)->update([
            'name' => $request->name,
            'description' => $request->description,
            'updated_at' => now(),
        ]);

        return redirect('/departments')->with('success', 'Department updated successfully.');
    })->middleware('role:Admin,HR');

    Route::delete('/departments/{id}', function ($id) {
        DB::table('departments')->where('id', $id)->delete();
        return redirect('/departments')->with('success', 'Department deleted successfully.');
    })->middleware('role:Admin,HR');

    /*
    | Employees
    */
    Route::get('/employees', function () {
        $employees = DB::table('employees')->orderBy('id', 'desc')->get();

        return view('employees.index', [
            'employees' => $employees,
            'totalEmployees' => DB::table('employees')->count(),
            'activeEmployees' => DB::table('employees')->where('status', 'Active')->count(),
            'onLeaveEmployees' => DB::table('employees')->where('status', 'On Leave')->count(),
            'departments' => DB::table('employees')->whereNotNull('department')->distinct()->count('department'),
        ]);
    });

    Route::get('/employees/create', function () {
        return view('employees.create', [
            'departments' => DB::table('departments')->orderBy('name')->get(),
        ]);
    })->middleware('role:Admin,HR');

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
    })->middleware('role:Admin,HR');

    Route::get('/employees/{id}', function ($id) {
        $employee = DB::table('employees')->where('id', $id)->first();
        abort_if(! $employee, 404);

        $attendance = DB::table('attendances')->where('employee_id', $id)
            ->orderBy('attendance_date', 'desc')->limit(5)->get();
        $leaves = DB::table('leaves')->where('employee_id', $id)
            ->orderBy('id', 'desc')->limit(5)->get();
        $payrolls = DB::table('payrolls')->where('employee_id', $id)
            ->orderBy('id', 'desc')->limit(5)->get();

        return view('employees.show', [
            'employee' => $employee,
            'attendance' => $attendance,
            'leaves' => $leaves,
            'payrolls' => $payrolls,
        ]);
    });

    Route::get('/employees/{id}/edit', function ($id) {
        $employee = DB::table('employees')->where('id', $id)->first();
        abort_if(! $employee, 404);
        return view('employees.edit', [
            'employee' => $employee,
            'departments' => DB::table('departments')->orderBy('name')->get(),
        ]);
    })->middleware('role:Admin,HR');

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

        DB::table('employees')->where('id', $id)->update([
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
    })->middleware('role:Admin,HR');

    Route::delete('/employees/{id}', function ($id) {
        DB::table('employees')->where('id', $id)->delete();
        return redirect('/employees')->with('success', 'Employee deleted successfully.');
    })->middleware('role:Admin,HR');

    /*
    | Attendance
    */
    Route::get('/attendance', function () {
        $today = now()->toDateString();

        $attendanceRecords = DB::table('attendances')
            ->join('employees', 'attendances.employee_id', '=', 'employees.id')
            ->select('attendances.*', 'employees.name as employee_name', 'employees.employee_id as employee_code', 'employees.department', 'employees.position')
            ->orderBy('attendances.attendance_date', 'desc')
            ->orderBy('attendances.id', 'desc')
            ->get();

        return view('attendance.index', [
            'attendanceRecords' => $attendanceRecords,
            'totalToday' => DB::table('attendances')->whereDate('attendance_date', $today)->count(),
            'presentToday' => DB::table('attendances')->whereDate('attendance_date', $today)->where('status', 'Present')->count(),
            'lateToday' => DB::table('attendances')->whereDate('attendance_date', $today)->where('status', 'Late')->count(),
            'absentToday' => DB::table('attendances')->whereDate('attendance_date', $today)->where('status', 'Absent')->count(),
        ]);
    });

    Route::get('/attendance/create', function () {
        return view('attendance.create', [
            'employees' => DB::table('employees')->where('status', 'Active')->orderBy('name')->get(),
        ]);
    })->middleware('role:Admin,HR');

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
    })->middleware('role:Admin,HR');

    Route::get('/attendance/{id}/edit', function ($id) {
        $record = DB::table('attendances')->where('id', $id)->first();
        abort_if(! $record, 404);
        return view('attendance.edit', [
            'record' => $record,
            'employees' => DB::table('employees')->orderBy('name')->get(),
        ]);
    })->middleware('role:Admin,HR');

    Route::put('/attendance/{id}', function (Request $request, $id) {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'attendance_date' => 'required|date',
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'status' => 'required|string|in:Present,Late,Absent,Half Day,On Leave',
            'remarks' => 'nullable|string',
        ]);

        DB::table('attendances')->where('id', $id)->update([
            'employee_id' => $request->employee_id,
            'attendance_date' => $request->attendance_date,
            'time_in' => $request->time_in,
            'time_out' => $request->time_out,
            'status' => $request->status,
            'remarks' => $request->remarks,
            'updated_at' => now(),
        ]);

        return redirect('/attendance')->with('success', 'Attendance record updated successfully.');
    })->middleware('role:Admin,HR');

    Route::delete('/attendance/{id}', function ($id) {
        DB::table('attendances')->where('id', $id)->delete();
        return redirect('/attendance')->with('success', 'Attendance record deleted successfully.');
    })->middleware('role:Admin,HR');

    /*
    | Leave
    */
    Route::get('/leave', function () {
        $leaveRequests = DB::table('leaves')
            ->join('employees', 'leaves.employee_id', '=', 'employees.id')
            ->select('leaves.*', 'employees.name as employee_name', 'employees.employee_id as employee_code', 'employees.department', 'employees.position')
            ->orderBy('leaves.id', 'desc')
            ->get();

        return view('leave.index', [
            'leaveRequests' => $leaveRequests,
            'totalLeaves' => DB::table('leaves')->count(),
            'pendingLeaves' => DB::table('leaves')->where('status', 'Pending')->count(),
            'approvedLeaves' => DB::table('leaves')->where('status', 'Approved')->count(),
            'rejectedLeaves' => DB::table('leaves')->where('status', 'Rejected')->count(),
        ]);
    });

    Route::get('/leave/create', function () {
        return view('leave.create', [
            'employees' => DB::table('employees')->where('status', 'Active')->orderBy('name')->get(),
        ]);
    })->middleware('role:Admin,HR');

    Route::post('/leave', function (Request $request) {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        $totalDays = Carbon::parse($request->start_date)->diffInDays(Carbon::parse($request->end_date)) + 1;

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
    })->middleware('role:Admin,HR');

    Route::get('/leave/{id}', function ($id) {
        $leave = DB::table('leaves')
            ->join('employees', 'leaves.employee_id', '=', 'employees.id')
            ->select('leaves.*', 'employees.name as employee_name', 'employees.employee_id as employee_code', 'employees.department', 'employees.position')
            ->where('leaves.id', $id)
            ->first();
        abort_if(! $leave, 404);
        return view('leave.show', ['leave' => $leave]);
    });

    Route::patch('/leave/{id}/approve', function ($id) {
        DB::table('leaves')->where('id', $id)->update([
            'status' => 'Approved',
            'remarks' => 'Approved by ' . (Auth::user()->name ?? 'HR'),
            'updated_at' => now(),
        ]);
        return redirect('/leave')->with('success', 'Leave request approved successfully.');
    })->middleware('role:Admin,HR');

    Route::patch('/leave/{id}/reject', function ($id) {
        DB::table('leaves')->where('id', $id)->update([
            'status' => 'Rejected',
            'remarks' => 'Rejected by ' . (Auth::user()->name ?? 'HR'),
            'updated_at' => now(),
        ]);
        return redirect('/leave')->with('success', 'Leave request rejected successfully.');
    })->middleware('role:Admin,HR');

    Route::delete('/leave/{id}', function ($id) {
        DB::table('leaves')->where('id', $id)->delete();
        return redirect('/leave')->with('success', 'Leave request deleted successfully.');
    })->middleware('role:Admin,HR');

    /*
    | Payroll
    */
    Route::get('/payroll', function () {
        $payrollRecords = DB::table('payrolls')
            ->join('employees', 'payrolls.employee_id', '=', 'employees.id')
            ->select('payrolls.*', 'employees.name as employee_name', 'employees.employee_id as employee_code', 'employees.department', 'employees.position')
            ->orderBy('payrolls.id', 'desc')
            ->get();

        return view('payroll.index', [
            'payrollRecords' => $payrollRecords,
            'totalPayrolls' => DB::table('payrolls')->count(),
            'pendingPayrolls' => DB::table('payrolls')->where('status', 'Pending')->count(),
            'paidPayrolls' => DB::table('payrolls')->where('status', 'Paid')->count(),
            'totalNetPay' => DB::table('payrolls')->sum('net_pay'),
        ]);
    });

    Route::get('/payroll/create', function () {
        return view('payroll.create', [
            'employees' => DB::table('employees')->where('status', 'Active')->orderBy('name')->get(),
        ]);
    })->middleware('role:Admin,HR');

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
    })->middleware('role:Admin,HR');

    Route::get('/payroll/{id}', function ($id) {
        $payroll = DB::table('payrolls')
            ->join('employees', 'payrolls.employee_id', '=', 'employees.id')
            ->select('payrolls.*', 'employees.name as employee_name', 'employees.employee_id as employee_code', 'employees.department', 'employees.position')
            ->where('payrolls.id', $id)
            ->first();
        abort_if(! $payroll, 404);
        return view('payroll.show', ['payroll' => $payroll]);
    });

    Route::get('/payroll/{id}/payslip', function ($id) {
        $payroll = DB::table('payrolls')
            ->join('employees', 'payrolls.employee_id', '=', 'employees.id')
            ->select('payrolls.*', 'employees.name as employee_name', 'employees.employee_id as employee_code', 'employees.department', 'employees.position')
            ->where('payrolls.id', $id)
            ->first();
        abort_if(! $payroll, 404);
        return view('payroll.payslip', ['payroll' => $payroll]);
    });

    Route::patch('/payroll/{id}/paid', function ($id) {
        DB::table('payrolls')->where('id', $id)->update([
            'status' => 'Paid',
            'payment_date' => now()->toDateString(),
            'updated_at' => now(),
        ]);
        return redirect('/payroll')->with('success', 'Payroll marked as paid successfully.');
    })->middleware('role:Admin,HR');

    Route::delete('/payroll/{id}', function ($id) {
        DB::table('payrolls')->where('id', $id)->delete();
        return redirect('/payroll')->with('success', 'Payroll record deleted successfully.');
    })->middleware('role:Admin,HR');

    /*
    | Reports
    */
    Route::get('/reports', function () {
        $headcountByDept = DB::table('employees')
            ->select('department', DB::raw('COUNT(*) as total'))
            ->groupBy('department')
            ->orderBy('total', 'desc')
            ->get();

        $attendanceSummary = DB::table('attendances')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        $leaveSummary = DB::table('leaves')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        $payrollByMonth = DB::table('payrolls')
            ->select('payroll_year', 'payroll_month', DB::raw('SUM(net_pay) as total_net'), DB::raw('COUNT(*) as records'))
            ->groupBy('payroll_year', 'payroll_month')
            ->orderBy('payroll_year', 'desc')
            ->get();

        return view('reports.index', [
            'headcountByDept' => $headcountByDept,
            'attendanceSummary' => $attendanceSummary,
            'leaveSummary' => $leaveSummary,
            'payrollByMonth' => $payrollByMonth,
            'totalEmployees' => DB::table('employees')->count(),
            'totalPaid' => DB::table('payrolls')->where('status', 'Paid')->sum('net_pay'),
        ]);
    });
});
