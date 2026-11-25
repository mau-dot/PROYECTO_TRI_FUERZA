<?php

namespace App\Http\Requests\Contabilidad;

use App\Enums\SistemaCosteo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CosteoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Las casillas sin marcar no se envian: se convierten en false */
    protected function prepareForValidation(): void
    {
        $this->merge(['provisional' => $this->boolean('provisional')]);
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:150', Rule::unique('costeos')->ignore($this->route('costeo')?->id)],
            'descripcion' => ['nullable', 'string', 'max:2000'],
            'sistema' => ['required', Rule::enum(SistemaCosteo::class)],
            'unidad' => ['required', 'string', 'max:60'],
            'volumen_anual' => ['required', 'numeric', 'gt:0', 'max:9999999999'],
            'provisional' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return ['volumen_anual' => 'volumen anual', 'unidad' => 'unidad de costeo'];
    }
}