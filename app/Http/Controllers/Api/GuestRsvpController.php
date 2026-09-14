<?php

namespace App\Http\Controllers\Api;

use App\Models\Guest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GuestRsvpController extends Controller
{
    public function show(string $token)
    {
        $guest = Guest::query()
            ->where('confirmation_token', $token)
            ->firstOrFail();

        return response()->json([
            'id' => $guest->id,
            'ci' => $guest->ci,
            'first_name' => $guest->first_name,
            'last_name' => $guest->last_name,
            'full_name' => trim($guest->first_name . ' ' . $guest->last_name),
            'phone' => $guest->phone,
            'email' => $guest->email,
            'invitations' => $guest->invitations,
            'confirmation_token' => $guest->confirmation_token,
            'companions' => (int) ($guest->companions ?? 0),
            'confirmacion' => $guest->confirmacion ?? 'pendiente',
            'confirmation_status' => $guest->confirmacion ?? 'pending',
            'notes' => $guest->notes,
        ]);
    }

    public function confirm(
        Request $request,
        string $token
    ) {
        $guest = Guest::query()
            ->where('confirmation_token', $token)
            ->firstOrFail();

        $statusInput = $request->input('confirmacion', $request->input('confirmation_status'));

        if (blank($statusInput)) {
            $status = 'pendiente';
        } else {
            $statusMap = [
                'confirmed' => 'confirmado',
                'confirmado' => 'confirmado',
                'declined' => 'cancelado',
                'cancelado' => 'cancelado',
                'pendiente' => 'pendiente',
                'pending' => 'pendiente',
            ];

            $status = $statusMap[strtolower((string) $statusInput)] ?? $statusInput;
        }

        $validated = $request->validate([
            'confirmacion' => [
                'nullable',
                'string',
                'in:pendiente,confirmado,cancelado,confirmed,declined,pending'
            ],
            'confirmation_status' => [
                'nullable',
                'string',
                'in:pendiente,confirmado,cancelado,confirmed,declined,pending'
            ],
            'companions' => [
                'nullable',
                'integer',
                'min:0',
                'max:10'
            ]
        ]);

        if (! in_array($status, ['confirmado', 'cancelado'], true)) {
            $guest->update([
                'confirmacion' => 'pendiente',
                'companions' => 0,
                'confirmed_at' => null,
            ]);

            return response()->json([
                'message' => 'Respuesta pendiente',
                'data' => [
                    'confirmacion' => 'pendiente',
                    'companions' => 0,
                    'confirmed_at' => null,
                ]
            ]);
        }

        $confirmedAt = now();
        $companions = (int) ($validated['companions'] ?? 0);

        $guest->update([
            'confirmacion' => $status,
            'companions' => $companions,
            'confirmed_at' => $confirmedAt,
        ]);

        $freshGuest = $guest->fresh();

        return response()->json([
            'message' => 'Confirmación registrada',
            'data' => [
                'confirmacion' => $freshGuest->confirmacion,
                'companions' => (int) $freshGuest->companions,
                'confirmed_at' => $freshGuest->confirmed_at ? $freshGuest->confirmed_at->toDateTimeString() : null,
            ]
        ]);
    }
}