<?php

namespace App\Http\Requests\Economia;

use App\Models\Decision;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AlternativaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'provisional' => $this->boolean('provisional')
        ]);
    }

    public function rules(): array
    {
        return [
            'nombre' => [
                'required',
                'string',
                'max:150',
                Rule::unique('alternativas')
                    ->where('decision_id', Decision::actual()?->id)
                    ->ignore($this->route('alternativa')?->id)
            ],

            'descripcion' => [
                'nullable',
                'string',
                'max:3000'
            ],

            'inversion' => [
                'required',
                'numeric',
                'gt:0',
                'max:9999999999999',
                'decimal:0,2'
            ],

            'vida_util' => [
                'required',
                'integer',
                'min:1',
                'max:100'
            ],

            'valor_salvamento' => [
                'required',
                'numeric',
                'min:0',
                'lt:inversion',
                'decimal:0,2'
            ],

            'beneficio_anual' => [
                'required',
                'numeric',
                'min:0',
                'max:9999999999999',
                'decimal:0,2'
            ],

            'costeo_id' => [
                'nullable',
                'integer',
                'exists:costeos,id'
            ],

            'costo_operacion' => [
                'nullable',
                'required_without:costeo_id',
                'numeric',
                'min:0',
                'max:9999999999999',
                'decimal:0,2'
            ],

            'provisional' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'costo_operacion.required_without' =>
                'Escribe el costo de operación o elige el costeo de donde sale.',

            'valor_salvamento.lt' =>
                'El valor de salvamento debe ser menor que la inversión.',
        ];
    }

    public function attributes(): array
    {
        return [
            'inversion' => 'inversión inicial',
            'vida_util' => 'vida útil',
            'valor_salvamento' => 'valor de salvamento',
            'beneficio_anual' => 'beneficio anual',
            'costeo_id' => 'costeo',
            'costo_operacion' => 'costo de operación',
        ];
    }
}
