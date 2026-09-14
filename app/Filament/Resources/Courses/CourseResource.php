<?php

namespace App\Filament\Resources\Courses;

use App\Filament\Exports\CourseExporter;
use App\Filament\Pages\GradeEdit;
use App\Filament\Pages\MonthlyPayment;
use App\Filament\Pages\SkillEvaluationPage;
use App\Filament\Resources\Courses\Pages\CourseBlockView;
use App\Filament\Resources\Courses\Pages\CourseEnrollments;
use App\Filament\Resources\Courses\Pages\CreateCourse;
use App\Filament\Resources\Courses\Pages\EditCourse;
use App\Filament\Resources\Courses\Pages\ListCourses;
use App\Models\Course;
use App\Models\Period;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Support\RawJs;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?int $navigationSort = 100;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('courses.view') ?? false;
    }

    public static function getModelLabel(): string
    {
        return __('Course');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Courses');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Temporarily replaced multi-tab layout with single Course Info section
                // (original 5 tabs: Course info / Resources / Pedagogy / Submodules / Schedule
                //  kept below commented for future re-enable)
                Section::make(__('Course info'))
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        // === Fields kept from original "Course info" tab ===
                        // Rhythm - REMOVED per request (kept commented for future)
                        // Select::make('rhythm_id')
                        //     ->label(__('Rhythm'))
                        //     ->relationship('rhythm', 'name')
                        //     ->required()
                        //     ->preload()
                        //     ->searchable(),
                        Select::make('level_id')
                            ->label(__('Level'))
                            ->relationship('level', 'name')
                            ->preload()
                            ->searchable()
                            ->nullable(),
                        TextInput::make('name')
                            ->label(__('Name'))
                            ->required()
                            ->minLength(1)
                            ->maxLength(100)
                            ->columnSpan(2),
                        TextInput::make('price')
                            ->label(__('Price'))
                            ->required()
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->minValue(0)
                            ->step(1)
                            ->prefix(config('academico.currency_position') === 'before' ? config('academico.currency_symbol') : null)
                            ->suffix(config('academico.currency_position') === 'after' ? config('academico.currency_symbol') : null),
                        TextInput::make('price_b')
                            ->label(__('Price B'))
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix(config('academico.currency_position') === 'before' ? config('academico.currency_symbol') : null)
                            ->suffix(config('academico.currency_position') === 'after' ? config('academico.currency_symbol') : null)
                            ->visible(fn (): bool => (bool) config('invoicing.price_categories_enabled')),
                        TextInput::make('price_c')
                            ->label(__('Price C'))
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix(config('academico.currency_position') === 'before' ? config('academico.currency_symbol') : null)
                            ->suffix(config('academico.currency_position') === 'after' ? config('academico.currency_symbol') : null)
                            ->visible(fn (): bool => (bool) config('invoicing.price_categories_enabled')),
                        // Volume - REMOVED per request (kept commented for future)
                        // TextInput::make('volume')
                        //     ->label(__('Volume'))
                        //     ->numeric()
                        //     ->minValue(0)
                        //     ->suffix('h')
                        //     ->nullable(),
                        // Remote volume - REMOVED per request (kept commented for future)
                        // TextInput::make('remote_volume')
                        //     ->label(__('Remote volume'))
                        //     ->numeric()
                        //     ->minValue(0)
                        //     ->suffix('h')
                        //     ->nullable(),
                        // Spots - REMOVED per request (kept commented for future)
                        // TextInput::make('spots')
                        //     ->label(__('Spots'))
                        //     ->required()
                        //     ->integer()
                        //     ->minValue(0),
                        // Exempt from attendance - REMOVED per request (kept commented for future)
                        // Checkbox::make('exempt_attendance')
                        //     ->label(__('Exempt from attendance')),
                        // Color - REMOVED per request (kept commented for future)
                        // ColorPicker::make('color')
                        //     ->label(__('Color'))
                        //     ->nullable(),

                        // === Fields kept from original "Resources" tab ===
                        TextInput::make('teacher_name')
                            ->label(__('Teacher name'))
                            ->maxLength(255)
                            ->nullable(),
                        // Room - REMOVED per request (kept commented for future)
                        // Select::make('room_id')
                        //     ->label(__('Room'))
                        //     ->relationship('room', 'name')
                        //     ->preload()
                        //     ->nullable(),

                        // === Fields kept from original "Pedagogy" tab ===
                        // Books - REMOVED per request (kept commented for future)
                        // Select::make('books')
                        //     ->label(__('Books'))
                        //     ->relationship('books', 'name')
                        //     ->multiple()
                        //     ->preload()
                        //     ->searchable(),
                        // Evaluation Type - REMOVED per request (kept commented for future)
                        // Select::make('evaluation_type_id')
                        //     ->label(__('Evaluation Type'))
                        //     ->relationship('evaluationType', 'name')
                        //     ->preload()
                        //     ->nullable(),
                        Checkbox::make('marked')
                            ->label(__('Evaluation ready'))
                            ->visibleOn('edit')
                            ->columnSpanFull(),

                        Placeholder::make(__('Note: if you modify the course dates later, existing attendance for this course will be lost.'))
                            ->columnSpanFull()
                            ->visibleOn('edit'),
                        DatePicker::make('start_date')
                            ->label(__('Start Date'))
                            ->nullable(),
                        DatePicker::make('end_date')
                            ->label(__('End Date'))
                            ->nullable(),
                        Select::make('schedule_days')
                            ->label(__('Days'))
                            ->options([
                                0 => __('Sunday'),
                                1 => __('Monday'),
                                2 => __('Tuesday'),
                                3 => __('Wednesday'),
                                4 => __('Thursday'),
                                5 => __('Friday'),
                                6 => __('Saturday'),
                            ])
                            ->multiple()
                            ->searchable()
                            ->nullable(),
                        TimePicker::make('schedule_start')
                            ->label(__('Start'))
                            ->seconds(false)
                            ->nullable(),
                        TimePicker::make('schedule_end')
                            ->label(__('End'))
                            ->seconds(false)
                            ->nullable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $defaultPeriod = Period::get_default_period();

        return $table
            ->columns([
                // Mobile: stacked course info (name + rhythm · level)
                TextColumn::make('mobile_name')
                    ->label(__('Course'))
                    ->state(fn ($record) => $record->name)
                    ->description(fn ($record) => collect([$record->rhythm?->name, $record->level?->name])->filter()->implode(' · '))
                    ->searchable(query: fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('name', $direction))
                    ->wrap()
                    ->hiddenFrom('md'),
                // Mobile: stacked details (teacher, room, schedule)
                TextColumn::make('mobile_details')
                    ->label(__('Details'))
                    ->state(fn ($record) => $record->course_teacher_name)
                    ->description(fn ($record) => collect([$record->room?->name, $record->course_times])->filter()->implode(' · '))
                    ->wrap()
                    ->hiddenFrom('md'),
                // Mobile: stacked dates (start → end)
                TextColumn::make('mobile_dates')
                    ->label(__('Dates'))
                    ->state(fn ($record) => $record->start_date?->format('M j, Y'))
                    ->description(fn ($record) => $record->end_date?->format('M j, Y'))
                    ->sortable(query: fn ($query, $direction) => $query->orderBy('start_date', $direction))
                    ->hiddenFrom('md'),
                // Desktop columns
                // TextColumn::make('rhythm.name')
                //     ->label(__('Rhythm'))
                //     ->sortable()
                //     ->visibleFrom('md'),
                TextColumn::make('level.name')
                    ->label(__('Level'))
                    ->weight(\Filament\Support\Enums\FontWeight::Bold)
                    ->size(\Filament\Support\Enums\TextSize::Large)
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->weight(\Filament\Support\Enums\FontWeight::Bold)
                    ->size(\Filament\Support\Enums\TextSize::Large)
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->width('200px')
                    ->visibleFrom('md'),
                // TextColumn::make('volume')
                //     ->label(__('Volume'))
                //     ->suffix('h')
                //     ->sortable()
                //     ->toggleable()
                //     ->visibleFrom('md'),
                // TextColumn::make('remote_volume')
                //     ->label(__('Remote volume'))
                //     ->suffix('h')
                //     ->sortable()
                //     ->toggleable()
                //     ->visibleFrom('lg'),
                TextColumn::make('course_teacher_name')
                    ->label(__('Teacher'))
                    ->weight(\Filament\Support\Enums\FontWeight::Bold)
                    ->size(\Filament\Support\Enums\TextSize::Large)
                    ->sortable()
                    ->wrap()
                    ->width('200px')
                    ->visibleFrom('md'),
                // TextColumn::make('room.name')
                //     ->label(__('Room'))
                //     ->sortable()
                //     ->toggleable()
                //     ->visibleFrom('lg'),
                TextColumn::make('course_times')
                    ->label(__('Schedule'))
                    ->weight(\Filament\Support\Enums\FontWeight::Bold)
                    ->size(\Filament\Support\Enums\TextSize::Large)
                    ->wrap()
                    ->width('200px')
                    ->visibleFrom('lg'),
                TextColumn::make('course_enrollments_count')
                    ->label(__('Enrollments'))
                    ->weight(\Filament\Support\Enums\FontWeight::Bold)
                    ->size(\Filament\Support\Enums\TextSize::Large)
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('start_date')
                    ->label(__('Start Date'))
                    ->date()
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('end_date')
                    ->label(__('End Date'))
                    ->date()
                    ->sortable()
                    ->visibleFrom('lg'),
                IconColumn::make('parent_course_id')
                    ->label('')
                    ->icon(fn ($state) => $state ? 'heroicon-o-arrow-uturn-left' : null)
                    ->tooltip(__('Submodule'))
                    ->hidden(),
                // IconColumn::make('marked')
                //     ->boolean()
                //     ->label(__('Evaluation complete'))
                //     ->toggleable()
                //     ->visibleFrom('md'),
            ])
            ->filters([])
            ->defaultSort('start_date', 'desc')
            ->recordUrl(fn ($record): string => MonthlyPayment::getUrl(['courseId' => $record->id]))
            ->actionsPosition(\Filament\Tables\Enums\RecordActionsPosition::AfterColumns)
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    Action::make('evaluate_skills')
                        ->label(__('Evaluate Skills'))
                        ->icon('heroicon-o-star')
                        ->url(fn ($record) => SkillEvaluationPage::getUrl(['courseId' => $record->id]))
                        ->visible(fn ($record) => $record->evaluationType?->skills()?->count() > 0 && $record->enrollments()->count() > 0),
                    Action::make('manage_grades')
                        ->label(__('Manage Grades'))
                        ->icon('heroicon-o-pencil-square')
                        ->url(fn ($record) => GradeEdit::getUrl(['courseId' => $record->id]))
                        ->visible(fn ($record) => $record->evaluationType?->gradeTypes()?->count() > 0 && $record->enrollments()->count() > 0),
                    DeleteAction::make(),
                ]),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(CourseExporter::class),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exporter(CourseExporter::class),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourses::route('/'),
            'block-view' => CourseBlockView::route('/block-view'),
            'create' => CreateCourse::route('/create'),
            'edit' => EditCourse::route('/{record}/edit'),
            'enrollments' => CourseEnrollments::route('/{record}/enrollments'),
        ];
    }
}
