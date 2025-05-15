<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class NotaLampiran extends Model
{
    use HasFactory;

    protected $table = 'nota_lampirans';

    protected $fillable = [
        'nota_dinas_id',
        'nama_file',
        'path'
    ];

    /**
     * Relasi ke model NotaDinas
     */
    public function notaDinas()
    {
        return $this->belongsTo(NotaDinas::class);
    }
    
    protected static function booted()
    {
        static::deleted(function ($lampiran) {
            try {
                Storage::disk('public')->delete($lampiran->path);
            } catch (\Exception $e) {
                Log::error("Gagal hapus lampiran: " . $e->getMessage());
            }
        });
    }
   
}
