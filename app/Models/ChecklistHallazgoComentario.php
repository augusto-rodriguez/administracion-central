<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistHallazgoComentario extends Model
{
    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected $table = 'checklist_hallazgo_comentarios';

    protected $fillable = [
        'hallazgo_id',
        'user_id',
        'comentario',
        'estado_anterior',
        'estado_nuevo',
    ];

    // 🔗 Relaciones

    public function hallazgo()
    {
        return $this->belongsTo(ChecklistHallazgo::class, 'hallazgo_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // 🛠 Helpers

    /**
     * Indica si este comentario registra un cambio de estado.
     */
    public function esCambioEstado(): bool
    {
        return !is_null($this->estado_anterior) && !is_null($this->estado_nuevo);
    }
}