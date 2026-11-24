<?php

namespace App\Http\Controllers;

use App\Enums\TipoEvidencia;
use App\Http\Requests\Evidencias\EvidenciaRequest;
use App\Http\Requests\Evidencias\VincularRequest;
use App\Models\Evidencia;
use App\Models\Periodo;
use App\Models\Vinculo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Evidencias: es compartido por Contabilidad y Economia (por eso no esta en ninguna de las dos carpetas).
 */
class EvidenciaController extends Controller
{
    public function index(Request $request)
    {
        $tipo = TipoEvidencia::tryFrom((string) $request->query('tipo'));
        $buscar = trim((string) $request->query('buscar'));

        $evidencias = Evidencia::query()
            ->withCount('vinculos')
            ->when($tipo, fn ($q) => $q->where('tipo', $tipo))
            ->when($buscar, fn ($q) => $q->where(fn ($q) => $q
                ->whereRaw('LOWER(titulo) LIKE ?', ['%'.mb_strtolower($buscar).'%'])
                ->orWhereRaw('LOWER(fuente) LIKE ?', ['%'.mb_strtolower($buscar).'%'])))
            ->latest()
            ->get();

        // Trazabilidad del periodo activo: cuantas partidas tienen 0, 1 o 2+ fuentes
        $periodo = Periodo::activo();
        $minimo = config('evidencias.minimo_fuentes');
        $conteo = $periodo ? $periodo->partidas()->withCount('evidencias')->pluck('evidencias_count') : collect();
        $resumen = [
            'total' => $conteo->count(),
            'sin' => $conteo->filter(fn ($n) => $n === 0)->count(),
            'incompletas' => $conteo->filter(fn ($n) => $n > 0 && $n < $minimo)->count(),
            'completas' => $conteo->filter(fn ($n) => $n >= $minimo)->count(),
        ];

        return view('evidencias.index', [
            'evidencias' => $evidencias,
            'tipos' => TipoEvidencia::cases(),
            'tipo' => $tipo,
            'buscar' => $buscar,
            'periodo' => $periodo,
            'resumen' => $resumen,
            'minimo' => $minimo,
        ]);
    }

    /** ?vincular=partida:5: (tipo:id:campo) para enlazarla a ese dato al guardar */
    public function create(Request $request)
    {
        $evidencia = new Evidencia([
            'tipo' => TipoEvidencia::tryFrom((string) $request->query('tipo')) ?? TipoEvidencia::Factura,
        ]);

        return view('evidencias.form', [
            'evidencia' => $evidencia,
            'tipos' => TipoEvidencia::cases(),
            'vincular' => $request->query('vincular'),
            'destino' => $this->describirDestino($request->query('vincular')),
        ]);
    }

    public function store(EvidenciaRequest $request)
    {
        $evidencia = DB::transaction(function () use ($request) {
            $evidencia = new Evidencia($request->safe()->except(['archivo', 'vincular']));
            $evidencia->user_id = $request->user()->id;

            if ($request->hasFile('archivo')) {
                $this->guardarArchivo($evidencia, $request->file('archivo'));
            }
            $evidencia->save();

            // Si vino desde un dato ("Subir nueva y vincular"), queda enlazada de una vez
            if ($destino = $this->resolverDestino($request->input('vincular'))) {
                [$registro, $campo] = $destino;
                $registro->evidencias()->attach($evidencia->id, ['campo' => $campo]);
            }

            return $evidencia;
        });

        // Regresar al dato desde el que se subio, si lo hay
        $vinculo = $evidencia->vinculos()->first();
        $volver = $vinculo?->urlDato() ?? route('evidencias.show', $evidencia);

        return redirect($volver)->with('success', "Evidencia \"{$evidencia->titulo}\" guardada".($vinculo ? ' y vinculada.' : '.'));
    }

    public function show(Evidencia $evidencia)
    {
        $evidencia->load(['vinculos.evidenciable', 'usuario']);

        return view('evidencias.show', compact('evidencia'));
    }

    public function edit(Evidencia $evidencia)
    {
        return view('evidencias.form', [
            'evidencia' => $evidencia,
            'tipos' => TipoEvidencia::cases(),
            'vincular' => null,
            'destino' => null,
        ]);
    }

    public function update(EvidenciaRequest $request, Evidencia $evidencia)
    {
        $evidencia->fill($request->safe()->except(['archivo', 'vincular']));

        if ($request->hasFile('archivo')) {
            $evidencia->borrarArchivo();                         // reemplaza el archivo anterior
            $this->guardarArchivo($evidencia, $request->file('archivo'));
        }
        $evidencia->save();

        return redirect()->route('evidencias.show', $evidencia)->with('success', 'Evidencia actualizada.');
    }

    public function destroy(Evidencia $evidencia)
    {
        $evidencia->borrarArchivo();
        $evidencia->delete();                                    // sus vinculos se borran solos (cascade)

        return redirect()->route('evidencias.index')->with('success', "Evidencia \"{$evidencia->titulo}\" eliminada.");
    }

    /** Ver (PDF e imagenes) o descargar (lo demás). ?descargar=1 fuerza la descarga. */
    public function archivo(Request $request, Evidencia $evidencia)
    {
        abort_unless($evidencia->tieneArchivo(), 404, 'Esta evidencia no tiene archivo.');

        $disco = Storage::disk('local');

        return $evidencia->seVeEnNavegador() && ! $request->boolean('descargar')
            ? $disco->response($evidencia->archivo_ruta, $evidencia->archivo_nombre)
            : $disco->download($evidencia->archivo_ruta, $evidencia->archivo_nombre);
    }

    /** Enlazar una evidencia existente a un dato */
    public function vincular(VincularRequest $request)
    {
        $registro = $request->registro();
        $campo = $request->campo();

        $yaEsta = $registro->evidencias()->wherePivot('campo', $campo)->whereKey($request->input('evidencia_id'))->exists();
        if ($yaEsta) {
            return back()->with('warning', 'Esa evidencia ya estaba vinculada a este dato.');
        }

        $registro->evidencias()->attach($request->input('evidencia_id'), ['campo' => $campo]);

        return back()->with('success', 'Evidencia vinculada.');
    }

    /** Quitar el vinculo (la evidencia no se borra) */
    public function desvincular(Vinculo $vinculo)
    {
        $vinculo->delete();

        return back()->with('success', 'Se quitó el vínculo. La evidencia sigue guardada en Evidencias.');
    }

    private function guardarArchivo(Evidencia $evidencia, UploadedFile $archivo): void
    {
        $evidencia->archivo_ruta = $archivo->store('evidencias', 'local'); // storage/app/private/evidencias
        $evidencia->archivo_nombre = $archivo->getClientOriginalName();
        $evidencia->archivo_mime = $archivo->getMimeType();
        $evidencia->archivo_tamano = $archivo->getSize();
    }

    /** "partida:5:" -> [Partida 5, ''] ; devuelve null si no es valido */
    private function resolverDestino(?string $vincular): ?array
    {
        if (! $vincular || substr_count($vincular, ':') < 1) {
            return null;
        }

        [$tipo, $id, $campo] = array_pad(explode(':', $vincular, 3), 3, '');
        $clase = Relation::getMorphedModel($tipo);
        $campos = config("evidencias.vinculables.$tipo.campos", []);

        if (! $clase || ! array_key_exists($campo, $campos) || ! ($registro = $clase::find($id))) {
            return null;
        }

        return [$registro, $campo];
    }

    private function describirDestino(?string $vincular): ?string
    {
        $destino = $this->resolverDestino($vincular);
        if (! $destino) {
            return null;
        }

        [$registro, $campo] = $destino;
        $vinculo = new Vinculo(['evidenciable_type' => $registro->getMorphClass(), 'evidenciable_id' => $registro->id, 'campo' => $campo]);
        $vinculo->setRelation('evidenciable', $registro);

        return $vinculo->descripcionDato();
    }
}