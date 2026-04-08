<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Boletin extends Model
{
    protected $table = 'boletines';

    protected $fillable = ['fecha', 'tipo','texto_guardia'];

    public function cuarteleros()
    {
        return $this->belongsToMany(Cuartelero::class, 'boletin_cuarteleros');
    }

    public function maquinistas()
    {
        return $this->hasMany(BoletinMaquinista::class);
    }
    
    public function generarTexto()
    {
        $saludo = $this->tipo === 'am' ? 'Buenos días' : 'Buenas noches';

        Carbon::setLocale('es');
        $fechaTexto = Carbon::parse($this->fecha)->translatedFormat('d \\d\\e F \\d\\e Y');

        $horario = $this->tipo === 'am' ? 'DE 08 A 20 HRS' : 'DE 20 A 08 HRS';

        $texto  = strtoupper("{$saludo}, 11-7 CV5 124 DEL CUERPO DE BOMBEROS DE SAN PEDRO DE LA PAZ ");
        $texto .= strtoupper("CORRESPONDIENTE A HOY {$fechaTexto}.\n\n");
        $texto .= strtoupper("EN TURNO {$horario}:\n");

        // Cuarteleros
        foreach ($this->cuarteleros as $c) {
            $alias = $c->compania ? '38-' . $c->compania->numero : $c->nombre;
            $texto .= "- {$alias}\n";
        }

        // Maquinistas
        $texto .= "\nMAQUINISTAS EN SERVICIO:\n";
        foreach ($this->maquinistas as $m) {
            $unidades = $m->unidades_texto ?: $m->unidad->nombre;
            $texto .= strtoupper("{$unidades} {$m->estado} VOL. {$m->voluntario->nombre}\n");
        }

        // Citaciones
        $citaciones = Citacion::with('compania')
            ->where(function($q) {
                $q->whereNull('fecha_citacion')
                ->orWhere('fecha_citacion', '>=', now());
            })
            ->orderBy('compania_id')
            ->get();

        if ($citaciones->isNotEmpty()) {
            $texto .= "\nCITACIONES:\n";
            foreach ($citaciones as $c) {
                $texto .= strtoupper("{$c->compania->nombre}: {$c->mensaje}\n");
            }
        }

        // Cambio de guardia — separado, NO se incluye en el texto plano
        // Se maneja visualmente en el modal con $boletin->texto_guardia

        return $texto;
    }
}