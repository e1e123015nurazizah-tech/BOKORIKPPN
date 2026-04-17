<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPpnpn extends Model
{
    use HasFactory;

    protected $table = 'detail_ppnpns';

    // Mengizinkan semua kolom diisi secara otomatis dari Controller
    protected $guarded = [];

    // Relasi balik ke tabel utama Pengajuans
    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id');
    }
}