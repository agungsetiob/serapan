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
        
    ];

    public function subKegiatan()
    {
        return $this->belongsTo(SubKegiatan::class, 'sub_kegiatan_id');
    }
    public function lampirans()
    {
        return $this->hasMany(NotaLampiran::class, 'nota_dinas_id');
    }

}
