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
            'mesa_id' => $this->mesa_id,
            'mesa' => $this->whenLoaded('mesa', function () {
                return $this->mesa ? [
                    'id' => $this->mesa->id,
                    'numero' => $this->mesa->numero,
                    'color' => $this->mesa->color,
                    'estado' => $this->estado,
                    'invitados_actuales' => $this->invitados_actuales,
                    'confirmados_actuales' => $this->confirmados_actuales,
                ] : null;
            }),
            'invitations' => $this->invitations,
            'confirmation_token' => $this->confirmation_token,
            'companions' => (int) ($this->companions ?? 0),
            'confirmacion' => $this->confirmacion ?? 'pendiente',
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
