<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    protected $fillable = [
        'ci','first_name', 'last_name', 'phone', 'email', 'invitations', 'notes'
    ];
}
