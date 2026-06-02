<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResumeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'title' => $this->title,
            'slug' => $this->slug,
            'template' => [
                'id' => $this->template?->id,
                'slug' => $this->template?->slug,
                'name' => $this->template?->name,
            ],
            'visibility' => $this->visibility,
            'locale' => $this->locale,
            'direction' => $this->direction,
            'version_number' => $this->version_number,
            'snapshot_version' => $this->snapshot_version,
            'snapshot_hash' => $this->snapshot_hash,
            'completeness_score' => (float) $this->completeness_score,
            'published_at' => $this->published_at?->toISOString(),
            'last_generated_at' => $this->last_generated_at?->toISOString(),
            'public_url' => $this->shareUrl(),
            'sections' => $this->sections->map(fn ($section) => [
                'id' => $section->id,
                'key' => $section->section_key,
                'title' => $section->title,
                'visible' => $section->is_visible,
                'sort_order' => $section->sort_order,
                'settings' => $section->settings,
            ])->values(),
        ];
    }
}
