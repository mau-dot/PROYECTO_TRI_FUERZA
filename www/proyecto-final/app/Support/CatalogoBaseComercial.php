<?php

namespace App\Support;

use App\Enums\Naturaleza;
use App\Enums\Rubro;
use App\Enums\TipoCuenta;
use App\Models\Cuenta;
use Illuminate\Support\Facades\DB;

/**
 * Catalogo de cuentas BASE para una empresa COMERCIAL "estructura usual".
 *
 * Columnas: codigo, nombre, tipo (null = el del padre), naturaleza (null = la normal del tipo), rubro (null = el del padre)
 */
class CatalogoBaseComercial
{
    public const CUENTAS = [
        ['1', 'ACTIVO', 'activo', null, null],
        ['11', 'ACTIVO CORRIENTE', null, null, 'corriente'],
        ['1101', 'Efectivo y equivalentes de efectivo', null, null, null],
        ['110101', 'Caja general', null, null, null],
        ['110102', 'Caja chica', null, null, null],
        ['110103', 'Bancos', null, null, null],
        ['1102', 'Cuentas por cobrar comerciales', null, null, null],
        ['110201', 'Clientes', null, null, null],
        ['110202', 'Documentos por cobrar', null, null, null],
        ['1103', 'Estimación para cuentas incobrables', null, 'acreedora', null],
        ['1104', 'Inventario de mercadería', null, null, null],
        ['1105', 'IVA crédito fiscal', null, null, null],
        ['1106', 'Pagos anticipados', null, null, null],
        ['110601', 'Seguros pagados por anticipado', null, null, null],
        ['110602', 'Alquileres pagados por anticipado', null, null, null],
        ['110603', 'Pago a cuenta de impuesto sobre la renta', null, null, null],
        ['12', 'ACTIVO NO CORRIENTE', null, null, 'no_corriente'],
        ['1201', 'Propiedad, planta y equipo', null, null, null],
        ['120101', 'Terrenos', null, null, null],
        ['120102', 'Edificaciones', null, null, null],
        ['120103', 'Mobiliario y equipo', null, null, null],
        ['120104', 'Equipo de transporte', null, null, null],
        ['120105', 'Equipo de cómputo', null, null, null],
        ['1202', 'Depreciación acumulada', null, 'acreedora', null],
        ['120201', 'Depreciación acumulada de edificaciones', null, 'acreedora', null],
        ['120202', 'Depreciación acumulada de mobiliario y equipo', null, 'acreedora', null],
        ['120203', 'Depreciación acumulada de equipo de transporte', null, 'acreedora', null],
        ['120204', 'Depreciación acumulada de equipo de cómputo', null, 'acreedora', null],

        ['2', 'PASIVO', 'pasivo', null, null],
        ['21', 'PASIVO CORRIENTE', null, null, 'corriente'],
        ['2101', 'Cuentas por pagar comerciales', null, null, null],
        ['210101', 'Proveedores', null, null, null],
        ['210102', 'Documentos por pagar', null, null, null],
        ['2102', 'Préstamos bancarios a corto plazo', null, null, null],
        ['2103', 'Retenciones y descuentos', null, null, null],
        ['210301', 'ISSS por pagar', null, null, null],
        ['210302', 'AFP por pagar', null, null, null],
        ['210303', 'Retención de impuesto sobre la renta', null, null, null],
        ['2104', 'IVA débito fiscal', null, null, null],
        ['2105', 'Impuesto sobre la renta por pagar', null, null, null],
        ['2106', 'Gastos acumulados por pagar', null, null, null],
        ['22', 'PASIVO NO CORRIENTE', null, null, 'no_corriente'],
        ['2201', 'Préstamos bancarios a largo plazo', null, null, null],

        ['3', 'PATRIMONIO', 'patrimonio', null, null],
        ['31', 'CAPITAL CONTABLE', null, null, null],
        ['3101', 'Capital social', null, null, null],
        ['3102', 'Reserva legal', null, null, null],
        ['3103', 'Utilidades acumuladas', null, null, null],
        ['3104', 'Utilidad del ejercicio', null, null, null],
        ['3105', 'Pérdidas acumuladas', null, 'deudora', null],
        ['3106', 'Pérdida del ejercicio', null, 'deudora', null],

        ['4', 'CUENTAS DE RESULTADO DEUDORAS', 'gasto', null, null],
        ['41', 'COSTOS', 'costo', null, null],
        ['4101', 'Costo de venta', null, null, null],
        ['4102', 'Compras', null, null, null],
        ['4103', 'Gastos sobre compras', null, null, null],
        ['42', 'GASTOS DE OPERACIÓN', 'gasto', null, null],
        ['4201', 'Gastos de venta', null, null, null],
        ['420101', 'Sueldos y salarios (ventas)', null, null, null],
        ['420102', 'Publicidad y propaganda', null, null, null],
        ['420103', 'Depreciación (ventas)', null, null, null],
        ['420104', 'Cuentas incobrables', null, null, null],
        ['4202', 'Gastos de administración', null, null, null],
        ['420201', 'Sueldos y salarios (administración)', null, null, null],
        ['420202', 'Cuota patronal ISSS y AFP', null, null, null],
        ['420203', 'Energía eléctrica', null, null, null],
        ['420204', 'Agua', null, null, null],
        ['420205', 'Telefonía e internet', null, null, null],
        ['420206', 'Alquileres', null, null, null],
        ['420207', 'Papelería y útiles', null, null, null],
        ['420208', 'Seguros', null, null, null],
        ['420209', 'Depreciación (administración)', null, null, null],
        ['43', 'GASTOS NO DE OPERACIÓN', 'gasto', null, null],
        ['4301', 'Gastos financieros', null, null, null],
        ['430101', 'Intereses', null, null, null],
        ['430102', 'Comisiones bancarias', null, null, null],
        ['44', 'IMPUESTO SOBRE LA RENTA', 'gasto', null, null],
        ['4401', 'Gasto por impuesto sobre la renta', null, null, null],

        ['5', 'CUENTAS DE RESULTADO ACREEDORAS', 'ingreso', null, null],
        ['51', 'INGRESOS DE OPERACIÓN', null, null, null],
        ['5101', 'Ventas', null, null, null],
        ['5102', 'Rebajas y devoluciones sobre ventas', null, 'deudora', null],
        ['5103', 'Rebajas y devoluciones sobre compras', 'costo', 'acreedora', null],
        ['52', 'OTROS INGRESOS', null, null, null],
        ['5201', 'Otros ingresos', null, null, null],
        ['5202', 'Intereses ganados', null, null, null],

        ['6', 'CUENTA LIQUIDADORA', 'liquidadora', null, null],
        ['61', 'Pérdidas y ganancias', null, null, null],
    ];

    /**
     * Carga las cuentas que falten (no toca las que ya existen).
     * Devuelve cuantas cuentas creo.
     */
    public static function cargar(): int
    {
        return DB::transaction(function () {
            $creadas = 0;

            foreach (self::CUENTAS as [$codigo, $nombre, $tipo, $naturaleza, $rubro]) {
                if (Cuenta::where('codigo', $codigo)->exists()) {
                    continue;
                }

                $cuenta = new Cuenta(['codigo' => $codigo, 'nombre' => $nombre]);
                $cuenta->ubicarEnArbol();
                $padre = $cuenta->padre_id ? Cuenta::find($cuenta->padre_id) : null;

                $cuenta->tipo = $tipo ? TipoCuenta::from($tipo) : $padre->tipo;
                $cuenta->naturaleza = $naturaleza ? Naturaleza::from($naturaleza) : $cuenta->tipo->naturalezaNormal();
                $cuenta->rubro = $rubro ? Rubro::from($rubro) : $padre?->rubro;
                $cuenta->save();

                $creadas++;
            }

            return $creadas;
        });
    }
}