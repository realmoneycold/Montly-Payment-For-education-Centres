<?php

namespace App\Filament\Resources\Students;

use App\Filament\Exports\StudentExporter;
use App\Filament\Resources\Students\Pages\CreateStudent;
use App\Filament\Resources\Students\Pages\EditStudent;
use App\Filament\Resources\Students\Pages\EnrollStudent;
use App\Filament\Resources\Students\Pages\ListStudents;
use App\Filament\Resources\Students\Pages\ViewStudent;
use App\Filament\Resources\Students\RelationManagers\ContactsRelationManager;
use App\Filament\Resources\Students\RelationManagers\MonthlyPaymentsRelationManager;
use App\Models\Course;
use App\Models\Institution;
use App\Models\Period;
use App\Models\Student;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('enrollments.view') ?? false;
    }

    protected static ?int $navigationSort = 200;

    // Original navigation group (kept for future re-enable):
    // public static function getNavigationGroup(): ?string
    // {
    //     return __('Administration');
    // }

    public static function getNavigationGroup(): ?string
    {
        // Temporarily ungrouped (flat sidebar list, no slide collapse button)
        return null;
    }

    public static function getModelLabel(): string
    {
        return __('Student');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Students');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Student Info'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('Name'))
                            ->weight(\Filament\Support\Enums\FontWeight::Bold)
                            ->size(\Filament\Support\Enums\TextSize::Large),
                        TextEntry::make('user.birthdate')
                            ->label(__('Birthdate'))
                            ->weight(\Filament\Support\Enums\FontWeight::Bold)
                            ->size(\Filament\Support\Enums\TextSize::Large)
                            ->date(),
                        TextEntry::make('student_age')
                            ->label(__('Age'))
                            ->weight(\Filament\Support\Enums\FontWeight::Bold)
                            ->size(\Filament\Support\Enums\TextSize::Large),
                        TextEntry::make('formatted_gender')
                            ->label(__('Gender'))
                            ->weight(\Filament\Support\Enums\FontWeight::Bold)
                            ->size(\Filament\Support\Enums\TextSize::Large)
                            ->placeholder('-'),
                        TextEntry::make('phone.phone_number')
                            ->label(__('Phone'))
                            ->weight(\Filament\Support\Enums\FontWeight::Bold)
                            ->size(\Filament\Support\Enums\TextSize::Large)
                            ->badge()
                            ->placeholder('-'),
                        TextEntry::make('institution.name')
                            ->label(__('Institution'))
                            ->weight(\Filament\Support\Enums\FontWeight::Bold)
                            ->size(\Filament\Support\Enums\TextSize::Large)
                            ->placeholder('-'),
                    ]),
            ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Student')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make(__('Student Info'))
                            ->schema([
                                Select::make('course_id')
                                    ->label(__('Group'))
                                    ->options(function (): array {
                                        $courses = Course::query()
                                            ->whereNull('parent_course_id');

                                        $defaultPeriodId = Period::get_default_period()?->id;
                                        $courseId = request()->query('course_id');

                                        $courses->where(function ($query) use ($defaultPeriodId, $courseId) {
                                            if ($defaultPeriodId) {
                                                $query->where('period_id', $defaultPeriodId);
                                            }
                                            if ($courseId) {
                                                $query->orWhere('id', $courseId);
                                            }
                                        });

                                        return $courses->orderBy('name')
                                            ->get()
                                            ->mapWithKeys(fn (Course $course): array => [
                                                $course->id => $course->name,
                                            ])
                                            ->all();
                                    })
                                    ->default(fn (): ?int => request()->integer('course_id') ?: null)
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                                TextInput::make('firstname')
                                    ->label(__('First name'))
                                    ->required()
                                    ->maxLength(30),
                                TextInput::make('lastname')
                                    ->label(__('Last name'))
                                    ->required()
                                    ->maxLength(30),
                                DatePicker::make('birthdate')
                                    ->label(__('Birthdate'))
                                    ->nullable(),
                                Radio::make('gender_id')
                                    ->label(__('Gender'))
                                    ->options([
                                        1 => __('Female'),
                                        2 => __('Male'),
                                    ])
                                    ->required()
                                    ->inline(),
                                TextInput::make('phone_number')
                                    ->label(__('Phone number'))
                                    ->tel()
                                    ->nullable(),
                                Select::make('institution_id')
                                    ->label(__('Institution'))
                                    ->options(fn (): array => collect([
                                        'School',
                                        'College',
                                        'Institution',
                                        "Doesn't study anywhere",
                                    ])->mapWithKeys(fn (string $name): array => [
                                        Institution::firstOrCreate(['name' => $name])->id => __($name),
                                    ])->all())
                                    ->searchable()
                                    ->nullable(),
                            ]),

                        Tab::make(__('Invoicing Info'))
                            ->schema([
                                TextInput::make('iban')
                                    ->label(__('IBAN'))
                                    ->nullable()
                                    ->maxLength(90),
                                TextInput::make('bic')
                                    ->label(__('BIC'))
                                    ->nullable()
                                    ->maxLength(30),
                            ])
                            ->visible(fn (): bool => (bool) config('settings.collect_student_banking_info')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Mobile: stacked student info (name + phone)
                TextColumn::make('mobile_name')
                    ->label(__('Student'))
                    ->state(fn ($record) => $record->user?->lastname.', '.$record->user?->firstname)
                    ->description(fn ($record) => collect([/* $record->user?->email, */ $record->phone->first()?->phone_number])->filter()->implode(' · ')) // Email commented out for now (future: remove comment)
                    ->searchable(query: fn ($query, $search) => $query->whereHas('user', fn ($q) => $q->where('lastname', 'like', "%{$search}%")->orWhere('firstname', 'like', "%{$search}%")/* ->orWhere('email', 'like', "%{$search}%") */)) // Email search disabled for now
                    ->sortable(query: fn ($query, $direction) => $query->join('users', 'students.user_id', '=', 'users.id')->orderBy('users.lastname', $direction))
                    ->wrap()
                    ->hiddenFrom('md'),
                // Desktop columns
                // ID column temporarily removed per request (keep for future re-enable):
                // TextColumn::make('idnumber')
                //     ->label(__('ID'))
                //     ->searchable()
                //     ->visibleFrom('md'),
                TextColumn::make('user.lastname')
                    ->label(__('Last name'))
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('user.firstname')
                    ->label(__('First name'))
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('md'),
                // Email column temporarily removed (kept for future re-enable - remove comments below)
                // TextColumn::make('user.email')
                //     ->label(__('Email'))
                //     ->wrap()
                //     ->width('180px')
                //     ->searchable()
                //     ->visibleFrom('md'),
                TextColumn::make('user.username')
                    ->label(__('Username'))
                    ->searchable()
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('student_age')
                    ->label(__('Age'))
                    ->visibleFrom('md'),
                TextColumn::make('user.birthdate')
                    ->label(__('Birthdate'))
                    ->date()
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('phone.phone_number')
                    ->label(__('Phone'))
                    ->badge()
                    ->visibleFrom('md'),
            ])
            ->filters([])
            ->defaultSort('id', 'desc')
            ->actionsPosition(\Filament\Tables\Enums\RecordActionsPosition::AfterColumns)
            ->recordActions([
                \Filament\Actions\ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    \Filament\Actions\DeleteAction::make(),
                ]),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(StudentExporter::class),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('enroll_in_group')
                        ->label(__('Enroll in group'))
                        ->icon('heroicon-o-user-group')
                        ->form([
                            Select::make('course_id')
                                ->label(__('Group'))
                                ->options(function (): array {
                                    $periodId = Period::get_default_period()?->id;

                                    return Course::query()
                                        ->when($periodId, fn (Builder $query) => $query->where('period_id', $periodId))
                                        ->whereNull('parent_course_id')
                                        ->with('level')
                                        ->orderBy('name')
                                        ->get()
                                        ->mapWithKeys(fn (Course $course): array => [
                                            $course->id => $course->name.($course->level?->name ? ' - '.$course->level->name : ''),
                                        ])
                                        ->all();
                                })
                                ->searchable()
                                ->preload()
                                ->required(),
                        ])
                        ->requiresConfirmation()
                        ->action(function (array $data, Collection $records): void {
                            $course = Course::findOrFail($data['course_id']);
                            $records->each(fn (Student $student): int => $student->enroll($course));

                            Notification::make()
                                ->title(__('Students enrolled successfully'))
                                ->body(__(':count students were added to :group.', [
                                    'count' => $records->count(),
                                    'group' => $course->name,
                                ]))
                                ->success()
                                ->send();
                        })
                        ->visible(fn (): bool => Gate::allows('enroll-students')),
                    ExportBulkAction::make()
                        ->exporter(StudentExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ContactsRelationManager::class,
            MonthlyPaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStudents::route('/'),
            'create' => CreateStudent::route('/create'),
            'view' => ViewStudent::route('/{record}'),
            'edit' => EditStudent::route('/{record}/edit'),
            'enroll' => EnrollStudent::route('/{record}/enroll'),
        ];
    }
}
