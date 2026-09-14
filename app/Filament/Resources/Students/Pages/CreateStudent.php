<?php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Pages\MonthlyPayment;
use App\Filament\Resources\Students\StudentResource;
use App\Models\Course;
use App\Models\Student;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    public ?int $returnCourseId = null;

    public function mount(): void
    {
        parent::mount();

        $this->returnCourseId = request()->integer('course_id') ?: null;

        if ($this->returnCourseId) {
            $this->previousUrl = MonthlyPayment::getUrl(['courseId' => $this->returnCourseId]);
        }
    }

    protected function getRedirectUrl(): string
    {
        $courseId = $this->returnCourseId ?? ($this->data['course_id'] ?? null);

        if ($courseId) {
            return MonthlyPayment::getUrl(['courseId' => $courseId]);
        }

        return parent::getRedirectUrl();
    }

    protected function getCancelFormAction(): Action
    {
        if ($this->returnCourseId) {
            return Action::make('cancel')
                ->label(__('filament-panels::resources/pages/create-record.form.actions.cancel.label'))
                ->url(MonthlyPayment::getUrl(['courseId' => $this->returnCourseId]))
                ->color('gray');
        }

        return parent::getCancelFormAction();
    }

    protected function handleRecordCreation(array $data): Model
    {
        $courseId = $data['course_id'] ?? null;

        $user = User::create([
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'email' => $data['email'] ?? null,
            'username' => Str::slug($data['firstname'].'.'.$data['lastname']),
            'password' => bcrypt(Str::random(16)),
        ]);

        $student = Student::create([
            'id' => $user->id,
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

        if (filled($data['phone_number'] ?? null)) {
            $student->phone()->create(['phone_number' => $data['phone_number']]);
        }

        if (filled($courseId)) {
            $student->enroll(Course::findOrFail($courseId));
        }

        return $student;
    }
}
