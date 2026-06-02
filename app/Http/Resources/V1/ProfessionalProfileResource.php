<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfessionalProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'headline' => $this->headline,
            'current_title' => $this->current_title,
            'current_company' => $this->current_company,
            'industry' => $this->industry,
            'seniority_level' => $this->seniority_level,
            'location' => [
                'country' => $this->location_country,
                'city' => $this->location_city,
            ],
            'open_to_work' => $this->open_to_work,
            'visibility' => $this->profile_visibility,
            'public_sections' => $this->public_sections,
            'completion_score' => (float) $this->profile_completeness_score,
        ];
    }
}
