<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'content' => $this->content,
            'status' => $this->status,
            'team' => new TeamResource($this->whenLoaded('team')),
            'volunteer' => new VolunteerResource($this->whenLoaded('volunteer')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 