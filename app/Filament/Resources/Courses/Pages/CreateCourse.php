<?php

namespace App\Filament\Resources\Courses\Pages;

use App\Filament\Resources\Courses\CourseResource;
use App\Models\Period;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Collection;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;

    protected function getRedirectUrl(): string
    {
        return CourseResource::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['period_id'] ??= Period::get_default_period()?->id;
        unset($data['schedule_days'], $data['schedule_start'], $data['schedule_end']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $state = $this->form->getState();
        $this->record->saveCourseTimes($this->courseTimesFromState($state));
    }

    /** @return Collection<int, array{day: int, start: string, end: string}> */
    private function courseTimesFromState(array $state): Collection
    {
        if (blank($state['schedule_start'] ?? null) || blank($state['schedule_end'] ?? null)) {
            return collect();
        }

        return collect($state['schedule_days'] ?? [])
            ->map(fn ($day): array => [
                'day' => (int) $day,
                'start' => $state['schedule_start'],
                'end' => $state['schedule_end'],
            ]);
    }
}
