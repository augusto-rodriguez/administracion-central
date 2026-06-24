<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Boletin extends Model
{
    protected $table = 'boletines';

    protected $fillable = ['fecha', 'tipo', 'texto_guardia'];

    public function cuarteleros()
    {
        return $this->belongsToMany(Cuartelero::class, 'boletin_cuarteleros');
    }

    public function maquinistas()
    {
        return $this->hasMany(BoletinMaquinista::class);
    }

    /**
     * Extrae primer nombre + primer apellido de un nombre completo.
     *
     * Regla:
     *   1 token  → se devuelve tal cual
     *   2 tokens → Nombre Apellido          → igual
     *   3 tokens → Nombre Segundo Apellido  → token[0] + token[2]
     *   4 tokens → Nombre Segundo Ap1 Ap2   → token[0] + token[2]
     *
     * Caso borde conocido: nombres compuestos como "María José Rojas"
     * quedarán como "María Rojas". No hay forma de resolverlo sin datos extra.
     */
    private function nombreCorto(string $nombreCompleto): string
    {
        $partes = preg_split('/\s+/', trim($nombreCompleto));

        return match (count($partes)) {
            1       => $partes[0],
            2       => $partes[0] . ' ' . $partes[1],
            default => $partes[0] . ' ' . $partes[2],
        };
    }

    public function generarTexto()
    {
        $saludo = $this->tipo === 'am' ? 'Buenos días' : 'Buenas noches';

        Carbon::setLocale('es');
        $fechaTexto = Carbon::parse($this->fecha)->translatedFormat('l d \\d\\e F \\d\\e Y');

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
            if ($m->voluntario->clave_actual) {
                $texto .= strtoupper("{$unidades} {$m->estado} {$m->voluntario->clave_actual}\n");
            } else {
                $corto  = $this->nombreCorto($m->voluntario->nombre);
                $texto .= strtoupper("{$unidades} {$m->estado} VOL. {$corto}\n");
            }
        }

        // Unidades fuera de servicio (0-8)
        $unidadesFueraServicio = \App\Models\Unidad::with('compania')
            ->where('activa', false)
            ->orderBy('compania_id')
            ->orderBy('nombre')
            ->get();

        if ($unidadesFueraServicio->isNotEmpty()) {
            $texto .= "\nUNIDADES 0-8:\n";
            foreach ($unidadesFueraServicio as $u) {
                $texto .= strtoupper("- {$u->nombre}\n");
            }
        }

        // Citaciones
        $citaciones = Citacion::with('compania')
            ->where(function($q) {
                $q->whereNull('fecha_citacion')
                ->orWhere('fecha_citacion', '>=', now());
            })
            ->orderByRaw('compania_id IS NULL DESC')
            ->orderBy('compania_id')
            ->get();

        if ($citaciones->isNotEmpty()) {
            $texto .= "\nCITACIONES:\n";
            foreach ($citaciones as $c) {
                $nombreCompania = $c->compania ? $c->compania->nombre : 'CUERPO DE BOMBEROS';
                $texto .= strtoupper("{$nombreCompania}: {$c->mensaje}\n");
            }
        }

        // Oficiales fuera de servicio (8-1)
        $oficialesFuera = \App\Models\OficialFueraServicio::with('voluntario')
            ->whereNull('fecha_fin')
            ->orWhere('fecha_fin', '>=', now())
            ->get();

        if ($oficialesFuera->isNotEmpty()) {
            $texto .= "\nOFICIALES 8-1:\n";
            foreach ($oficialesFuera as $of) {
                $clave = $of->voluntario->clave_actual
                    ? $of->voluntario->clave_actual
                    : strtoupper($of->voluntario->nombre);
                $texto .= strtoupper("- {$clave}\n");
            }
        }

        // Cambio de guardia (domingo PM)
        if ($this->texto_guardia) {
            $texto .= "\n{$this->texto_guardia}\n";
        }

        return $texto;
    }
}