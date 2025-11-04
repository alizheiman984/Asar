<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\CampaignResource;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\VolunteerResource;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'is_attended' => $this->is_attended,
            'date_attendance' => $this->date_attendance,
            'image' => $this->image,
            'volunteer' => new VolunteerResource($this->whenLoaded('volunteer')),
            'campaign' => new CampaignResource($this->whenLoaded('campaign')),
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 