<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VolunteerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'national_number' => $this->national_number,
            'nationality' => $this->nationality,
            'phone' => $this->phone,
            'email' => $this->email,
            'image' => $this->image,
            'birth_date' => $this->birth_date,
            'specialization' => new SpecializationResource($this->whenLoaded('specialization')),
            'campaigns' => CampaignResource::collection($this->whenLoaded('campaigns')),
            'points' => PointResource::collection($this->whenLoaded('points')),
            'attendances' => AttendanceResource::collection($this->whenLoaded('attendances')),
            'requests' => RequestResource::collection($this->whenLoaded('requests')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 