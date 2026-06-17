<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password as PasswordRule;
use App\Models\User;
use App\Support\Audit;
use App\Support\LeaveBalance;
use App\Support\Notify;
use App\Support\Workdays;
use App\Support\PayrollCalculator;
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

        // Brute-force protection: throttle by email + IP, max 5 attempts per minute.
        $throttleKey = Str::transliterate(Str::lower($credentials['email']) . '|' . $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Block sign-in if the linked employee record is inactive.
            $linked = Auth::user()->employee_id
                ? DB::table('employees')->where('id', Auth::user()->employee_id)->first()
                : null;

            if ($linked && $linked->status === 'Inactive') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'This account is inactive. Please contact HR.',
                ])->onlyInput('email');
            }

            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            return redirect()->intended(match (Auth::user()->role) {
                'Employee' => '/portal',
                'Manager' => '/tasks',
                default => '/dashboard',
            });
        }

        RateLimiter::hit($throttleKey); // 1 minute decay (default)

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    });

    /*
    | Password reset (forgot password)
    */
    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');

    Route::post('/forgot-password', function (Request $request) {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', 'A password reset link has been sent to your email.')
            : back()->withErrors(['email' => __($status)]);
    })->name('password.email');

    Route::get('/reset-password/{token}', function (string $token, Request $request) {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    })->name('password.reset');

    Route::post('/reset-password', function (Request $request) {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'must_change_password' => false,
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect('/login')->with('success', 'Your password has been reset. You can now sign in.')
            : back()->withErrors(['email' => __($status)]);
    })->name('password.update');
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->middleware('auth');

/*
| Landing page: show the login form to guests, dashboard to signed-in users.
*/
Route::get('/', function () {
    if (! Auth::check()) {
        return view('auth.login');
    }

    return redirect(match (Auth::user()->role) {
        'Employee' => '/portal',
        'Manager' => '/tasks',
        default => '/dashboard',
    });
});

/*
|--------------------------------------------------------------------------
| Notifications (all signed-in users)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'password.changed'])->group(function () {
    Route::get('/notifications', function () {
        $notifications = DB::table('notifications')
            ->where('user_id', Auth::id())
            ->orderBy('id', 'desc')
            ->limit(50)
            ->get();

        // Mark all as read on view.
        DB::table('notifications')->where('user_id', Auth::id())->whereNull('read_at')->update(['read_at' => now()]);

        return view('notifications.index', ['notifications' => $notifications]);
    });
});

/*
|--------------------------------------------------------------------------
| Tasks — assigned by Admin / HR / Manager
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Admin,HR,Manager'])->group(function () {

    // Employees this user may assign to: managers are limited to their own team.
    $assignableEmployees = function () {
        $query = DB::table('employees')->where('status', 'Active')->orderBy('name');

        if (Auth::user()->role === 'Manager') {
            $query->where('manager_id', Auth::user()->employee_id);
        }

        return $query->get();
    };

    Route::get('/tasks', function () use ($assignableEmployees) {
        $assignable = $assignableEmployees();
        $assignableIds = $assignable->pluck('id')->all();

        $tasks = DB::table('tasks')
            ->join('employees', 'tasks.assigned_to', '=', 'employees.id')
            ->leftJoin('users', 'tasks.assigned_by', '=', 'users.id')
            ->select('tasks.*', 'employees.name as employee_name', 'employees.position', 'users.name as assigner_name')
            ->when(Auth::user()->role === 'Manager', function ($q) use ($assignableIds) {
                // Managers see tasks they assigned or that belong to their team.
                $q->where(function ($w) use ($assignableIds) {
                    $w->where('tasks.assigned_by', Auth::id());
                    if ($assignableIds) {
                        $w->orWhereIn('tasks.assigned_to', $assignableIds);
                    }
                });
            })
            ->orderBy('tasks.id', 'desc')
            ->get();

        $base = DB::table('tasks');
        if (Auth::user()->role === 'Manager') {
            $ids = $assignableIds ?: [0];
            $base = DB::table('tasks')->whereIn('assigned_to', $ids);
        }

        return view('tasks.index', [
            'tasks' => $tasks,
            'employees' => $assignable,
            'totalTasks' => (clone $base)->count(),
            'pendingTasks' => (clone $base)->where('status', 'Pending')->count(),
            'inProgressTasks' => (clone $base)->where('status', 'In Progress')->count(),
            'completedTasks' => (clone $base)->where('status', 'Completed')->count(),
        ]);
    });

    Route::post('/tasks', function (Request $request) use ($assignableEmployees) {
        $request->validate([
            'assigned_to' => 'required|exists:employees,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:Low,Medium,High',
            'due_date' => 'nullable|date',
        ]);

        // Managers can only assign within their team.
        if (! $assignableEmployees()->pluck('id')->contains((int) $request->assigned_to)) {
            abort(403, 'You can only assign tasks to your team members.');
        }

        DB::table('tasks')->insert([
            'title' => $request->title,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to,
            'assigned_by' => Auth::id(),
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'status' => 'Pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $recipient = DB::table('users')->where('employee_id', $request->assigned_to)->first();
        if ($recipient) {
            Notify::send($recipient->id, Auth::user()->name . ' assigned you a task: "' . $request->title . '"', '/portal/tasks');
        }

        Audit::log('task.assign', Auth::user()->name . ' assigned task "' . $request->title . '"');

        return redirect('/tasks')->with('success', 'Task assigned successfully.');
    });

    Route::get('/tasks/{id}/edit', function ($id) use ($assignableEmployees) {
        $task = DB::table('tasks')->where('id', $id)->first();
        abort_if(! $task, 404);
        return view('tasks.edit', ['task' => $task, 'employees' => $assignableEmployees()]);
    });

    Route::put('/tasks/{id}', function (Request $request, $id) {
        $task = DB::table('tasks')->where('id', $id)->first();
        abort_if(! $task, 404);

        $request->validate([
            'assigned_to' => 'required|exists:employees,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:Low,Medium,High',
            'due_date' => 'nullable|date',
            'status' => 'required|in:Pending,In Progress,Completed',
        ]);

        DB::table('tasks')->where('id', $id)->update([
            'assigned_to' => $request->assigned_to,
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'status' => $request->status,
            'updated_at' => now(),
        ]);

        Audit::log('task.update', Auth::user()->name . ' updated task "' . $request->title . '"');

        return redirect('/tasks')->with('success', 'Task updated.');
    });

    Route::delete('/tasks/{id}', function ($id) {
        DB::table('tasks')->where('id', $id)->delete();
        Audit::log('task.delete', Auth::user()->name . ' deleted a task');
        return redirect('/tasks')->with('success', 'Task deleted.');
    });

    // Team roster (managers see their team; Admin/HR see everyone with managers).
    Route::get('/team', function () {
        $query = DB::table('employees')
            ->leftJoin('employees as mgr', 'employees.manager_id', '=', 'mgr.id')
            ->select('employees.*', 'mgr.name as manager_name');

        if (Auth::user()->role === 'Manager') {
            $query->where('employees.manager_id', Auth::user()->employee_id);
        }

        return view('team.index', ['members' => $query->orderBy('employees.name')->get()]);
    });
});

/*
|--------------------------------------------------------------------------
| Employee Self-Service Portal
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Employee', 'password.changed'])->prefix('portal')->group(function () {

    // Resolve the linked employee record (shared by all portal routes).
    $myEmployee = fn () => DB::table('employees')->where('id', Auth::user()->employee_id)->first();

    // Change password (also the forced landing for temp-password accounts).
    Route::get('/password', function () {
        return view('portal.password');
    });

    Route::post('/password', function (Request $request) {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->forceFill([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ])->save();

        return redirect('/portal')->with('success', 'Your password has been updated.');
    });

    Route::get('/', function () use ($myEmployee) {
        $me = $myEmployee();

        $data = ['me' => $me, 'recentAttendance' => collect(), 'pendingLeaves' => 0, 'lastPayslip' => null, 'leaveCount' => 0, 'payslipCount' => 0, 'openTasks' => 0, 'myTasks' => collect(), 'today' => null, 'balances' => []];

        if ($me) {
            $data['recentAttendance'] = DB::table('attendances')->where('employee_id', $me->id)
                ->orderBy('attendance_date', 'desc')->limit(5)->get();
            $data['pendingLeaves'] = DB::table('leaves')->where('employee_id', $me->id)->where('status', 'Pending')->count();
            $data['leaveCount'] = DB::table('leaves')->where('employee_id', $me->id)->count();
            $data['payslipCount'] = DB::table('payrolls')->where('employee_id', $me->id)->count();
            $data['lastPayslip'] = DB::table('payrolls')->where('employee_id', $me->id)
                ->orderBy('id', 'desc')->first();
            $data['openTasks'] = DB::table('tasks')->where('assigned_to', $me->id)->where('status', '!=', 'Completed')->count();
            $data['myTasks'] = DB::table('tasks')->where('assigned_to', $me->id)
                ->orderBy('id', 'desc')->limit(5)->get();
            $data['today'] = DB::table('attendances')->where('employee_id', $me->id)
                ->whereDate('attendance_date', now()->toDateString())->first();
            $data['balances'] = LeaveBalance::summary($me);
        }

        $data['announcements'] = DB::table('announcements')->orderBy('id', 'desc')->limit(3)->get();

        return view('portal.dashboard', $data);
    });

    // Self-service clock in / out for today.
    Route::post('/clock-in', function () use ($myEmployee) {
        $me = $myEmployee();
        abort_if(! $me, 403);

        $today = now()->toDateString();
        $existing = DB::table('attendances')->where('employee_id', $me->id)->whereDate('attendance_date', $today)->first();

        if ($existing && $existing->time_in) {
            return redirect('/portal')->with('success', 'You already clocked in today.');
        }

        $status = now()->format('H:i') > '09:00' ? 'Late' : 'Present';

        if ($existing) {
            DB::table('attendances')->where('id', $existing->id)->update([
                'time_in' => now()->format('H:i:s'), 'status' => $status, 'updated_at' => now(),
            ]);
        } else {
            DB::table('attendances')->insert([
                'employee_id' => $me->id, 'attendance_date' => $today,
                'time_in' => now()->format('H:i:s'), 'status' => $status,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        return redirect('/portal')->with('success', 'Clocked in at ' . now()->format('h:i A') . ' (' . $status . ').');
    });

    Route::post('/clock-out', function () use ($myEmployee) {
        $me = $myEmployee();
        abort_if(! $me, 403);

        $today = now()->toDateString();
        $existing = DB::table('attendances')->where('employee_id', $me->id)->whereDate('attendance_date', $today)->first();

        if (! $existing || ! $existing->time_in) {
            return redirect('/portal')->with('success', 'Please clock in first.');
        }

        DB::table('attendances')->where('id', $existing->id)->update([
            'time_out' => now()->format('H:i:s'), 'updated_at' => now(),
        ]);

        return redirect('/portal')->with('success', 'Clocked out at ' . now()->format('h:i A') . '.');
    });

    Route::get('/profile', function () use ($myEmployee) {
        return view('portal.profile', ['me' => $myEmployee()]);
    });

    Route::get('/attendance', function () use ($myEmployee) {
        $me = $myEmployee();
        $records = $me
            ? DB::table('attendances')->where('employee_id', $me->id)->orderBy('attendance_date', 'desc')->get()
            : collect();
        return view('portal.attendance', ['me' => $me, 'records' => $records]);
    });

    Route::get('/leave', function () use ($myEmployee) {
        $me = $myEmployee();
        $leaves = $me
            ? DB::table('leaves')->where('employee_id', $me->id)->orderBy('id', 'desc')->get()
            : collect();
        return view('portal.leave', [
            'me' => $me,
            'leaves' => $leaves,
            'balances' => $me ? LeaveBalance::summary($me) : [],
        ]);
    });

    Route::post('/leave', function (Request $request) use ($myEmployee) {
        $me = $myEmployee();
        abort_if(! $me, 403, 'Your account is not linked to an employee record. Please contact HR.');

        $request->validate([
            'leave_type' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        $totalDays = Workdays::between($request->start_date, $request->end_date);

        // Enforce remaining balance for tracked leave types.
        $remaining = LeaveBalance::remainingFor($me, $request->leave_type);
        if ($remaining !== null && $totalDays > $remaining) {
            return back()->withInput()->withErrors([
                'leave_type' => "Insufficient {$request->leave_type} balance. You have {$remaining} day(s) left.",
            ]);
        }

        DB::table('leaves')->insert([
            'employee_id' => $me->id,
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

        return redirect('/portal/leave')->with('success', 'Leave request submitted. Awaiting HR approval.');
    });

    Route::get('/payslips', function () use ($myEmployee) {
        $me = $myEmployee();
        $payrolls = $me
            ? DB::table('payrolls')->where('employee_id', $me->id)->orderBy('id', 'desc')->get()
            : collect();
        return view('portal.payslips', ['me' => $me, 'payrolls' => $payrolls]);
    });

    Route::get('/payslips/{id}', function ($id) {
        $payroll = DB::table('payrolls')
            ->join('employees', 'payrolls.employee_id', '=', 'employees.id')
            ->select('payrolls.*', 'employees.name as employee_name', 'employees.employee_id as employee_code', 'employees.department', 'employees.position')
            ->where('payrolls.id', $id)
            ->where('payrolls.employee_id', Auth::user()->employee_id) // ownership check
            ->first();
        abort_if(! $payroll, 404);
        return view('payroll.payslip', ['payroll' => $payroll]);
    });

    Route::get('/payslips/{id}/pdf', function ($id) {
        $payroll = DB::table('payrolls')
            ->join('employees', 'payrolls.employee_id', '=', 'employees.id')
            ->select('payrolls.*', 'employees.name as employee_name', 'employees.employee_id as employee_code', 'employees.department', 'employees.position')
            ->where('payrolls.id', $id)
            ->where('payrolls.employee_id', Auth::user()->employee_id) // ownership check
            ->first();
        abort_if(! $payroll, 404);

        $pdf = Barryvdh\DomPDF\Facade\Pdf::loadView('payroll.payslip-pdf', ['payroll' => $payroll]);
        return $pdf->download('payslip-' . $payroll->payroll_month . $payroll->payroll_year . '.pdf');
    });

    // My documents (read-only)
    Route::get('/documents', function () use ($myEmployee) {
        $me = $myEmployee();
        $documents = $me
            ? DB::table('employee_documents')->where('employee_id', $me->id)->orderBy('id', 'desc')->get()
            : collect();
        return view('portal.documents', ['me' => $me, 'documents' => $documents]);
    });

    Route::get('/documents/{id}/download', function ($id) {
        $doc = DB::table('employee_documents')
            ->where('id', $id)
            ->where('employee_id', Auth::user()->employee_id) // ownership check
            ->first();
        abort_if(! $doc, 404);
        abort_if(! Storage::disk('local')->exists($doc->file_path), 404);
        return Storage::disk('local')->download($doc->file_path, $doc->original_name ?? $doc->name);
    });

    Route::get('/tasks', function () use ($myEmployee) {
        $me = $myEmployee();
        $tasks = $me
            ? DB::table('tasks')
                ->leftJoin('users', 'tasks.assigned_by', '=', 'users.id')
                ->select('tasks.*', 'users.name as assigner_name')
                ->where('tasks.assigned_to', $me->id)
                ->orderBy('tasks.id', 'desc')
                ->get()
            : collect();

        return view('portal.tasks', ['me' => $me, 'tasks' => $tasks]);
    });

    Route::patch('/tasks/{id}/status', function (Request $request, $id) use ($myEmployee) {
        $me = $myEmployee();
        abort_if(! $me, 403);

        $request->validate(['status' => 'required|in:Pending,In Progress,Completed']);

        // Ownership: only the assigned employee may update their task.
        $task = DB::table('tasks')->where('id', $id)->where('assigned_to', $me->id)->first();
        abort_if(! $task, 404);

        DB::table('tasks')->where('id', $id)->update([
            'status' => $request->status,
            'updated_at' => now(),
        ]);

        // Notify the assigner of the update.
        Notify::send($task->assigned_by, $me->name . ' marked task "' . $task->title . '" as ' . $request->status, '/tasks');

        return redirect('/portal/tasks')->with('success', 'Task status updated.');
    });
});

/*
|--------------------------------------------------------------------------
| Protected HRIS Routes (Admin / HR backoffice)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Admin,HR'])->group(function () {

    /*
    | Dashboard
    */
    Route::get('/dashboard', function () {
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

        $headcountByDept = DB::table('employees')
            ->select('department', DB::raw('COUNT(*) as total'))
            ->groupBy('department')->orderBy('total', 'desc')->get();

        $leaveByStatus = DB::table('leaves')
            ->select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->get();

        return view('welcome', [
            'stats' => $stats,
            'recentEmployees' => $recentEmployees,
            'pendingLeaveList' => $pendingLeaveList,
            'headcountByDept' => $headcountByDept,
            'leaveByStatus' => $leaveByStatus,
            'upcomingHolidays' => DB::table('holidays')->whereDate('date', '>=', $today)->orderBy('date')->limit(4)->get(),
            'announcements' => DB::table('announcements')->orderBy('id', 'desc')->limit(3)->get(),
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
    Route::get('/employees', function (Request $request) {
        $q = trim((string) $request->query('q', ''));

        $employees = DB::table('employees')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('employee_id', 'like', "%{$q}%")
                        ->orWhere('department', 'like', "%{$q}%")
                        ->orWhere('position', 'like', "%{$q}%");
                });
            })
            ->orderBy('id', 'desc')
            ->get();

        return view('employees.index', [
            'employees' => $employees,
            'q' => $q,
            'totalEmployees' => DB::table('employees')->count(),
            'activeEmployees' => DB::table('employees')->where('status', 'Active')->count(),
            'onLeaveEmployees' => DB::table('employees')->where('status', 'On Leave')->count(),
            'departments' => DB::table('employees')->whereNotNull('department')->distinct()->count('department'),
        ]);
    });

    Route::get('/employees/create', function () {
        return view('employees.create', [
            'departments' => DB::table('departments')->orderBy('name')->get(),
            'managers' => DB::table('employees')->orderBy('name')->get(),
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
            'manager_id' => 'nullable|exists:employees,id',
            'vacation_credits' => 'nullable|integer|min:0|max:365',
            'sick_credits' => 'nullable|integer|min:0|max:365',
            'photo' => 'nullable|image|max:2048',
            'create_account' => 'nullable|boolean',
            'temp_password' => 'nullable|required_if:create_account,1|string|min:6',
        ]);

        // A login account needs a unique email as the identifier.
        if ($request->boolean('create_account')) {
            $request->validate([
                'email' => 'required|email|unique:users,email',
            ], [], ['email' => 'email address']);
        }

        $photoPath = $request->hasFile('photo')
            ? $request->file('photo')->store('photos', 'public')
            : null;

        $newId = DB::table('employees')->insertGetId([
            'name' => $request->name,
            'email' => $request->email,
            'contact_number' => $request->contact_number,
            'birthdate' => $request->birthdate,
            'address' => $request->address,
            'photo' => $photoPath,
            'employee_id' => $request->employee_id,
            'department' => $request->department,
            'position' => $request->position,
            'manager_id' => $request->manager_id,
            'date_hired' => $request->date_hired,
            'employment_type' => $request->employment_type,
            'status' => $request->status,
            'vacation_credits' => $request->vacation_credits ?? 15,
            'sick_credits' => $request->sick_credits ?? 15,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Audit::log('employee.create', Auth::user()->name . ' added employee ' . $request->name);

        $message = 'Employee added successfully.';

        if ($request->boolean('create_account')) {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'role' => 'Employee',
                'employee_id' => $newId,
                'password' => Hash::make($request->temp_password),
                'must_change_password' => true,
            ]);

            $message = 'Employee added and portal login created. They must change the temporary password on first sign-in.';
        }

        return redirect('/employees')->with('success', $message);
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

        $account = DB::table('users')->where('employee_id', $id)->first();

        return view('employees.show', [
            'employee' => $employee,
            'attendance' => $attendance,
            'leaves' => $leaves,
            'payrolls' => $payrolls,
            'account' => $account,
            'balances' => LeaveBalance::summary($employee),
            'manager' => $employee->manager_id ? DB::table('employees')->where('id', $employee->manager_id)->first() : null,
            'documents' => DB::table('employee_documents')->where('employee_id', $id)->orderBy('id', 'desc')->get(),
        ]);
    });

    // Employee documents (201 file)
    Route::post('/employees/{id}/documents', function (Request $request, $id) {
        abort_if(! DB::table('employees')->where('id', $id)->exists(), 404);
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'document' => 'required|file|max:10240', // 10MB
        ]);

        $path = $request->file('document')->store('documents'); // private (storage/app)

        DB::table('employee_documents')->insert([
            'employee_id' => $id,
            'name' => $request->name,
            'category' => $request->category,
            'file_path' => $path,
            'original_name' => $request->file('document')->getClientOriginalName(),
            'uploaded_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Audit::log('document.upload', Auth::user()->name . ' uploaded a document for employee #' . $id);
        return redirect('/employees/' . $id)->with('success', 'Document uploaded.');
    })->middleware('role:Admin,HR');

    Route::get('/documents/{id}/download', function ($id) {
        $doc = DB::table('employee_documents')->where('id', $id)->first();
        abort_if(! $doc, 404);
        abort_if(! Storage::disk('local')->exists($doc->file_path), 404);
        return Storage::disk('local')->download($doc->file_path, $doc->original_name ?? $doc->name);
    })->middleware('role:Admin,HR');

    Route::delete('/documents/{id}', function ($id) {
        $doc = DB::table('employee_documents')->where('id', $id)->first();
        abort_if(! $doc, 404);
        Storage::disk('local')->delete($doc->file_path);
        DB::table('employee_documents')->where('id', $id)->delete();
        Audit::log('document.delete', Auth::user()->name . ' deleted a document');
        return redirect('/employees/' . $doc->employee_id)->with('success', 'Document removed.');
    })->middleware('role:Admin,HR');

    // Create or reset a portal login account for an employee.
    Route::post('/employees/{id}/account', function (Request $request, $id) {
        $employee = DB::table('employees')->where('id', $id)->first();
        abort_if(! $employee, 404);

        $request->validate([
            'email' => 'required|email',
            'temp_password' => 'required|string|min:6',
        ]);

        $existing = DB::table('users')->where('employee_id', $id)->first();

        // Email must be unique across users (ignoring this employee's own account).
        $request->validate([
            'email' => 'unique:users,email' . ($existing ? ',' . $existing->id : ''),
        ], [], ['email' => 'email address']);

        if ($existing) {
            User::where('id', $existing->id)->update([
                'name' => $employee->name,
                'email' => $request->email,
                'password' => Hash::make($request->temp_password),
                'must_change_password' => true,
            ]);
            $msg = 'Login account reset. The employee must set a new password on next sign-in.';
        } else {
            User::create([
                'name' => $employee->name,
                'email' => $request->email,
                'role' => 'Employee',
                'employee_id' => $employee->id,
                'password' => Hash::make($request->temp_password),
                'must_change_password' => true,
            ]);
            $msg = 'Portal login created. Share the temporary password with the employee.';
        }

        return redirect('/employees/' . $id)->with('success', $msg);
    })->middleware('role:Admin,HR');

    Route::get('/employees/{id}/edit', function ($id) {
        $employee = DB::table('employees')->where('id', $id)->first();
        abort_if(! $employee, 404);
        return view('employees.edit', [
            'employee' => $employee,
            'departments' => DB::table('departments')->orderBy('name')->get(),
            'managers' => DB::table('employees')->where('id', '!=', $id)->orderBy('name')->get(),
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
            'manager_id' => 'nullable|exists:employees,id',
            'vacation_credits' => 'nullable|integer|min:0|max:365',
            'sick_credits' => 'nullable|integer|min:0|max:365',
            'photo' => 'nullable|image|max:2048',
        ]);

        $update = [
            'name' => $request->name,
            'email' => $request->email,
            'contact_number' => $request->contact_number,
            'birthdate' => $request->birthdate,
            'address' => $request->address,
            'employee_id' => $request->employee_id,
            'department' => $request->department,
            'position' => $request->position,
            'manager_id' => $request->manager_id ?: null,
            'date_hired' => $request->date_hired,
            'employment_type' => $request->employment_type,
            'status' => $request->status,
            'vacation_credits' => $request->vacation_credits ?? 15,
            'sick_credits' => $request->sick_credits ?? 15,
            'updated_at' => now(),
        ];

        if ($request->hasFile('photo')) {
            $update['photo'] = $request->file('photo')->store('photos', 'public');
        }

        DB::table('employees')->where('id', $id)->update($update);

        // Deactivate the linked login if the employee is set inactive.
        if ($request->status === 'Inactive') {
            DB::table('users')->where('employee_id', $id)->update(['updated_at' => now()]);
        }

        Audit::log('employee.update', Auth::user()->name . ' updated employee ' . $request->name);

        return redirect('/employees')->with('success', 'Employee updated successfully.');
    })->middleware('role:Admin,HR');

    Route::delete('/employees/{id}', function ($id) {
        $emp = DB::table('employees')->where('id', $id)->first();
        abort_if(! $emp, 404);

        // Remove dependent records to avoid orphans.
        DB::table('attendances')->where('employee_id', $id)->delete();
        DB::table('leaves')->where('employee_id', $id)->delete();
        DB::table('payrolls')->where('employee_id', $id)->delete();
        DB::table('tasks')->where('assigned_to', $id)->delete();
        DB::table('users')->where('employee_id', $id)->delete();           // their login
        DB::table('employees')->where('manager_id', $id)->update(['manager_id' => null]); // unlink reports
        DB::table('employees')->where('id', $id)->delete();

        Audit::log('employee.delete', Auth::user()->name . ' deleted employee ' . ($emp->name ?? $id));
        return redirect('/employees')->with('success', 'Employee and related records deleted.');
    })->middleware('role:Admin,HR');

    /*
    | Attendance
    */
    Route::get('/attendance', function (Request $request) {
        $today = now()->toDateString();
        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status', '');

        $attendanceRecords = DB::table('attendances')
            ->join('employees', 'attendances.employee_id', '=', 'employees.id')
            ->select('attendances.*', 'employees.name as employee_name', 'employees.employee_id as employee_code', 'employees.department', 'employees.position')
            ->when($q !== '', fn ($query) => $query->where(fn ($w) =>
                $w->where('employees.name', 'like', "%{$q}%")->orWhere('employees.employee_id', 'like', "%{$q}%")))
            ->when($status !== '', fn ($query) => $query->where('attendances.status', $status))
            ->orderBy('attendances.attendance_date', 'desc')
            ->orderBy('attendances.id', 'desc')
            ->paginate(15)->withQueryString();

        return view('attendance.index', [
            'attendanceRecords' => $attendanceRecords,
            'q' => $q,
            'status' => $status,
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

        $dupe = DB::table('attendances')
            ->where('employee_id', $request->employee_id)
            ->whereDate('attendance_date', $request->attendance_date)
            ->exists();
        if ($dupe) {
            return back()->withInput()->withErrors([
                'attendance_date' => 'An attendance record already exists for this employee on that date.',
            ]);
        }

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

        $dupe = DB::table('attendances')
            ->where('employee_id', $request->employee_id)
            ->whereDate('attendance_date', $request->attendance_date)
            ->where('id', '!=', $id)
            ->exists();
        if ($dupe) {
            return back()->withInput()->withErrors([
                'attendance_date' => 'Another attendance record already exists for this employee on that date.',
            ]);
        }

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
    Route::get('/leave', function (Request $request) {
        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status', '');

        $leaveRequests = DB::table('leaves')
            ->join('employees', 'leaves.employee_id', '=', 'employees.id')
            ->select('leaves.*', 'employees.name as employee_name', 'employees.employee_id as employee_code', 'employees.department', 'employees.position')
            ->when($q !== '', fn ($query) => $query->where(fn ($w) =>
                $w->where('employees.name', 'like', "%{$q}%")->orWhere('leaves.leave_type', 'like', "%{$q}%")))
            ->when($status !== '', fn ($query) => $query->where('leaves.status', $status))
            ->orderBy('leaves.id', 'desc')
            ->paginate(15)->withQueryString();

        return view('leave.index', [
            'leaveRequests' => $leaveRequests,
            'q' => $q,
            'status' => $status,
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

        $totalDays = Workdays::between($request->start_date, $request->end_date);

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
        $leave = DB::table('leaves')->where('id', $id)->first();
        DB::table('leaves')->where('id', $id)->update([
            'status' => 'Approved',
            'remarks' => 'Approved by ' . (Auth::user()->name ?? 'HR'),
            'updated_at' => now(),
        ]);

        if ($leave) {
            $recipient = DB::table('users')->where('employee_id', $leave->employee_id)->first();
            if ($recipient) {
                Notify::send($recipient->id, 'Your ' . $leave->leave_type . ' request was approved.', '/portal/leave');
            }
            Audit::log('leave.approve', Auth::user()->name . ' approved a leave request');
        }

        return redirect('/leave')->with('success', 'Leave request approved successfully.');
    })->middleware('role:Admin,HR');

    Route::patch('/leave/{id}/reject', function ($id) {
        $leave = DB::table('leaves')->where('id', $id)->first();
        DB::table('leaves')->where('id', $id)->update([
            'status' => 'Rejected',
            'remarks' => 'Rejected by ' . (Auth::user()->name ?? 'HR'),
            'updated_at' => now(),
        ]);

        if ($leave) {
            $recipient = DB::table('users')->where('employee_id', $leave->employee_id)->first();
            if ($recipient) {
                Notify::send($recipient->id, 'Your ' . $leave->leave_type . ' request was rejected.', '/portal/leave');
            }
            Audit::log('leave.reject', Auth::user()->name . ' rejected a leave request');
        }

        return redirect('/leave')->with('success', 'Leave request rejected successfully.');
    })->middleware('role:Admin,HR');

    Route::delete('/leave/{id}', function ($id) {
        DB::table('leaves')->where('id', $id)->delete();
        return redirect('/leave')->with('success', 'Leave request deleted successfully.');
    })->middleware('role:Admin,HR');

    /*
    | Payroll
    */
    Route::get('/payroll', function (Request $request) {
        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status', '');

        $payrollRecords = DB::table('payrolls')
            ->join('employees', 'payrolls.employee_id', '=', 'employees.id')
            ->select('payrolls.*', 'employees.name as employee_name', 'employees.employee_id as employee_code', 'employees.department', 'employees.position')
            ->when($q !== '', fn ($query) => $query->where(fn ($w) =>
                $w->where('employees.name', 'like', "%{$q}%")->orWhere('payrolls.payroll_month', 'like', "%{$q}%")))
            ->when($status !== '', fn ($query) => $query->where('payrolls.status', $status))
            ->orderBy('payrolls.id', 'desc')
            ->paginate(15)->withQueryString();

        return view('payroll.index', [
            'payrollRecords' => $payrollRecords,
            'q' => $q,
            'status' => $status,
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
            'other_deductions' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        $basicSalary = (float) ($request->basic_salary ?? 0);
        $allowances = (float) ($request->allowances ?? 0);
        $overtimePay = (float) ($request->overtime_pay ?? 0);
        $otherDeductions = (float) ($request->other_deductions ?? 0);

        // Auto-compute PH statutory contributions + withholding tax.
        $stat = PayrollCalculator::deductions($basicSalary);
        $totalDeductions = $stat['sss'] + $stat['philhealth'] + $stat['pagibig'] + $stat['tax'] + $otherDeductions;

        $grossPay = $basicSalary + $allowances + $overtimePay;
        $netPay = $grossPay - $totalDeductions;

        DB::table('payrolls')->insert([
            'employee_id' => $request->employee_id,
            'payroll_month' => $request->payroll_month,
            'payroll_year' => $request->payroll_year,
            'basic_salary' => $basicSalary,
            'allowances' => $allowances,
            'overtime_pay' => $overtimePay,
            'sss' => $stat['sss'],
            'philhealth' => $stat['philhealth'],
            'pagibig' => $stat['pagibig'],
            'tax' => $stat['tax'],
            'other_deductions' => $otherDeductions,
            'deductions' => round($totalDeductions, 2),
            'gross_pay' => $grossPay,
            'net_pay' => $netPay,
            'status' => 'Pending',
            'payment_date' => null,
            'remarks' => $request->remarks,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Audit::log('payroll.create', Auth::user()->name . ' created a payroll record');

        return redirect('/payroll')->with('success', 'Payroll added. Statutory deductions were computed automatically.');
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

    Route::get('/payroll/{id}/payslip/pdf', function ($id) {
        $payroll = DB::table('payrolls')
            ->join('employees', 'payrolls.employee_id', '=', 'employees.id')
            ->select('payrolls.*', 'employees.name as employee_name', 'employees.employee_id as employee_code', 'employees.department', 'employees.position')
            ->where('payrolls.id', $id)
            ->first();
        abort_if(! $payroll, 404);

        $pdf = Barryvdh\DomPDF\Facade\Pdf::loadView('payroll.payslip-pdf', ['payroll' => $payroll]);
        return $pdf->download('payslip-' . $payroll->employee_code . '-' . $payroll->payroll_month . $payroll->payroll_year . '.pdf');
    });

    Route::patch('/payroll/{id}/paid', function ($id) {
        DB::table('payrolls')->where('id', $id)->update([
            'status' => 'Paid',
            'payment_date' => now()->toDateString(),
            'updated_at' => now(),
        ]);

        $pay = DB::table('payrolls')->where('id', $id)->first();
        if ($pay) {
            $recipient = DB::table('users')->where('employee_id', $pay->employee_id)->first();
            if ($recipient) {
                Notify::send($recipient->id, 'Your payslip for ' . $pay->payroll_month . ' ' . $pay->payroll_year . ' is now available.', '/portal/payslips');
            }
        }
        Audit::log('payroll.paid', Auth::user()->name . ' marked a payroll as paid');

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

        // 13th-month pay = (total basic salary earned this year) / 12, per employee.
        $year = (int) now()->format('Y');
        $thirteenthMonth = DB::table('payrolls')
            ->join('employees', 'payrolls.employee_id', '=', 'employees.id')
            ->where('payrolls.payroll_year', $year)
            ->groupBy('employees.id', 'employees.name')
            ->select('employees.name as employee_name', DB::raw('SUM(payrolls.basic_salary) as total_basic'), DB::raw('SUM(payrolls.basic_salary) / 12 as thirteenth'))
            ->orderBy('employees.name')
            ->get();

        return view('reports.index', [
            'headcountByDept' => $headcountByDept,
            'attendanceSummary' => $attendanceSummary,
            'leaveSummary' => $leaveSummary,
            'payrollByMonth' => $payrollByMonth,
            'thirteenthMonth' => $thirteenthMonth,
            'reportYear' => $year,
            'totalEmployees' => DB::table('employees')->count(),
            'totalPaid' => DB::table('payrolls')->where('status', 'Paid')->sum('net_pay'),
        ]);
    });

    // CSV export: employees
    Route::get('/reports/export/employees', function () {
        $rows = DB::table('employees')->orderBy('name')->get();
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Employee ID', 'Name', 'Email', 'Department', 'Position', 'Status', 'Date Hired']);
            foreach ($rows as $r) {
                fputcsv($out, [$r->employee_id, $r->name, $r->email, $r->department, $r->position, $r->status, $r->date_hired]);
            }
            fclose($out);
        }, 'employees-' . now()->format('Ymd') . '.csv', ['Content-Type' => 'text/csv']);
    });

    // CSV export: payroll
    Route::get('/reports/export/payroll', function () {
        $rows = DB::table('payrolls')
            ->join('employees', 'payrolls.employee_id', '=', 'employees.id')
            ->select('payrolls.*', 'employees.name as employee_name', 'employees.employee_id as code')
            ->orderBy('payrolls.id', 'desc')->get();
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Employee ID', 'Name', 'Period', 'Gross', 'Deductions', 'Net Pay', 'Status', 'Payment Date']);
            foreach ($rows as $r) {
                fputcsv($out, [$r->code, $r->employee_name, $r->payroll_month . ' ' . $r->payroll_year, $r->gross_pay, $r->deductions, $r->net_pay, $r->status, $r->payment_date]);
            }
            fclose($out);
        }, 'payroll-' . now()->format('Ymd') . '.csv', ['Content-Type' => 'text/csv']);
    });

    /*
    | Holidays
    */
    Route::get('/holidays', function () {
        return view('holidays.index', [
            'holidays' => DB::table('holidays')->orderBy('date')->get(),
        ]);
    });

    Route::post('/holidays', function (Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date',
            'type' => 'required|in:Regular,Special',
        ]);
        DB::table('holidays')->insert([
            'name' => $request->name, 'date' => $request->date, 'type' => $request->type,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        Audit::log('holiday.create', Auth::user()->name . ' added holiday ' . $request->name);
        return redirect('/holidays')->with('success', 'Holiday added.');
    });

    Route::delete('/holidays/{id}', function ($id) {
        DB::table('holidays')->where('id', $id)->delete();
        return redirect('/holidays')->with('success', 'Holiday removed.');
    });

    /*
    | Announcements
    */
    Route::get('/announcements', function () {
        return view('announcements.index', [
            'announcements' => DB::table('announcements')->orderBy('id', 'desc')->get(),
        ]);
    });

    Route::post('/announcements', function (Request $request) {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        DB::table('announcements')->insert([
            'title' => $request->title,
            'body' => $request->body,
            'posted_by' => Auth::id(),
            'author_name' => Auth::user()->name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Bell-notify everyone (no email blast).
        $userIds = DB::table('users')->pluck('id');
        $rows = $userIds->map(fn ($uid) => [
            'user_id' => $uid,
            'message' => 'New announcement: ' . $request->title,
            'url' => '/announcements',
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();
        if ($rows) {
            DB::table('notifications')->insert($rows);
        }

        Audit::log('announcement.create', Auth::user()->name . ' posted "' . $request->title . '"');
        return redirect('/announcements')->with('success', 'Announcement posted.');
    })->middleware('role:Admin,HR');

    Route::delete('/announcements/{id}', function ($id) {
        DB::table('announcements')->where('id', $id)->delete();
        return redirect('/announcements')->with('success', 'Announcement removed.');
    })->middleware('role:Admin,HR');

    /*
    | Audit log (Admin only)
    */
    Route::get('/audit', function () {
        return view('audit.index', [
            'logs' => DB::table('audit_logs')->orderBy('id', 'desc')->limit(200)->get(),
        ]);
    })->middleware('role:Admin');
});
