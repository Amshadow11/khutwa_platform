<?php

namespace App\Services\Resume;

use App\Models\Resume;
use App\Models\ResumeTemplate;

class ResumeTemplateResolver
{
    public function resolve(?string $slug = null): ResumeTemplate
    {
        $slug ??= config('resumes.default_template', 'modern');

        return ResumeTemplate::query()
            ->where('is_active', true)
            ->where('slug', $slug)
            ->first()
            ?? ResumeTemplate::query()->where('is_active', true)->orderBy('id')->firstOrFail();
    }

    public function viewFor(Resume $resume): string
    {
        $template = $resume->template ?: $this->resolve();

        return $template->view_path;
    }
}
