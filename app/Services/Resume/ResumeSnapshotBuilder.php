<?php

namespace App\Services\Resume;

use App\Models\User;

class ResumeSnapshotBuilder
{
    public function build(User $user, ?string $tailoredSummary = null): array
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
            'snapshot_schema' => 1,
            'captured_at' => now()->toISOString(),
            'identity' => [
                'name' => $user->display_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'city' => $user->professionalProfile?->location_city ?: $user->address,
                'country' => $user->professionalProfile?->location_country,
                'headline' => $user->professionalProfile?->headline,
                'current_title' => $user->professionalProfile?->current_title,
                'current_company' => $user->professionalProfile?->current_company,
                'summary' => $tailoredSummary ?: $user->bio,
                'links' => [
                    'linkedin' => $user->linkedin_url,
                    'github' => $user->github_url,
                    'portfolio' => $user->portfolio_url,
                ],
            ],
            'skills' => $user->canonicalSkills->map(fn ($skill) => [
                'id' => $skill->id,
                'name' => $skill->name,
                'category' => $skill->category,
                'type' => $skill->type,
                'level' => $skill->pivot?->proficiency_level,
                'score' => $skill->pivot?->proficiency_score,
                'years' => $skill->pivot?->years_experience,
                'featured' => (bool) $skill->pivot?->is_featured,
                'endorsements' => $skill->pivot?->endorsement_count,
            ])->values()->all(),
            'experiences' => $user->experiences->map(fn ($experience) => [
                'id' => $experience->id,
                'title' => $experience->title,
                'company_name' => $experience->company_name,
                'employment_type' => $experience->employment_type,
                'location' => $experience->location,
                'is_remote' => $experience->is_remote,
                'start_date' => $experience->start_date?->toDateString(),
                'end_date' => $experience->end_date?->toDateString(),
                'is_current' => $experience->is_current,
                'summary' => $experience->summary,
                'highlights' => $experience->highlights,
            ])->values()->all(),
            'educations' => $user->educations->map(fn ($education) => [
                'id' => $education->id,
                'institution_name' => $education->institution_name,
                'degree' => $education->degree,
                'field_of_study' => $education->field_of_study,
                'grade' => $education->grade,
                'start_date' => $education->start_date?->toDateString(),
                'end_date' => $education->end_date?->toDateString(),
                'is_current' => $education->is_current,
                'description' => $education->description,
            ])->values()->all(),
            'projects' => $user->projects->map(fn ($project) => [
                'id' => $project->id,
                'title' => $project->title,
                'description' => $project->description,
                'role' => $project->role,
                'project_url' => $project->project_url,
                'repository_url' => $project->repository_url,
                'featured' => $project->is_featured,
            ])->values()->all(),
            'certifications' => $user->certifications->map(fn ($certification) => [
                'id' => $certification->id,
                'name' => $certification->name,
                'issuing_organization' => $certification->issuing_organization,
                'credential_url' => $certification->credential_url,
                'issued_at' => $certification->issued_at?->toDateString(),
                'expires_at' => $certification->expires_at?->toDateString(),
                'does_not_expire' => $certification->does_not_expire,
            ])->values()->all(),
            'languages' => $user->languages->map(fn ($language) => [
                'id' => $language->id,
                'name' => $language->name,
                'native_name' => $language->native_name,
                'iso_code' => $language->iso_code,
                'level' => $language->pivot?->proficiency_level,
                'native' => (bool) $language->pivot?->is_native,
            ])->values()->all(),
        ];
    }

    public function hash(array $snapshot): string
    {
        return hash('sha256', json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
