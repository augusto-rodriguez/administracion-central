<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ChecklistHallazgoFoto extends Model
{
    public $timestamps = false;

    protected $table = 'checklist_hallazgo_fotos';

    protected $fillable = [
        'hallazgo_id',
        'ruta',
        'nombre_original',
        'tipo',
    ];

    // 🔗 Relaciones

    public function hallazgo()
    {
        return $this->belongsTo(ChecklistHallazgo::class, 'hallazgo_id');
    }

    // 🛠 Helpers

    /**
     * URL pública de la foto.
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->ruta);
    }
}