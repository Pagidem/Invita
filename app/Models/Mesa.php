<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
    use HasFactory;

    protected $table = 'mesas';

    protected $fillable = [
        'numero',
        'color',
    ];

    protected $appends = [
        'invitados_actuales',
        'estado',
    ];

    public function guests()
    {
        return $this->hasMany(Guest::class);
    }

    public function getInvitadosActualesAttribute()
    {
        return $this->guests()
        ->sum('invitations');
    }

    public function getConfirmadosActualesAttribute()
    {
        return $this->guests()
            ->where('confirmacion', 'confirmado')
            ->get()
            ->sum(function ($guest) {

                return 1 + ($guest->companions ?? 0);
            });
    }

    public function getEstadoAttribute()
    {
        $cantidad = $this->invitados_actuales;

        if ($cantidad === 0) {
            return 'vacia';
        }

        if ($cantidad >= 10) {
            return 'llena';
        }

        return 'libre';
    }
}
