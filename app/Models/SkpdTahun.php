<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkpdTahun extends Model
{
    use HasFactory;

    protected $table = 'skpd_tahun';
    protected $fillable = ['skpd_id', 'kabupaten_id', 'tahun_anggaran'];

    public function skpd()
    {
        return $this->belongsTo(Skpd::class);
    }

    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class);
    }
}

