<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tool' => [
                'id' => $this->tool->id,
                'name' => $this->tool->name,
                'category' => $this->tool->category,
            ],
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'total_price' => $this->total_price,
            'status' => $this->status,
            'created_at' => $this->created_at
        ];
    }
} 