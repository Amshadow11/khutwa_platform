<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'role' => $this->role,
            'project_url' => $this->project_url,
            'repository_url' => $this->repository_url,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'is_featured' => $this->is_featured,
            'sort_order' => $this->sort_order,
        ];
    }
}
