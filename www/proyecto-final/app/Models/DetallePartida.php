<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['cuenta_id', 'concepto', 'debe', 'haber'])]
class DetallePartida extends Model
{
    protected $attributes = [
        'debe' => 0,
        'haber' => 0,
    ];

    protected function casts(): array
    {
        return [
            'debe' => 'decimal:2',
            'haber' => 'decimal:2',
        ];
    }

    public function partida(): BelongsTo
    {
        return $this->belongsTo(Partida::class);
    }

    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(Cuenta::class);
    }
}