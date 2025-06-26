<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaDinas extends Model
{
    use HasFactory;
    protected $fillable = [
        'nomor_nota', 
        'perihal', 
        'anggaran', 
        'tanggal_pengajuan',
        'sub_kegiatan_id',
        'skpd_id',
        'jenis'
        
    ];

    public function subKegiatan()
    {
        return $this->belongsTo(SubKegiatan::class, 'sub_kegiatan_id');
    }
    public function skpd()
    {
        return $this->belongsTo(Skpd::class, 'skpd_id');
    }
    public function lampirans()
    {
        return $this->hasMany(NotaLampiran::class, 'nota_dinas_id');
    }

    // Nota yang dikaitkan (anak)
    public function terkait()
    {
        return $this->belongsToMany(
            NotaDinas::class,
            'nota_dinas_relasis',
            'nota_dinas_id',
            'terkait_id'
        )->withPivot('anggaran')->withTimestamps();
    }

    // Nota yang mengaitkan nota ini (parent)
    public function dikaitkanOleh()
    {
        return $this->belongsToMany(
            NotaDinas::class,
            'nota_dinas_relasis',
            'terkait_id',
            'nota_dinas_id'
        )->withPivot('anggaran')->withTimestamps();
    }

    // Helper method untuk mendapatkan SKPD baik langsung atau melalui sub kegiatan
    public function getSkpdAttribute()
    {
        if ($this->skpd_id) {
            return $this->skpd;
        }

        if ($this->subKegiatan && $this->subKegiatan->kegiatan && $this->subKegiatan->kegiatan->skpd) {
            return $this->subKegiatan->kegiatan->skpd;
        }

        return null;
    }

    protected $appends = ['sisa_anggaran'];

    public function getSisaAnggaranAttribute()
    {
        // jika belum eager-load, maka $this->terkait belum ada Collection
        if (! $this->relationLoaded('terkait')) {
            $this->load('terkait');
        }

        // Collection::sum() dengan key string
        $dipakai = $this->terkait->sum('pivot.anggaran');

        return $this->anggaran - $dipakai;
    }
}
