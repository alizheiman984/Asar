<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'amount' => $this->amount,
            'status' => $this->status,
            'team_id' => $this->team_id,
            'benefactor_id' => $this->benefactor_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'team' => $this->whenLoaded('team'),
            'benefactor' => $this->whenLoaded('benefactor'),
        ];
    }
} 