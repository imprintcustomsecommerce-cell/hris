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
use App\Support\Setting;
use App\Support\Shift;
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
                'Applicant' => '/apply',
                'CEO' => '/projects',
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
        'Applicant' => '/apply',
        'CEO' => '/projects',
        default => '/dashboard',
    });
});

/*
|--------------------------------------------------------------------------
| Shared account: change password (any signed-in user)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/account/password', function () {
        return view('account.password');
    });

    Route::post('/account/password', function (Request $request) {
        $user = $request->user();
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->forceFill([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ])->save();

        $home = match ($user->role) {
            'Employee' => '/portal',
            'Manager' => '/tasks',
            'Applicant' => '/apply',
            'CEO' => '/projects',
            default => '/dashboard',
        };

        return redirect($home)->with('success', 'Your password has been updated.');
    });
});

/*
|--------------------------------------------------------------------------
| Applicant Portal (temporary accounts)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Applicant', 'password.changed'])->prefix('apply')->group(function () {

    $me = fn () => DB::table('applicants')->where('id', Auth::user()->applicant_id)->first();

    Route::get('/', function () use ($me) {
        $applicant = $me();
        $nextInterview = $applicant
            ? DB::table('interviews')->where('applicant_id', $applicant->id)->where('status', 'Scheduled')
                ->orderBy('scheduled_at')->first()
            : null;
        $docs = $applicant ? DB::table('applicant_documents')->where('applicant_id', $applicant->id)->count() : 0;

        return view('apply.dashboard', ['me' => $applicant, 'nextInterview' => $nextInterview, 'docCount' => $docs]);
    });

    Route::get('/documents', function () use ($me) {
        $applicant = $me();
        $documents = $applicant
            ? DB::table('applicant_documents')->where('applicant_id', $applicant->id)->orderBy('id', 'desc')->get()
            : collect();
        return view('apply.documents', ['me' => $applicant, 'documents' => $documents]);
    });

    Route::post('/documents', function (Request $request) use ($me) {
        $applicant = $me();
        abort_if(! $applicant, 403);
        $request->validate([
            'name' => 'required|string|max:255',
            'document' => 'required|file|max:10240',
        ]);

        $path = $request->file('document')->store('applicant-documents');

        DB::table('applicant_documents')->insert([
            'applicant_id' => $applicant->id,
            'name' => $request->name,
            'category' => 'Requirement',
            'file_path' => $path,
            'original_name' => $request->file('document')->getClientOriginalName(),
            'uploaded_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect('/apply/documents')->with('success', 'Document submitted.');
    });

    Route::get('/documents/{id}/download', function ($id) use ($me) {
        $applicant = $me();
        $doc = DB::table('applicant_documents')->where('id', $id)->where('applicant_id', optional($applicant)->id)->first();
        abort_if(! $doc || ! Storage::disk('local')->exists($doc->file_path), 404);
        return Storage::disk('local')->download($doc->file_path, $doc->original_name ?? $doc->name);
    });

    Route::get('/interviews', function () use ($me) {
        $applicant = $me();
        $interviews = $applicant
            ? DB::table('interviews')->where('applicant_id', $applicant->id)->orderBy('scheduled_at', 'desc')->get()
            : collect();
        return view('apply.interviews', ['me' => $applicant, 'interviews' => $interviews]);
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
| Projects — CEO assigns to a Manager; Manager breaks into team tasks
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Admin,CEO,Manager'])->group(function () {

    $canManageProject = function ($project) {
        // CEO/Admin manage all; a Manager manages only their assigned projects.
        return in_array(Auth::user()->role, ['Admin', 'CEO'], true) || $project->manager_id === Auth::id();
    };

    Route::get('/projects', function () {
        $isExec = in_array(Auth::user()->role, ['Admin', 'CEO'], true);

        $projects = DB::table('projects')
            ->leftJoin('users', 'projects.manager_id', '=', 'users.id')
            ->select('projects.*', 'users.name as manager_name')
            ->when(! $isExec, fn ($q) => $q->where('projects.manager_id', Auth::id()))
            ->orderBy('projects.id', 'desc')
            ->get();

        // Per-project task progress.
        $progress = DB::table('tasks')
            ->select('project_id', DB::raw('COUNT(*) as total'), DB::raw("SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as done"))
            ->whereNotNull('project_id')
            ->groupBy('project_id')
            ->get()->keyBy('project_id');

        return view('projects.index', [
            'projects' => $projects,
            'progress' => $progress,
            'isExec' => $isExec,
            'managers' => DB::table('users')->where('role', 'Manager')->orderBy('name')->get(),
        ]);
    });

    Route::post('/projects', function (Request $request) {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'manager_id' => 'required|exists:users,id',
            'deadline' => 'nullable|date',
        ]);

        $id = DB::table('projects')->insertGetId([
            'title' => $request->title,
            'description' => $request->description,
            'manager_id' => $request->manager_id,
            'created_by' => Auth::id(),
            'creator_name' => Auth::user()->name,
            'deadline' => $request->deadline,
            'status' => 'Pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Notify::send((int) $request->manager_id, Auth::user()->name . ' assigned you a project: "' . $request->title . '"', '/projects/' . $id);
        Audit::log('project.create', Auth::user()->name . ' created project "' . $request->title . '"');

        return redirect('/projects/' . $id)->with('success', 'Project created and assigned.');
    })->middleware('role:Admin,CEO');

    Route::get('/projects/{id}', function ($id) use ($canManageProject) {
        $project = DB::table('projects')
            ->leftJoin('users', 'projects.manager_id', '=', 'users.id')
            ->select('projects.*', 'users.name as manager_name')
            ->where('projects.id', $id)->first();
        abort_if(! $project, 404);
        abort_if(! $canManageProject($project), 403);

        $tasks = DB::table('tasks')
            ->join('employees', 'tasks.assigned_to', '=', 'employees.id')
            ->select('tasks.*', 'employees.name as employee_name')
            ->where('tasks.project_id', $id)->orderBy('tasks.id', 'desc')->get();

        // Employees the current manager (or exec) may assign to.
        $managerEmpId = DB::table('users')->where('id', $project->manager_id)->value('employee_id');
        $team = DB::table('employees')->where('status', 'Active')
            ->when($managerEmpId, fn ($q) => $q->where(fn ($w) => $w->where('manager_id', $managerEmpId)->orWhere('id', $managerEmpId)))
            ->orderBy('name')->get();

        return view('projects.show', [
            'project' => $project,
            'tasks' => $tasks,
            'team' => $team,
            'doneCount' => $tasks->where('status', 'Completed')->count(),
            'canManage' => $canManageProject($project),
        ]);
    });

    Route::post('/projects/{id}/tasks', function (Request $request, $id) use ($canManageProject) {
        $project = DB::table('projects')->where('id', $id)->first();
        abort_if(! $project, 404);
        abort_if(! $canManageProject($project), 403);

        $request->validate([
            'assigned_to' => 'required|exists:employees,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:Low,Medium,High',
            'due_date' => 'nullable|date',
        ]);

        DB::table('tasks')->insert([
            'project_id' => $id,
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

        // Project moves to In Progress once it has tasks.
        if ($project->status === 'Pending') {
            DB::table('projects')->where('id', $id)->update(['status' => 'In Progress', 'updated_at' => now()]);
        }

        $recipient = DB::table('users')->where('employee_id', $request->assigned_to)->first();
        if ($recipient) {
            Notify::send($recipient->id, Auth::user()->name . ' assigned you a task on "' . $project->title . '": ' . $request->title, '/portal/tasks');
        }

        return redirect('/projects/' . $id)->with('success', 'Task added to project.');
    });

    Route::patch('/projects/{id}/complete', function ($id) use ($canManageProject) {
        $project = DB::table('projects')->where('id', $id)->first();
        abort_if(! $project, 404);
        abort_if(! $canManageProject($project), 403);

        $total = DB::table('tasks')->where('project_id', $id)->count();
        $open = DB::table('tasks')->where('project_id', $id)->where('status', '!=', 'Completed')->count();

        if ($total === 0) {
            return back()->with('success', 'Add at least one task before completing the project.');
        }
        if ($open > 0) {
            return back()->with('success', "Cannot complete: {$open} task(s) still unfinished.");
        }

        DB::table('projects')->where('id', $id)->update(['status' => 'Completed', 'updated_at' => now()]);

        if ($project->created_by) {
            Notify::send($project->created_by, 'Project "' . $project->title . '" has been completed.', '/projects/' . $id);
        }
        Audit::log('project.complete', Auth::user()->name . ' completed project "' . $project->title . '"');

        return redirect('/projects/' . $id)->with('success', 'Project marked as completed.');
    });

    Route::delete('/projects/{id}', function ($id) {
        DB::table('tasks')->where('project_id', $id)->update(['project_id' => null]);
        DB::table('projects')->where('id', $id)->delete();
        return redirect('/projects')->with('success', 'Project deleted.');
    })->middleware('role:Admin,CEO');
});

/*
|--------------------------------------------------------------------------
| Performance Reviews (HR / Admin / Manager)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:Admin,HR,Manager'])->group(function () {

    // Employees the reviewer may appraise (managers limited to their team).
    $reviewable = function () {
        $q = DB::table('employees')->where('status', 'Active')->orderBy('name');
        if (Auth::user()->role === 'Manager') {
            $q->where('manager_id', Auth::user()->employee_id);
        }
        return $q->get();
    };

    Route::get('/reviews', function () use ($reviewable) {
        $ids = $reviewable()->pluck('id')->all();
        $isManager = Auth::user()->role === 'Manager';

        $reviews = DB::table('performance_reviews')
            ->join('employees', 'performance_reviews.employee_id', '=', 'employees.id')
            ->select('performance_reviews.*', 'employees.name as employee_name', 'employees.position')
            ->when($isManager, fn ($q) => $q->where(function ($w) use ($ids) {
                $w->where('performance_reviews.reviewer_id', Auth::id());
                if ($ids) $w->orWhereIn('performance_reviews.employee_id', $ids);
            }))
            ->orderBy('performance_reviews.id', 'desc')
            ->get();

        return view('reviews.index', ['reviews' => $reviews, 'employees' => $reviewable()]);
    });

    Route::post('/reviews', function (Request $request) use ($reviewable) {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'period' => 'required|string|max:255',
            'rating_quality' => 'required|integer|min:1|max:5',
            'rating_productivity' => 'required|integer|min:1|max:5',
            'rating_teamwork' => 'required|integer|min:1|max:5',
            'rating_punctuality' => 'required|integer|min:1|max:5',
            'strengths' => 'nullable|string',
            'improvements' => 'nullable|string',
            'comments' => 'nullable|string',
        ]);

        if (! $reviewable()->pluck('id')->contains((int) $request->employee_id)) {
            abort(403, 'You can only review your team members.');
        }

        $id = DB::table('performance_reviews')->insertGetId([
            'employee_id' => $request->employee_id,
            'reviewer_id' => Auth::id(),
            'reviewer_name' => Auth::user()->name,
            'period' => $request->period,
            'rating_quality' => $request->rating_quality,
            'rating_productivity' => $request->rating_productivity,
            'rating_teamwork' => $request->rating_teamwork,
            'rating_punctuality' => $request->rating_punctuality,
            'strengths' => $request->strengths,
            'improvements' => $request->improvements,
            'comments' => $request->comments,
            'status' => 'Draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Audit::log('review.create', Auth::user()->name . ' drafted a performance review');
        return redirect('/reviews/' . $id)->with('success', 'Review saved as draft.');
    });

    Route::get('/reviews/{id}', function ($id) use ($reviewable) {
        $review = DB::table('performance_reviews')
            ->join('employees', 'performance_reviews.employee_id', '=', 'employees.id')
            ->select('performance_reviews.*', 'employees.name as employee_name', 'employees.position', 'employees.department')
            ->where('performance_reviews.id', $id)->first();
        abort_if(! $review, 404);
        if (Auth::user()->role === 'Manager' && ! $reviewable()->pluck('id')->contains($review->employee_id) && $review->reviewer_id !== Auth::id()) {
            abort(403);
        }
        return view('reviews.show', ['review' => $review]);
    });

    Route::patch('/reviews/{id}/finalize', function ($id) {
        $review = DB::table('performance_reviews')->where('id', $id)->first();
        abort_if(! $review, 404);
        DB::table('performance_reviews')->where('id', $id)->update(['status' => 'Finalized', 'updated_at' => now()]);

        $acct = DB::table('users')->where('employee_id', $review->employee_id)->first();
        if ($acct) {
            Notify::send($acct->id, 'Your performance review for ' . $review->period . ' is now available.', '/portal/reviews');
        }
        Audit::log('review.finalize', Auth::user()->name . ' finalized a performance review');
        return redirect('/reviews/' . $id)->with('success', 'Review finalized and shared with the employee.');
    });

    Route::delete('/reviews/{id}', function ($id) {
        DB::table('performance_reviews')->where('id', $id)->delete();
        return redirect('/reviews')->with('success', 'Review deleted.');
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

        $timeIn = now()->format('H:i:s');
        $status = Shift::isLate($timeIn) ? 'Late' : 'Present';
        $lateMinutes = Shift::metrics($timeIn, null)['late'];

        if ($existing) {
            DB::table('attendances')->where('id', $existing->id)->update([
                'time_in' => $timeIn, 'status' => $status, 'late_minutes' => $lateMinutes, 'updated_at' => now(),
            ]);
        } else {
            DB::table('attendances')->insert([
                'employee_id' => $me->id, 'attendance_date' => $today,
                'time_in' => $timeIn, 'status' => $status, 'late_minutes' => $lateMinutes,
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

        $timeOut = now()->format('H:i:s');
        $m = Shift::metrics($existing->time_in, $timeOut);

        DB::table('attendances')->where('id', $existing->id)->update([
            'time_out' => $timeOut,
            'undertime_minutes' => $m['undertime'],
            'overtime_minutes' => $m['overtime'],
            'updated_at' => now(),
        ]);

        return redirect('/portal')->with('success', 'Clocked out at ' . now()->format('h:i A') . '.');
    });

    Route::get('/profile', function () use ($myEmployee) {
        return view('portal.profile', ['me' => $myEmployee()]);
    });

    Route::get('/profile/edit', function () use ($myEmployee) {
        $me = $myEmployee();
        abort_if(! $me, 403, 'Your account is not linked to an employee record. Please contact HR.');
        return view('portal.profile-edit', ['me' => $me]);
    });

    Route::put('/profile', function (Request $request) use ($myEmployee) {
        $me = $myEmployee();
        abort_if(! $me, 403);

        $request->validate([
            'contact_number' => 'nullable|string|max:255',
            'birthdate' => 'nullable|date',
            'address' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
        ]);

        $update = [
            'contact_number' => $request->contact_number,
            'birthdate' => $request->birthdate,
            'address' => $request->address,
            'updated_at' => now(),
        ];

        if ($request->hasFile('photo')) {
            if ($me->photo) {
                Storage::disk('public')->delete($me->photo);
            }
            $update['photo'] = $request->file('photo')->store('photos', 'public');
        }

        DB::table('employees')->where('id', $me->id)->update($update);

        return redirect('/portal/profile')->with('success', 'Your profile has been updated.');
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

    // My performance reviews (finalized only)
    Route::get('/reviews', function () use ($myEmployee) {
        $me = $myEmployee();
        $reviews = $me
            ? DB::table('performance_reviews')->where('employee_id', $me->id)->where('status', 'Finalized')->orderBy('id', 'desc')->get()
            : collect();
        return view('portal.reviews', ['me' => $me, 'reviews' => $reviews]);
    });

    Route::get('/reviews/{id}', function ($id) {
        $review = DB::table('performance_reviews')
            ->where('id', $id)
            ->where('employee_id', Auth::user()->employee_id) // ownership
            ->where('status', 'Finalized')
            ->first();
        abort_if(! $review, 404);
        return view('portal.review-show', ['review' => $review]);
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
    | Org chart
    */
    Route::get('/org-chart', function () {
        $all = DB::table('employees')->orderBy('name')->get();
        return view('org.index', [
            'roots' => $all->whereNull('manager_id')->values(),
            'children' => $all->groupBy('manager_id'),
            'total' => $all->count(),
        ]);
    });

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
            'defaultVacation' => (int) Setting::get('default_vacation_credits', 15),
            'defaultSick' => (int) Setting::get('default_sick_credits', 15),
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

        $m = Shift::metrics($request->time_in, $request->time_out);
        DB::table('attendances')->insert([
            'employee_id' => $request->employee_id,
            'attendance_date' => $request->attendance_date,
            'time_in' => $request->time_in,
            'time_out' => $request->time_out,
            'status' => $request->status,
            'late_minutes' => $m['late'],
            'undertime_minutes' => $m['undertime'],
            'overtime_minutes' => $m['overtime'],
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

        $m = Shift::metrics($request->time_in, $request->time_out);
        DB::table('attendances')->where('id', $id)->update([
            'employee_id' => $request->employee_id,
            'attendance_date' => $request->attendance_date,
            'time_in' => $request->time_in,
            'time_out' => $request->time_out,
            'status' => $request->status,
            'late_minutes' => $m['late'],
            'undertime_minutes' => $m['undertime'],
            'overtime_minutes' => $m['overtime'],
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

    Route::get('/leave-calendar', function (Request $request) {
        $month = $request->query('month');
        $base = $month ? Carbon::parse($month . '-01') : Carbon::now()->startOfMonth();
        $monthStart = $base->copy()->startOfMonth();
        $monthEnd = $base->copy()->endOfMonth();

        $gridStart = $monthStart->copy()->startOfWeek(Carbon::SUNDAY);
        $gridEnd = $monthEnd->copy()->endOfWeek(Carbon::SATURDAY);

        $holidays = DB::table('holidays')
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->get()->keyBy(fn ($h) => Carbon::parse($h->date)->toDateString());

        $leaves = DB::table('leaves')
            ->join('employees', 'leaves.employee_id', '=', 'employees.id')
            ->where('leaves.status', 'Approved')
            ->whereDate('leaves.start_date', '<=', $monthEnd->toDateString())
            ->whereDate('leaves.end_date', '>=', $monthStart->toDateString())
            ->select('employees.name as employee_name', 'leaves.start_date', 'leaves.end_date', 'leaves.leave_type')
            ->get();

        $days = [];
        for ($d = $gridStart->copy(); $d->lte($gridEnd); $d->addDay()) {
            $ds = $d->toDateString();
            $days[] = [
                'date' => $d->copy(),
                'inMonth' => $d->month === $base->month,
                'isToday' => $d->isToday(),
                'holiday' => $holidays[$ds]->name ?? null,
                'leaves' => $leaves->filter(fn ($l) => $ds >= $l->start_date && $ds <= $l->end_date)->values(),
            ];
        }

        return view('leave.calendar', [
            'days' => $days,
            'title' => $base->format('F Y'),
            'prev' => $base->copy()->subMonth()->format('Y-m'),
            'next' => $base->copy()->addMonth()->format('Y-m'),
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

        // Active loan amortization for this employee (capped at outstanding balance).
        $loanDeduction = (float) DB::table('loans')
            ->where('employee_id', $request->employee_id)
            ->where('status', 'Active')
            ->get()
            ->sum(fn ($l) => min((float) $l->monthly_amortization, (float) $l->balance));

        $totalDeductions = $stat['sss'] + $stat['philhealth'] + $stat['pagibig'] + $stat['tax'] + $otherDeductions + $loanDeduction;

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
            'loan_deduction' => round($loanDeduction, 2),
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
        $pay = DB::table('payrolls')->where('id', $id)->first();
        abort_if(! $pay, 404);

        DB::table('payrolls')->where('id', $id)->update([
            'status' => 'Paid',
            'payment_date' => now()->toDateString(),
            'updated_at' => now(),
        ]);

        // Apply this payroll's loan deduction against the employee's active loans.
        if ($pay->loan_deduction > 0) {
            $remaining = (float) $pay->loan_deduction;
            $loans = DB::table('loans')->where('employee_id', $pay->employee_id)->where('status', 'Active')->orderBy('id')->get();
            foreach ($loans as $loan) {
                if ($remaining <= 0) break;
                $pay_amt = min($remaining, (float) $loan->monthly_amortization, (float) $loan->balance);
                $newBalance = round((float) $loan->balance - $pay_amt, 2);
                DB::table('loans')->where('id', $loan->id)->update([
                    'balance' => $newBalance,
                    'status' => $newBalance <= 0 ? 'Paid' : 'Active',
                    'updated_at' => now(),
                ]);
                $remaining -= $pay_amt;
            }
        }

        $recipient = DB::table('users')->where('employee_id', $pay->employee_id)->first();
        if ($recipient) {
            Notify::send($recipient->id, 'Your payslip for ' . $pay->payroll_month . ' ' . $pay->payroll_year . ' is now available.', '/portal/payslips');
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
    | Recruitment — Applicants
    */
    Route::get('/applicants', function () {
        return view('applicants.index', [
            'applicants' => DB::table('applicants')->orderBy('id', 'desc')->get(),
        ]);
    });

    Route::post('/applicants', function (Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'position_applied' => 'required|string|max:255',
            'create_account' => 'nullable|boolean',
            'temp_password' => 'nullable|required_if:create_account,1|string|min:6',
        ]);

        if ($request->boolean('create_account')) {
            $request->validate(['email' => 'required|email|unique:users,email'], [], ['email' => 'email address']);
        }

        $id = DB::table('applicants')->insertGetId([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'position_applied' => $request->position_applied,
            'status' => 'Applied',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $msg = 'Applicant added.';
        if ($request->boolean('create_account')) {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'role' => 'Applicant',
                'applicant_id' => $id,
                'password' => Hash::make($request->temp_password),
                'must_change_password' => true,
            ]);
            $msg = 'Applicant added with a temporary portal login. Share the password so they can submit requirements.';
        }

        Audit::log('applicant.create', Auth::user()->name . ' added applicant ' . $request->name);
        return redirect('/applicants/' . $id)->with('success', $msg);
    })->middleware('role:Admin,HR');

    Route::get('/applicants/{id}', function ($id) {
        $applicant = DB::table('applicants')->where('id', $id)->first();
        abort_if(! $applicant, 404);
        return view('applicants.show', [
            'applicant' => $applicant,
            'account' => DB::table('users')->where('applicant_id', $id)->first(),
            'interviews' => DB::table('interviews')->where('applicant_id', $id)->orderBy('scheduled_at', 'desc')->get(),
            'documents' => DB::table('applicant_documents')->where('applicant_id', $id)->orderBy('id', 'desc')->get(),
        ]);
    });

    Route::patch('/applicants/{id}/status', function (Request $request, $id) {
        $request->validate(['status' => 'required|in:Applied,For Interview,For Requirements,Hired,Rejected']);
        DB::table('applicants')->where('id', $id)->update(['status' => $request->status, 'updated_at' => now()]);

        $acct = DB::table('users')->where('applicant_id', $id)->first();
        if ($acct) {
            Notify::send($acct->id, 'Your application status is now: ' . $request->status, '/apply');
        }
        return redirect('/applicants/' . $id)->with('success', 'Status updated.');
    })->middleware('role:Admin,HR');

    Route::post('/applicants/{id}/account', function (Request $request, $id) {
        $applicant = DB::table('applicants')->where('id', $id)->first();
        abort_if(! $applicant, 404);
        $existing = DB::table('users')->where('applicant_id', $id)->first();
        $request->validate([
            'email' => 'required|email|unique:users,email' . ($existing ? ',' . $existing->id : ''),
            'temp_password' => 'required|string|min:6',
        ], [], ['email' => 'email address']);

        if ($existing) {
            User::where('id', $existing->id)->update([
                'email' => $request->email,
                'password' => Hash::make($request->temp_password),
                'must_change_password' => true,
            ]);
        } else {
            User::create([
                'name' => $applicant->name, 'email' => $request->email, 'role' => 'Applicant',
                'applicant_id' => $id, 'password' => Hash::make($request->temp_password), 'must_change_password' => true,
            ]);
        }
        return redirect('/applicants/' . $id)->with('success', 'Temporary login ready.');
    })->middleware('role:Admin,HR');

    Route::post('/applicants/{id}/interviews', function (Request $request, $id) {
        abort_if(! DB::table('applicants')->where('id', $id)->exists(), 404);
        $request->validate([
            'scheduled_at' => 'required|date',
            'mode' => 'required|in:Onsite,Online,Phone',
            'location' => 'nullable|string|max:255',
            'interviewer' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        DB::table('interviews')->insert([
            'applicant_id' => $id,
            'scheduled_at' => $request->scheduled_at,
            'mode' => $request->mode,
            'location' => $request->location,
            'interviewer' => $request->interviewer,
            'notes' => $request->notes,
            'status' => 'Scheduled',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('applicants')->where('id', $id)->update(['status' => 'For Interview', 'updated_at' => now()]);

        $acct = DB::table('users')->where('applicant_id', $id)->first();
        if ($acct) {
            Notify::send($acct->id, 'An interview has been scheduled for ' . Carbon::parse($request->scheduled_at)->format('M d, Y h:i A') . ' (' . $request->mode . ').', '/apply/interviews');
        }
        Audit::log('interview.schedule', Auth::user()->name . ' scheduled an interview');
        return redirect('/applicants/' . $id)->with('success', 'Interview scheduled.');
    })->middleware('role:Admin,HR');

    Route::delete('/interviews/{id}', function ($id) {
        DB::table('interviews')->where('id', $id)->delete();
        return back()->with('success', 'Interview removed.');
    })->middleware('role:Admin,HR');

    Route::get('/applicant-documents/{id}/download', function ($id) {
        $doc = DB::table('applicant_documents')->where('id', $id)->first();
        abort_if(! $doc || ! Storage::disk('local')->exists($doc->file_path), 404);
        return Storage::disk('local')->download($doc->file_path, $doc->original_name ?? $doc->name);
    })->middleware('role:Admin,HR');

    // Convert a hired applicant into a permanent employee (carries the login over).
    Route::post('/applicants/{id}/convert', function (Request $request, $id) {
        $applicant = DB::table('applicants')->where('id', $id)->first();
        abort_if(! $applicant, 404);

        $request->validate([
            'employee_id' => 'required|string|max:255|unique:employees,employee_id',
            'department' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'date_hired' => 'required|date',
            'employment_type' => 'nullable|string|max:255',
        ]);

        $employeeId = DB::table('employees')->insertGetId([
            'name' => $applicant->name,
            'email' => $applicant->email,
            'contact_number' => $applicant->phone,
            'employee_id' => $request->employee_id,
            'department' => $request->department,
            'position' => $request->position,
            'date_hired' => $request->date_hired,
            'employment_type' => $request->employment_type ?: 'Probationary',
            'status' => 'Active',
            'vacation_credits' => (int) Setting::get('default_vacation_credits', 15),
            'sick_credits' => (int) Setting::get('default_sick_credits', 15),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Carry over submitted documents into the 201 file.
        foreach (DB::table('applicant_documents')->where('applicant_id', $id)->get() as $doc) {
            DB::table('employee_documents')->insert([
                'employee_id' => $employeeId,
                'name' => $doc->name,
                'category' => $doc->category,
                'file_path' => $doc->file_path,
                'original_name' => $doc->original_name,
                'uploaded_by' => $doc->uploaded_by,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Promote the temporary account to a permanent employee login.
        $acct = DB::table('users')->where('applicant_id', $id)->first();
        if ($acct) {
            DB::table('users')->where('id', $acct->id)->update([
                'role' => 'Employee',
                'employee_id' => $employeeId,
                'applicant_id' => null,
                'updated_at' => now(),
            ]);
            Notify::send($acct->id, 'Congratulations! Your account is now a regular employee account.', '/portal');
        }

        DB::table('applicants')->where('id', $id)->update(['status' => 'Hired', 'updated_at' => now()]);
        Audit::log('applicant.convert', Auth::user()->name . ' hired applicant ' . $applicant->name);

        return redirect('/employees/' . $employeeId)->with('success', 'Applicant hired and converted to a permanent employee.');
    })->middleware('role:Admin,HR');

    /*
    | Loans / Cash Advances
    */
    Route::get('/loans', function () {
        $loans = DB::table('loans')
            ->join('employees', 'loans.employee_id', '=', 'employees.id')
            ->select('loans.*', 'employees.name as employee_name', 'employees.employee_id as code')
            ->orderBy('loans.status')->orderBy('loans.id', 'desc')
            ->get();

        return view('loans.index', [
            'loans' => $loans,
            'employees' => DB::table('employees')->where('status', 'Active')->orderBy('name')->get(),
            'activeTotal' => DB::table('loans')->where('status', 'Active')->sum('balance'),
        ]);
    });

    Route::post('/loans', function (Request $request) {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type' => 'required|in:Loan,Cash Advance',
            'principal' => 'required|numeric|min:1',
            'monthly_amortization' => 'required|numeric|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        DB::table('loans')->insert([
            'employee_id' => $request->employee_id,
            'type' => $request->type,
            'principal' => $request->principal,
            'monthly_amortization' => $request->monthly_amortization,
            'balance' => $request->principal,
            'status' => 'Active',
            'reason' => $request->reason,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Audit::log('loan.create', Auth::user()->name . ' recorded a ' . $request->type);
        return redirect('/loans')->with('success', 'Loan / cash advance recorded.');
    })->middleware('role:Admin,HR');

    Route::delete('/loans/{id}', function ($id) {
        DB::table('loans')->where('id', $id)->delete();
        return redirect('/loans')->with('success', 'Loan removed.');
    })->middleware('role:Admin,HR');

    /*
    | Settings (Admin only)
    */
    Route::get('/settings', function () {
        return view('settings.index', [
            'settings' => DB::table('settings')->pluck('value', 'key')->all(),
        ]);
    })->middleware('role:Admin');

    Route::post('/settings', function (Request $request) {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'shift_start' => 'required|date_format:H:i',
            'shift_end' => 'required|date_format:H:i',
            'grace_minutes' => 'required|integer|min:0|max:240',
            'default_vacation_credits' => 'required|integer|min:0|max:365',
            'default_sick_credits' => 'required|integer|min:0|max:365',
        ]);

        foreach (['company_name', 'shift_start', 'shift_end', 'grace_minutes', 'default_vacation_credits', 'default_sick_credits'] as $key) {
            Setting::set($key, $request->input($key));
        }

        Audit::log('settings.update', Auth::user()->name . ' updated system settings');
        return redirect('/settings')->with('success', 'Settings saved.');
    })->middleware('role:Admin');

    /*
    | Audit log (Admin only)
    */
    Route::get('/audit', function () {
        return view('audit.index', [
            'logs' => DB::table('audit_logs')->orderBy('id', 'desc')->limit(200)->get(),
        ]);
    })->middleware('role:Admin');
});
