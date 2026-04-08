<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class GuardiaComandante extends Model
{
    protected $table = 'guardia_comandante';

    protected $fillable = ['voluntario_id', 'fecha_inicio', 'fecha_fin'];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];

    public function voluntario()
    {
        return $this->belongsTo(Voluntario::class);
    }

    public static function activa()
    {
        $ahora = Carbon::now('America/Santiago');

        $domingoInicio = $ahora->copy()->startOfWeek(Carbon::SUNDAY);

        if ($ahora->dayOfWeek === Carbon::SUNDAY && $ahora->hour < 21) {
            $domingoInicio->subWeek();
        }

        $fechaInicio = $domingoInicio->toDateString();

        return static::where('fecha_inicio', $fechaInicio)
            ->with('voluntario.roles')
            ->first();
    }
}