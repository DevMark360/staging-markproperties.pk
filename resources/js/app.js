import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';
import combo from './combo.js';
import intersect from '@alpinejs/intersect';
import squeeze from './squeeze.js';

Alpine.plugin(collapse);
Alpine.plugin(combo); // x-combo: modern, searchable dropdowns over the native selects
Alpine.plugin(focus); // x-trap: keyboard focus stays inside an open pop-up panel and returns to its button after (L3)
Alpine.plugin(intersect);

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// Home page areas: one open panel, the rest squeezed into narrower panels and slats (resources/js/squeeze.js)
Alpine.data('squeeze', squeeze);

// Homepage hero: background slides + featured launch card, advanced by the progress bar
Alpine.data('heroSlider', (count) => ({
    i: 0,
    count,
    paused: false, // pointer or keyboard focus on the card
    stopped: false, // the pause button (H1)
    autoplay: !reducedMotion && count > 1,
    // Slides whose photo may load: the current one and the next (H2), so the page doesn't download every photo up front
    ready: { 0: true, 1: true },
    init() {
        this.$watch('i', (i) => {
            this.ready = { ...this.ready, [i]: true, [(i + 1) % this.count]: true };
        });
    },
    next() {
        this.i = (this.i + 1) % this.count;
    },
    prev() {
        this.i = (this.i - 1 + this.count) % this.count;
    },
}));

// Segmented control with a sliding selected background and a hover highlight
Alpine.data('segmented', (initial) => ({
    value: initial,
    indicator: { left: 0, width: 0 },
    hover: null,
    // Declared here so it is stored on this component — Alpine puts undeclared properties on the outermost scope
    root: null,
    init() {
        // Keep the group element: inside methods `this.$el` is whichever element triggered the call
        this.root = this.$el;
        this.measure();
        new ResizeObserver(() => this.measure()).observe(this.root);
        document.fonts?.ready.then(() => this.measure());
    },
    measure(attempt = 0) {
        const el = this.root.querySelector(`[data-value="${this.value}"]`);
        if (!el) return;
        // Not laid out yet (e.g. inside a panel that is still hidden): try again on the next frames
        if (!el.offsetWidth && attempt < 60) {
            requestAnimationFrame(() => this.measure(attempt + 1));
            return;
        }
        this.indicator = { left: el.offsetLeft, width: el.offsetWidth };
    },
    select(value) {
        this.value = value;
        this.measure();
    },
    // Arrow keys (radio-group behaviour): move to the previous / next choice, wrapping round, and focus it
    step(direction) {
        const options = [...this.root.querySelectorAll('[data-value]')];
        const next = options[(options.findIndex((el) => el.dataset.value === this.value) + direction + options.length) % options.length];
        this.select(next.dataset.value);
        next.focus();
    },
    highlight(el) {
        this.hover = el ? { left: el.offsetLeft, width: el.offsetWidth } : null;
    },
}));

// Horizontal card carousel with arrows, scroll progress and an optional filter
Alpine.data('carousel', () => ({
    filter: '',
    atStart: true,
    atEnd: false,
    progress: 0,
    init() {
        this.$nextTick(() => this.update());
        this.$watch('filter', () => {
            this.$refs.track.scrollTo({ left: 0 });
            this.$nextTick(() => this.update());
        });
    },
    update() {
        const track = this.$refs.track;
        const max = track.scrollWidth - track.clientWidth;
        this.atStart = track.scrollLeft < 8;
        this.atEnd = track.scrollLeft > max - 8;
        this.progress = max > 0 ? track.scrollLeft / max : 1;
    },
    slide(direction) {
        const track = this.$refs.track;
        track.scrollBy({ left: direction * track.clientWidth * 0.85, behavior: reducedMotion ? 'auto' : 'smooth' });
    },
}));

/*
| Contents menus (blog article, buyer's guide): `current` is the last section whose top is above 30% of the
| screen, worked out from the scroll position (once per frame), so it's right even after a quick jump. One copy (T2).
*/
window.scrollSpy = (selector, first) => ({
    current: first,
    init() {
        const sections = [...document.querySelectorAll(selector)];
        let queued = false;
        const update = () => {
            queued = false;
            const line = innerHeight * 0.3;
            this.current = (sections.filter((el) => el.getBoundingClientRect().top <= line).pop() ?? sections[0])?.id ?? this.current;
        };
        addEventListener('scroll', () => {
            if (!queued) {
                queued = true;
                requestAnimationFrame(update);
            }
        }, { passive: true });
        update();
    },
});

// Copies text; resolves true only if it really reached the clipboard (T3: no false "Link copied")
window.copyText = async (text) => {
    try {
        await navigator.clipboard.writeText(text);
        return true;
    } catch {
        return false; // no clipboard access (older browser, or the page isn't on HTTPS)
    }
};

// Posts a form in the background. Resolves to { data } on success or { error } with a readable message.
async function postForm(form) {
    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: { Accept: 'application/json' },
            body: new FormData(form),
        });
        const data = await response.json().catch(() => ({}));
        if (response.ok) {
            clearFieldErrors(form);
            return { ok: true, data };
        }
        if (response.status === 429) {
            return { ok: false, error: 'Too many attempts. Please try again in a minute.' };
        }
        if (response.status === 419) {
            // The page was open so long that its security token expired (C12): nothing was sent
            return { ok: false, error: 'This page has expired. Please refresh the page and send it again.' };
        }
        // Validation errors go under their own fields; only what can't be shown there stays in the line under the button
        const leftover = showFieldErrors(form, data.errors ?? {});
        // ok:false even when every message sits under its field (error is then empty): the form must stay on screen
        return { ok: false, error: leftover ?? (data.errors ? '' : 'Something went wrong. Please try again.') };
    } catch {
        return { ok: false, error: 'Could not connect. Please check your internet and try again.' };
    }
}

/*
| Field errors: a red border on each invalid field and its message right under it (aria-invalid +
| aria-describedby), focus on the first one, and each error clears as soon as that field is edited.
| Returns the first message that has no visible field to sit under (e.g. a hidden step), or null.
*/
function showFieldErrors(form, errors) {
    clearFieldErrors(form);
    let first = null;
    let leftover = null;

    for (const [name, messages] of Object.entries(errors)) {
        const field = form.elements[name] ?? form.elements[name + '[]'];
        const controls = field instanceof RadioNodeList ? [...field] : field ? [field] : [];
        const control = controls[0];
        if (!control || control.type === 'hidden') {
            leftover ??= messages[0];
            continue;
        }

        const group = control.type === 'radio' || control.type === 'checkbox';
        const place = group
            ? { el: control.closest('fieldset, [role=radiogroup], [data-field]') ?? control.closest('label')?.parentElement ?? control, inside: false }
            : fieldCell(control);
        if (!place.el.offsetParent) {
            leftover ??= messages[0];
            continue;
        }

        const message = document.createElement('p');
        message.className = 'field-error';
        message.id = 'err-' + Math.random().toString(36).slice(2, 9);
        message.textContent = messages[0];
        place.inside ? place.el.append(message) : place.el.after(message);
        controls.forEach((c) => {
            c.setAttribute('aria-invalid', 'true');
            c.setAttribute('aria-describedby', message.id);
        });
        first ??= control;
    }

    if (first) {
        // After Alpine has re-rendered the form (sending -> false), or the focus is lost again
        requestAnimationFrame(() => {
            first.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'center' });
            first.focus({ preventScroll: true });
        });
    }
    if (!form.dataset.fieldErrors) {
        form.dataset.fieldErrors = '1';
        const clear = (event) => clearFieldError(form, event.target);
        form.addEventListener('input', clear);
        form.addEventListener('change', clear);
    }
    return leftover;
}

/*
| Where a field's message goes so it stays in the field's own cell: inside the field's <label>, or, when the
| field sits straight in a grid/flex row, inside a wrapper that takes over the field's place in that row.
*/
function fieldCell(control) {
    const label = control.closest('label');
    if (label && !label.contains(control.form)) {
        return { el: label, inside: true };
    }
    const parent = control.parentElement;
    const layout = getComputedStyle(parent).display;
    if (!layout.includes('grid') && !layout.includes('flex')) {
        return { el: control, inside: false };
    }
    if (parent.classList.contains('field-cell')) {
        return { el: parent, inside: true };
    }
    const cell = document.createElement('div');
    cell.className = 'field-cell';
    const style = getComputedStyle(control);
    cell.style.gridColumn = style.gridColumn;
    cell.style.flex = style.flex;
    cell.style.minWidth = '0';
    parent.insertBefore(cell, control);
    cell.append(control);
    // Neighbours in the row keep their own height instead of stretching to the field + message
    if (layout.includes('grid')) parent.style.alignItems = 'start';
    return { el: cell, inside: true };
}

function clearFieldError(form, control) {
    if (!control?.name) return;
    const field = form.elements[control.name];
    const controls = field instanceof RadioNodeList ? [...field] : [control];
    controls.forEach((c) => {
        document.getElementById(c.getAttribute('aria-describedby'))?.remove();
        c.removeAttribute('aria-invalid');
        c.removeAttribute('aria-describedby');
    });
}

function clearFieldErrors(form) {
    form.querySelectorAll('.field-error').forEach((p) => p.remove());
    form.querySelectorAll('[aria-invalid]').forEach((c) => {
        c.removeAttribute('aria-invalid');
        c.removeAttribute('aria-describedby');
    });
}

// Simple background form (launch alerts, contact): submits and shows the result inline
const backgroundForm = () => ({
    sending: false,
    done: false,
    error: '',
    result: null, // the server's reply, e.g. the Contact form's link to the chosen area's projects
    async submit(form) {
        this.sending = true;
        this.error = '';
        const { ok, data, error } = await postForm(form);
        this.sending = false;
        this.error = error ?? '';
        this.done = ok;
        this.result = ok ? data : null;
    },
});
Alpine.data('launchAlerts', backgroundForm);
Alpine.data('backgroundForm', backgroundForm);

// Project enquiry form. Any button can open it with a preset request: $dispatch('enquire', { interest: 'site-visit' })
Alpine.data('enquiryForm', (interest = 'general') => ({
    interest,
    sending: false,
    done: false,
    error: '',
    brochure: null,
    open(detail) {
        if (detail?.interest) this.interest = detail.interest;
        this.$el.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth', block: 'center' });
        this.$nextTick(() => this.$el.querySelector('input[name=name]')?.focus({ preventScroll: true }));
    },
    async submit(form) {
        this.sending = true;
        this.error = '';
        const { ok, data, error } = await postForm(form);
        this.sending = false;
        this.error = error ?? '';
        this.done = ok;
        this.brochure = data?.brochure ?? null;
    },
}));

// Full-screen photo viewer with arrow-key and swipe navigation
Alpine.data('gallery', (count) => ({
    open: false,
    i: 0,
    count,
    touchX: null,
    show(k) {
        this.i = k;
        this.open = true;
    },
    next() {
        this.i = (this.i + 1) % this.count;
    },
    prev() {
        this.i = (this.i - 1 + this.count) % this.count;
    },
    swipeStart(event) {
        this.touchX = event.touches[0].clientX;
    },
    swipeEnd(event) {
        const dx = event.changedTouches[0].clientX - (this.touchX ?? 0);
        if (Math.abs(dx) > 40) dx < 0 ? this.next() : this.prev();
        this.touchX = null;
    },
}));

// Listing page: grid / map toggle. The map library loads only the first time the map is shown.
const projectListing = (initialView, points) => ({
    view: initialView,
    map: null,
    mapFailed: false, // no WebGL, no connection, or the map service is down (C11): the page offers the grid instead
    init() {
        if (this.view === 'map') this.$nextTick(() => this.showMap());
        this.$watch('view', (view) => {
            const url = new URL(location.href);
            view === 'map' ? url.searchParams.set('view', 'map') : url.searchParams.delete('view');
            history.replaceState(null, '', url);
            if (view === 'map') this.$nextTick(() => this.showMap());
        });
    },
    async showMap() {
        if (this.map) {
            this.map.resize(); // the container was hidden while the grid was showing
            return;
        }
        this.mapFailed = false;
        try {
            const { renderMap } = await import('./projects-map.js');
            this.map = await renderMap(this.$refs.map, points, () => (this.mapFailed = true));
            this.mapReady?.();
        } catch {
            this.mapFailed = true; // e.g. the device has no WebGL, or the map code couldn't be downloaded
        }
    },
    retryMap() {
        this.map?.remove();
        this.map = null;
        this.showMap();
    },
});
Alpine.data('projectListing', projectListing);

// Developer page: the same grid / map toggle, plus filters that work in the page (a builder has few projects).
// items: [{ slug, type, place, status }]; place is the city, or the area when all projects are in one city.
Alpine.data('developerProjects', (initialView, points, items) => {
    const listing = projectListing(initialView, points);
    return {
        ...listing,
        type: '',
        place: '',
        area: '', // multi-city builders: an area within the chosen city (or any city)
        status: '',
        limit: 12,
        // Worked out once per filter change, not once per card (N5): slug -> position among the matches
        matches: [],
        rank: new Map(),
        init() {
            listing.init.call(this);
            this.match();
            // A new city clears an area that may belong to another city
            this.$watch('place', () => (this.area = ''));
            ['type', 'place', 'area', 'status'].forEach((key) => this.$watch(key, () => {
                this.limit = 12;
                this.match();
                this.map?.showOnly(this.filtered ? new Set(this.matches) : null);
            }));
        },
        match() {
            this.matches = items
                .filter((p) => (!this.type || p.type === this.type) && (!this.place || p.place === this.place) && (!this.area || p.area === this.area) && (!this.status || p.status === this.status))
                .map((p) => p.slug);
            this.rank = new Map(this.matches.map((slug, i) => [slug, i]));
        },
        mapReady() {
            if (this.filtered) this.map.showOnly(new Set(this.matches));
        },
        get filtered() {
            return Boolean(this.type || this.place || this.area || this.status);
        },
        shown(slug) {
            const rank = this.rank.get(slug);
            return rank !== undefined && rank < this.limit;
        },
        clear() {
            this.type = this.place = this.area = this.status = '';
        },
        // "View on map" in the builder card
        openMap() {
            this.view = 'map';
            this.$nextTick(() => this.$root.scrollIntoView({ behavior: 'smooth', block: 'start' }));
        },
    };
});

window.Alpine = Alpine;
Alpine.start();
