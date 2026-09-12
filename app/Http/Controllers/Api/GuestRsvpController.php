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
            'data' => [
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
                'notes' => $guest->notes,
            ]
        ]);
    }

    public function confirm(
        Request $request,
        string $token
    ) {
        $guest = Guest::query()
            ->where('confirmation_token', $token)
            ->firstOrFail();

        $statusInput = $request->input('confirmacion', $request->input('confirmation_status', 'pendiente'));

        $statusMap = [
            'confirmed' => 'confirmado',
            'confirmado' => 'confirmado',
            'declined' => 'cancelado',
            'cancelado' => 'cancelado',
            'pendiente' => 'pendiente',
        ];

        $status = $statusMap[$statusInput] ?? $statusInput;

        $validated = $request->validate([
            'confirmacion' => [
                'nullable',
                'string',
                'in:pendiente,confirmado,cancelado,confirmed,declined'
            ],
            'confirmation_status' => [
                'nullable',
                'string',
                'in:pendiente,confirmado,cancelado,confirmed,declined'
            ],
            'companions' => [
                'required',
                'integer',
                'min:0',
                'max:10'
            ]
        ]);

        $confirmedAt = in_array($status, ['confirmado', 'cancelado'], true) ? now() : null;

        $guest->update([
            'confirmacion' => $status,
            'companions' => (int) $validated['companions'],
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