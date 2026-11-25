<?php

namespace App\Models;

use App\Enums\SistemaCosteo;
use App\Models\Concerns\TieneEvidencias;
use App\Services\Contabilidad\CalculoCosteo;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/*el costeo de UNA alternativa de inversion (costos estandar anuales de operarla)*/
#[Fillable(['nombre', 'descripcion', 'sistema', 'unidad', 'volumen_anual', 'provisional'])]
class Costeo extends Model
{
    use TieneEvidencias;

    protected $attributes = [
        'sistema' => 'estandar',
        'provisional' => false,
    ];

    protected function casts(): array
    {
        return [
            'sistema' => SistemaCosteo::class,
            'volumen_anual' => 'decimal:2',
            'provisional' => 'boolean',
        ];
    }

    public function elementos(): HasMany
    {
        return $this->hasMany(CosteoElemento::class)->orderBy('elemento')->orderBy('id');
    }

    /*totales calculados (en centavos). ver CalculoCosteo*/
    public function resumen(): array
    {
        return CalculoCosteo::resumen($this);
    }

}