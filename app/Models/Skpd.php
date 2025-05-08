<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use function PHPUnit\Framework\returnArgument;

class Skpd extends Model
{
    use HasFactory;

    protected $fillable = ['nama_skpd', 'status'];

    public function kabupatens()
    {
        return $this->belongsToMany(Kabupaten::class, 'skpd_tahun');
    }

    public function skpdTahun()
    {
        return $this->hasMany(SkpdTahun::class);
    }

    public function kegiatans()
    {
        return $this->hasMany(Kegiatan::class);
    }

}
