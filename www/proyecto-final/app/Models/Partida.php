<?php

namespace App\Models;

use App\Enums\TipoPartida;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['fecha', 'tipo', 'concepto'])]
class Partida extends Model
{
    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'tipo' => TipoPartida::class,
        ];
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(Periodo::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePartida::class)->orderBy('id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Siguiente numero correlativo dentro del periodo */
    public static function siguienteNumero(Periodo $periodo): int
    {
        return (int) static::where('periodo_id', $periodo->id)->max('numero') + 1;
    }

    public function totalDebe(): string
    {
        return number_format((float) $this->detalles->sum('debe'), 2, '.', '');
    }

    public function totalHaber(): string
    {
        return number_format((float) $this->detalles->sum('haber'), 2, '.', '');
    }
}