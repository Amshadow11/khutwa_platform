<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'job_type' => $this->job_type,
            'experience_level' => $this->experience_level,
            'location' => $this->location,
            'remote_work' => (bool) $this->remote_work,
            'salary' => $this->salary,
            'status' => $this->status,
            'featured' => (bool) $this->featured,
            'urgent' => (bool) $this->urgent,
            'deadline' => $this->deadline?->toDateString(),
            'company' => $this->whenLoaded('company', fn() => [
                'id' => $this->company->id,
                'name' => $this->company->company_name,
                'logo_url' => $this->company->logo_url,
            ]),
        ];
    }
}
