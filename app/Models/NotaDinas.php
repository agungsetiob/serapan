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
        'jenis'
        
    ];

    public function subKegiatan()
    {
        return $this->belongsTo(SubKegiatan::class, 'sub_kegiatan_id');
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
        )->withTimestamps();
    }

    // Nota yang mengaitkan nota ini (parent)
    public function dikaitkanOleh()
    {
        return $this->belongsToMany(
            NotaDinas::class,
            'nota_dinas_relasis',
            'terkait_id',
            'nota_dinas_id'
        )->withTimestamps();
    }

}
