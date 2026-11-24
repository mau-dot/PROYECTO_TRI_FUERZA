<?php

namespace App\Models;

use App\Models\Concerns\TieneEvidencias;

use App\Enums\ClasificacionEmpresa;
use App\Enums\TamanoEmpresa;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'nombre_comercial', 'nit', 'nrc', 'giro', 'clasificacion', 'tamano', 'direccion', 'telefono', 'tasa_iva'])]
class Empresa extends Model
{
    use TieneEvidencias;
    
    protected function casts(): array
    {
        return [
            'clasificacion' => ClasificacionEmpresa::class,
            'tamano' => TamanoEmpresa::class,
            'tasa_iva' => 'decimal:2',
        ];
    }

    /** La empresa del caso (hay una sola). Devuelve null si aun no se registra. */
    public static function actual(): ?self
    {
        return static::query()->oldest('id')->first();
    }

    public function periodos(): HasMany
    {
        return $this->hasMany(Periodo::class);
    }
}