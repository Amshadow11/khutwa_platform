<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SkillResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'category' => $this->category,
            'type' => $this->type,
            'proficiency_level' => $this->pivot?->proficiency_level,
            'proficiency_score' => $this->pivot?->proficiency_score,
            'years_experience' => $this->pivot?->years_experience,
            'endorsement_count' => $this->pivot?->endorsement_count,
            'is_featured' => $this->pivot?->is_featured,
        ];
    }
}
