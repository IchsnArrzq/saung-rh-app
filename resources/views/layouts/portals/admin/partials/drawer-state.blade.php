{{--
    Sidebar drawer state.

    `#admin-drawer` carries two different meanings depending on the viewport,
    which is why the server cannot render its initial value on its own:

      >= lg  `lg:drawer-open` keeps the panel on screen, so the checkbox only
             picks the width -- checked = expanded (w-72), unchecked = the
             64px icon rail.
      <  lg  the checkbox controls visibility -- checked drops the drawer over
             the page behind a scrim and locks body scroll.

    So the input renders unchecked (the only value that is safe on a phone) and
    this script promotes it to the remembered width once it knows the viewport.
    It has to run before first paint, hence an inline script placed directly
    after the input rather than a module in resources/js -- same reasoning as
    layouts/partials/theme-script.blade.php.

    Without JS, desktop falls back to the icon rail instead of the expanded
    sidebar. The rail is fully navigable, so that degrades cleanly.
--}}
<script>
    (function () {
        const storageKey = 'saung-rh-sidebar-collapsed';

        // Held in a variable on purpose: a MediaQueryList that nothing
        // references can be garbage collected, and its listeners go silent with
        // it. Registering on a throwaway `matchMedia(...)` result loses the
        // breakpoint-crossing update.
        const desktopMedia = window.matchMedia('(min-width: 64rem)'); // Tailwind `lg`

        function drawerToggle() {
            return document.getElementById('admin-drawer');
        }

        function isDesktop() {
            return desktopMedia.matches;
        }

        function storedCollapsed() {
            try {
                return localStorage.getItem(storageKey) === '1';
            } catch (error) {
                return false;
            }
        }

        function remember(collapsed) {
            try {
                localStorage.setItem(storageKey, collapsed ? '1' : '0');
            } catch (error) {
                // The current page still toggles; only the preference is lost.
            }
        }

        // The control's meaning changes with the viewport, so the accessible
        // name has to change with it: on desktop the sidebar is never hidden,
        // it only narrows to the icon rail.
        function syncToggleButtons() {
            const toggle = drawerToggle();

            if (!toggle) {
                return;
            }

            const open = toggle.checked;
            const name = isDesktop()
                ? (open ? 'Ciutkan sidebar' : 'Bentangkan sidebar')
                : (open ? 'Tutup menu samping' : 'Buka menu samping');

            document.querySelectorAll('[data-drawer-toggle]').forEach((button) => {
                button.setAttribute('aria-expanded', String(open));
                button.setAttribute('aria-label', name);
                button.setAttribute('title', name);
            });
        }

        function apply() {
            const toggle = drawerToggle();

            if (!toggle) {
                return;
            }

            toggle.checked = isDesktop() ? !storedCollapsed() : false;
            syncToggleButtons();
        }

        // Runs on every evaluation, including the re-run after a wire:navigate
        // visit swaps the body back in.
        apply();

        if (window.SaungSidebar) {
            return;
        }

        window.SaungSidebar = { apply, storageKey };

        document.addEventListener('change', (event) => {
            const toggle = event.target;

            if (!(toggle instanceof HTMLInputElement) || toggle.id !== 'admin-drawer') {
                return;
            }

            syncToggleButtons();

            // On a phone the same checkbox means "the overlay is open", which
            // is not a collapse preference worth carrying to the next visit.
            if (!isDesktop()) {
                return;
            }

            remember(!toggle.checked);
        });

        // A <label> is not keyboard-operable on its own, and at >=lg daisyUI
        // hides the checkbox with `display:none`, so it cannot be focused
        // either. Without this the sidebar cannot be collapsed by keyboard.
        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') {
                return;
            }

            const target = event.target;

            if (!(target instanceof Element) || !target.closest('[data-drawer-toggle]')) {
                return;
            }

            const toggle = drawerToggle();

            if (!toggle) {
                return;
            }

            event.preventDefault(); // Space would otherwise scroll the page.
            toggle.checked = !toggle.checked;
            toggle.dispatchEvent(new Event('change', { bubbles: true }));
        });

        // apply() runs before paint, which is earlier than the topbar exists --
        // the toggle button lives in `.drawer-content`, further down the
        // document. Its labelling is not visual, so it can wait for the parse
        // to finish. (After a wire:navigate swap `livewire:navigated` covers
        // it, since the body is complete by then.)
        document.addEventListener('DOMContentLoaded', syncToggleButtons);
        document.addEventListener('livewire:navigated', apply);
        desktopMedia.addEventListener('change', apply);
    })();
</script>
