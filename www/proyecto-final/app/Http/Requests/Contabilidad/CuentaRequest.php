<?php

namespace App\Http\Requests\Contabilidad;

use App\Enums\Naturaleza;
use App\Enums\Rubro;
use App\Enums\TipoCuenta;
use App\Models\Cuenta;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CuentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'regex:/^\d{1,20}$/', Rule::unique('cuentas')->ignore($this->route('cuenta')?->id)],
            'nombre' => ['required', 'string', 'max:150'],
            'tipo' => ['required', Rule::enum(TipoCuenta::class)],
            'naturaleza' => ['required', Rule::enum(Naturaleza::class)],
            'rubro' => ['nullable', Rule::enum(Rubro::class)],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $cuenta = $this->route('cuenta'); // null al crear
                $codigo = $this->input('codigo');

                // Cambiar el codigo de una cuenta que ya tiene subcuentas o movimientos rompería el arbol
                if ($cuenta && $cuenta->codigo !== $codigo
                    && ($cuenta->hijas()->exists() || $cuenta->movimientos()->exists())) {
                    $validator->errors()->add('codigo', 'No se puede cambiar el código: la cuenta ya tiene subcuentas o movimientos.');

                    return;
                }

                // Al crear (o cambiar el codigo), el padre no puede ser una cuenta con movimientos:
                // una cuenta que ya se uso en partidas no puede convertirse en cuenta de grupo
                $cambiaUbicacion = ! $cuenta || $cuenta->codigo !== $codigo;
                $padre = Cuenta::buscarPadre($codigo);

                if ($cambiaUbicacion && $padre && $padre->movimientos()->exists()) {
                    $validator->errors()->add('codigo', "La cuenta {$padre->etiqueta()} ya tiene movimientos en el libro diario, así que no puede tener subcuentas.");
                }
            },
        ];
    }

    public function attributes(): array
    {
        return ['codigo' => 'código'];
    }

    public function messages(): array
    {
        return ['codigo.regex' => 'El código solo puede tener números (por ejemplo 110101).'];
    }
}