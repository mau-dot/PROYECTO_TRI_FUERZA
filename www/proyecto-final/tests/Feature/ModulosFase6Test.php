<?php

namespace Tests\Feature;

use App\Models\Alternativa;
use App\Models\Costeo;
use App\Models\Decision;
use App\Models\Evidencia;
use App\Models\User;
use App\Services\Economia\Depreciacion;
use App\Services\Economia\Factores;
use App\Services\Economia\FlujosCaja;
use App\Support\CatalogoBaseComercial;
use App\Support\EconomiaProvisional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModulosFase6Test extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        CatalogoBaseComercial::cargar();
    }

    private function alternativa(string $letra): Alternativa
    {
        return Alternativa::where(
            'nombre',
            'like',
            "Alternativa $letra:%"
        )->firstOrFail();
    }

    // PRUEBA 1: Autenticación
    public function test_sin_sesion_pide_login(): void
    {
        $this->get(route('economia.alternativas.index'))
            ->assertRedirect(route('login'));

        $this->get(route('economia.decision.edit'))
            ->assertRedirect(route('login'));
    }

    // PRUEBA 2: Factores de interés compuesto
    public function test_factores_al_15_por_ciento(): void
    {
        $this->assertEqualsWithDelta(
            0.2472, Factores::pf(0.15, 10), 0.00005
        );

        $this->assertEqualsWithDelta(
            5.0188, Factores::pa(0.15, 10), 0.00005
        );

        $this->assertEqualsWithDelta(
            0.1993, Factores::ap(0.15, 10), 0.00005
        );

        $this->assertEqualsWithDelta(
            0.0493, Factores::af(0.15, 10), 0.00005
        );

        $this->assertEqualsWithDelta(
            4.0456, Factores::fp(0.15, 10), 0.00005
        );

        $this->assertEqualsWithDelta(
            20.3037, Factores::fa(0.15, 10), 0.00005
        );

        $this->assertEqualsWithDelta(
            3.3522, Factores::pa(0.15, 5), 0.00005
        );

        $this->assertEqualsWithDelta(
            0.126825,
            Factores::efectiva(0.12, 12),
            0.000001
        );
    }

    // PRUEBA 3: Los tres métodos de depreciación
    public function test_depreciacion_por_los_tres_metodos(): void
    {
        $t = Depreciacion::todas(1800, 200, 10);

        $this->assertSame(160.0, $t['lr'][0]['depreciacion']);
        $this->assertSame(1640.0, $t['lr'][0]['libros']);

        $this->assertSame(290.91, $t['sda'][0]['depreciacion']);
        $this->assertSame(29.09, $t['sda'][9]['depreciacion']);

        $this->assertSame(360.0, $t['sdd'][0]['depreciacion']);
        $this->assertSame(147.46, $t['sdd'][4]['depreciacion']);
        $this->assertSame(589.82, $t['sdd'][4]['libros']);
        $this->assertSame(41.59, $t['sdd'][9]['depreciacion']);

        foreach ($t as $metodo => $filas) {

            $this->assertEqualsWithDelta(
                1600,
                array_sum(array_column($filas, 'depreciacion')),
                0.001,
                $metodo
            );

            $this->assertSame(
                200.0,
                end($filas)['libros'],
                $metodo
            );
        }

        // No depreciar por debajo del salvamento
        $alto = Depreciacion::tabla('sdd', 1000, 600, 4);

        $this->assertSame(400.0, $alto[0]['depreciacion']);
        $this->assertSame(0.0, $alto[1]['depreciacion']);
        $this->assertSame(600.0, $alto[3]['libros']);
    }

    // PRUEBA 4: Datos provisionales y conexión con Contabilidad
    public function test_el_ejemplo_provisional_carga_decision_y_alternativas_enlazadas(): void
    {
        $this->actingAs($this->user)
            ->post(route('economia.alternativas.cargar-ejemplo'))
            ->assertRedirect(route('economia.alternativas.index'));

        $d = Decision::actual();

        $this->assertSame(0.15, $d->i());
        $this->assertSame(0.25, $d->t());
        $this->assertTrue($d->provisional);

        $this->assertSame(3, Alternativa::count());
        $this->assertSame(3, Costeo::count());

        $a = $this->alternativa('A');

        $this->assertSame(
            'Alternativa A: estantes metálicos nuevos',
            $a->costeo->nombre
        );

        $this->assertSame(489.6, $a->costoOperacion());
        $this->assertSame(510.4, $a->flujoAnual());

        $this->assertSame(
            120.0,
            $this->alternativa('B')->flujoAnual()
        );

        $this->assertSame(
            374.4,
            $this->alternativa('C')->flujoAnual()
        );

        // Comprobar que no se dupliquen los datos
        $this->assertSame(0, EconomiaProvisional::cargar());
    }

    // PRUEBA 5: Flujos de caja
    public function test_flujos_de_caja(): void
    {
        EconomiaProvisional::cargar();

        $f = FlujosCaja::generar(
            $this->alternativa('A')
        );

        $this->assertCount(11, $f);

        $this->assertSame(-1800.0, $f[0]['neto']);
        $this->assertSame(510.4, $f[1]['neto']);
        $this->assertSame(710.4, $f[10]['neto']);

        // Si cambia el costeo, debe cambiar el flujo
        $costeo = $this->alternativa('A')->costeo;

        $costeo->elementos()
            ->where('concepto', 'like', 'Mantenimiento%')
            ->update(['costo_unitario' => 50]);

        $this->assertSame(
            500.4,
            $this->alternativa('A')->flujoAnual()
        );
    }

    // PRUEBA 6: Decisión de inversión y validaciones
    public function test_decision_y_validaciones(): void
    {
        $this->actingAs($this->user);

        $this->put(route('economia.decision.update'), [
            'titulo' => '',
            'tmar' => 0,
            'tasa_isr' => 150,
        ])->assertSessionHasErrors([
            'titulo',
            'tmar',
            'tasa_isr'
        ]);

        $this->put(route('economia.decision.update'), [
            'titulo' => 'Comprar o alquilar',
            'tmar' => '12.6825',
            'tasa_isr' => '30',
        ])->assertRedirect(
            route('economia.alternativas.index')
        );

        $this->put(route('economia.decision.update'), [
            'titulo' => 'Comprar o alquilar',
            'tmar' => '13',
            'tasa_isr' => '30',
        ])->assertRedirect(
            route('economia.decision.edit')
        );

        $this->assertSame(1, Decision::count());
        $this->assertSame(0.13, Decision::actual()->i());
    }

    // PRUEBA 7: Alternativas manuales y validaciones
    public function test_alternativa_manual_y_validaciones(): void
    {
        $this->actingAs($this->user);

        $this->get(route('economia.alternativas.create'))
            ->assertRedirect(route('economia.decision.edit'));

        Decision::create([
            'titulo' => 'D',
            'tmar' => 15,
            'tasa_isr' => 25,
        ]);

        $this->post(route('economia.alternativas.store'), [
            'nombre' => 'X',
            'inversion' => 100,
            'vida_util' => 0,
            'valor_salvamento' => 100,
            'beneficio_anual' => 0,
        ])->assertSessionHasErrors([
            'vida_util',
            'valor_salvamento',
            'costo_operacion'
        ]);

        $this->post(route('economia.alternativas.store'), [
            'nombre' => 'Alquilar',
            'inversion' => 500,
            'vida_util' => 3,
            'valor_salvamento' => 0,
            'beneficio_anual' => 0,
            'costo_operacion' => '1200',
        ]);

        $alt = Alternativa::firstOrFail();

        $this->assertSame(1200.0, $alt->costoOperacion());
        $this->assertSame(-1200.0, $alt->flujoAnual());
        $this->assertSame('Ingreso manual', $alt->origenCosto());

        // Validación de nombres repetidos
        $this->post(route('economia.alternativas.store'), [
            'nombre' => 'Alquilar',
            'inversion' => 1,
            'vida_util' => 1,
            'valor_salvamento' => 0,
            'beneficio_anual' => 0,
            'costo_operacion' => 1,
        ])->assertSessionHasErrors('nombre');
    }

    // PRUEBA 8: Evidencias y eliminación de alternativas
    public function test_evidencias_por_dato_y_borrado(): void
    {
        $this->actingAs($this->user);

        EconomiaProvisional::cargar();

        $a = $this->alternativa('A');

        $ev = Evidencia::create([
            'tipo' => 'cotizacion',
            'titulo' => 'Cotización góndolas',
            'fecha' => '2026-11-20',
            'fuente' => 'Proveedor 1',
        ]);

        $this->post(route('evidencias.vincular'), [
            'evidencia_id' => $ev->id,
            'tipo' => 'alternativa',
            'id' => $a->id,
            'campo' => 'inversion',
        ])->assertSessionHasNoErrors();

        $this->post(route('evidencias.vincular'), [
            'evidencia_id' => $ev->id,
            'tipo' => 'decision',
            'id' => Decision::actual()->id,
            'campo' => 'tmar',
        ])->assertSessionHasNoErrors();

        $this->assertSame(
            1, $a->numFuentes('inversion')
        );

        $this->assertSame(
            0, $a->numFuentes('vida_util')
        );

        $this->get(route('evidencias.show', $ev))
            ->assertOk()
            ->assertSee(
                route('economia.alternativas.show', $a),
                false
            );

        $this->delete(route('economia.alternativas.destroy', $a))
            ->assertRedirect(route('economia.alternativas.index'));

        $this->assertSame(2, Alternativa::count());

        // El costeo de Contabilidad no debe eliminarse
        $this->assertSame(3, Costeo::count());

        $this->assertSame(
            1,
            \DB::table('evidenciables')->count()
        );
    }

    // PRUEBA 9: Funcionamiento de las pantallas
    public function test_las_pantallas_cargan(): void
    {
        $this->actingAs($this->user);

        $this->get(route('economia.alternativas.index'))
            ->assertOk()
            ->assertSee('Cargar ejemplo provisional');

        $this->get(route('economia.flujos.index'))
            ->assertRedirect(route('economia.decision.edit'));

        $this->get(route('economia.factores'))
            ->assertOk()
            ->assertSee('6.1446');

        EconomiaProvisional::cargar();

        $a = $this->alternativa('A');

        $this->get(route('economia.decision.edit'))
            ->assertOk()
            ->assertSee('Fuentes de la TMAR');

        $this->get(route('economia.alternativas.index'))
            ->assertOk()
            ->assertSee('510.40')
            ->assertSee('Provisional');

        $this->get(route('economia.alternativas.show', $a))
            ->assertOk()
            ->assertSee('Fuentes: Inversión inicial (P)')
            ->assertSee('489.60');

        $this->get(route('economia.alternativas.create'))
            ->assertOk();

        $this->get(route('economia.alternativas.edit', $a))
            ->assertOk();

        $this->get(route('economia.flujos.index'))
            ->assertOk()
            ->assertSee('710.40')
            ->assertSee('(1,800.00)');

        $this->get(route('economia.factores'))
            ->assertOk()
            ->assertSee('5.0188')
            ->assertSee('0.1993');

        $this->get(route('economia.factores', [
            'i' => 10,
            'n' => 5
        ]))
            ->assertOk()
            ->assertSee('3.7908');

        $this->get(route('economia.depreciacion', [
            'alternativa' => $a->id
        ]))
            ->assertOk()
            ->assertSee('290.91')
            ->assertSee('41.59');

        $this->get(route('economia.dashboard'))
            ->assertOk()
            ->assertSee('15 %');
    }
}
