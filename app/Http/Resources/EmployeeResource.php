<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\TeamResource;
use App\Http\Resources\PointResource;
use App\Http\Resources\CampaignResource;
use App\Http\Resources\AttendanceResource;
use App\Http\Resources\DonorPaymentResource;
use App\Http\Resources\SpecializationResource;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'national_number' => $this->national_number,
            'position' => $this->position,
            'date_accession' => $this->date_accession,
            'image' => $this->image,
            'team' => new TeamResource($this->whenLoaded('team')),
            'specialization' => new SpecializationResource($this->whenLoaded('specialization')),
            'campaigns' => CampaignResource::collection($this->whenLoaded('campaigns')),
            'points' => PointResource::collection($this->whenLoaded('points')),
            'attendances' => AttendanceResource::collection($this->whenLoaded('attendances')),
            'donor_payments' => DonorPaymentResource::collection($this->whenLoaded('donorPayments')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 