<?php

namespace Tests\Feature;

use App\Enums\EstadoPeriodo;
use App\Models\Cuenta;
use App\Models\Empresa;
use App\Models\Partida;
use App\Models\Periodo;
use App\Models\User;
use App\Support\CatalogoBaseComercial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModulosFase2Test extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Periodo $periodo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $empresa = Empresa::create([
            'nombre' => 'Tienda de Prueba', 'giro' => 'Venta al por menor',
            'clasificacion' => 'comercial', 'tamano' => 'micro', 'tasa_iva' => 13,
        ]);
        $this->periodo = $empresa->periodos()->create(['nombre' => 'P1', 'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-06-30']);
        CatalogoBaseComercial::cargar();
    }

    private function id(string $codigo): int
    {
        return Cuenta::where('codigo', $codigo)->value('id');
    }

    /** Venta al contado de $100 + IVA 13 % */
    private function partidaValida(array $cambios = []): array
    {
        return array_merge([
            'fecha' => '2026-01-15',
            'tipo' => 'diario',
            'concepto' => 'Venta al contado según factura 001',
            'lineas' => [
                ['cuenta_id' => $this->id('110101'), 'debe' => '113.00', 'haber' => ''],
                ['cuenta_id' => $this->id('5101'), 'debe' => '', 'haber' => '100.00'],
                ['cuenta_id' => $this->id('2104'), 'debe' => '', 'haber' => '13.00'],
            ],
        ], $cambios);
    }

    // ---------- Catálogo ----------

    public function test_el_catalogo_base_arma_el_arbol(): void
    {
        $caja = Cuenta::where('codigo', '110101')->first();

        $this->assertSame('1101', $caja->padre->codigo);
        $this->assertSame(4, $caja->nivel);
        $this->assertSame('activo', $caja->tipo->value);
        $this->assertSame('deudora', $caja->naturaleza->value);
        $this->assertSame('corriente', $caja->rubro->value);

        // contra cuenta: depreciación acumulada es activo de naturaleza acreedora
        $dep = Cuenta::where('codigo', '120203')->first();
        $this->assertSame('acreedora', $dep->naturaleza->value);
        $this->assertSame('no_corriente', $dep->rubro->value);

        // cargarlo otra vez no duplica nada
        $this->assertSame(0, CatalogoBaseComercial::cargar());
    }

    public function test_el_boton_carga_el_catalogo_base(): void
    {
        Cuenta::query()->orderByDesc('nivel')->get()->each->delete();

        $this->actingAs($this->user)->get('/contabilidad/cuentas')->assertSee('Cargar catálogo base comercial');
        $this->actingAs($this->user)->post('/contabilidad/cuentas/cargar-base')->assertRedirect(route('contabilidad.cuentas.index'));

        $this->assertSame(count(CatalogoBaseComercial::CUENTAS), Cuenta::count());
    }

    public function test_crear_subcuenta_la_ubica_sola_en_el_arbol(): void
    {
        $this->actingAs($this->user)->get('/contabilidad/cuentas/create?padre=1101')
            ->assertOk()->assertSee('110104'); // código sugerido

        $this->actingAs($this->user)->post('/contabilidad/cuentas', [
            'codigo' => '110104', 'nombre' => 'Bancos - cuenta de ahorro', 'tipo' => 'activo', 'naturaleza' => 'deudora', 'rubro' => 'corriente',
        ])->assertSessionHasNoErrors();

        $nueva = Cuenta::where('codigo', '110104')->first();
        $this->assertSame('1101', $nueva->padre->codigo);
        $this->assertSame(4, $nueva->nivel);
    }

    public function test_codigo_repetido_o_con_letras_se_rechaza(): void
    {
        $datos = ['nombre' => 'X', 'tipo' => 'activo', 'naturaleza' => 'deudora'];

        $this->actingAs($this->user)->post('/contabilidad/cuentas', $datos + ['codigo' => '110101'])->assertSessionHasErrors('codigo');
        $this->actingAs($this->user)->post('/contabilidad/cuentas', $datos + ['codigo' => '11A'])->assertSessionHasErrors('codigo');
    }

    public function test_una_cuenta_con_movimientos_no_puede_tener_subcuentas_ni_borrarse(): void
    {
        $this->actingAs($this->user)->post('/contabilidad/partidas', $this->partidaValida());
        $caja = Cuenta::where('codigo', '110101')->first();

        $this->actingAs($this->user)->post('/contabilidad/cuentas', [
            'codigo' => '11010101', 'nombre' => 'Caja sucursal', 'tipo' => 'activo', 'naturaleza' => 'deudora',
        ])->assertSessionHasErrors('codigo');

        $this->actingAs($this->user)->delete("/contabilidad/cuentas/{$caja->id}");
        $this->assertNotNull($caja->fresh());
    }

    // ---------- Libro diario ----------

    public function test_registrar_una_partida_que_cuadra(): void
    {
        $this->actingAs($this->user)->post('/contabilidad/partidas', $this->partidaValida())
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $partida = Partida::with('detalles')->first();
        $this->assertSame(1, $partida->numero);
        $this->assertSame($this->periodo->id, $partida->periodo_id);
        $this->assertSame($this->user->id, $partida->user_id);
        $this->assertCount(3, $partida->detalles);
        $this->assertSame('113.00', $partida->totalDebe());
        $this->assertSame('113.00', $partida->totalHaber());

        // la segunda partida lleva el número 2
        $this->actingAs($this->user)->post('/contabilidad/partidas', $this->partidaValida());
        $this->assertSame(2, Partida::max('numero'));
    }

    public function test_una_partida_que_no_cuadra_se_rechaza(): void
    {
        $datos = $this->partidaValida();
        $datos['lineas'][2]['haber'] = '12.99';

        $this->actingAs($this->user)->post('/contabilidad/partidas', $datos)->assertSessionHasErrors('lineas');
        $this->assertSame(0, Partida::count());
    }

    public function test_decimales_que_con_float_no_cuadrarian(): void
    {
        // 0.10 + 0.20 = 0.30 en centavos; con float daría 0.30000000000000004
        $datos = $this->partidaValida(['lineas' => [
            ['cuenta_id' => $this->id('420203'), 'debe' => '0.10', 'haber' => ''],
            ['cuenta_id' => $this->id('420204'), 'debe' => '0.20', 'haber' => ''],
            ['cuenta_id' => $this->id('110101'), 'debe' => '', 'haber' => '0.30'],
        ]]);

        $this->actingAs($this->user)->post('/contabilidad/partidas', $datos)->assertSessionHasNoErrors();
    }

    public function test_reglas_de_cada_linea(): void
    {
        $base = $this->partidaValida();

        // una sola línea
        $this->actingAs($this->user)->post('/contabilidad/partidas', array_merge($base, ['lineas' => [$base['lineas'][0]]]))
            ->assertSessionHasErrors('lineas');

        // debe y haber en la misma línea
        $d = $base;
        $d['lineas'][0]['haber'] = '5';
        $this->actingAs($this->user)->post('/contabilidad/partidas', $d)->assertSessionHasErrors('lineas.0.debe');

        // monto negativo
        $d = $base;
        $d['lineas'][0]['debe'] = '-113';
        $this->actingAs($this->user)->post('/contabilidad/partidas', $d)->assertSessionHasErrors('lineas.0.debe');

        // cuenta de grupo (1101 tiene subcuentas)
        $d = $base;
        $d['lineas'][0]['cuenta_id'] = $this->id('1101');
        $this->actingAs($this->user)->post('/contabilidad/partidas', $d)->assertSessionHasErrors('lineas.0.cuenta_id');

        // cuenta desactivada
        Cuenta::where('codigo', '110101')->update(['activa' => false]);
        $this->actingAs($this->user)->post('/contabilidad/partidas', $base)->assertSessionHasErrors('lineas.0.cuenta_id');

        $this->assertSame(0, Partida::count());
    }

    public function test_las_filas_vacias_del_formulario_se_ignoran(): void
    {
        $datos = $this->partidaValida();
        $datos['lineas'][] = ['cuenta_id' => '', 'debe' => '', 'haber' => ''];

        $this->actingAs($this->user)->post('/contabilidad/partidas', $datos)->assertSessionHasNoErrors();
        $this->assertSame(3, Partida::first()->detalles()->count());
    }

    public function test_fecha_fuera_del_periodo_o_periodo_cerrado(): void
    {
        $this->actingAs($this->user)->post('/contabilidad/partidas', $this->partidaValida(['fecha' => '2026-07-01']))
            ->assertSessionHasErrors('fecha');

        $this->periodo->estado = EstadoPeriodo::Cerrado;
        $this->periodo->save();

        $this->actingAs($this->user)->post('/contabilidad/partidas', $this->partidaValida())->assertSessionHasErrors('fecha');
        $this->actingAs($this->user)->get('/contabilidad/partidas/create')->assertRedirect(route('contabilidad.partidas.index'));

        $this->assertSame(0, Partida::count());
    }

    public function test_solo_una_partida_de_apertura_por_periodo(): void
    {
        $this->actingAs($this->user)->post('/contabilidad/partidas', $this->partidaValida(['tipo' => 'apertura']))->assertSessionHasNoErrors();
        $this->actingAs($this->user)->post('/contabilidad/partidas', $this->partidaValida(['tipo' => 'apertura']))->assertSessionHasErrors('tipo');
    }

    public function test_editar_y_eliminar_una_partida(): void
    {
        $this->actingAs($this->user)->post('/contabilidad/partidas', $this->partidaValida());
        $partida = Partida::first();

        $datos = $this->partidaValida(['concepto' => 'Corregida', 'lineas' => [
            ['cuenta_id' => $this->id('110103'), 'debe' => '226', 'haber' => ''],
            ['cuenta_id' => $this->id('5101'), 'debe' => '', 'haber' => '200'],
            ['cuenta_id' => $this->id('2104'), 'debe' => '', 'haber' => '26'],
        ]]);
        $this->actingAs($this->user)->put("/contabilidad/partidas/{$partida->id}", $datos)->assertSessionHasNoErrors();

        $partida->refresh();
        $this->assertSame('Corregida', $partida->concepto);
        $this->assertSame('226.00', $partida->totalDebe());
        $this->assertSame(1, $partida->numero); // conserva su número

        $this->actingAs($this->user)->delete("/contabilidad/partidas/{$partida->id}");
        $this->assertSame(0, Partida::count());
        $this->assertDatabaseCount('detalle_partidas', 0);
    }

    public function test_no_se_borra_un_periodo_con_partidas(): void
    {
        $this->actingAs($this->user)->post('/contabilidad/partidas', $this->partidaValida());

        $this->actingAs($this->user)->delete("/contabilidad/periodos/{$this->periodo->id}");
        $this->assertNotNull($this->periodo->fresh());
    }

    public function test_las_pantallas_cargan(): void
    {
        $this->actingAs($this->user)->post('/contabilidad/partidas', $this->partidaValida());
        $this->actingAs($this->user)->post('/contabilidad/partidas', $this->partidaValida(['tipo' => 'ajuste', 'concepto' => 'Ajuste de prueba']));
        $partida = Partida::first();

        $this->actingAs($this->user)->get('/contabilidad/cuentas')->assertOk()->assertSee('Caja general');
        $this->actingAs($this->user)->get('/contabilidad/cuentas/create')->assertOk();
        $this->actingAs($this->user)->get('/contabilidad/partidas')->assertOk()->assertSee('Partida #1')->assertSee('Cuadra');
        $this->actingAs($this->user)->get('/contabilidad/partidas/create')->assertOk()->assertSee('Caja general');
        $this->actingAs($this->user)->get("/contabilidad/partidas/{$partida->id}")->assertOk()->assertSee('113.00');
        $this->actingAs($this->user)->get("/contabilidad/partidas/{$partida->id}/edit")->assertOk();
        $this->actingAs($this->user)->get('/contabilidad/ajustes')->assertOk()->assertSee('Ajuste de prueba')->assertDontSee('Venta al contado');
        $this->actingAs($this->user)->get('/contabilidad')->assertOk()->assertSee('Ver libro diario');
    }
}