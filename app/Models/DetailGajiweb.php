<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailGajiweb extends Model
{
    use HasFactory;

    protected $table = 'detail_gajiwebs';

    // Pakai guarded agar semua kolom bisa diisi otomatis
    protected $guarded = [];

    // Relasi balik ke tabel Pengajuans
    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id');
    }
}