<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ShortLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'guest_id',
        'target_url',
    ];

    protected static function booted(): void
    {
        static::creating(function ($link) {
            if (blank($link->code)) {
                do {
                    $code = Str::random(6);
                } while (self::where('code', $code)->exists());

                $link->code = $code;
            }
        });
    }
}
