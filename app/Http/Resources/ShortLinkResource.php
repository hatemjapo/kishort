<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShortLinkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'target_url' => $this->target_url,
            'code' => $this->code,
            'url' => url($this->code),
            'expires_at' => $this->expires_at,
            'is_active' => $this->is_active,
        ];
    }
}
