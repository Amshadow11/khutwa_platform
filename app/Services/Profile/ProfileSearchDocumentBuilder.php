<?php

namespace App\Services\Profile;

use App\Models\User;

class ProfileSearchDocumentBuilder
{
    public function build(User $user): array
    {
        $user->loadMissing([
            'professionalProfile',
            'canonicalSkills',
            'experiences',
            'educations',
            'projects',
            'certifications',
            'languages',
        ]);

        return [
            'user_id' => $user->id,
            'name' => $user->display_name,
            'headline' => $user->professionalProfile?->headline,
            'current_title' => $user->professionalProfile?->current_title,
            'industry' => $user->professionalProfile?->industry,
            'location' => [
                'country' => $user->professionalProfile?->location_country,
                'city' => $user->professionalProfile?->location_city,
            ],
            'skills' => $user->canonicalSkills->pluck('normalized_name')->values()->all(),
            'experience_titles' => $user->experiences->pluck('title')->values()->all(),
            'companies' => $user->experiences->pluck('company_name')->filter()->unique()->values()->all(),
            'education' => $user->educations
                ->map(fn ($education) => trim($education->degree . ' ' . $education->field_of_study . ' ' . $education->institution_name))
                ->values()
                ->all(),
            'projects' => $user->projects->pluck('title')->values()->all(),
            'certifications' => $user->certifications->pluck('name')->values()->all(),
            'languages' => $user->languages->pluck('iso_code')->values()->all(),
            'updated_at' => now()->toISOString(),
        ];
    }

    public function refresh(User $user): array
    {
        $document = $this->build($user);

        $user->professionalProfile()->firstOrCreate([])->update([
            'search_document' => $document,
            'last_indexed_at' => now(),
        ]);

        return $document;
    }
}
