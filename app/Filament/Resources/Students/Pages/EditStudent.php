<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use App\Models\Course;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\RelationManagers\RelationManagerConfiguration;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Livewire;
use Illuminate\Database\Eloquent\Model;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = $this->record->user;

        $data['firstname'] = $user?->firstname ?? '';
        $data['lastname'] = $user?->lastname ?? '';
        $data['phone_number'] = $this->record->phone->first()?->phone_number;
        $data['course_id'] = $this->record->enrollments()
            ->whereNull('parent_id')
            ->latest('created_at')
            ->value('course_id');

        return $data;
    }

    public function getRelationManagersContentComponent(): Component
    {
        $managers = $this->getRelationManagers();
        $ownerRecord = $this->getRecord();
        $managerLivewireData = ['ownerRecord' => $ownerRecord, 'pageClass' => static::class];

        return Group::make(
            collect($managers)
                ->map(fn ($manager, $key): Livewire => Livewire::make(
                    $normalizedClass = $this->normalizeRelationManagerClass($manager),
                    [...$managerLivewireData, ...(($manager instanceof RelationManagerConfiguration) ? [...$manager->relationManager::getDefaultProperties(), ...$manager->getProperties()] : $manager::getDefaultProperties())],
                )->key("{$normalizedClass}-{$key}"))
                ->all()
        );
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $courseId = $data['course_id'] ?? null;

        $record->user->update([
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
        ]);

        $record->update([
            'birthdate' => $data['birthdate'] ?? null,
            'gender_id' => $data['gender_id'],
            'address' => $data['address'] ?? null,
            'zip_code' => $data['zip_code'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'country' => $data['country'] ?? null,
            'iban' => $data['iban'] ?? null,
            'bic' => $data['bic'] ?? null,
            'institution_id' => $data['institution_id'] ?? null,
        ]);

        $this->record->phone()->delete();
        if (filled($data['phone_number'] ?? null)) {
            $this->record->phone()->create(['phone_number' => $data['phone_number']]);
        }

        if (filled($courseId) && ! $record->enrollments()->where('course_id', $courseId)->exists()) {
            $record->enroll(Course::findOrFail($courseId));
        }

        return $record;
    }
}
