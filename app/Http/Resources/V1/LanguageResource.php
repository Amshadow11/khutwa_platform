<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LanguageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'native_name' => $this->native_name,
            'iso_code' => $this->iso_code,
            'proficiency_level' => $this->pivot?->proficiency_level,
            'proficiency_score' => $this->pivot?->proficiency_score,
            'is_native' => $this->pivot?->is_native,
        ];
    }
}
