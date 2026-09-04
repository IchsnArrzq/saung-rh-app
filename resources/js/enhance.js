import TomSelect from 'tom-select';
import flatpickr from 'flatpickr';
import { Indonesian } from 'flatpickr/dist/l10n/id.js';

// Their stylesheets are imported from resources/css/app.css instead of here:
// bundled through JS they land in a second <link> that loads *after* app.css,
// where every vendor rule beats our same-specificity theme overrides.

flatpickr.localize(Indonesian);

/**
 * Progressive enhancement for native form controls.
 *
 * Every <select> becomes a Tom Select -- searchable, clearable and keyboard
 * driven -- and every date/time input becomes a flatpickr, without touching
 * the Blade markup. Opt a field (or a whole subtree) out with
 * data-enhance="off".
 *
 * Both libraries inject sibling nodes that Livewire's morph would treat as
 * unexpected DOM, so enhancement is torn down before a morph and reapplied
 * afterwards -- at diff time the DOM matches the server's HTML exactly.
 */

const SELECT_SELECTOR = 'select:not([data-enhance="off"]):not([multiple][size])';
const DATE_SELECTOR = 'input[type="date"], input[type="datetime-local"], input[type="time"]';

// flatpickr builds its month picker out of a real <select>, and Tom Select
// renders <input>s of its own. Neither may be enhanced recursively.
const GENERATED_DOM = '.flatpickr-calendar, .ts-wrapper, .ts-dropdown';

// Native value formats, preserved so the server contract never changes.
const FLATPICKR_CONFIG = {
    date: {
        dateFormat: 'Y-m-d',
        altFormat: 'j F Y',
    },
    'datetime-local': {
        dateFormat: 'Y-m-d\\TH:i',
        altFormat: 'j F Y, H:i',
        enableTime: true,
        time_24hr: true,
    },
    time: {
        dateFormat: 'H:i',
        altFormat: 'H:i',
        enableTime: true,
        noCalendar: true,
        time_24hr: true,
    },
};

/**
 * Bookkeeping for what an element looked like before enhancement.
 *
 * This deliberately lives in a WeakMap rather than in data-* attributes:
 * Livewire's morph reconciles attributes against the server's HTML and strips
 * anything the server didn't render, which would erase the bookkeeping
 * mid-flight and leave the element impossible to tear down. Morph patches
 * elements in place, so the element identity -- and this map -- survives.
 */
const originalState = new WeakMap();

function isOptedOut(el) {
    return el.closest('[data-enhance="off"]') !== null || el.closest(GENERATED_DOM) !== null;
}

/**
 * Livewire's wire:model binds on `input`; Tom Select and flatpickr only emit
 * `change`. Fire both so bindings stay in sync either way.
 */
function notify(el) {
    el.dispatchEvent(new Event('input', { bubbles: true }));
    el.dispatchEvent(new Event('change', { bubbles: true }));
}

/**
 * Read a field's authoritative value out of its Livewire component.
 *
 * Livewire renders wire:model fields without a `value` attribute and hydrates
 * them client-side, so a field's value survives a morph only by virtue of the
 * live DOM node being left alone. Tearing the enhancement down and rebuilding
 * it loses that, while the component's own state stays correct -- so re-read
 * the value from there after enhancing rather than trusting the DOM.
 */
const MODEL_ATTRIBUTE = /^wire:model(\.|$)/;

function livewireValue(el) {
    const attribute = el.getAttributeNames().find((name) => MODEL_ATTRIBUTE.test(name));
    if (!attribute || !window.Livewire) {
        return undefined;
    }

    const host = el.closest('[wire\\:id]');
    if (!host) {
        return undefined;
    }

    // Livewire.find() returns the $wire proxy itself, which exposes get()
    // directly -- there is no nested `.$wire` to reach through.
    const wire = window.Livewire.find(host.getAttribute('wire:id'));

    return wire?.get?.(el.getAttribute(attribute));
}

// daisyUI field classes carry their own height, border and dropdown arrow.
// Tom Select copies the original element's classes onto its wrapper, where
// those would stack on top of .ts-control's styling and produce a double
// border. Strip them for the lifetime of the enhancement -- then re-express
// the ones that still carry meaning (size, error state) as the `ts-*` classes
// the stylesheet understands.
const DAISY_FIELD_CLASS =
    /\bselect(-bordered|-ghost|-xs|-sm|-md|-lg|-xl|-primary|-secondary|-accent|-info|-success|-warning|-error)?\b/g;

const SIZE_CLASS = {
    'select-xs': 'ts-xs',
    'select-sm': 'ts-sm',
    'select-lg': 'ts-lg',
    'select-xl': 'ts-xl',
};

/** Translate the daisyUI classes we strip into their `ts-*` equivalents. */
function wrapperClasses(originalClass) {
    const classes = [];

    for (const [daisy, ts] of Object.entries(SIZE_CLASS)) {
        if (new RegExp('\\b' + daisy + '\\b').test(originalClass)) {
            classes.push(ts);
        }
    }

    if (/\bselect-error\b/.test(originalClass)) {
        classes.push('ts-error');
    }

    return classes;
}

/**
 * The ancestors that would clip a dropdown rendered inside the wrapper.
 *
 * Tom Select renders the menu as a child of its own wrapper, so a scrollable
 * ancestor cuts it off -- `<x-data-table>` wraps every table in
 * `overflow-x-auto`, and a box that scrolls on one axis clips the other, which
 * left the menu on the last table row all but invisible. daisyUI's
 * `.modal-box` (`overflow-y: auto`) does the same. Such fields get their
 * dropdown re-parented to <body>, and these are the nodes whose scrolling it
 * then has to follow.
 */
function clippingAncestors(el) {
    const clippers = [];

    for (let node = el.parentElement; node && node !== document.body; node = node.parentElement) {
        const { overflowX, overflowY } = getComputedStyle(node);

        if (overflowX !== 'visible' || overflowY !== 'visible') {
            clippers.push(node);
        }
    }

    return clippers;
}

/**
 * Tom Select only ever drops the menu downwards. For a field near the bottom of
 * the window -- the last rows of a table, exactly the ones re-parenting just
 * rescued -- that runs off the fold. Flip it above the control when there is no
 * room below and more room above.
 *
 * Overriding the instance method rather than listening for `dropdown_open` is
 * deliberate: Tom Select re-runs `positionDropdown()` on window scroll and
 * resize, and anything it recomputes there would undo a flip applied elsewhere.
 * `open()` gives the dropdown `display: block` before positioning it, so it is
 * measurable by the time this runs.
 */
function keepDropdownInView(instance, clippers) {
    const position = instance.positionDropdown.bind(instance);

    instance.positionDropdown = () => {
        position();

        const control = instance.control.getBoundingClientRect();
        const height = instance.dropdown.offsetHeight;
        const roomBelow = window.innerHeight - control.bottom;

        if (height <= roomBelow || control.top <= roomBelow) {
            return;
        }

        // `top` is a document-space offset that the dropdown's own margin then
        // shifts down by, so the gap has to be subtracted twice to end up the
        // same distance above the control as it would sit below it.
        const gap = parseFloat(getComputedStyle(instance.dropdown).marginTop) || 0;

        instance.dropdown.style.top = `${control.top + window.scrollY - height - gap * 2}px`;
    };

    const reposition = () => {
        if (instance.isOpen) {
            instance.positionDropdown();
        }
    };

    // Re-run once the menu is on screen. Tom Select positions from inside
    // open(), which some paths reach before the option list has been rendered
    // -- measuring an all-but-empty dropdown there would decide it fits.
    // positionDropdown() recomputes from scratch, so repeating it is free.
    instance.on('dropdown_open', reposition);

    // Tom Select watches only the window for scroll, but the field that needed
    // re-parenting is by definition inside something that scrolls. Bind to
    // those boxes rather than to `document`: scroll does not bubble, and a
    // document-level listener would outlive the page a wire:navigate replaces.
    clippers.forEach((node) => node.addEventListener('scroll', reposition, { passive: true }));
    instance.on('destroy', () => {
        clippers.forEach((node) => node.removeEventListener('scroll', reposition));
    });
}

function enhanceSelect(el) {
    if (el.tomselect || isOptedOut(el)) {
        return;
    }

    const originalClass = el.getAttribute('class') || '';
    originalState.set(el, { originalClass });
    el.setAttribute('class', originalClass.replace(DAISY_FIELD_CLASS, '').replace(/\s+/g, ' ').trim());

    // `dropdown_input` puts the search box at the top of the dropdown instead
    // of inside the control, so the chosen value stays readable while typing.
    const plugins = { dropdown_input: {} };

    // A required field has no valid empty state, so a clear button there could
    // only ever produce a validation error. `<x-select :required>` marks itself
    // with data-clearable="false" even when it leaves the HTML attribute off.
    if (!el.required && el.dataset.clearable !== 'false') {
        plugins.clear_button = { title: 'Kosongkan pilihan' };
    }

    const settings = {
        allowEmptyOption: true,
        maxOptions: null,
        plugins,
        placeholder: el.dataset.placeholder || 'Pilih...',
        render: {
            no_results: () => '<div class="no-results">Tidak ada hasil yang cocok.</div>',
        },
        onChange() {
            notify(el);
        },
    };

    // Tom Select keeps a body-parented dropdown aligned on window scroll and
    // resize by itself; `ts-dropdown-floating` is what lifts it clear of
    // daisyUI's `.modal` (z-index 999), which it no longer nests inside.
    const clippers = clippingAncestors(el);

    if (clippers.length > 0) {
        settings.dropdownParent = 'body';
        settings.dropdownClass = 'ts-dropdown ts-dropdown-floating';
    }

    const instance = new TomSelect(el, settings);

    // Only a body-parented dropdown is positioned by script at all; the rest is
    // laid out by CSS directly under its control and cannot be moved from here.
    if (clippers.length > 0) {
        keepDropdownInView(instance, clippers);
    }

    instance.control_input.placeholder = el.dataset.searchPlaceholder || 'Cari...';
    instance.control_input.setAttribute('aria-label', 'Cari pilihan');

    const extra = wrapperClasses(originalClass);
    if (extra.length > 0) {
        instance.wrapper.classList.add(...extra);
    }

    // Silent: this reflects existing state, it isn't a user edit.
    const state = livewireValue(el);
    if (state !== undefined && state !== null && String(state) !== el.value) {
        instance.setValue(String(state), true);
    }
}

function destroySelect(el) {
    if (!el.tomselect) {
        return;
    }

    el.tomselect.destroy();

    const { originalClass } = originalState.get(el) || {};
    if (originalClass !== undefined) {
        el.setAttribute('class', originalClass);
        originalState.delete(el);
    }
}

function enhanceDate(el) {
    // A destroyed flatpickr leaves `_flatpickr` pointing at a dead instance, so
    // the property alone doesn't mean "already enhanced" -- `config` is only
    // present while the instance is live.
    if (el._flatpickr?.config || isOptedOut(el)) {
        return;
    }

    const config = FLATPICKR_CONFIG[el.type];
    if (!config) {
        return;
    }

    // Remember the native type so destroy() can put it back, and drop it now so
    // the browser's own picker doesn't fight flatpickr's.
    originalState.set(el, { nativeType: el.type });
    el.type = 'text';

    const instance = flatpickr(el, {
        ...config,
        altInput: true,
        altInputClass: `${el.className} flatpickr-alt`,
        allowInput: true,
        onChange() {
            notify(el);
        },
    });

    // `false` suppresses onChange: this reflects existing state, not an edit.
    const state = livewireValue(el);
    if (state) {
        instance.setDate(state, false);
    }
}

function destroyDate(el) {
    const instance = el._flatpickr;

    if (!instance) {
        return;
    }

    if (instance.config) {
        instance.destroy();
    }

    // flatpickr's own destroy() does not clear this back-reference; leaving the
    // dead instance in place would make enhanceDate() skip the element forever.
    delete el._flatpickr;

    const { nativeType } = originalState.get(el) || {};
    if (nativeType) {
        el.type = nativeType;
        originalState.delete(el);
    }
}

/** Run `fn` over `root` itself plus every descendant matching `selector`. */
function walk(root, selector, fn) {
    if (root instanceof Element && root.matches(selector)) {
        fn(root);
    }

    root.querySelectorAll?.(selector).forEach(fn);
}

export function enhance(root = document) {
    walk(root, SELECT_SELECTOR, enhanceSelect);
    walk(root, DATE_SELECTOR, enhanceDate);
}

export function teardown(root = document) {
    // Both pass over every candidate and decide from the live JS instance
    // (el.tomselect / el._flatpickr) rather than any attribute, since morph
    // may already have stripped attributes we added.
    walk(root, 'select', destroySelect);
    walk(root, 'input', destroyDate);
}

document.addEventListener('livewire:init', () => {
    Livewire.hook('morph', ({ el }) => teardown(el));
    Livewire.hook('morphed', ({ el }) => enhance(el));
});

// Fires on first load and after every wire:navigate visit.
document.addEventListener('livewire:navigated', () => enhance());
