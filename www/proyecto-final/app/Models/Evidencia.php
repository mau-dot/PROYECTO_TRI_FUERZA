<?php

namespace App\Models;

use App\Enums\TipoEvidencia;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable(['tipo', 'titulo', 'descripcion', 'fecha', 'fuente', 'cargo'])]
class Evidencia extends Model
{
    protected $table = 'evidencias';

    protected function casts(): array
    {
        return [
            'tipo' => TipoEvidencia::class,
            'fecha' => 'date',
        ];
    }

    /** Los datos que respalda esta evidencia */
    public function vinculos(): HasMany
    {
        return $this->hasMany(Vinculo::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tieneArchivo(): bool
    {
        return $this->archivo_ruta && Storage::disk('local')->exists($this->archivo_ruta);
    }

    /** Imágenes y PDF se pueden ver en el navegador; lo demás se descarga */
    public function seVeEnNavegador(): bool
    {
        return str_starts_with((string) $this->archivo_mime, 'image/') || $this->archivo_mime === 'application/pdf';
    }

    public function esImagen(): bool
    {
        return str_starts_with((string) $this->archivo_mime, 'image/');
    }

    /** "1.2 MB" */
    public function tamanoLegible(): string
    {
        $bytes = (int) $this->archivo_tamano;

        return $bytes >= 1048576 ? round($bytes / 1048576, 1).' MB' : max(1, round($bytes / 1024)).' KB';
    }

    /** Borra el archivo del disco (al eliminar la evidencia o al reemplazar el archivo) */
    public function borrarArchivo(): void
    {
        if ($this->archivo_ruta) {
            Storage::disk('local')->delete($this->archivo_ruta);
        }
    }
}