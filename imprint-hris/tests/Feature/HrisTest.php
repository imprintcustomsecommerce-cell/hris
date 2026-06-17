<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Workdays;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HrisTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_role_gating(): void
    {
        $this->actingAs($this->user('employee@imprintcustoms.ph'));
        $this->get('/employees')->assertForbidden();
        $this->get('/dashboard')->assertForbidden();
        $this->get('/portal')->assertOk();

        $this->actingAs($this->user('manager@imprintcustoms.ph'));
        $this->get('/dashboard')->assertForbidden();
        $this->get('/tasks')->assertOk();

        $this->actingAs($this->user('hr@imprintcustoms.ph'));
        $this->get('/employees')->assertOk();
    }

    public function test_employee_cannot_exceed_leave_balance(): void
    {
        $this->actingAs($this->user('employee@imprintcustoms.ph'));

        $response = $this->post('/portal/leave', [
            'leave_type' => 'Vacation Leave',
            'start_date' => '2026-07-01',
            'end_date' => '2026-09-30', // far more than 15 working days
            'reason' => 'Too long',
        ]);

        $response->assertSessionHasErrors('leave_type');
        $this->assertDatabaseMissing('leaves', ['reason' => 'Too long']);
    }

    public function test_assigning_a_task_notifies_the_employee(): void
    {
        $manager = $this->user('manager@imprintcustoms.ph');
        $employee = $this->user('employee@imprintcustoms.ph');
        $this->actingAs($manager);

        $this->post('/tasks', [
            'assigned_to' => $employee->employee_id,
            'title' => 'Test task',
            'priority' => 'High',
        ])->assertRedirect('/tasks');

        $this->assertDatabaseHas('tasks', ['title' => 'Test task']);
        $this->assertDatabaseHas('notifications', ['user_id' => $employee->id]);
    }

    public function test_payroll_auto_computes_statutory_deductions(): void
    {
        $this->actingAs($this->user('hr@imprintcustoms.ph'));
        $employeeRecordId = DB::table('employees')->where('employee_id', 'EMP-0001')->value('id');

        $this->post('/payroll', [
            'employee_id' => $employeeRecordId,
            'payroll_month' => 'June',
            'payroll_year' => 2026,
            'basic_salary' => 20000,
        ])->assertRedirect('/payroll');

        $row = DB::table('payrolls')->where('basic_salary', 20000)->latest('id')->first();
        $this->assertEqualsWithDelta(900, $row->sss, 0.01);
        $this->assertEqualsWithDelta(500, $row->philhealth, 0.01);
        $this->assertEqualsWithDelta(200, $row->pagibig, 0.01);
    }

    public function test_workdays_excludes_weekends(): void
    {
        // 2026-06-15 (Mon) .. 2026-06-19 (Fri) = 5 weekdays.
        $this->assertSame(5, Workdays::between('2026-06-15', '2026-06-19'));
        // A weekend only = 0.
        $this->assertSame(0, Workdays::between('2026-06-13', '2026-06-14'));
    }
}
