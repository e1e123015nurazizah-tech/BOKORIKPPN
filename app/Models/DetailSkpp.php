<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailSkpp extends Model
{
    use HasFactory;

    // Memberitahu Laravel nama tabelnya
    protected $table = 'detail_skpps';

    // MEMBUKA GERBANG DATA: Mengizinkan semua kolom diisi (Mass Assignment)
    protected $guarded = [];

    // Relasi ke tabel pengajuans (Opsional tapi sangat berguna nanti)
    public function pengajuan()
    {
        return $this->belongsTo(Pengajuan::class, 'pengajuan_id');
    }
}