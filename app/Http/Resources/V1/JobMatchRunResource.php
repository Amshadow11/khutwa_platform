<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobMatchRunResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'job_id' => $this->job_id,
            'company_id' => $this->company_id,
            'status' => $this->status,
            'provider' => $this->provider,
            'model' => $this->model,
            'matching_version' => $this->matching_version,
            'job_snapshot_hash' => $this->job_snapshot_hash,
            'applications_total' => $this->applications_total,
            'applications_processed' => $this->applications_processed,
            'applications_reused' => $this->applications_reused,
            'applications_failed' => $this->applications_failed,
            'started_at' => $this->started_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'error_message' => $this->error_message,
            'metadata' => $this->metadata,
        ];
    }
}
