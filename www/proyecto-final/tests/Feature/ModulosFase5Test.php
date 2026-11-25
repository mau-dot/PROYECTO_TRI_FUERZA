<?php

namespace Tests\Feature;

use App\Models\Costeo;
use App\Models\CosteoElemento;
use App\Models\Cuenta;
use App\Models\Evidencia;
use App\Models\User;
use App\Services\Contabilidad\CalculoCosteo;
use App\Support\CatalogoBaseComercial;
use App\Support\CosteosProvisionales;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Valores esperados calculados A MANO con el ejemplo provisional (estantes):
 *   A: 208 h × 1.70 = 353.60 + 40 + 12 × 5 = 60 + 12 × 3 = 36  -> total 489.60 ; ÷ 12 m = 40.80
 *   B: 260 h × 1.70 = 442.00 + 120 + 60 + 36 × 3 = 108         -> total 730.00 ; ÷ 10 m = 73.00
 *   C: 353.60 + 90 + 60 + 24 × 3 = 72                          -> total 575.60 ; ÷ 12 m = 47.9666… = 47.97
 */
class ModulosFase5Test extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        CatalogoBaseComercial::cargar();
    }

    private function costeo(string $letra): Costeo
    {
        return Costeo::where('nombre', 'like', "Alternativa $letra:%")->firstOrFail();
    }

    public function test_sin_sesion_pide_login(): void
    {
        $this->get(route('contabilidad.costeos.index'))->assertRedirect(route('login'));
    }

    public function test_el_monto_se_redondea_al_centavo(): void
    {
        $this->assertSame(35360, CalculoCosteo::monto('208.00', '1.70'));
        $this->assertSame(1, CalculoCosteo::monto('0.5', '0.01'));     // 0.005 -> 0.01
        $this->assertSame(4797, CalculoCosteo::dividir(57560 * 100, 1200));
    }

    public function test_el_boton_carga_el_ejemplo_provisional_una_sola_vez(): void
    {
        $this->actingAs($this->user)->post(route('contabilidad.costeos.cargar-ejemplo'))
            ->assertRedirect(route('contabilidad.costeos.index'));

        $this->assertSame(3, Costeo::count());
        $this->assertSame(12, CosteoElemento::count());
        $this->assertSame(3, Costeo::where('provisional', true)->count());
        $this->assertSame(12, CosteoElemento::where('estimado', true)->count());
        // la mano de obra queda enlazada a su cuenta del catálogo
        $this->assertSame('420201', $this->costeo('A')->elementos->firstWhere('elemento.value', 'mod')->cuenta->codigo);

        $this->assertSame(0, CosteosProvisionales::cargar());
        $this->assertSame(3, Costeo::count());
    }

    public function test_resumen_calculado_a_mano(): void
    {
        CosteosProvisionales::cargar();

        $a = $this->costeo('A')->resumen();
        $this->assertSame(48960, $a['total']);
        $this->assertSame(45360, $a['fijo']);
        $this->assertSame(3600, $a['variable']);
        $this->assertSame(4080, $a['unitario']);
        $this->assertSame(300, $a['variable_unitario']);
        $this->assertSame(35360, $a['por_elemento']['mod']);
        $this->assertSame(10000, $a['por_elemento']['cif']);

        $this->assertSame(73000, $this->costeo('B')->resumen()['total']);
        $this->assertSame(7300, $this->costeo('B')->resumen()['unitario']);
        $this->assertSame(57560, $this->costeo('C')->resumen()['total']);
        $this->assertSame(4797, $this->costeo('C')->resumen()['unitario']);
    }

    public function test_crear_un_costeo_y_validaciones(): void
    {
        $this->actingAs($this->user);

        $this->post(route('contabilidad.costeos.store'), ['nombre' => '', 'sistema' => 'inventado', 'unidad' => '', 'volumen_anual' => 0])
            ->assertSessionHasErrors(['nombre', 'sistema', 'unidad', 'volumen_anual']);

        $this->post(route('contabilidad.costeos.store'), [
            'nombre' => 'Alternativa X', 'sistema' => 'estandar', 'unidad' => 'unidad vendida', 'volumen_anual' => '1000',
        ])->assertRedirect(route('contabilidad.costeos.show', Costeo::first()));

        $this->assertFalse(Costeo::first()->provisional);   // casilla sin marcar = false

        // nombre repetido
        $this->post(route('contabilidad.costeos.store'), ['nombre' => 'Alternativa X', 'sistema' => 'estandar', 'unidad' => 'u', 'volumen_anual' => 1])
            ->assertSessionHasErrors('nombre');
    }

    public function test_agregar_editar_y_eliminar_un_costo(): void
    {
        $this->actingAs($this->user);
        $costeo = Costeo::create(['nombre' => 'Alt', 'sistema' => 'estandar', 'unidad' => 'mes', 'volumen_anual' => 12]);

        $this->post(route('contabilidad.costeos.elementos.store', $costeo), [
            'elemento' => 'cif', 'comportamiento' => 'variable', 'concepto' => 'Energía', 'cantidad' => '0', 'costo_unitario' => '-1', 'cuenta_id' => 99999,
        ])->assertSessionHasErrors(['cantidad', 'costo_unitario', 'cuenta_id']);

        $this->post(route('contabilidad.costeos.elementos.store', $costeo), [
            'elemento' => 'cif', 'comportamiento' => 'variable', 'concepto' => 'Energía', 'cantidad' => '120', 'unidad_medida' => 'kWh',
            'costo_unitario' => '0.25', 'cuenta_id' => Cuenta::where('codigo', '420203')->value('id'), 'estimado' => '1',
        ])->assertRedirect(route('contabilidad.costeos.show', $costeo));

        $e = CosteoElemento::first();
        $this->assertSame(3000, $e->monto());
        $this->assertTrue($e->estimado);

        $this->put(route('contabilidad.costeos.elementos.update', $e), [
            'elemento' => 'cif', 'comportamiento' => 'variable', 'concepto' => 'Energía', 'cantidad' => '120', 'costo_unitario' => '0.30',
        ])->assertRedirect(route('contabilidad.costeos.show', $costeo));
        $this->assertSame(3600, $e->fresh()->monto());
        $this->assertFalse($e->fresh()->estimado);           // ya confirmado
        $this->assertSame(300, $costeo->fresh()->resumen()['unitario']);   // 36.00 ÷ 12

        $this->delete(route('contabilidad.costeos.elementos.destroy', $e))->assertRedirect(route('contabilidad.costeos.show', $costeo));
        $this->assertSame(0, CosteoElemento::count());
    }

    public function test_evidencias_por_costo_y_borrado(): void
    {
        $this->actingAs($this->user);
        CosteosProvisionales::cargar();
        $a = $this->costeo('A');
        $elemento = $a->elementos->first();
        $evidencia = Evidencia::create(['tipo' => 'cotizacion', 'titulo' => 'Cotización mantenimiento', 'fecha' => '2026-11-20', 'fuente' => 'Taller']);

        $this->post(route('evidencias.vincular'), ['evidencia_id' => $evidencia->id, 'tipo' => 'costeo_elemento', 'id' => $elemento->id, 'campo' => 'costo_unitario'])
            ->assertSessionHasNoErrors();
        $this->assertSame(1, $elemento->numFuentes('costo_unitario'));

        // el vinculo lleva a la pantalla del costo
        $this->get(route('evidencias.show', $evidencia))->assertOk()->assertSee(route('contabilidad.costeos.elementos.edit', $elemento), false);

        // al borrar el costeo se van sus costos y los vinculos, pero la evidencia se queda
        $this->delete(route('contabilidad.costeos.destroy', $a))->assertRedirect(route('contabilidad.costeos.index'));
        $this->assertSame(8, CosteoElemento::count());
        $this->assertSame(0, \DB::table('evidenciables')->count());
        $this->assertTrue(Evidencia::whereKey($evidencia->id)->exists());
    }

    public function test_las_pantallas_cargan(): void
    {
        $this->actingAs($this->user);

        $this->get(route('contabilidad.costeos.index'))->assertOk()->assertSee('Cargar ejemplo provisional');

        CosteosProvisionales::cargar();
        $a = $this->costeo('A');

        $this->get(route('contabilidad.costeos.index'))->assertOk()
            ->assertSee('Provisional')->assertSee('489.60')->assertSee('730.00')->assertSee('47.97')->assertSee('Menor');
        $this->get(route('contabilidad.costeos.show', $a))->assertOk()->assertSee('40.80')->assertSee('353.60')->assertSee('Evidencias de la alternativa');
        $this->get(route('contabilidad.costeos.create'))->assertOk();
        $this->get(route('contabilidad.costeos.edit', $a))->assertOk();
        $this->get(route('contabilidad.costeos.elementos.edit', $a->elementos->first()))->assertOk()->assertSee('Evidencias de este costo');
        $this->get(route('contabilidad.dashboard'))->assertOk();
    }
}