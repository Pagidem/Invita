<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'ci','first_name', 'last_name', 'phone', 'email', 'invitations', 'confirmacion', 'notes'
    ];
}
