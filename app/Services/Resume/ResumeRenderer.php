<?php

namespace App\Services\Resume;

use App\Models\Resume;
use Illuminate\Contracts\View\View;

class ResumeRenderer
{
    public function __construct(
        private readonly ResumeTemplateResolver $templateResolver,
        private readonly ResumeSectionDataBuilder $sectionDataBuilder,
    ) {
    }

    public function view(Resume $resume): View
    {
        $resume->loadMissing(['user', 'template', 'sections.items']);
        $templateView = $this->templateResolver->viewFor($resume);

        return view($templateView, [
            'resume' => $resume,
            'snapshot' => $resume->profile_snapshot ?? [],
            'sections' => $this->sectionDataBuilder->build($resume),
            'settings' => $resume->settings ?? [],
        ]);
    }

    public function html(Resume $resume): string
    {
        return $this->view($resume)->render();
    }
}
