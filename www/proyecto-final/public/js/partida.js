/*
 * Formulario de partida (libro diario):
 *  - agregar y quitar lineas;
 *  - si escribes en el DEBE se vacía el HABER de esa linea (y al reves);
 *  - totales en vivo y aviso de si la partida cuadra.
 * El servidor vuelve a validar todo; esto solo ayuda mientras se escribe.
 */
document.addEventListener('DOMContentLoaded', function () {
    const tabla = document.getElementById('tabla-lineas');
    if (!tabla) return;

    const cuerpo = tabla.querySelector('tbody');
    const totalDebe = document.getElementById('total-debe');
    const totalHaber = document.getElementById('total-haber');
    const estado = document.getElementById('estado-cuadre');

    // Trabajar en centavos evita errores como 0.1 + 0.2 = 0.30000000000000004
    const centavos = (valor) => Math.round((parseFloat(valor) || 0) * 100);
    const dinero = (cent) => (cent / 100).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function recalcular() {
        let debe = 0, haber = 0;
        cuerpo.querySelectorAll('input.debe').forEach(i => debe += centavos(i.value));
        cuerpo.querySelectorAll('input.haber').forEach(i => haber += centavos(i.value));

        totalDebe.textContent = dinero(debe);
        totalHaber.textContent = dinero(haber);

        if (debe === 0 && haber === 0) {
            estado.className = 'badge text-bg-secondary';
            estado.textContent = 'Sin montos';
        } else if (debe === haber) {
            estado.className = 'badge text-bg-success';
            estado.textContent = '✓ La partida cuadra';
        } else {
            estado.className = 'badge text-bg-danger';
            estado.textContent = 'Diferencia: ' + dinero(Math.abs(debe - haber));
        }
    }

    // Siguiente indice libre para los name="lineas[N][...]"
    let indice = cuerpo.querySelectorAll('tr.linea').length;

    document.getElementById('agregar-linea').addEventListener('click', function () {
        const modelo = cuerpo.querySelector('tr.linea');
        const nueva = modelo.cloneNode(true);

        nueva.querySelectorAll('select, input').forEach(campo => {
            campo.name = campo.name.replace(/lineas\[\d+\]/, 'lineas[' + indice + ']');
            campo.value = '';
            campo.classList.remove('is-invalid');
        });
        nueva.querySelectorAll('.invalid-feedback').forEach(e => e.remove());

        cuerpo.appendChild(nueva);
        indice++;
        nueva.querySelector('select').focus();
    });

    cuerpo.addEventListener('click', function (e) {
        const boton = e.target.closest('.quitar-linea');
        if (!boton) return;

        if (cuerpo.querySelectorAll('tr.linea').length <= 2) {
            // Siempre quedan al menos 2 líneas: solo se limpian
            boton.closest('tr').querySelectorAll('select, input').forEach(c => c.value = '');
        } else {
            boton.closest('tr').remove();
        }
        recalcular();
    });

    cuerpo.addEventListener('input', function (e) {
        const campo = e.target;
        if (!campo.classList.contains('monto')) return;

        if (campo.value !== '') {
            const fila = campo.closest('tr');
            const otro = fila.querySelector(campo.classList.contains('debe') ? 'input.haber' : 'input.debe');
            otro.value = '';
        }
        recalcular();
    });

    recalcular();
});