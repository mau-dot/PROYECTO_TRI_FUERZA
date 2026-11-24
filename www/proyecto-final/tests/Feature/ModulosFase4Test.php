<?php

namespace Tests\Feature;

use App\Models\Cuenta;
use App\Models\Empresa;
use App\Models\Partida;
use App\Models\Periodo;
use App\Models\User;
use App\Services\Contabilidad\BalanzaComprobacion;
use App\Services\Contabilidad\EstadosFinancieros;
use App\Services\Contabilidad\HojaTrabajo;
use App\Services\Contabilidad\LibroMayor;
use App\Support\CatalogoBaseComercial;
use App\Support\Dinero;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Datos de prueba calculados A MANO verificacion manuel:
 *   #1 Apertura: Bancos 5,000 + Inventario 3,000 + Mobiliario 1,200 = Capital 9,200
 *   #2 Venta al contado: Caja 113 = Ventas 100 + IVA débito 13
 *   #3 Compra al credito: Inventario 500 + IVA credito 65 = Proveedores 565
 *   #4 Pago de energia: Energia electrica 40 = Bancos 40
 *   #5 Ajuste depreciación: Depreciacio n (adm.) 150 = Dep. acumulada mobiliario 150
 *   #6 Ajuste costo de venta: Costo de venta 300 = Inventario 300
 */
class ModulosFase4Test extends TestCase
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

        $this->partida('apertura', '2026-01-01', 'Balance inicial', ['110103' => 5000, '1104' => 3000, '120103' => 1200], ['3101' => 9200]);
        $this->partida('diario', '2026-01-10', 'Venta al contado', ['110101' => 113], ['5101' => 100, '2104' => 13]);
        $this->partida('diario', '2026-01-15', 'Compra al crédito', ['1104' => 500, '1105' => 65], ['210101' => 565]);
        $this->partida('diario', '2026-01-20', 'Pago de energía', ['420203' => 40], ['110103' => 40]);
        $this->partida('ajuste', '2026-06-30', 'Depreciación del semestre', ['420209' => 150], ['120202' => 150]);
        $this->partida('ajuste', '2026-06-30', 'Costo de lo vendido', ['4101' => 300], ['1104' => 300]);
    }

    private function cuenta(string $codigo): Cuenta
    {
        return Cuenta::where('codigo', $codigo)->firstOrFail();
    }

    private function partida(string $tipo, string $fecha, string $concepto, array $debe, array $haber): Partida
    {
        $p = new Partida(['fecha' => $fecha, 'tipo' => $tipo, 'concepto' => $concepto]);
        $p->periodo_id = $this->periodo->id;
        $p->numero = Partida::siguienteNumero($this->periodo);
        $p->save();

        foreach ($debe as $codigo => $monto) {
            $p->detalles()->create(['cuenta_id' => $this->cuenta($codigo)->id, 'debe' => $monto, 'haber' => 0]);
        }
        foreach ($haber as $codigo => $monto) {
            $p->detalles()->create(['cuenta_id' => $this->cuenta($codigo)->id, 'debe' => 0, 'haber' => $monto]);
        }

        return $p;
    }

    public function test_dinero_convierte_sin_errores_de_redondeo(): void
    {
        $this->assertSame(123450, Dinero::centavos('1234.5'));
        $this->assertSame(-30, Dinero::centavos('-0.30'));
        $this->assertSame(30, Dinero::centavos(0.1 + 0.2));
        $this->assertSame('(150.00)', Dinero::formato(-15000));
        $this->assertSame('1,234.50', Dinero::formato(123450));
        $this->assertSame('', Dinero::celda(0));
    }

    public function test_libro_mayor_con_saldo_acumulado(): void
    {
        $bancos = LibroMayor::generar($this->cuenta('110103'), $this->periodo);
        $this->assertSame([500000, 496000], array_column($bancos['movimientos'], 'saldo'));

        // cuenta de grupo: junta Caja general y Bancos, en orden de fecha
        $efectivo = LibroMayor::generar($this->cuenta('1101'), $this->periodo);
        $this->assertSame([500000, 511300, 507300], array_column($efectivo['movimientos'], 'saldo'));

        // contra cuenta (acreedora): el saldo normal sale positivo
        $this->assertSame(15000, LibroMayor::generar($this->cuenta('120202'), $this->periodo)['saldo']);
    }

    public function test_balanza_antes_y_despues_de_ajustes(): void
    {
        $antes = BalanzaComprobacion::generar($this->periodo);
        $this->assertTrue($antes['cuadra']);
        $this->assertSame(991800, $antes['totales']['debe']);
        $this->assertSame(987800, $antes['totales']['deudor']);
        $this->assertSame(987800, $antes['totales']['acreedor']);

        $ajustada = BalanzaComprobacion::generar($this->periodo, true);
        $this->assertTrue($ajustada['cuadra']);
        $this->assertSame(1002800, $ajustada['totales']['deudor']);

        // agrupada en cuentas de mayor: 1101 Efectivo = 113 + 4,960
        $mayor = collect(BalanzaComprobacion::generar($this->periodo, true, 3)['filas'])->keyBy(fn ($f) => $f['cuenta']->codigo);
        $this->assertSame(507300, $mayor['1101']['deudor']);
        $this->assertSame(320000, $mayor['1104']['deudor']);
    }

    public function test_hoja_de_trabajo_de_10_columnas(): void
    {
        $hoja = HojaTrabajo::generar($this->periodo);

        $this->assertNotContains(false, $hoja['comprobaciones']);
        $this->assertSame(45000, $hoja['totales']['aju_d']);
        $this->assertSame(1002800, $hoja['totales']['adj_d']);
        $this->assertSame(49000, $hoja['totales']['er_d']);   // 300 + 40 + 150
        $this->assertSame(10000, $hoja['totales']['er_h']);
        $this->assertSame(-39000, $hoja['utilidad']);          // pérdida de 390
        $this->assertSame(992800, $hoja['finales']['bg_d']);
        $this->assertSame(992800, $hoja['finales']['bg_h']);
    }

    public function test_estado_de_resultados(): void
    {
        $r = EstadosFinancieros::resultados($this->periodo);

        $this->assertSame(10000, $r['secciones']['ventas']['total']);
        $this->assertSame(30000, $r['secciones']['costo']['total']);
        $this->assertSame(-20000, $r['utilidad_bruta']);
        $this->assertSame(19000, $r['secciones']['gastos_operacion']['total']);
        $this->assertSame(-39000, $r['utilidad_neta']);
        $this->assertSame([], $r['sin_clasificar']['lineas']);
        $this->assertSame(HojaTrabajo::generar($this->periodo)['utilidad'], $r['utilidad_neta']);
    }

    public function test_balance_general_cuadra(): void
    {
        $b = EstadosFinancieros::balance($this->periodo, -39000);

        $this->assertTrue($b['cuadra']);
        $this->assertSame(938800, $b['total_activo']);
        $this->assertSame(833800, $b['grupos']['activo']['rubros']['corriente']['total']);
        $this->assertSame(105000, $b['grupos']['activo']['rubros']['no_corriente']['total']);  // 1,200 - 150
        $this->assertSame(57800, $b['grupos']['pasivo']['total']);
        $this->assertSame(881000, $b['grupos']['patrimonio']['total']);                       // 9,200 - 390

        // la depreciación acumulada resta dentro del activo
        $dep = collect($b['grupos']['activo']['rubros']['no_corriente']['lineas'])->firstWhere('cuenta.codigo', '1202');
        $this->assertSame(-15000, $dep['monto']);
    }

    public function test_las_partidas_de_cierre_no_entran_a_los_estados(): void
    {
        $this->partida('cierre', '2026-06-30', 'Cierre de ventas', ['5101' => 100], ['61' => 100]);

        $this->assertSame(-39000, HojaTrabajo::generar($this->periodo)['utilidad']);
        $this->assertSame(1002800, BalanzaComprobacion::generar($this->periodo, true)['totales']['deudor']);
        // pero el libro mayor sí la muestra
        $this->assertCount(2, LibroMayor::generar($this->cuenta('5101'), $this->periodo)['movimientos']);
    }

    public function test_cuenta_sin_seccion_aparece_como_sin_clasificar(): void
    {
        $otra = new Cuenta(['codigo' => '45', 'nombre' => 'Otros gastos raros', 'tipo' => 'gasto', 'naturaleza' => 'deudora']);
        $otra->ubicarEnArbol();
        $otra->save();
        $this->partida('diario', '2026-02-01', 'Gasto no clasificado', ['45' => 10], ['110101' => 10]);

        $r = EstadosFinancieros::resultados($this->periodo);
        $this->assertCount(1, $r['sin_clasificar']['lineas']);
        $this->assertSame(-40000, $r['utilidad_neta']);
        $this->assertSame(HojaTrabajo::generar($this->periodo)['utilidad'], $r['utilidad_neta']);
    }

    public function test_las_pantallas_muestran_las_cifras(): void
    {
        $this->actingAs($this->user);

        $this->get(route('contabilidad.mayor'))->assertOk()->assertSee('Bancos');
        $this->get(route('contabilidad.mayor', ['cuenta' => $this->cuenta('110103')->id]))
            ->assertOk()->assertSee('4,960.00')->assertSee('Evidencias del saldo de la cuenta');
        $this->get(route('contabilidad.balanza'))->assertOk()->assertSee('9,878.00')->assertSee('Cuadra');
        $this->get(route('contabilidad.balanza', ['ajustada' => 1, 'nivel' => 'mayor']))->assertOk()->assertSee('10,028.00');
        $this->get(route('contabilidad.hoja-trabajo'))->assertOk()->assertSee('Pérdida del ejercicio')->assertSee('9,928.00');
        $this->get(route('contabilidad.estados'))->assertOk()->assertSee('PÉRDIDA NETA DEL EJERCICIO')->assertSee('(390.00)');
        $this->get(route('contabilidad.estados', ['estado' => 'balance']))->assertOk()->assertSee('9,388.00')->assertSee('8,810.00');
        $this->get(route('contabilidad.dashboard'))->assertOk()->assertSee('10,028.00');
    }

    public function test_sin_periodo_redirige_y_sin_sesion_pide_login(): void
    {
        $this->get(route('contabilidad.balanza'))->assertRedirect(route('login'));

        Partida::query()->delete();
        Periodo::query()->delete();
        $this->actingAs($this->user)->get(route('contabilidad.hoja-trabajo'))
            ->assertRedirect(route('contabilidad.periodos.index'));
    }
}