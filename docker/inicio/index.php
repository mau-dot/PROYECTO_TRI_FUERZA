<?php

declare(strict_types=1);

/**
 * Índice de proyectos del servidor multiproyecto Laravel.
 * Vive en /var/www/inicio (fuera de www/ para no exponer los .env).
 */

$raiz = '/var/www/html';
$proyectos = [];

foreach (glob($raiz . '/*', GLOB_ONLYDIR) ?: [] as $ruta) {
    $nombre = basename($ruta);

    if ($nombre === '' || $nombre[0] === '.') {
        continue;
    }

    $proyectos[$nombre] = [
        'tiene_public'  => is_dir($ruta . '/public'),
        'es_laravel'    => is_file($ruta . '/artisan'),
        'modificado'    => @filemtime($ruta) ?: 0,
    ];
}

ksort($proyectos, SORT_NATURAL | SORT_FLAG_CASE);
$total = count($proyectos);

/** @param mixed $valor */
function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function fecha(int $timestamp): string
{
    if ($timestamp <= 0) {
        return '—';
    }

    return date('d/m/Y H:i', $timestamp);
}
?>


<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Proyectos en el servidor · Laravel</title>
    <style>
        :root {
            --fondo: #0b1220;
            --panel: #131c2e;
            --panel-borde: #22304a;
            --texto: #e6edf7;
            --texto-suave: #93a4bd;
            --acento: #ff2d20;
            --ok: #34d399;
            --aviso: #fbbf24;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            padding: 2.5rem 1.25rem 3rem;
            background:
                radial-gradient(900px 400px at 15% -10%, rgba(255, 45, 32, .22), transparent 65%),
                radial-gradient(700px 350px at 95% 0%, rgba(56, 189, 248, .16), transparent 60%),
                var(--fondo);
            color: var(--texto);
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            line-height: 1.55;
        }

        .contenedor { max-width: 1040px; margin: 0 auto; }

        header { margin-bottom: 2rem; }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            margin: 0 0 .75rem;
            padding: .3rem .75rem;
            border: 1px solid var(--panel-borde);
            border-radius: 999px;
            background: rgba(19, 28, 46, .8);
            color: var(--texto-suave);
            font-size: .78rem;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .punto {
            width: .5rem;
            height: .5rem;
            border-radius: 50%;
            background: var(--ok);
            box-shadow: 0 0 0 4px rgba(52, 211, 153, .15);
        }

        h1 {
            margin: 0 0 .5rem;
            font-size: clamp(1.8rem, 4vw, 2.6rem);
            letter-spacing: -.02em;
        }

        .subtitulo { margin: 0; max-width: 62ch; color: var(--texto-suave); }

        .barra {
            display: flex;
            flex-wrap: wrap;
            gap: .75rem 1.25rem;
            align-items: center;
            justify-content: space-between;
            margin: 1.75rem 0 1rem;
            padding-bottom: .75rem;
            border-bottom: 1px solid var(--panel-borde);
        }

        .contador { color: var(--texto-suave); font-size: .92rem; }
        .contador strong { color: var(--texto); }

        .chips { display: flex; gap: .5rem; flex-wrap: wrap; }

        .chip {
            padding: .25rem .6rem;
            border-radius: 6px;
            background: rgba(255, 255, 255, .05);
            border: 1px solid var(--panel-borde);
            color: var(--texto-suave);
            font-size: .78rem;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
            gap: 1rem;
        }

        .tarjeta {
            display: flex;
            flex-direction: column;
            gap: .9rem;
            padding: 1.15rem;
            border: 1px solid var(--panel-borde);
            border-radius: 12px;
            background: linear-gradient(180deg, rgba(255, 255, 255, .03), transparent), var(--panel);
            text-decoration: none;
            color: inherit;
            transition: transform .15s ease, border-color .15s ease;
        }

        a.tarjeta:hover {
            transform: translateY(-2px);
            border-color: rgba(255, 45, 32, .55);
        }

        .tarjeta-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .75rem;
        }

        .nombre {
            font-size: 1.05rem;
            font-weight: 650;
            letter-spacing: -.01em;
            overflow-wrap: anywhere;
        }

        .flecha { color: var(--texto-suave); font-size: 1.1rem; }
        .tarjeta:hover .flecha { color: var(--acento); }

        .meta {
            display: flex;
            flex-wrap: wrap;
            gap: .4rem;
        }

        .etiqueta {
            padding: .18rem .5rem;
            border-radius: 5px;
            font-size: .74rem;
            font-weight: 600;
        }

        .etiqueta-laravel { background: rgba(255, 45, 32, .14); color: #ff8a80; }
        .etiqueta-ok { background: rgba(52, 211, 153, .13); color: var(--ok); }
        .etiqueta-aviso { background: rgba(251, 191, 36, .13); color: var(--aviso); }

        .url {
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: .78rem;
            color: var(--texto-suave);
            overflow-wrap: anywhere;
        }

        .vacio {
            padding: 3rem 1.5rem;
            text-align: center;
            border: 1px dashed var(--panel-borde);
            border-radius: 12px;
            background: rgba(19, 28, 46, .55);
        }

        .vacio h2 { margin: 0 0 .5rem; font-size: 1.15rem; }
        .vacio p { margin: 0 0 1.25rem; color: var(--texto-suave); }

        pre {
            margin: 0 auto;
            max-width: 100%;
            padding: .9rem 1rem;
            overflow-x: auto;
            border-radius: 8px;
            border: 1px solid var(--panel-borde);
            background: #0a101c;
            color: #c7d5ea;
            font-size: .8rem;
            text-align: left;
        }

        .ayuda {
            margin-top: 2rem;
            padding: 1rem 1.15rem;
            border-left: 3px solid var(--acento);
            border-radius: 0 8px 8px 0;
            background: rgba(255, 45, 32, .07);
            color: var(--texto-suave);
            font-size: .88rem;
        }

        .ayuda strong { color: var(--texto); }

        footer {
            margin-top: 2.5rem;
            padding-top: 1rem;
            border-top: 1px solid var(--panel-borde);
            color: var(--texto-suave);
            font-size: .8rem;
            display: flex;
            flex-wrap: wrap;
            gap: .5rem 1rem;
            justify-content: space-between;
        }

        code {
            padding: .1rem .35rem;
            border-radius: 4px;
            background: rgba(255, 255, 255, .07);
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: .85em;
        }
    </style>
</head>
<body>
    <div class="contenedor">
        <header>
            <p class="eyebrow"><span class="punto"></span> Servidor multiproyecto Laravel</p>
            <h1>Proyectos en el servidor</h1>
            <p class="subtitulo">
                Cada carpeta dentro de <code>www/</code> se sirve en su propio subdominio
                <code>&lt;proyecto&gt;.localhost:8083</code> apuntando a su carpeta <code>public/</code>.
            </p>
        </header>

        <div class="barra">
            <span class="contador">
                <?php if ($total === 0): ?>
                    Sin proyectos todavía
                <?php else: ?>
                    <strong><?= e($total) ?></strong> <?= $total === 1 ? 'proyecto' : 'proyectos' ?>
                <?php endif; ?>
            </span>
            <div class="chips">
                <span class="chip">Apache + PHP <?= e(PHP_VERSION) ?></span>
                <span class="chip">PostgreSQL 17</span>
                <span class="chip">Vite :5173</span>
            </div>
        </div>

        <?php if ($total === 0): ?>
            <section class="vacio">
                <h2>Todavía no hay proyectos</h2>
                <p>Crea el primero con Composer dentro del contenedor (como <code>www-data</code>):</p>
                <pre>cd ~/PROYECTO_TRI_FUERZA
docker compose exec -u www-data servidor \
  composer create-project laravel/laravel:^12.0 mi-proyecto</pre>
            </section>
        <?php else: ?>
            <section class="grid">
                <?php foreach ($proyectos as $nombre => $datos): ?>
                    <?php $url = 'http://' . $nombre . '.localhost:8083'; ?>
                    <?php if ($datos['tiene_public']): ?>
                        <a class="tarjeta" href="<?= e($url) ?>">
                    <?php else: ?>
                        <div class="tarjeta">
                    <?php endif; ?>
                            <div class="tarjeta-top">
                                <span class="nombre"><?= e($nombre) ?></span>
                                <span class="flecha"><?= $datos['tiene_public'] ? '↗' : '·' ?></span>
                            </div>
                            <div class="meta">
                                <?php if ($datos['es_laravel']): ?>
                                    <span class="etiqueta etiqueta-laravel">Laravel</span>
                                <?php endif; ?>
                                <?php if ($datos['tiene_public']): ?>
                                    <span class="etiqueta etiqueta-ok">public/ listo</span>
                                <?php else: ?>
                                    <span class="etiqueta etiqueta-aviso">falta public/</span>
                                <?php endif; ?>
                            </div>
                            <div class="url"><?= e($url) ?></div>
                            <div class="url">modificado: <?= e(fecha($datos['modificado'])) ?></div>
                    <?php if ($datos['tiene_public']): ?>
                        </a>
                    <?php else: ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <div class="ayuda">
            <strong>Vite:</strong> solo un proyecto puede usar <code>npm run dev</code> a la vez porque todos
            comparten el puerto 5173. Los demás usan <code>npm run build</code>.
            Los proyectos deben vivir en el sistema de archivos de Ubuntu, nunca en <code>/mnt/c/</code>.
        </div>

        <footer>
            <span>Índice servido desde <code>/var/www/inicio</code></span>
            <span><?= e(date('d/m/Y H:i')) ?> · <?= e(date_default_timezone_get()) ?></span>
        </footer>
    </div>
</body>
</html>