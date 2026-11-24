<?php

namespace App\Http\Requests\Evidencias;

use App\Enums\TipoEvidencia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EvidenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $creando = ! $this->route('evidencia');
        $entrevista = $this->input('tipo') === TipoEvidencia::Entrevista->value;

        return [
            'tipo' => ['required', Rule::enum(TipoEvidencia::class)],
            'titulo' => ['required', 'string', 'max:150'],
            // documentar la entrevitas con nombre, cargo y fecha
            'fecha' => [$entrevista ? 'required' : 'nullable', 'date'],
            'fuente' => [$entrevista ? 'required' : 'nullable', 'string', 'max:150'],
            'cargo' => [$entrevista ? 'required' : 'nullable', 'string', 'max:100'],
            'descripcion' => [$entrevista ? 'required' : 'nullable', 'string', 'max:5000'],
            // El archivo es obligatorio al crear, salvo en entrevistas (pueden ser solo el resumen escrito)
            'archivo' => [
                $creando && ! $entrevista ? 'required' : 'nullable',
                'file',
                'max:'.config('evidencias.tamano_maximo_kb'),
                'extensions:'.implode(',', config('evidencias.extensiones')),
            ],
            'vincular' => ['nullable', 'string', 'max:100'], // "partida:5:" para enlazarla al guardar
        ];
    }

    public function attributes(): array
    {
        return [
            'titulo' => 'título',
            'descripcion' => 'descripción',
            'fuente' => $this->input('tipo') === TipoEvidencia::Entrevista->value ? 'nombre del entrevistado' : 'fuente',
        ];
    }

    public function messages(): array
    {
        return [
            'archivo.required' => 'Sube el archivo de la evidencia (PDF, imagen, Word o Excel).',
            'archivo.extensions' => 'Tipo de archivo no permitido. Usa: '.implode(', ', config('evidencias.extensiones')).'.',
            'archivo.max' => 'El archivo pesa más de '.(config('evidencias.tamano_maximo_kb') / 1024).' MB.',
            'archivo.uploaded' => 'No se pudo subir el archivo. Puede ser muy grande para la configuración de PHP (upload_max_filesize).',
        ];
    }
}