<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\AttendanceType;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Event;
use BackedEnum;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Gate;

class CourseAttendanceBackup extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can('attendance.view') ?? false;
    }

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-table-cells';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.course-attendance';

    protected static bool $shouldRegisterNavigation = false;

    public ?int $courseId = null;

    public string $courseName = '';

    public string $month;

    /** @var array<int, array<string, mixed>> */
    public array $events = [];

    /** @var array<int, array<string, mixed>> */
    public array $students = [];

    /** @var array<int, array<string, mixed>> */
    public array $attendanceTypes = [];

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
        $this->courseId = request()->integer('courseId') ?: null;

        if (! $this->courseId) {
            return;
        }

        $course = Course::find($this->courseId);

        if (! $course) {
            return;
        }

        abort_unless(Gate::allows('view-course-attendance', $course), 403);

        $this->courseName = $course->name;

        $this->attendanceTypes = AttendanceType::all()
            ->map(fn ($type) => [
                'id' => (int) $type->id,
                'name' => $type->name,
                'color' => $type->class ?? 'gray',
            ])
            ->toArray();

        $this->loadData();
    }

    public function getCourseStats(): array
    {
        $enrollments = Enrollment::where('course_id', $this->courseId)->get();

        return [
            'students' => $enrollments->count(),
            'paid' => $enrollments->where('status_id', 2)->count(),
            'unpaid' => $enrollments->where('status_id', '!=', 2)->count(),
        ];
    }

    protected function loadData(): void
    {
        $course = Course::with('times')->find($this->courseId);

        if (! $course) {
            return;
        }

        $this->ensureMonthlyEvents($course);

        $course = Course::with(['events' => fn ($q) => $q->orderBy('start'), 'enrollments.student'])
            ->find($this->courseId);

        if (! $course) {
            return;
        }

        $this->events = $course->events
            ->filter(fn ($event): bool => $event->start?->format('Y-m') === $this->month)
            ->map(fn ($event) => [
                'id' => (int) $event->id,
                'date' => $event->start ? Carbon::parse($event->start)->format('d') : '',
                'weekday' => $event->start ? Carbon::parse($event->start)->format('D') : '',
            ])
            ->toArray();

        $eventIds = collect($this->events)->pluck('id')->toArray();

        $enrollments = Enrollment::with(['student'])
            ->where('course_id', $this->courseId)
            ->get();

        $allAttendance = Attendance::whereIn('event_id', $eventIds)
            ->whereIn('student_id', $enrollments->pluck('student_id'))
            ->orderBy('id')
            ->get()
            ->groupBy(fn ($a) => $a->student_id.'-'.$a->event_id)
            ->map->first();

        $studentsData = [];
        foreach ($enrollments as $enrollment) {
            $eventAttendances = [];
            foreach ($eventIds as $eventId) {
                $key = $enrollment->student_id.'-'.$eventId;
                $att = $allAttendance->get($key);
                $eventAttendances[$eventId] = $att ? (int) $att->attendance_type_id : null;
            }

            $studentsData[] = [
                'studentId' => (int) $enrollment->student_id,
                'studentName' => $enrollment->student?->name ?? '',
                'attendances' => $eventAttendances,
            ];
        }

        $this->students = collect($studentsData)->sortBy('studentName')->values()->toArray();
    }

    protected function ensureMonthlyEvents(Course $course): void
    {
        $monthStart = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        if ($course->start_date) {
            $monthStart = $monthStart->max(Carbon::parse($course->start_date)->startOfDay());
        }

        if ($course->end_date) {
            $monthEnd = $monthEnd->min(Carbon::parse($course->end_date)->endOfDay());
        }

        if ($monthStart->greaterThan($monthEnd)) {
            return;
        }

        for ($date = $monthStart->copy(); $date->lessThanOrEqualTo($monthEnd); $date->addDay()) {
            foreach ($course->times as $courseTime) {
                if ((int) $date->dayOfWeek !== (int) $courseTime->day) {
                    continue;
                }

                $start = $date->copy()->setTimeFromTimeString($courseTime->start);
                $end = $date->copy()->setTimeFromTimeString($courseTime->end);

                Event::firstOrCreate(
                    [
                        'course_id' => $course->id,
                        'course_time_id' => $courseTime->id,
                        'start' => $start,
                    ],
                    [
                        'teacher_id' => $course->teacher_id,
                        'room_id' => $course->room_id,
                        'end' => $end,
                        'name' => trim($course->name),
                        'exempt_attendance' => $course->exempt_attendance,
                    ],
                );
            }
        }
    }

    public function toggleAttendance(int $studentId, int $eventId, int $typeId): void
    {
        $event = \App\Models\Event::find($eventId);
        abort_unless($event && Gate::allows('edit-attendance', $event), 403);

        Attendance::updateOrCreate(
            [
                'student_id' => $studentId,
                'event_id' => $eventId,
            ],
            [
                'attendance_type_id' => $typeId,
            ],
        );

        foreach ($this->students as $sIndex => $student) {
            if ($student['studentId'] === $studentId) {
                $this->students[$sIndex]['attendances'][$eventId] = $typeId;

                break;
            }
        }

        Notification::make()
            ->success()
            ->title(__('Attendance updated'))
            ->duration(1500)
            ->send();
    }

    public function togglePresent(int $studentId, int $eventId): void
    {
        $event = Event::find($eventId);
        abort_unless($event && Gate::allows('edit-attendance', $event), 403);

        $attendance = Attendance::where('student_id', $studentId)
            ->where('event_id', $eventId)
            ->first();

        if ($attendance?->attendance_type_id === 1) {
            $attendance->delete();
            $typeId = null;
        } else {
            Attendance::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'event_id' => $eventId,
                ],
                [
                    'attendance_type_id' => 1,
                ],
            );
            $typeId = 1;
        }

        foreach ($this->students as $sIndex => $student) {
            if ($student['studentId'] === $studentId) {
                $this->students[$sIndex]['attendances'][$eventId] = $typeId;

                break;
            }
        }

        Notification::make()
            ->success()
            ->title(__('Attendance updated'))
            ->duration(1000)
            ->send();
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Attendance');
    }

    public static function getNavigationLabel(): string
    {
        return __('Course Attendance');
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return $this->courseName
            ? __('Attendance').': '.$this->courseName
            : __('Course Attendance');
    }
}
