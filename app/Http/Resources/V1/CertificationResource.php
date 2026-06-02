<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CertificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'issuing_organization' => $this->issuing_organization,
            'credential_id' => $this->credential_id,
            'credential_url' => $this->credential_url,
            'issued_at' => $this->issued_at?->toDateString(),
            'expires_at' => $this->expires_at?->toDateString(),
            'does_not_expire' => $this->does_not_expire,
            'verification_status' => $this->verification_status,
            'sort_order' => $this->sort_order,
        ];
    }
}
