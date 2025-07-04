<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    use HasFactory;

    protected $fillable = [
        'skpd_id', 
        'nama', 
        'pagu', 
        'tahun_anggaran', 
        'total_serapan', 
        'presentase_serapan',
        'program_id',];

    public function skpd()
    {
        return $this->belongsTo(Skpd::class);
    }
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function subKegiatans()
    {
        return $this->hasMany(SubKegiatan::class);
    }
}

