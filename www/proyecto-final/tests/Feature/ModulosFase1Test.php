<?php

namespace Tests\Feature;

use App\Enums\EstadoPeriodo;
use App\Models\Empresa;
use App\Models\Periodo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModulosFase1Test extends TestCase
{
    use RefreshDatabase;

    private function empresa(): Empresa
    {
        return Empresa::create([
            'nombre' => 'Tienda de Prueba, S.A. de C.V.',
            'giro' => 'Venta al por menor',
            'clasificacion' => 'comercial',
            'tamano' => 'micro',
            'tasa_iva' => 13,
        ]);
    }

    private function datosEmpresa(array $cambios = []): array
    {
        return array_merge([
            'nombre' => 'Tienda La Esperanza',
            'giro' => 'Venta al por menor de productos de consumo',
            'clasificacion' => 'comercial',
            'tamano' => 'micro',
            'tasa_iva' => 13,
        ], $cambios);
    }

    public function test_sin_sesion_los_modulos_mandan_al_login(): void
    {
        foreach (['/contabilidad', '/economia', '/contabilidad/empresa', '/contabilidad/periodos'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }
    }

    public function test_los_dashboards_cargan_con_su_sidebar(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/contabilidad')
            ->assertOk()
            ->assertSee('Dashboard de Contabilidad')
            ->assertSee('Libro diario')          // opción futura del menú
            ->assertSee('Pronto')                // marcada como pendiente
            ->assertSee('Registrar empresa');    // aviso: aún no hay empresa

        $this->actingAs($user)->get('/economia')
            ->assertOk()
            ->assertSee('Dashboard de Economía')
            ->assertSee('VPN · TIR · CAUE · B/C');
    }

    public function test_el_inicio_enlaza_a_los_dos_modulos(): void
    {
        $this->actingAs(User::factory()->create())->get('/')
            ->assertOk()
            ->assertSee(route('contabilidad.dashboard'))
            ->assertSee(route('economia.dashboard'));
    }

    public function test_registrar_la_empresa_y_luego_editarla_sin_duplicarla(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/contabilidad/empresa', $this->datosEmpresa())
            ->assertRedirect(route('contabilidad.periodos.create'));

        $this->actingAs($user)->put('/contabilidad/empresa', $this->datosEmpresa(['nombre' => 'Tienda La Esperanza 2']))
            ->assertRedirect(route('contabilidad.empresa.edit'));

        $this->assertSame(1, Empresa::count());
        $this->assertSame('Tienda La Esperanza 2', Empresa::actual()->nombre);
    }

    public function test_la_empresa_valida_la_clasificacion(): void
    {
        $this->actingAs(User::factory()->create())
            ->put('/contabilidad/empresa', $this->datosEmpresa(['clasificacion' => 'inventada']))
            ->assertSessionHasErrors('clasificacion');

        $this->assertSame(0, Empresa::count());
    }

    public function test_no_se_crea_periodo_sin_empresa(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/contabilidad/periodos', ['nombre' => 'P1', 'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-06-30'])
            ->assertRedirect(route('contabilidad.empresa.edit'));

        $this->assertSame(0, Periodo::count());
    }

    public function test_crear_periodo_lo_deja_activo(): void
    {
        $this->empresa();

        $this->actingAs(User::factory()->create())
            ->post('/contabilidad/periodos', ['nombre' => 'Enero - Junio 2026', 'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-06-30'])
            ->assertRedirect(route('contabilidad.periodos.index'));

        $periodo = Periodo::first();
        $this->assertSame(EstadoPeriodo::Abierto, $periodo->estado);
        $this->assertSame($periodo->id, session(Periodo::SESION));
    }

    public function test_fechas_invalidas_o_cruzadas_se_rechazan(): void
    {
        $empresa = $this->empresa();
        $empresa->periodos()->create(['nombre' => 'Primer semestre', 'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-06-30']);
        $user = User::factory()->create();

        // fin antes del inicio
        $this->actingAs($user)
            ->post('/contabilidad/periodos', ['nombre' => 'X', 'fecha_inicio' => '2026-08-01', 'fecha_fin' => '2026-07-01'])
            ->assertSessionHasErrors('fecha_fin');

        // se cruza con el primer semestre
        $this->actingAs($user)
            ->post('/contabilidad/periodos', ['nombre' => 'Y', 'fecha_inicio' => '2026-06-01', 'fecha_fin' => '2026-12-31'])
            ->assertSessionHasErrors('fecha_inicio');

        // nombre repetido
        $this->actingAs($user)
            ->post('/contabilidad/periodos', ['nombre' => 'Primer semestre', 'fecha_inicio' => '2027-01-01', 'fecha_fin' => '2027-06-30'])
            ->assertSessionHasErrors('nombre');

        $this->assertSame(1, Periodo::count());
    }

    public function test_editar_un_periodo_no_choca_consigo_mismo(): void
    {
        $periodo = $this->empresa()->periodos()->create(['nombre' => 'P1', 'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-06-30']);

        $this->actingAs(User::factory()->create())
            ->put("/contabilidad/periodos/{$periodo->id}", ['nombre' => 'P1 editado', 'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-07-31'])
            ->assertSessionHasNoErrors();

        $this->assertSame('P1 editado', $periodo->fresh()->nombre);
    }

    public function test_activar_cerrar_y_reabrir(): void
    {
        $empresa = $this->empresa();
        $p1 = $empresa->periodos()->create(['nombre' => 'P1', 'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-06-30']);
        $p2 = $empresa->periodos()->create(['nombre' => 'P2', 'fecha_inicio' => '2026-07-01', 'fecha_fin' => '2026-12-31']);
        $user = User::factory()->create();

        // sin elegir, el activo es el abierto más reciente
        $this->assertTrue(Periodo::activo()->is($p2));

        $this->actingAs($user)->post("/contabilidad/periodos/{$p1->id}/activar");
        $this->assertTrue(Periodo::activo()->is($p1));

        // cerrar: ya no se puede editar ni borrar
        $this->actingAs($user)->patch("/contabilidad/periodos/{$p1->id}/estado");
        $this->assertSame(EstadoPeriodo::Cerrado, $p1->fresh()->estado);

        $this->actingAs($user)->get("/contabilidad/periodos/{$p1->id}/edit")->assertRedirect(route('contabilidad.periodos.index'));
        $this->actingAs($user)->delete("/contabilidad/periodos/{$p1->id}");
        $this->assertNotNull($p1->fresh());

        // reabrir
        $this->actingAs($user)->patch("/contabilidad/periodos/{$p1->id}/estado");
        $this->assertSame(EstadoPeriodo::Abierto, $p1->fresh()->estado);
    }

    public function test_las_pantallas_de_empresa_y_periodos_cargan(): void
    {
        $user = User::factory()->create();
        $this->empresa()->periodos()->create(['nombre' => 'P1', 'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-06-30']);

        $this->actingAs($user)->get('/contabilidad/empresa')->assertOk()->assertSee('Tienda de Prueba');
        $this->actingAs($user)->get('/contabilidad/periodos')->assertOk()->assertSee('P1')->assertSee('Activo');
        $this->actingAs($user)->get('/contabilidad/periodos/create')->assertOk();
    }
}