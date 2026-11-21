(() => {
    'use strict';

    const root = document.documentElement;
    const themeButton = document.getElementById('boton-tema');
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const canHover = window.matchMedia('(hover: hover) and (pointer: fine)');

    function syncThemeButton(theme) {
        if (!themeButton) return;
        const isDark = theme === 'dark';
        themeButton.setAttribute('aria-pressed', String(isDark));
        themeButton.setAttribute('aria-label', isDark ? 'Usar tema claro' : 'Usar tema oscuro');
        themeButton.setAttribute('title', isDark ? 'Tema claro' : 'Tema oscuro');

        const icon = themeButton.querySelector('.theme-icon');
        if (icon) {
            icon.classList.toggle('fa-sun', isDark);
            icon.classList.toggle('fa-moon', !isDark);
        }
    }

    if (themeButton) {
        syncThemeButton(root.dataset.bsTheme);

        themeButton.addEventListener('click', () => {
            const next = root.dataset.bsTheme === 'dark' ? 'light' : 'dark';
            root.dataset.bsTheme = next;
            try {
                localStorage.setItem('tfu-theme', next);
            } catch (error) {
                // Sin almacenamiento el tema sigue funcionando en esta visita.
            }
            syncThemeButton(next);
        });
    }

    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const input = document.getElementById(button.dataset.passwordToggle);
        if (!input) return;

        button.addEventListener('click', () => {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.setAttribute('aria-pressed', String(show));
            button.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');

            const icon = button.querySelector('.eye-icon');
            if (icon) {
                icon.classList.toggle('fa-eye-slash', show);
                icon.classList.toggle('fa-eye', !show);
            }
        });
    });

    // Titular por palabras: solo si el movimiento está permitido.
    document.querySelectorAll('[data-split]').forEach((title) => {
        if (reducedMotion.matches) return;

        const words = title.textContent.trim().split(/\s+/);
        if (words.length < 2) return;

        title.textContent = '';
        words.forEach((word, index) => {
            const span = document.createElement('span');
            span.className = 'word';
            span.textContent = word;
            span.style.setProperty('--i', index);
            title.append(span);
            if (index < words.length - 1) title.append(document.createTextNode(' '));
        });
    });

    // Orden de aparición de los campos del formulario.
    document.querySelectorAll('.auth-card form').forEach((form) => {
        Array.from(form.children)
            .filter((child) => child.type !== 'hidden')
            .forEach((child, index) => child.style.setProperty('--i', index));
    });

    // Ripple en botones: feedback en el punto del clic.
    document.addEventListener('click', (event) => {
        const button = event.target.closest('button.btn');
        if (!button || button.disabled || reducedMotion.matches) return;

        const rect = button.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = event.detail === 0 ? rect.width / 2 : event.clientX - rect.left;
        const y = event.detail === 0 ? rect.height / 2 : event.clientY - rect.top;

        const ripple = document.createElement('span');
        ripple.className = 'ripple';
        ripple.style.width = `${size}px`;
        ripple.style.height = `${size}px`;
        ripple.style.left = `${x - size / 2}px`;
        ripple.style.top = `${y - size / 2}px`;
        ripple.addEventListener('animationend', () => ripple.remove(), { once: true });
        button.append(ripple);
    });

    // Magnet y spotlight: solo con puntero fino y sin movimiento reducido.
    if (canHover.matches && !reducedMotion.matches) {
        document.querySelectorAll('[data-magnet]').forEach((element) => {
            let frame = null;
            const strength = 6;

            element.addEventListener('pointermove', (event) => {
                const rect = element.getBoundingClientRect();
                const x = ((event.clientX - rect.left) / rect.width - 0.5) * 2;
                const y = ((event.clientY - rect.top) / rect.height - 0.5) * 2;

                if (frame) cancelAnimationFrame(frame);
                frame = requestAnimationFrame(() => {
                    element.style.transform = `translate(${x * strength}px, ${y * strength}px)`;
                });
            });

            element.addEventListener('pointerleave', () => {
                if (frame) cancelAnimationFrame(frame);
                element.style.transform = '';
            });
        });

        document.querySelectorAll('.surface-card').forEach((card) => {
            let frame = null;

            card.addEventListener('pointermove', (event) => {
                const rect = card.getBoundingClientRect();
                const px = (event.clientX - rect.left) / rect.width;
                const py = (event.clientY - rect.top) / rect.height;

                if (frame) cancelAnimationFrame(frame);
                frame = requestAnimationFrame(() => {
                    card.style.setProperty('--mx', `${px * 100}%`);
                    card.style.setProperty('--my', `${py * 100}%`);
                    card.style.setProperty('--rx', `${(0.5 - py) * 4}deg`);
                    card.style.setProperty('--ry', `${(px - 0.5) * 4}deg`);
                    card.classList.add('is-tilting');
                });
            });

            card.addEventListener('pointerleave', () => {
                if (frame) cancelAnimationFrame(frame);
                card.classList.remove('is-tilting');
                card.style.removeProperty('--rx');
                card.style.removeProperty('--ry');
            });
        });
    }

    // La barra gana sombra cuando el contenido pasa por debajo.
    const header = document.querySelector('header');
    if (header) {
        const syncHeader = () => header.classList.toggle('is-scrolled', window.scrollY > 8);
        window.addEventListener('scroll', syncHeader, { passive: true });
        syncHeader();
    }
})();
