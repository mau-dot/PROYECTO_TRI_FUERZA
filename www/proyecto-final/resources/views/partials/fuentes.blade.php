{{-- Insignia de trazabilidad: cuántas fuentes respaldan un dato. Uso: @include('partials.fuentes', ['n' => 2]) --}}
@php($minimo = config('evidencias.minimo_fuentes'))
@if ($n >= $minimo)
    <span class="badge text-bg-success" title="Respaldado con {{ $n }} fuentes"><i class="fa-solid fa-paperclip"></i> {{ $n }} fuentes</span>
@elseif ($n > 0)
    <span class="badge text-bg-warning" title="La guía pide mínimo {{ $minimo }} fuentes"><i class="fa-solid fa-paperclip"></i> {{ $n }} de {{ $minimo }}</span>
@else
    <span class="badge text-bg-danger" title="Sin evidencia: la guía pide mínimo {{ $minimo }} fuentes"><i class="fa-solid fa-paperclip"></i> Sin fuentes</span>
@endif