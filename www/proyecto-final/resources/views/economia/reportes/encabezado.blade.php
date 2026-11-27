{{-- Encabezado común de los reportes de Economía --}}

<div class="text-center mb-3">

    <div class="fw-bold fs-5">
        {{ \App\Models\Empresa::actual()?->nombre ?? 'Empresa sin registrar' }}
    </div>

    <div class="fw-semibold">
        {{ $titulo }}
    </div>

    @if ($decision)

        <div class="small text-body-secondary">

            {{ $decision->titulo }}

            · TMAR {{ rtrim(rtrim(number_format($decision->tmar, 4), '0'), '.') }} %

            · Cifras en dólares (US$)

            @if ($decision->provisional)

                · <span class="badge text-bg-warning">
                    Datos provisionales
                </span>

            @endif

        </div>

    @endif

</div>