<script>
    (function () {
        if (window.SaungTheme) {
            return;
        }

        const storageKey = 'saung-rh-theme';
        const lightTheme = 'cr-cafe-resto';
        const darkTheme = 'cr-cafe-resto-dark';

        function currentTheme() {
            return document.documentElement.dataset.theme === darkTheme ? darkTheme : lightTheme;
        }

        function storedTheme() {
            try {
                return localStorage.getItem(storageKey);
            } catch (error) {
                return null;
            }
        }

        function rememberTheme(theme) {
            try {
                localStorage.setItem(storageKey, theme);
            } catch (error) {
                // Theme still changes for the current page when storage is unavailable.
            }
        }

        function initialTheme() {
            const theme = storedTheme();

            if ([lightTheme, darkTheme].includes(theme)) {
                return theme;
            }

            return window.matchMedia('(prefers-color-scheme: dark)').matches ? darkTheme : lightTheme;
        }

        function syncThemeToggle(theme) {
            document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
                const isDark = theme === darkTheme;
                const icon = button.querySelector('[data-theme-toggle-icon]');

                button.setAttribute('aria-pressed', String(isDark));
                button.setAttribute('aria-label', isDark ? 'Switch to light mode' : 'Switch to dark mode');
                button.setAttribute('title', isDark ? 'Light mode' : 'Dark mode');

                if (icon) {
                    icon.classList.toggle('ri-sun-line', isDark);
                    icon.classList.toggle('ri-moon-line', !isDark);
                }
            });
        }

        function applyTheme(theme) {
            document.documentElement.dataset.theme = theme;
            document.documentElement.classList.toggle('dark', theme === darkTheme);
            syncThemeToggle(theme);
        }

        function toggleTheme() {
            const nextTheme = currentTheme() === darkTheme ? lightTheme : darkTheme;

            rememberTheme(nextTheme);
            applyTheme(nextTheme);
        }

        window.SaungTheme = {
            applyTheme,
            toggleTheme,
            currentTheme,
            lightTheme,
            darkTheme,
        };

        applyTheme(initialTheme());

        document.addEventListener('DOMContentLoaded', () => applyTheme(initialTheme()));
        document.addEventListener('livewire:navigated', () => applyTheme(initialTheme()));
        document.addEventListener('click', (event) => {
            const target = event.target;

            if (!(target instanceof Element)) {
                return;
            }

            const trigger = target.closest('[data-theme-toggle]');

            if (!trigger) {
                return;
            }

            event.preventDefault();
            toggleTheme();
        });
    })();
</script>
