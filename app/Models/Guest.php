<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\support\Str;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'ci',
        'first_name',
        'last_name',
        'phone',
        'email',
        'invitations',
        'confirmacion',
        'notes',
        'confirmation_token',
        'companions',
        'confirmed_at',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'companions' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function ($guest) {
            if (blank($guest->confirmation_token)) {
                $guest->confirmation_token = (string) Str::uuid();
            }
        });
    }
}
