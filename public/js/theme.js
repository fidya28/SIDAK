(function () {
    const storageKey = 'sintuvu-theme';
    const savedTheme = localStorage.getItem(storageKey);
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const isDark = savedTheme ? savedTheme === 'dark' : systemPrefersDark;

    function setTheme(dark) {
        document.documentElement.classList.toggle('dark-mode', dark);
        document.documentElement.classList.toggle('dark', dark);
    }

    function updateButton(button, dark) {
        button.setAttribute('aria-pressed', String(dark));
        button.setAttribute('aria-label', dark ? 'Aktifkan mode terang' : 'Aktifkan mode malam');
        button.title = dark ? 'Aktifkan mode terang' : 'Aktifkan mode malam';
        button.innerHTML = dark
            ? '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="2"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>'
            : '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20.2 15.3A8.5 8.5 0 0 1 8.7 3.8 8.5 8.5 0 1 0 20.2 15.3Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    }

    setTheme(isDark);

    function addThemeToggle() {
        if (document.body.dataset.hideThemeToggle === 'true') {
            return;
        }

        const button = document.createElement('button');
        let dark = isDark;

        button.id = 'theme-toggle';
        button.type = 'button';
        updateButton(button, dark);
        button.addEventListener('click', function () {
            dark = !dark;
            localStorage.setItem(storageKey, dark ? 'dark' : 'light');
            setTheme(dark);
            updateButton(button, dark);
        });

        const systemStatus = document.querySelector('[data-system-status]');
        const fallbackContainer = document.querySelector('.nav-actions, .auth-brand-row');
        if (systemStatus) {
            button.classList.add('theme-toggle-inline');
            systemStatus.insertAdjacentElement('afterend', button);
        } else if (fallbackContainer) {
            button.classList.add('theme-toggle-inline');
            fallbackContainer.appendChild(button);
        } else {
            document.body.appendChild(button);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', addThemeToggle, { once: true });
    } else {
        addThemeToggle();
    }
})();
