<?php

namespace App\Filament\Resources\Courses\Pages;

use App\Filament\Resources\Courses\CourseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Collection;

class EditCourse extends EditRecord
{
    protected static string $resource = CourseResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $firstTime = $this->record->times->first();
        $data['schedule_days'] = $this->record->times->pluck('day')->map(fn ($day): string => (string) $day)->all();
        $data['schedule_start'] = $firstTime?->start;
        $data['schedule_end'] = $firstTime?->end;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['schedule_days'], $data['schedule_start'], $data['schedule_end']);

        return $data;
    }

    protected function afterSave(): void
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

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
