<?php

namespace Tests\Feature;

use App\Models\Cuenta;
use App\Models\Empresa;
use App\Models\Evidencia;
use App\Models\Partida;
use App\Models\Periodo;
use App\Models\User;
use App\Support\CatalogoBaseComercial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ModulosFase3Test extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Empresa $empresa;

    private Partida $partida;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local'); // los archivos de prueba no tocan storage/ de verdad

        $this->user = User::factory()->create();
        $this->empresa = Empresa::create([
            'nombre' => 'Tienda de Prueba', 'giro' => 'Venta al por menor',
            'clasificacion' => 'comercial', 'tamano' => 'micro', 'tasa_iva' => 13,
        ]);
        $periodo = $this->empresa->periodos()->create(['nombre' => 'P1', 'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-06-30']);
        CatalogoBaseComercial::cargar();

        $this->partida = new Partida(['fecha' => '2026-01-15', 'tipo' => 'diario', 'concepto' => 'Venta al contado']);
        $this->partida->periodo_id = $periodo->id;
        $this->partida->numero = 1;
        $this->partida->save();
        $this->partida->detalles()->createMany([
            ['cuenta_id' => Cuenta::where('codigo', '110101')->value('id'), 'debe' => 113],
            ['cuenta_id' => Cuenta::where('codigo', '5101')->value('id'), 'haber' => 100],
            ['cuenta_id' => Cuenta::where('codigo', '2104')->value('id'), 'haber' => 13],
        ]);
    }

    private function subir(array $cambios = [])
    {
        return $this->actingAs($this->user)->post('/evidencias', array_merge([
            'tipo' => 'factura',
            'titulo' => 'Factura 001',
            'fuente' => 'Distribuidora El Sol',
            'fecha' => '2026-01-15',
            'archivo' => UploadedFile::fake()->create('factura-001.pdf', 120, 'application/pdf'),
        ], $cambios));
    }

    public function test_sin_sesion_no_se_ven_las_evidencias(): void
    {
        $this->get('/evidencias')->assertRedirect('/login');
    }

    public function test_subir_una_factura_guarda_el_archivo(): void
    {
        $this->subir()->assertSessionHasNoErrors();

        $e = Evidencia::first();
        $this->assertSame('Factura 001', $e->titulo);
        $this->assertSame('factura-001.pdf', $e->archivo_nombre);
        $this->assertSame($this->user->id, $e->user_id);
        Storage::disk('local')->assertExists($e->archivo_ruta);
    }

    public function test_el_archivo_es_obligatorio_salvo_en_entrevistas(): void
    {
        $this->subir(['archivo' => null])->assertSessionHasErrors('archivo');

        // entrevista: sin archivo, pero con nombre, cargo, fecha y resumen
        $this->subir(['tipo' => 'entrevista', 'archivo' => null, 'fuente' => '', 'cargo' => ''])
            ->assertSessionHasErrors(['fuente', 'cargo', 'descripcion']);

        $this->subir([
            'tipo' => 'entrevista', 'archivo' => null, 'titulo' => 'Entrevista con el propietario',
            'fuente' => 'Juan Pérez', 'cargo' => 'Propietario', 'descripcion' => 'Confirma ventas mensuales de $4,000.',
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, Evidencia::count());
    }

    public function test_archivos_no_permitidos_se_rechazan(): void
    {
        $this->subir(['archivo' => UploadedFile::fake()->create('virus.exe', 10)])->assertSessionHasErrors('archivo');
        $this->subir(['archivo' => UploadedFile::fake()->create('enorme.pdf', 20000, 'application/pdf')])->assertSessionHasErrors('archivo');
        $this->assertSame(0, Evidencia::count());
    }

    public function test_subir_desde_una_partida_la_deja_vinculada(): void
    {
        $this->subir(['vincular' => "partida:{$this->partida->id}:"])
            ->assertRedirect(route('contabilidad.partidas.show', $this->partida));

        $this->assertSame(1, $this->partida->fresh()->numFuentes());
        $this->assertDatabaseHas('evidenciables', ['evidenciable_type' => 'partida', 'evidenciable_id' => $this->partida->id]);
    }

    public function test_vincular_y_desvincular_una_evidencia_existente(): void
    {
        $this->subir();
        $this->subir(['titulo' => 'Cotización', 'tipo' => 'cotizacion']);
        [$a, $b] = Evidencia::orderBy('id')->get();

        foreach ([$a, $b] as $e) {
            $this->actingAs($this->user)->post('/evidencias/vincular', ['evidencia_id' => $e->id, 'tipo' => 'partida', 'id' => $this->partida->id])
                ->assertSessionHasNoErrors();
        }
        $this->assertSame(2, $this->partida->fresh()->numFuentes()); // ya cumple el mínimo

        // vincular otra vez la misma no duplica
        $this->actingAs($this->user)->post('/evidencias/vincular', ['evidencia_id' => $a->id, 'tipo' => 'partida', 'id' => $this->partida->id]);
        $this->assertSame(2, $this->partida->fresh()->numFuentes());

        // desvincular quita el vínculo pero la evidencia sigue
        $vinculo = $a->vinculos()->first();
        $this->actingAs($this->user)->delete("/evidencias/vinculos/{$vinculo->id}");
        $this->assertSame(1, $this->partida->fresh()->numFuentes());
        $this->assertNotNull($a->fresh());
    }

    public function test_vincular_a_un_dato_que_no_existe_o_no_permitido(): void
    {
        $this->subir();
        $e = Evidencia::first();

        $this->actingAs($this->user)->post('/evidencias/vincular', ['evidencia_id' => $e->id, 'tipo' => 'partida', 'id' => 999])
            ->assertSessionHasErrors('id');
        $this->actingAs($this->user)->post('/evidencias/vincular', ['evidencia_id' => $e->id, 'tipo' => 'user', 'id' => $this->user->id])
            ->assertSessionHasErrors('tipo');
        $this->actingAs($this->user)->post('/evidencias/vincular', ['evidencia_id' => $e->id, 'tipo' => 'partida', 'id' => $this->partida->id, 'campo' => 'inventado'])
            ->assertSessionHasErrors('campo');
    }

    public function test_cada_dato_de_la_empresa_cuenta_sus_fuentes_por_separado(): void
    {
        $this->subir(['tipo' => 'autorizacion', 'titulo' => 'Carta firmada', 'vincular' => "empresa:{$this->empresa->id}:autorizacion"]);

        $empresa = $this->empresa->fresh();
        $this->assertSame(1, $empresa->numFuentes('autorizacion'));
        $this->assertSame(0, $empresa->numFuentes('estados_financieros'));
    }

    public function test_ver_y_descargar_el_archivo(): void
    {
        $this->subir();
        $e = Evidencia::first();

        $this->actingAs($this->user)->get("/evidencias/{$e->id}/archivo")->assertOk();
        $this->actingAs($this->user)->get("/evidencias/{$e->id}/archivo?descargar=1")
            ->assertOk()->assertDownload('factura-001.pdf');
    }

    public function test_reemplazar_el_archivo_borra_el_anterior(): void
    {
        $this->subir();
        $e = Evidencia::first();
        $viejo = $e->archivo_ruta;

        $this->actingAs($this->user)->put("/evidencias/{$e->id}", [
            'tipo' => 'factura', 'titulo' => 'Factura 001 (corregida)',
            'archivo' => UploadedFile::fake()->image('foto-factura.jpg'),
        ])->assertSessionHasNoErrors();

        $e->refresh();
        Storage::disk('local')->assertMissing($viejo);
        Storage::disk('local')->assertExists($e->archivo_ruta);
        $this->assertSame('Factura 001 (corregida)', $e->titulo);
    }

    public function test_eliminar_la_evidencia_borra_archivo_y_vinculos(): void
    {
        $this->subir(['vincular' => "partida:{$this->partida->id}:"]);
        $e = Evidencia::first();

        $this->actingAs($this->user)->delete("/evidencias/{$e->id}")->assertRedirect(route('evidencias.index'));

        Storage::disk('local')->assertMissing($e->archivo_ruta);
        $this->assertDatabaseCount('evidenciables', 0);
        $this->assertSame(0, $this->partida->fresh()->numFuentes());
    }

    public function test_eliminar_la_partida_quita_sus_vinculos_pero_no_la_evidencia(): void
    {
        $this->subir(['vincular' => "partida:{$this->partida->id}:"]);

        $this->actingAs($this->user)->delete("/contabilidad/partidas/{$this->partida->id}");

        $this->assertDatabaseCount('evidenciables', 0);
        $this->assertSame(1, Evidencia::count());
    }

    public function test_las_pantallas_cargan_y_muestran_la_trazabilidad(): void
    {
        $this->subir(['vincular' => "partida:{$this->partida->id}:"]);
        $e = Evidencia::first();

        $this->actingAs($this->user)->get('/evidencias')->assertOk()->assertSee('Factura 001')->assertSee('Con menos de 2 fuentes');
        $this->actingAs($this->user)->get('/evidencias/create')->assertOk();
        $this->actingAs($this->user)->get("/evidencias/create?vincular=partida:{$this->partida->id}:")->assertOk()->assertSee('Partida #1');
        $this->actingAs($this->user)->get("/evidencias/{$e->id}")->assertOk()->assertSee('Partida #1');
        $this->actingAs($this->user)->get("/evidencias/{$e->id}/edit")->assertOk();
        $this->actingAs($this->user)->get("/contabilidad/partidas/{$this->partida->id}")->assertOk()->assertSee('Evidencias de esta partida')->assertSee('1 de 2');
        $this->actingAs($this->user)->get('/contabilidad/partidas')->assertOk()->assertSee('1 de 2');
        $this->actingAs($this->user)->get('/contabilidad/empresa')->assertOk()->assertSee('Carta de autorización');
    }
}