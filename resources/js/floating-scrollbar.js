/**
 * Floating overlay scrollbar.
 *
 * A native scrollbar is part of the layout: it steals width from the scroll
 * container's content box, so menu items reflow the moment it appears. Hiding
 * it and reserving a permanent gutter only trades the shift for dead space.
 *
 * Instead the thumb lives on <body> as a fixed-position element painted over
 * the container, tracking it via getBoundingClientRect. It costs no layout
 * width, so nothing around it ever moves. It fades in while scrolling or
 * hovering and fades out when idle.
 */

const MIN_THUMB_HEIGHT = 32;
const HIDE_DELAY = 900;

function attach(scroller) {
    const thumb = document.createElement('div');
    thumb.className = 'floating-scrollbar-thumb';
    thumb.setAttribute('aria-hidden', 'true');
    document.body.appendChild(thumb);

    let hideTimer = null;
    let dragging = false;

    const metrics = () => {
        const { scrollHeight, clientHeight } = scroller;
        const rect = scroller.getBoundingClientRect();
        const thumbHeight = Math.max(MIN_THUMB_HEIGHT, (clientHeight / scrollHeight) * rect.height);

        return {
            rect,
            thumbHeight,
            maxScroll: scrollHeight - clientHeight,
            maxOffset: rect.height - thumbHeight,
        };
    };

    const render = () => {
        // Nothing to scroll, or the drawer is closed/off-screen.
        if (scroller.scrollHeight <= scroller.clientHeight || scroller.clientHeight === 0) {
            thumb.classList.remove('is-visible');
            return;
        }

        const { rect, thumbHeight, maxScroll, maxOffset } = metrics();
        const progress = maxScroll > 0 ? scroller.scrollTop / maxScroll : 0;
        const x = rect.right - 8;
        const y = rect.top + progress * maxOffset;

        thumb.style.height = `${thumbHeight}px`;
        thumb.style.transform = `translate3d(${x}px, ${y}px, 0)`;
    };

    const reveal = () => {
        render();

        if (scroller.scrollHeight <= scroller.clientHeight) {
            return;
        }

        thumb.classList.add('is-visible');
        clearTimeout(hideTimer);

        if (!dragging) {
            hideTimer = setTimeout(() => {
                if (!scroller.matches(':hover')) {
                    thumb.classList.remove('is-visible');
                }
            }, HIDE_DELAY);
        }
    };

    scroller.addEventListener('scroll', reveal, { passive: true });
    scroller.addEventListener('mouseenter', reveal);
    scroller.addEventListener('mouseleave', () => {
        if (!dragging) {
            thumb.classList.remove('is-visible');
        }
    });

    thumb.addEventListener('pointerdown', (event) => {
        event.preventDefault();
        dragging = true;
        thumb.setPointerCapture(event.pointerId);
        thumb.classList.add('is-dragging');

        const { rect, thumbHeight, maxScroll, maxOffset } = metrics();
        const grabOffset = event.clientY - (thumb.getBoundingClientRect().top);

        const onMove = (moveEvent) => {
            const offset = moveEvent.clientY - rect.top - grabOffset;
            const clamped = Math.min(Math.max(offset, 0), maxOffset);
            scroller.scrollTop = maxOffset > 0 ? (clamped / maxOffset) * maxScroll : 0;
        };

        const onUp = () => {
            dragging = false;
            thumb.classList.remove('is-dragging');
            thumb.releasePointerCapture(event.pointerId);
            thumb.removeEventListener('pointermove', onMove);
            thumb.removeEventListener('pointerup', onUp);
            reveal();
        };

        thumb.addEventListener('pointermove', onMove);
        thumb.addEventListener('pointerup', onUp);
    });

    // The thumb tracks the container's box, so anything that moves or resizes
    // it has to trigger a repaint.
    const resizeObserver = new ResizeObserver(render);
    resizeObserver.observe(scroller);
    window.addEventListener('resize', render, { passive: true });
    window.addEventListener('scroll', render, { passive: true, capture: true });

    // Expanding a menu group changes scrollHeight without firing scroll.
    const observer = new MutationObserver(render);
    observer.observe(scroller, {
        childList: true,
        subtree: true,
        attributes: true,
    });

    render();

    return () => {
        clearTimeout(hideTimer);
        observer.disconnect();
        resizeObserver.disconnect();
        window.removeEventListener('resize', render);
        window.removeEventListener('scroll', render, { capture: true });
        thumb.remove();
    };
}

let instances = [];

function init() {
    // A wire:navigate visit swaps the sidebar out; drop thumbs whose scroller
    // is gone so they don't pile up on <body>.
    instances = instances.filter(({ el, detach }) => {
        if (document.contains(el)) {
            return true;
        }

        detach();

        return false;
    });

    document.querySelectorAll('[data-floating-scrollbar]').forEach((el) => {
        if (instances.some((instance) => instance.el === el)) {
            return;
        }

        instances.push({ el, detach: attach(el) });
    });
}

document.addEventListener('DOMContentLoaded', init);
document.addEventListener('livewire:navigated', init);
