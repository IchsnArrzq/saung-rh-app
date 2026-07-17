import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';
import { Indonesian } from 'flatpickr/dist/l10n/id.js';

flatpickr.localize(Indonesian);

/**
 * Progressive enhancement for native form controls.
 *
 * Every <select> becomes a Tom Select and every date/time input becomes a
 * flatpickr, without touching the Blade markup. Opt a field (or a whole
 * subtree) out with data-enhance="off".
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

// Below this many options a search box costs more than it helps.
const SEARCH_THRESHOLD = 8;

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
// border. Strip them for the lifetime of the enhancement.
const DAISY_FIELD_CLASS = /\bselect(-bordered|-ghost|-xs|-sm|-md|-lg|-primary|-secondary|-accent|-info|-success|-warning|-error)?\b/g;
const SMALL_FIELD_CLASS = /\bselect-(xs|sm)\b/;

function enhanceSelect(el) {
    if (el.tomselect || isOptedOut(el)) {
        return;
    }

    const originalClass = el.getAttribute('class') || '';
    originalState.set(el, { originalClass });
    el.setAttribute('class', originalClass.replace(DAISY_FIELD_CLASS, '').replace(/\s+/g, ' ').trim());

    const settings = {
        allowEmptyOption: true,
        maxOptions: null,
        onChange() {
            notify(el);
        },
    };

    // Tom Select merges settings over its defaults with Object.assign, so an
    // explicit `undefined` would overwrite a default rather than fall back to
    // it -- passing `controlInput: undefined` silently removes the search box.
    // Only set these keys when there is a real value to set.
    if (el.options.length < SEARCH_THRESHOLD) {
        // `null` drops the text field, leaving a plain dropdown.
        settings.controlInput = null;
    }

    if (el.dataset.placeholder) {
        settings.placeholder = el.dataset.placeholder;
    }

    const instance = new TomSelect(el, settings);

    if (SMALL_FIELD_CLASS.test(originalClass)) {
        instance.wrapper.classList.add('ts-sm');
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
