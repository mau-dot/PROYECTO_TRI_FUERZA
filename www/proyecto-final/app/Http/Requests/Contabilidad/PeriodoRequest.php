<?php

namespace App\Http\Requests\Contabilidad;

use App\Models\Empresa;
use App\Models\Periodo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PeriodoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $periodo = $this->route('periodo'); // null al crear

        return [
            'nombre' => [
                'required', 'string', 'max:100',
                Rule::unique('periodos')
                    ->where('empresa_id', Empresa::actual()?->id)
                    ->ignore($periodo?->id),
            ],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after:fecha_inicio'],
        ];
    }

    /** Dos periodos no pueden cruzarse en fechas */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $cruce = Periodo::query()
                    ->where('fecha_inicio', '<=', $this->date('fecha_fin'))
                    ->where('fecha_fin', '>=', $this->date('fecha_inicio'))
                    ->when($this->route('periodo'), fn ($q, $p) => $q->whereKeyNot($p->id))
                    ->first();

                if ($cruce) {
                    $validator->errors()->add(
                        'fecha_inicio',
                        "Las fechas se cruzan con el periodo \"{$cruce->nombre}\" ({$cruce->rango()})."
                    );
                }
            },
        ];
    }

    public function attributes(): array
    {
        return [
            'fecha_inicio' => 'fecha de inicio',
            'fecha_fin' => 'fecha de fin',
        ];
    }
}