<?php

namespace App\Filament\Pages;

use App\Models\Enrollment;
use App\Models\MonthlyPaymentRecord;
use BackedEnum;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class MonthlyPayment extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can('attendance.view') ?? false;
    }

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.monthly-payment';

    protected static bool $shouldRegisterNavigation = false;

    public ?int $courseId = null;

    public string $courseName = '';

    public string $month;

    /** @var array<int, array<string, mixed>> */
    public array $students = [];

    // Edit modal state
    public bool $showEditModal = false;

    public ?int $editStudentId = null;

    public string $editFirstname = '';

    public string $editLastname = '';

    public string $editPhoneNumber = '';

    public int $editGenderId = 2;

    public ?string $editBirthdate = null;

    // Delete modal state
    public bool $showDeleteModal = false;

    public ?int $deleteStudentId = null;

    // Comment modal state
    public bool $showCommentModal = false;

    public ?int $commentEnrollmentId = null;

    public string $commentText = '';

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
        $this->courseId = request()->integer('courseId') ?: null;

        if (! $this->courseId) {
            return;
        }

        $course = \App\Models\Course::find($this->courseId);

        if (! $course) {
            return;
        }

        $this->courseName = $course->name;

        $this->loadData();
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('add_student')
                ->label(__('Create Student account'))
                ->icon('heroicon-o-user-plus')
                ->modalHeading(__('Create Student account'))
                ->modalWidth('lg')
                ->schema([
                    \Filament\Schemas\Components\Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\TextInput::make('firstname')
                                ->label(__('First name'))
                                ->required()
                                ->maxLength(30),
                            \Filament\Forms\Components\TextInput::make('lastname')
                                ->label(__('Last name'))
                                ->required()
                                ->maxLength(30),
                            \Filament\Forms\Components\TextInput::make('phone_number')
                                ->label(__('Phone number'))
                                ->tel()
                                ->nullable(),
                            \Filament\Forms\Components\Radio::make('gender_id')
                                ->label(__('Gender'))
                                ->options([
                                    2 => __('Male'),
                                    1 => __('Female'),
                                ])
                                ->default(2)
                                ->required()
                                ->inline(),
                            \Filament\Forms\Components\DatePicker::make('birthdate')
                                ->label(__('Birthdate'))
                                ->nullable(),
                            \Filament\Forms\Components\Select::make('institution_id')
                                ->label(__('Institution'))
                                ->options(fn (): array => collect([
                                    'School',
                                    'College',
                                    'Institution',
                                    "Doesn't study anywhere",
                                ])->mapWithKeys(fn (string $name): array => [
                                    \App\Models\Institution::firstOrCreate(['name' => $name])->id => __($name),
                                ])->all())
                                ->searchable()
                                ->nullable(),
                        ]),
                ])
                ->action(function (array $data): void {
                    $course = \App\Models\Course::find($this->courseId);
                    if (! $course) {
                        return;
                    }

                    $baseUsername = \Illuminate\Support\Str::slug($data['firstname'].'.'.$data['lastname']);
                    $username = $baseUsername ?: 'student';
                    $counter = 1;
                    while (\App\Models\User::where('username', $username)->exists()) {
                        $username = $baseUsername.$counter;
                        $counter++;
                    }

                    $user = \App\Models\User::create([
                        'firstname' => $data['firstname'],
                        'lastname' => $data['lastname'],
                        'email' => null,
                        'username' => $username,
                        'password' => bcrypt(\Illuminate\Support\Str::random(16)),
                    ]);

                    $student = \App\Models\Student::create([
                        'id' => $user->id,
                        'birthdate' => $data['birthdate'] ?? null,
                        'gender_id' => $data['gender_id'],
                        'institution_id' => $data['institution_id'] ?? null,
                    ]);

                    if (filled($data['phone_number'] ?? null)) {
                        $student->phone()->create(['phone_number' => $data['phone_number']]);
                    }

                    $student->enroll($course);

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title(__('Student account created & enrolled successfully'))
                        ->send();

                    $this->loadData();
                })
                ->visible(fn (): bool => $this->courseId !== null),
        ];
    }

    public function getMonthLabel(): string
    {
        return Carbon::createFromFormat('Y-m', $this->month)->format('F Y');
    }

    public function previousMonth(): void
    {
        $this->month = Carbon::createFromFormat('Y-m', $this->month)
            ->subMonth()
            ->format('Y-m');
        $this->loadData();
    }

    public function nextMonth(): void
    {
        $this->month = Carbon::createFromFormat('Y-m', $this->month)
            ->addMonth()
            ->format('Y-m');
        $this->loadData();
    }

    public function getCourseStats(): array
    {
        $enrollments = Enrollment::where('course_id', $this->courseId)->pluck('id');

        $paymentRecords = MonthlyPaymentRecord::whereIn('enrollment_id', $enrollments)
            ->where('month', $this->month)
            ->get();

        $paidCount = $paymentRecords->where('status', 'paid')->count();
        $partialCount = $paymentRecords->where('status', 'partial')->count();
        $unpaidCount = $enrollments->count() - $paidCount - $partialCount;

        $totalPaidAmount = (float) $paymentRecords->sum('paid_amount');

        return [
            'students' => $enrollments->count(),
            'paid' => $paidCount,
            'partial' => $partialCount,
            'unpaid' => $unpaidCount,
            'totalPaidAmount' => $totalPaidAmount,
        ];
    }

    protected function loadData(): void
    {
        $enrollments = Enrollment::with(['student'])
            ->where('course_id', $this->courseId)
            ->get();

        $monthlyPayments = MonthlyPaymentRecord::whereIn('enrollment_id', $enrollments->pluck('id'))
            ->where('month', $this->month)
            ->get()
            ->keyBy('enrollment_id');

        $studentsData = [];
        foreach ($enrollments as $enrollment) {
            $paymentRecord = $monthlyPayments->get($enrollment->id);
            $status = $paymentRecord?->status ?? 'unpaid';
            $studentsData[] = [
                'enrollmentId' => (int) $enrollment->id,
                'studentId' => (int) $enrollment->student_id,
                'studentName' => $enrollment->student?->name ?? '',
                'isPaid' => $status === 'paid',
                'status' => $status,
                'paidAmount' => $paymentRecord?->paid_amount,
                'comment' => $paymentRecord?->comment,
            ];
        }

        $this->students = collect($studentsData)
            ->sortBy('studentName')
            ->values()
            ->toArray();
    }

    public function updateStatus(int $enrollmentId, string $newStatus, ?string $paidAmount = null): void
    {
        if (! in_array($newStatus, ['paid', 'partial', 'unpaid'], true)) {
            return;
        }

        $enrollment = Enrollment::find($enrollmentId);

        if (! $enrollment) {
            return;
        }

        $paymentRecord = MonthlyPaymentRecord::firstOrNew([
            'enrollment_id' => $enrollmentId,
            'month' => $this->month,
        ]);

        $paymentRecord->status = $newStatus;
        $paymentRecord->paid_at = match ($newStatus) {
            'paid', 'partial' => $paymentRecord->paid_at ?? now(),
            'unpaid' => null,
        };

        if ($newStatus === 'partial' && filled($paidAmount)) {
            $amount = (float) str_replace(',', '', $paidAmount);
            $paymentRecord->paid_amount = $amount > 0 ? $amount : null;
        } elseif ($newStatus === 'paid') {
            if (! $paymentRecord->paid_amount) {
                $paymentRecord->paid_amount = null;
            }
        } else {
            $paymentRecord->paid_amount = null;
        }

        $paymentRecord->save();

        $statusLabel = match ($newStatus) {
            'paid' => __('Paid'),
            'partial' => __('Partially Paid'),
            'unpaid' => __('Not paid'),
        };

        // Update local state
        foreach ($this->students as $index => $student) {
            if ($student['enrollmentId'] === $enrollmentId) {
                $this->students[$index]['status'] = $newStatus;
                $this->students[$index]['isPaid'] = $newStatus === 'paid';
                if ($newStatus === 'partial' && filled($paidAmount)) {
                    $this->students[$index]['paidAmount'] = (float) str_replace(',', '', $paidAmount);
                } elseif ($newStatus === 'unpaid') {
                    $this->students[$index]['paidAmount'] = null;
                }
                break;
            }
        }

        Notification::make()
            ->success()
            ->title(__('Status updated').': '.$statusLabel)
            ->duration(1500)
            ->send();
    }

    public function openCommentModal(int $enrollmentId): void
    {
        $this->commentEnrollmentId = $enrollmentId;

        $currentComment = null;
        foreach ($this->students as $student) {
            if ($student['enrollmentId'] === $enrollmentId) {
                $currentComment = $student['comment'];
                break;
            }
        }

        $this->commentText = (string) $currentComment;
        $this->showCommentModal = true;
    }

    public function closeCommentModal(): void
    {
        $this->showCommentModal = false;
        $this->commentEnrollmentId = null;
        $this->commentText = '';
    }

    public function saveComment(): void
    {
        if (! $this->commentEnrollmentId) {
            return;
        }

        $enrollment = Enrollment::find($this->commentEnrollmentId);

        if (! $enrollment) {
            $this->closeCommentModal();

            return;
        }

        $paymentRecord = MonthlyPaymentRecord::firstOrNew([
            'enrollment_id' => $this->commentEnrollmentId,
            'month' => $this->month,
        ]);

        if (! $paymentRecord->status) {
            $paymentRecord->status = 'unpaid';
        }

        $paymentRecord->comment = filled($this->commentText) ? trim($this->commentText) : null;
        $paymentRecord->save();

        foreach ($this->students as $index => $student) {
            if ($student['enrollmentId'] === $this->commentEnrollmentId) {
                $this->students[$index]['comment'] = filled($this->commentText) ? trim($this->commentText) : null;
                break;
            }
        }

        $this->closeCommentModal();

        Notification::make()
            ->success()
            ->title(__('Comment saved'))
            ->duration(1500)
            ->send();
    }

    public function editStudent(int $studentId): void
    {
        $student = \App\Models\Student::with(['user', 'phone'])->find($studentId);

        if (! $student) {
            return;
        }

        $this->editStudentId = $studentId;
        $this->editFirstname = $student->user->firstname ?? '';
        $this->editLastname = $student->user->lastname ?? '';
        $this->editPhoneNumber = $student->phone->first()?->phone_number ?? '';
        $this->editGenderId = $student->gender_id ?? 2;
        $this->editBirthdate = $student->birthdate?->format('Y-m-d');
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
    }

    public function updateStudent(): void
    {
        $student = \App\Models\Student::with(['user', 'phone'])->find($this->editStudentId);

        if (! $student) {
            return;
        }

        $student->user->update([
            'firstname' => $this->editFirstname,
            'lastname' => $this->editLastname,
        ]);

        $student->update([
            'birthdate' => $this->editBirthdate ?: null,
            'gender_id' => $this->editGenderId,
        ]);

        if (filled($this->editPhoneNumber)) {
            if ($student->phone()->exists()) {
                $student->phone()->update(['phone_number' => $this->editPhoneNumber]);
            } else {
                $student->phone()->create(['phone_number' => $this->editPhoneNumber]);
            }
        } elseif ($student->phone()->exists()) {
            $student->phone()->delete();
        }

        $this->showEditModal = false;

        Notification::make()
            ->success()
            ->title(__('Student updated successfully'))
            ->send();

        $this->loadData();
    }

    public function confirmDeleteStudent(int $studentId): void
    {
        $this->deleteStudentId = $studentId;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
    }

    public function deleteStudent(int $studentId): void
    {
        $enrollment = Enrollment::where('course_id', $this->courseId)
            ->where('student_id', $studentId)
            ->first();

        if ($enrollment) {
            MonthlyPaymentRecord::where('enrollment_id', $enrollment->id)->delete();
            $enrollment->delete();
        }

        Notification::make()
            ->success()
            ->title(__('Student removed from course'))
            ->send();

        $this->loadData();
    }

    public static function getNavigationGroup(): ?string
    {
        return __('Attendance');
    }

    public static function getNavigationLabel(): string
    {
        return __('Monthly Payment');
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return $this->courseName
            ? __('Monthly Payment').': '.$this->courseName
            : __('Monthly Payment');
    }
}
