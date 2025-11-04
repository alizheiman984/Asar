<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\RequestResource;
use App\Http\Resources\CampaignResource;
use App\Http\Resources\EmployeeResource;
use App\Http\Resources\DonorPaymentResource;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'team_name' => $this->businessInformation->team_name,
            'license_number' => $this->businessInformation->license_number,
            'logo' => $this->businessInformation->logo ?? null,
            'phone' => $this->phone,
            'bank_account_number' => $this->businessInformation->bank_account_number,
            'email' => $this->email,
            'campaigns' => CampaignResource::collection($this->whenLoaded('campaigns')),
            'employees' => EmployeeResource::collection($this->whenLoaded('employees')),
            'requests' => RequestResource::collection($this->whenLoaded('requests')),
            'donor_payments' => DonorPaymentResource::collection($this->whenLoaded('donorPayments')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 