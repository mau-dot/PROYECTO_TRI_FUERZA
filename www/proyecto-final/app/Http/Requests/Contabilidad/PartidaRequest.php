<?php

namespace App\Http\Requests\Contabilidad;

use App\Enums\TipoPartida;
use App\Models\Cuenta;
use App\Models\Partida;
use App\Models\Periodo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Reglas de la partida doble:
 *  - al menos 2 lineas;
 *  - cada linea va al DEBE o al HABER (no a los dos, sin negativos);
 *  - solo cuentas de detalle y activas;
 *  - total del DEBE = total del HABER (comparado en centavos, sin errores de redondeo);
 *  - fecha dentro del periodo, y el periodo debe estar abierto.
 */
class PartidaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Periodo de la partida: el suyo al editar, el activo al crear */
    public function periodo(): ?Periodo
    {
        return $this->route('partida')?->periodo ?? Periodo::activo();
    }

    /** Quita las filas vacias que deja el formulario */
    protected function prepareForValidation(): void
    {
        $lineas = collect($this->input('lineas', []))
            ->filter(fn ($l) => filled($l['cuenta_id'] ?? null) || filled($l['debe'] ?? null) || filled($l['haber'] ?? null))
            ->values()
            ->all();

        $this->merge(['lineas' => $lineas]);
    }

    public function rules(): array
    {
        return [
            'fecha' => ['required', 'date'],
            'tipo' => ['required', Rule::enum(TipoPartida::class)],
            'concepto' => ['required', 'string', 'max:500'],
            'lineas' => ['required', 'array', 'min:2'],
            'lineas.*.cuenta_id' => ['required', 'integer', 'exists:cuentas,id'],
            'lineas.*.concepto' => ['nullable', 'string', 'max:255'],
            'lineas.*.debe' => ['nullable', 'numeric', 'min:0', 'decimal:0,2', 'max:9999999999999'],
            'lineas.*.haber' => ['nullable', 'numeric', 'min:0', 'decimal:0,2', 'max:9999999999999'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $periodo = $this->periodo();

                if (! $periodo) {
                    $validator->errors()->add('fecha', 'No hay un periodo contable activo.');

                    return;
                }
                if (! $periodo->estaAbierto()) {
                    $validator->errors()->add('fecha', "El periodo \"{$periodo->nombre}\" está cerrado.");

                    return;
                }
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                // Fecha dentro del periodo
                $fecha = $this->date('fecha');
                if ($fecha->lt($periodo->fecha_inicio) || $fecha->gt($periodo->fecha_fin)) {
                    $validator->errors()->add('fecha', "La fecha debe estar dentro del periodo ({$periodo->rango()}).");
                }

                // Solo una partida de apertura por periodo
                if ($this->input('tipo') === TipoPartida::Apertura->value) {
                    $otra = Partida::where('periodo_id', $periodo->id)
                        ->where('tipo', TipoPartida::Apertura)
                        ->when($this->route('partida'), fn ($q, $p) => $q->whereKeyNot($p->id))
                        ->exists();
                    if ($otra) {
                        $validator->errors()->add('tipo', 'Este periodo ya tiene su partida de apertura.');
                    }
                }

                // Cuentas: deben existir, ser de detalle y estar activas
                $cuentas = Cuenta::withCount('hijas')
                    ->whereIn('id', collect($this->input('lineas'))->pluck('cuenta_id'))
                    ->get()->keyBy('id');

                $totalDebe = 0;
                $totalHaber = 0;

                foreach ($this->input('lineas') as $i => $linea) {
                    $n = $i + 1;
                    $cuenta = $cuentas[$linea['cuenta_id']] ?? null;

                    if ($cuenta && $cuenta->hijas_count > 0) {
                        $validator->errors()->add("lineas.$i.cuenta_id", "Línea $n: {$cuenta->etiqueta()} es una cuenta de grupo; usa una de sus subcuentas.");
                    } elseif ($cuenta && ! $cuenta->activa) {
                        $validator->errors()->add("lineas.$i.cuenta_id", "Línea $n: la cuenta {$cuenta->etiqueta()} está desactivada.");
                    }

                    $debe = self::centavos($linea['debe'] ?? null);
                    $haber = self::centavos($linea['haber'] ?? null);

                    if ($debe > 0 && $haber > 0) {
                        $validator->errors()->add("lineas.$i.debe", "Línea $n: lleva monto en el debe y en el haber; debe ir solo en uno.");
                    } elseif ($debe === 0 && $haber === 0) {
                        $validator->errors()->add("lineas.$i.debe", "Línea $n: falta el monto (debe o haber).");
                    }

                    $totalDebe += $debe;
                    $totalHaber += $haber;
                }

                if ($validator->errors()->isEmpty() && $totalDebe !== $totalHaber) {
                    $validator->errors()->add('lineas', sprintf(
                        'La partida no cuadra: debe $%s y haber $%s (diferencia $%s).',
                        number_format($totalDebe / 100, 2),
                        number_format($totalHaber / 100, 2),
                        number_format(abs($totalDebe - $totalHaber) / 100, 2),
                    ));
                }
            },
        ];
    }

    /** "1250.5" -> 125050. Trabajar en centavos (enteros) evita errores de redondeo. */
    public static function centavos(mixed $valor): int
    {
        return filled($valor) && is_numeric($valor) ? (int) round(((float) $valor) * 100) : 0;
    }

    /** Lineas listas para guardar */
    public function lineasValidadas(): array
    {
        return collect($this->validated('lineas'))->map(fn ($l) => [
            'cuenta_id' => (int) $l['cuenta_id'],
            'concepto' => $l['concepto'] ?? null,
            'debe' => number_format(self::centavos($l['debe'] ?? null) / 100, 2, '.', ''),
            'haber' => number_format(self::centavos($l['haber'] ?? null) / 100, 2, '.', ''),
        ])->all();
    }

    public function attributes(): array
    {
        return [
            'lineas' => 'líneas',
            'lineas.*.cuenta_id' => 'cuenta',
            'lineas.*.debe' => 'debe',
            'lineas.*.haber' => 'haber',
        ];
    }

    public function messages(): array
    {
        return [
            'lineas.required' => 'La partida necesita al menos 2 líneas.',
            'lineas.min' => 'La partida necesita al menos 2 líneas (partida doble).',
            'lineas.*.cuenta_id.required' => 'Elige la cuenta en todas las líneas que tienen monto.',
        ];
    }
}