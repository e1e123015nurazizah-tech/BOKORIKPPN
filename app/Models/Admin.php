<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $table = 'admins';

    protected $fillable = [
        'nip',
        'nama_lengkap',
        'jabatan',
        'role', 
        'password',
        'is_active',
        'foto_profil',
    ];

    protected $hidden = [
        'password',
    ];
}