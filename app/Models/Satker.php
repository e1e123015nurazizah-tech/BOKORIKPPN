<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Satker extends Authenticatable
{
    use Notifiable;

    protected $table = 'satkers'; // Menegaskan nama tabelnya

    protected $fillable = [
        'kode_satker',
        'nama_satker',
        'password',
        'admin_skpp_id', 
    ];

    protected $hidden = [
        'password',
    ];

    public function petugasSkpp()
    {
        return $this->belongsTo(Admin::class, 'admin_skpp_id');
    }
    // Relasi ke Pengajuan
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($satker) {
            // Hapus pengajuan satu per satu agar detailnya ikut bersih
            $satker->pengajuans->each(function ($pengajuan) {
                $pengajuan->delete();
            });
        });
    }
}