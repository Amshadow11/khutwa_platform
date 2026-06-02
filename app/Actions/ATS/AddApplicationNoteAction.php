<?php

namespace App\Actions\ATS;

use App\Models\Application;
use App\Models\ApplicationNote;
use App\Models\Company;

class AddApplicationNoteAction
{
    public function __construct(private readonly LogApplicationActivityAction $activity)
    {
    }

    public function execute(Application $application, Company $company, array $data): ApplicationNote
    {
        $note = $application->atsNotes()->create([
            'company_id' => $company->id,
            'body' => $data['body'],
            'visibility' => 'internal',
        ]);

        $this->activity->execute($application, $company, 'note_added', 'تمت إضافة ملاحظة داخلية.');

        return $note;
    }
}
