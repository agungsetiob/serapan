<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kabupaten extends Model
{
    use HasFactory;

    protected $fillable = ['nama', 'pagu', 'tahun_anggaran', 'total_serapan', 'presentase_serapan'];

    public function skpds()
    {
        return $this->belongsToMany(Skpd::class, 'skpd_tahun')
            ->withPivot('tahun_anggaran')
            ->withTimestamps();
    }

    public function skpdTahun()
    {
        return $this->hasMany(SkpdTahun::class);
    }
}

