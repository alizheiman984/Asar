<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\DonorPaymentResource;
use Illuminate\Http\Resources\Json\JsonResource;

class BenefactorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'donor_payments' => DonorPaymentResource::collection($this->whenLoaded('donorPayments')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 