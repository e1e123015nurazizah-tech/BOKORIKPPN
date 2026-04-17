<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengajuan extends Model
{
    protected $guarded = [];

    /**
     * Relasi ke Detail Gaji (Gunakan ini untuk Monitoring GajiWeb)
     */
    public function detailGaji(): HasOne
    {
        // Saya sesuaikan dengan nama model yang kamu pakai di kodingan Dashboard
        return $this->hasOne(DetailGajiweb::class, 'pengajuan_id');
    }

    /**
     * Relasi ke Detail PPNPN
     */
    public function detailPpnpn(): HasOne
    {
        return $this->hasOne(DetailPpnpn::class, 'pengajuan_id');
    }

    /**
     * Relasi ke Detail SKPP
     */
    public function detailSkpp(): HasOne
    {
        return $this->hasOne(DetailSkpp::class, 'pengajuan_id');
    }

    /**
     * Relasi ke Petugas KPPN (Admin)
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
    /**
     * Relasi ke Satuan Kerja (Pemilik Pengajuan)
     */
    public function satker(): BelongsTo
    {
        return $this->belongsTo(Satker::class, 'satker_id');
    }
}