<?php

namespace App\Http\Controllers\Contabilidad;

use App\Enums\Naturaleza;
use App\Enums\Rubro;
use App\Enums\TipoCuenta;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contabilidad\CuentaRequest;
use App\Models\Cuenta;
use App\Support\CatalogoBaseComercial;
use Illuminate\Http\Request;

class CuentaController extends Controller
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->query('buscar'));

        // Ordenar por codigo como texto deja el arbol en orden: 1, 11, 1101, 110101, 110102, 12...
        $cuentas = Cuenta::query()
            ->withCount(['hijas', 'movimientos'])
            ->when($buscar, fn ($q) => $q->where(fn ($q) => $q
                ->where('codigo', 'like', "$buscar%")
                ->orWhereRaw('LOWER(nombre) LIKE ?', ['%'.mb_strtolower($buscar).'%'])))
            ->orderBy('codigo')
            ->get();

        return view('contabilidad.cuentas.index', [
            'cuentas' => $cuentas,
            'buscar' => $buscar,
            'catalogoVacio' => ! Cuenta::exists(),
        ]);
    }

    /** ?padre=1101 abre el formulario como "subcuenta de 1101" con sus datos ya puestos */
    public function create(Request $request)
    {
        $cuenta = new Cuenta;
        $padre = $request->query('padre') ? Cuenta::where('codigo', $request->query('padre'))->first() : null;

        if ($padre) {
            $cuenta->codigo = $this->siguienteCodigo($padre);
            $cuenta->tipo = $padre->tipo;
            $cuenta->naturaleza = $padre->naturaleza;
            $cuenta->rubro = $padre->rubro;
        }

        return view('contabilidad.cuentas.form', $this->datosFormulario($cuenta, $padre));
    }

    public function store(CuentaRequest $request)
    {
        $cuenta = new Cuenta($request->validated());
        $cuenta->ubicarEnArbol();
        $cuenta->save();

        return redirect()->route('contabilidad.cuentas.index', ['buscar' => $cuenta->codigo])
            ->with('success', "Cuenta {$cuenta->etiqueta()} creada.");
    }

    public function edit(Cuenta $cuenta)
    {
        return view('contabilidad.cuentas.form', $this->datosFormulario($cuenta, $cuenta->padre));
    }

    public function update(CuentaRequest $request, Cuenta $cuenta)
    {
        $cuenta->fill($request->validated());
        if ($cuenta->isDirty('codigo')) {
            $cuenta->ubicarEnArbol();
        }
        $cuenta->save();

        return redirect()->route('contabilidad.cuentas.index')->with('success', "Cuenta {$cuenta->etiqueta()} actualizada.");
    }

    public function destroy(Cuenta $cuenta)
    {
        if ($cuenta->hijas()->exists() || $cuenta->movimientos()->exists()) {
            return back()->with('error', "No se puede borrar {$cuenta->etiqueta()}: tiene subcuentas o movimientos. Puedes desactivarla.");
        }

        $cuenta->delete();

        return back()->with('success', "Cuenta {$cuenta->etiqueta()} eliminada.");
    }

    /** Activar o desactivar. Una cuenta desactivada no se puede usar en partidas nuevas. */
    public function cambiarEstado(Cuenta $cuenta)
    {
        $cuenta->activa = ! $cuenta->activa;
        $cuenta->save();

        return back()->with('success', "Cuenta {$cuenta->etiqueta()} ".($cuenta->activa ? 'activada.' : 'desactivada.'));
    }

    /** Boton "Cargar catalogo base para empresa comercial" */
    public function cargarBase()
    {
        $creadas = CatalogoBaseComercial::cargar();

        return redirect()->route('contabilidad.cuentas.index')
            ->with('success', $creadas
                ? "Se cargaron {$creadas} cuentas del catálogo base comercial. Revísenlo y ajústenlo a la tienda."
                : 'El catálogo base ya estaba cargado; no se agregó nada.');
    }

    /** Codigo sugerido para una subcuenta nueva: 1 -> 11, 12... ; 11 -> 1101, 1102... */
    private function siguienteCodigo(Cuenta $padre): string
    {
        $digitos = strlen($padre->codigo) === 1 ? 1 : 2;
        $ultimo = $padre->hijas()->get()->max(fn ($h) => (int) substr($h->codigo, strlen($padre->codigo)));

        return $padre->codigo.str_pad((string) (($ultimo ?? 0) + 1), $digitos, '0', STR_PAD_LEFT);
    }

    private function datosFormulario(Cuenta $cuenta, ?Cuenta $padre): array
    {
        return [
            'cuenta' => $cuenta,
            'padre' => $padre,
            'tipos' => TipoCuenta::cases(),
            'naturalezas' => Naturaleza::cases(),
            'rubros' => Rubro::cases(),
            'codigoBloqueado' => $cuenta->exists && ($cuenta->hijas()->exists() || $cuenta->movimientos()->exists()),
        ];
    }
}