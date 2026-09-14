<?php

namespace Tests\Feature;

use App\Filament\Pages\MonthlyPayment;
use App\Filament\Resources\Students\Pages\CreateStudent;
use App\Models\Course;
use App\Models\Period;
use App\Models\Student;
use App\Models\User;
use App\Models\Year;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MonthlyPaymentCreateStudentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('enrollment_status_types')->insert([
            ['id' => 1, 'name' => json_encode(['fr' => 'Pending'])],
            ['id' => 2, 'name' => json_encode(['fr' => 'Paid'])],
            ['id' => 3, 'name' => json_encode(['fr' => 'Cancelled'])],
        ]);

        foreach ([
            'courses.view', 'courses.edit', 'courses.delete',
            'enrollments.view', 'enrollments.edit', 'enrollments.delete',
            'attendance.view', 'attendance.edit',
        ] as $perm) {
            Permission::findOrCreate($perm, 'web');
        }

        $adminRole = Role::findOrCreate('admin', 'web');
        $adminRole->givePermissionTo(Permission::all());

        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $year = Year::factory()->create();
        $period = Period::factory()->create(['year_id' => $year->id]);
        \App\Models\Config::where('name', 'current_period')->update(['value' => $period->id]);

        $this->course = Course::factory()->create([
            'name' => 'IELTS 9 N1',
            'period_id' => $period->id,
        ]);
    }

    public function test_monthly_payment_page_has_add_student_action_with_form_inputs(): void
    {
        $this->actingAs($this->admin);

        Livewire::withQueryParams(['courseId' => $this->course->id])
            ->test(MonthlyPayment::class)
            ->assertActionExists('add_student')
            ->assertActionVisible('add_student')
            ->callAction('add_student', [
                'firstname' => 'Alice',
                'lastname' => 'Smith',
                'gender_id' => 1,
                'phone_number' => '+998901234567',
            ])
            ->assertHasNoActionErrors();

        $student = Student::whereHas('user', fn ($q) => $q->where('firstname', 'Alice')->where('lastname', 'Smith'))->first();
        $this->assertNotNull($student);
        $this->assertTrue($student->enrollments()->where('course_id', $this->course->id)->exists());
    }

    public function test_create_student_page_redirects_to_monthly_payment_when_course_id_provided(): void
    {
        $this->actingAs($this->admin);

        Livewire::withQueryParams(['course_id' => $this->course->id])
            ->test(CreateStudent::class)
            ->assertFormSet(['course_id' => $this->course->id])
            ->fillForm([
                'firstname' => 'John',
                'lastname' => 'Doe',
                'gender_id' => 2,
                'phone_number' => '+1234567890',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertRedirect(MonthlyPayment::getUrl(['courseId' => $this->course->id]));

        $student = Student::whereHas('user', fn ($q) => $q->where('firstname', 'John')->where('lastname', 'Doe'))->first();
        $this->assertNotNull($student);
        $this->assertTrue($student->enrollments()->where('course_id', $this->course->id)->exists());
    }

    public function test_create_student_cancel_action_points_to_monthly_payment(): void
    {
        $this->actingAs($this->admin);

        $component = Livewire::withQueryParams(['course_id' => $this->course->id])
            ->test(CreateStudent::class);

        $this->assertEquals(
            MonthlyPayment::getUrl(['courseId' => $this->course->id]),
            $component->instance()->previousUrl
        );
    }
}
