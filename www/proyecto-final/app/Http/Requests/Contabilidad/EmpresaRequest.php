<?php

namespace App\Http\Requests\Contabilidad;

use App\Enums\ClasificacionEmpresa;
use App\Enums\TamanoEmpresa;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmpresaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // la ruta ya exige sesion (middleware auth)
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:150'],
            'nombre_comercial' => ['nullable', 'string', 'max:150'],
            'nit' => ['nullable', 'string', 'max:20'],
            'nrc' => ['nullable', 'string', 'max:20'],
            'giro' => ['required', 'string', 'max:255'],
            'clasificacion' => ['required', Rule::enum(ClasificacionEmpresa::class)],
            'tamano' => ['required', Rule::enum(TamanoEmpresa::class)],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'tasa_iva' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre' => 'razón social',
            'nombre_comercial' => 'nombre comercial',
            'nrc' => 'NRC',
            'nit' => 'NIT',
            'giro' => 'giro o actividad económica',
            'clasificacion' => 'clasificación',
            'tamano' => 'tamaño',
            'tasa_iva' => 'tasa de IVA',
        ];
    }
}