<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ci' => $this->ci,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,

            'full_name' => $this->first_name . ' ' . $this->last_name,
            
            'phone' => $this->phone,
            'email' => $this->email,
            'invitations' => $this->invitations,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
