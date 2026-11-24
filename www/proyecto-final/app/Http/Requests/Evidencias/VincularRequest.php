<?php

namespace App\Http\Requests\Evidencias;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Enlazar una evidencia existente a un dato: "la evidencia 7 respalda el campo X del registro partida 5".
 */
class VincularRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'evidencia_id' => ['required', 'integer', 'exists:evidencias,id'],
            'tipo' => ['required', Rule::in(array_keys(config('evidencias.vinculables')))],
            'id' => ['required', 'integer'],
            'campo' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $campos = config("evidencias.vinculables.{$this->input('tipo')}.campos", []);
                if (! array_key_exists($this->campo(), $campos)) {
                    $validator->errors()->add('campo', 'Ese dato no admite evidencias.');
                }

                if (! $this->registro()) {
                    $validator->errors()->add('id', 'El dato que quieres respaldar no existe.');
                }
            },
        ];
    }

    public function campo(): string
    {
        return (string) $this->input('campo', '');
    }

    /** El registro respaldado (Partida, Empresa, Cuenta...) usando el morphMap */
    public function registro(): ?Model
    {
        $clase = Relation::getMorphedModel($this->input('tipo'));

        return $clase ? $clase::find($this->input('id')) : null;
    }
}