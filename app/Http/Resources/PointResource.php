<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PointResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'points_change' => $this->points_change,
            'change_reason' => $this->change_reason,
            'date_change' => $this->date_change,
            'volunteer' => new VolunteerResource($this->whenLoaded('volunteer')),
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'campaign' => new CampaignResource($this->whenLoaded('campaign')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 