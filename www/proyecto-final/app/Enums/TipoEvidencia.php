<?php

namespace App\Enums;

/**
 * Tipos de fuente que on requeridos,
 */
enum TipoEvidencia: string
{
    case Factura = 'factura';
    case Cotizacion = 'cotizacion';
    case EstadoFinanciero = 'estado_financiero';
    case Entrevista = 'entrevista';
    case Fotografia = 'fotografia';
    case Autorizacion = 'autorizacion';
    case Otro = 'otro';

    public function label(): string
    {
        return match ($this) {
            self::Factura => 'Factura o comprobante',
            self::Cotizacion => 'Cotización',
            self::EstadoFinanciero => 'Estado financiero o registro contable',
            self::Entrevista => 'Entrevista',
            self::Fotografia => 'Fotografía o captura',
            self::Autorizacion => 'Carta de autorización',
            self::Otro => 'Otro documento',
        };
    }

    public function icono(): string
    {
        return match ($this) {
            self::Factura => 'fa-solid fa-file-invoice',
            self::Cotizacion => 'fa-solid fa-file-signature',
            self::EstadoFinanciero => 'fa-solid fa-file-invoice-dollar',
            self::Entrevista => 'fa-solid fa-comments',
            self::Fotografia => 'fa-solid fa-camera',
            self::Autorizacion => 'fa-solid fa-stamp',
            self::Otro => 'fa-solid fa-file',
        };
    }
}