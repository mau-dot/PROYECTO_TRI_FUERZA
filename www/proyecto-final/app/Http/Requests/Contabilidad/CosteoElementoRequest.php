<?php

namespace App\Http\Requests\Contabilidad;

use App\Enums\Comportamiento;
use App\Enums\ElementoCosto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CosteoElementoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['estimado' => $this->boolean('estimado')]);
    }

    public function rules(): array
    {
        return [
            'elemento' => ['required', Rule::enum(ElementoCosto::class)],
            'comportamiento' => ['required', Rule::enum(Comportamiento::class)],
            'concepto' => ['required', 'string', 'max:150'],
            'cantidad' => ['required', 'numeric', 'gt:0', 'max:9999999999', 'decimal:0,2'],
            'unidad_medida' => ['nullable', 'string', 'max:30'],
            'costo_unitario' => ['required', 'numeric', 'min:0', 'max:9999999999999', 'decimal:0,2'],
            'cuenta_id' => ['nullable', 'integer', 'exists:cuentas,id'],
            'estimado' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return ['costo_unitario' => 'costo unitario', 'cuenta_id' => 'cuenta', 'unidad_medida' => 'unidad de medida'];
    }
}