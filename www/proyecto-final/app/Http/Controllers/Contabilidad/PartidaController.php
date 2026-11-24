<?php

namespace App\Http\Controllers\Contabilidad;

use App\Enums\TipoPartida;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contabilidad\PartidaRequest;
use App\Models\Cuenta;
use App\Models\Partida;
use App\Models\Periodo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PartidaController extends Controller
{
    /** Libro diario del periodo activo (todas las partidas, o filtradas por tipo) */
    public function index(Request $request)
    {
        return $this->listado($request->query('tipo'));
    }

    /** "Asientos de ajuste": el mismo libro diario, solo con las partidas de ajuste */
    public function ajustes()
    {
        return $this->listado(TipoPartida::Ajuste->value);
    }

    public function create(Request $request)
    {
        $periodo = Periodo::activo();

        if ($respuesta = $this->validarPeriodo($periodo)) {
            return $respuesta;
        }

        $partida = new Partida([
            'tipo' => TipoPartida::tryFrom((string) $request->query('tipo')) ?? TipoPartida::Diario,
            'fecha' => now()->between($periodo->fecha_inicio, $periodo->fecha_fin->copy()->endOfDay()) ? now() : $periodo->fecha_inicio,
        ]);

        return view('contabilidad.partidas.form', $this->datosFormulario($partida, $periodo));
    }

    public function store(PartidaRequest $request)
    {
        $periodo = $request->periodo();

        $partida = DB::transaction(function () use ($request, $periodo) {
            // Bloquea el periodo mientras se asigna el numero, para que dos usuarios no tomen el mismo
            Periodo::whereKey($periodo->id)->lockForUpdate()->first();

            $partida = new Partida($request->safe()->only(['fecha', 'tipo', 'concepto']));
            $partida->periodo_id = $periodo->id;
            $partida->numero = Partida::siguienteNumero($periodo);
            $partida->user_id = $request->user()->id;
            $partida->save();

            $partida->detalles()->createMany($request->lineasValidadas());

            return $partida;
        });

        return redirect()->route('contabilidad.partidas.show', $partida)
            ->with('success', "Partida #{$partida->numero} registrada.");
    }

    public function show(Partida $partida)
    {
        $partida->load(['detalles.cuenta', 'periodo', 'usuario']);

        return view('contabilidad.partidas.show', compact('partida'));
    }

    public function edit(Partida $partida)
    {
        if ($respuesta = $this->validarPeriodo($partida->periodo)) {
            return $respuesta;
        }

        $partida->load('detalles');

        return view('contabilidad.partidas.form', $this->datosFormulario($partida, $partida->periodo));
    }

    public function update(PartidaRequest $request, Partida $partida)
    {
        DB::transaction(function () use ($request, $partida) {
            $partida->update($request->safe()->only(['fecha', 'tipo', 'concepto']));
            $partida->detalles()->delete();                       // se reemplazan todas las lineas
            $partida->detalles()->createMany($request->lineasValidadas());
        });

        return redirect()->route('contabilidad.partidas.show', $partida)
            ->with('success', "Partida #{$partida->numero} actualizada.");
    }

    public function destroy(Partida $partida)
    {
        if ($respuesta = $this->validarPeriodo($partida->periodo)) {
            return $respuesta;
        }

        $partida->delete(); // sus lineas se borran solas (cascadeOnDelete)

        return redirect()->route('contabilidad.partidas.index')
            ->with('success', "Partida #{$partida->numero} eliminada.");
    }

    private function listado(?string $tipo)
    {
        $periodo = Periodo::activo();
        $tipo = TipoPartida::tryFrom((string) $tipo);

        $partidas = $periodo
            ? $periodo->partidas()
                ->with('detalles.cuenta')
                ->withCount('evidencias')
                ->when($tipo, fn ($q) => $q->where('tipo', $tipo))
                ->orderBy('fecha')->orderBy('numero')
                ->get()
            : collect();

        return view('contabilidad.partidas.index', [
            'periodo' => $periodo,
            'partidas' => $partidas,
            'tipo' => $tipo,
            'tipos' => TipoPartida::cases(),
            'totalDebe' => $partidas->sum(fn ($p) => (float) $p->totalDebe()),
            'totalHaber' => $partidas->sum(fn ($p) => (float) $p->totalHaber()),
        ]);
    }

    private function validarPeriodo(?Periodo $periodo)
    {
        if (! $periodo) {
            return redirect()->route('contabilidad.periodos.index')
                ->with('warning', 'Primero crea o activa un periodo contable.');
        }

        if (! $periodo->estaAbierto()) {
            return redirect()->route('contabilidad.partidas.index')
                ->with('error', "El periodo \"{$periodo->nombre}\" está cerrado: no se pueden registrar ni modificar partidas.");
        }

        return null;
    }

    private function datosFormulario(Partida $partida, Periodo $periodo): array
    {
        // Solo cuentas de detalle y activas, agrupadas por clase (1 Activo, 2 Pasivo...)
        $cuentas = Cuenta::deDetalle()->where('activa', true)->orderBy('codigo')->get();
        $clases = Cuenta::whereNull('padre_id')->pluck('nombre', 'codigo');
        $grupos = $cuentas->groupBy(fn ($c) => $clases[substr($c->codigo, 0, 1)] ?? 'Otras');

        // Lineas para el formulario: las de old() si hubo error, las guardadas, o 2 vacías
        $lineas = old('lineas', $partida->exists
            ? $partida->detalles->map(fn ($d) => [
                'cuenta_id' => $d->cuenta_id,
                'concepto' => $d->concepto,
                'debe' => (float) $d->debe > 0 ? $d->debe : '',
                'haber' => (float) $d->haber > 0 ? $d->haber : '',
            ])->all()
            : []);

        while (count($lineas) < 2) {
            $lineas[] = ['cuenta_id' => '', 'concepto' => '', 'debe' => '', 'haber' => ''];
        }

        return [
            'partida' => $partida,
            'periodo' => $periodo,
            'grupos' => $grupos,
            'lineas' => array_values($lineas),
            'tipos' => TipoPartida::cases(),
            'numero' => $partida->exists ? $partida->numero : Partida::siguienteNumero($periodo),
        ];
    }
}