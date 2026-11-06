/*
| x-combo: a modern dropdown drawn over a native <select>.
|
| The <select> stays in the page (hidden), so forms submit it, x-model and @change keep working, and without
| JavaScript the plain select shows. This adds a button showing the choice and a panel listing the options
| (optgroups become small headings). With more than 3 options the panel has a search box and shows 3 rows at a time,
| scrolling for the rest. Keyboard: arrows move, Enter picks, Escape closes. It opens upwards when there's no room below
| (the screen edge or a clipping card).
|
| Usage: <select x-combo ...> (inside an Alpine component).
*/
const ROW_LIMIT = 3;

let openCombo = null; // only one panel open at a time

export default function combo(Alpine) {
    Alpine.directive('combo', (select, _directive, { effect, cleanup }) => {
        const wrap = select.parentElement;
        if (getComputedStyle(wrap).position === 'static') wrap.style.position = 'relative';

        // ── The button that replaces the select on screen ──
        const button = document.createElement('button');
        button.type = 'button';
        button.className = `${select.className} combo-button`;
        button.setAttribute('aria-haspopup', 'listbox');
        button.setAttribute('aria-expanded', 'false');
        const text = document.createElement('span');
        text.className = 'combo-text';
        button.append(text);
        select.before(button);

        select.classList.add('combo-native');
        select.tabIndex = -1;
        select.setAttribute('aria-hidden', 'true');

        // Screen readers name the button after the field's label
        const label = select.closest('label')?.querySelector('span');
        if (label) {
            label.id ||= `combo-label-${Math.random().toString(36).slice(2, 8)}`;
            button.setAttribute('aria-labelledby', `${label.id} ${(text.id = `${label.id}-value`)}`);
        }

        // ── The panel ──
        const panel = document.createElement('div');
        panel.className = 'combo-panel';
        panel.hidden = true;
        const search = document.createElement('input');
        search.type = 'search';
        search.className = 'combo-search';
        search.placeholder = 'Search…';
        search.setAttribute('aria-label', 'Search the options');
        search.autocomplete = 'off';
        const list = document.createElement('div');
        list.className = 'combo-list';
        list.setAttribute('role', 'listbox');
        list.id = `combo-list-${Math.random().toString(36).slice(2, 8)}`;
        button.setAttribute('aria-controls', list.id);
        const empty = document.createElement('p');
        empty.className = 'combo-empty';
        empty.textContent = 'No matches';
        panel.append(search, list, empty);
        // Outside pop-up panels the list sits on top of the whole page (position: fixed on <body>), so no card that
        // clips its content can cut it off. Inside a pop-up (the phone filter panel) it stays in place, so the
        // panel's keyboard focus trap still includes it.
        const portal = !select.closest('[role=dialog]');
        if (portal) {
            panel.classList.add('combo-panel--fixed');
            document.body.append(panel);
        } else {
            wrap.append(panel);
        }
        // The panel sits inside the field's <label>: stop a click in it from also "clicking" the label (reopening)
        panel.addEventListener('click', (event) => event.target !== search && event.preventDefault());

        let items = []; // [{ option, row }]
        let active = -1;

        const sync = () => {
            const option = select.options[select.selectedIndex];
            text.textContent = option ? option.textContent.trim() : '';
            button.disabled = select.disabled;
            // A field error (set on the select by the form code) shows on the button too
            if (select.getAttribute('aria-invalid') === 'true') button.setAttribute('aria-invalid', 'true');
            else button.removeAttribute('aria-invalid');
        };

        // Rows from the select's current options (they can change, e.g. areas following the chosen city)
        const build = () => {
            list.replaceChildren();
            items = [];
            const add = (option) => {
                if (option.hidden) return;
                const row = document.createElement('div');
                row.className = 'combo-option';
                row.setAttribute('role', 'option');
                row.id = `${list.id}-${items.length}`;
                const label = document.createElement('span');
                label.className = 'combo-option-text';
                label.textContent = option.textContent.trim();
                row.title = label.textContent; // the full name when a long one is cut short
                row.append(label);
                row.setAttribute('aria-selected', String(option.selected));
                if (option.disabled) row.setAttribute('aria-disabled', 'true');
                row.addEventListener('mousedown', (event) => event.preventDefault()); // keep focus in the panel
                row.addEventListener('click', () => choose(option));
                items.push({ option, row });
                return row;
            };
            for (const child of select.children) {
                if (child.tagName === 'OPTGROUP') {
                    const rows = [...child.children].filter((el) => el.tagName === 'OPTION').map(add).filter(Boolean);
                    if (!rows.length) continue;
                    const heading = document.createElement('div');
                    heading.className = 'combo-group';
                    heading.textContent = child.label;
                    heading.setAttribute('role', 'presentation');
                    list.append(heading, ...rows);
                } else if (child.tagName === 'OPTION') {
                    const row = add(child);
                    if (row) list.append(row);
                }
            }
            // More than 3 choices: search, and 3 rows at a time
            const many = items.length > ROW_LIMIT;
            search.hidden = !many;
            panel.classList.toggle('combo-panel--many', many);
        };

        const filter = () => {
            const needle = search.value.trim().toLowerCase();
            let shown = 0;
            items.forEach(({ row }) => {
                const match = !needle || row.textContent.toLowerCase().includes(needle);
                row.hidden = !match;
                shown += match ? 1 : 0;
            });
            // A group heading hides when none of its options match
            list.querySelectorAll('.combo-group').forEach((heading) => {
                let next = heading.nextElementSibling;
                let any = false;
                while (next && !next.classList.contains('combo-group')) {
                    any ||= !next.hidden;
                    next = next.nextElementSibling;
                }
                heading.hidden = !any;
            });
            empty.hidden = shown > 0;
            const visible = visibleItems();
            setActive(visible.length ? items.indexOf(visible[0]) : -1);
        };

        const visibleItems = () => items.filter(({ row, option }) => !row.hidden && !option.disabled);

        const setActive = (index) => {
            items[active]?.row.classList.remove('is-active');
            active = index;
            const item = items[active];
            if (!item) return button.removeAttribute('aria-activedescendant');
            item.row.classList.add('is-active');
            item.row.scrollIntoView({ block: 'nearest' });
            (search.hidden ? button : search).setAttribute('aria-activedescendant', item.row.id);
        };

        const move = (step) => {
            const visible = visibleItems();
            if (!visible.length) return;
            const at = visible.indexOf(items[active]);
            const next = visible[(at + step + visible.length) % visible.length] ?? visible[0];
            setActive(items.indexOf(next));
        };

        // Open downwards, or upwards when the screen (or a card that clips its content) has no room below
        const place = () => {
            if (portal) return placeFixed();
            panel.classList.remove('combo-panel--up', 'combo-panel--end');
            // Wider than its field near the right edge: line it up with the field's right side instead
            if (panel.getBoundingClientRect().right > document.documentElement.clientWidth - 8) {
                panel.classList.add('combo-panel--end');
            }
            const rect = button.getBoundingClientRect();
            let top = 0;
            let bottom = innerHeight;
            for (let el = wrap.parentElement; el && el !== document.body; el = el.parentElement) {
                const style = getComputedStyle(el);
                if (style.display !== 'contents' && /(hidden|auto|scroll|clip)/.test(style.overflow + style.overflowY)) {
                    const box = el.getBoundingClientRect();
                    if (!box.height) continue; // no box of its own: clips nothing
                    top = Math.max(top, box.top);
                    bottom = Math.min(bottom, box.bottom);
                }
            }
            const needed = panel.offsetHeight + 8;
            if (bottom - rect.bottom < needed && rect.top - top > bottom - rect.bottom) {
                panel.classList.add('combo-panel--up');
            }
        };

        // On top of the page: below the field, or above it when the screen has no room below; never off the sides
        const placeFixed = () => {
            const rect = button.getBoundingClientRect();
            const screenWidth = document.documentElement.clientWidth;
            const width = Math.min(Math.max(rect.width, 240), screenWidth - 16);
            panel.style.width = `${width}px`;
            panel.style.left = `${Math.max(8, Math.min(rect.left, screenWidth - width - 8))}px`;
            const height = panel.offsetHeight;
            const below = innerHeight - rect.bottom;
            const up = below < height + 8 && rect.top > below;
            panel.style.top = `${up ? rect.top - height - 8 : rect.bottom + 8}px`;
            panel.classList.toggle('combo-panel--up', up);
        };

        const open = () => {
            if (button.disabled || !panel.hidden) return;
            if (openCombo && openCombo !== close) openCombo();
            openCombo = close;
            build();
            search.value = '';
            filter();
            panel.hidden = false;
            button.setAttribute('aria-expanded', 'true');
            const selected = items.findIndex(({ option }) => option.selected);
            if (selected >= 0) setActive(selected);
            place();
            (search.hidden ? button : search).focus({ preventScroll: true }); // arrows, Enter and Escape work straight away
        };

        function close(refocus = false) {
            if (panel.hidden) return;
            panel.hidden = true;
            button.setAttribute('aria-expanded', 'false');
            button.removeAttribute('aria-activedescendant');
            if (openCombo === close) openCombo = null;
            if (refocus) button.focus({ preventScroll: true });
        }

        const choose = (option) => {
            if (option.disabled) return;
            const changed = select.value !== option.value;
            select.value = option.value;
            sync();
            close(true);
            if (changed) {
                // x-model listens for "change" on selects; @change handlers (e.g. submit the filter form) run too
                select.dispatchEvent(new Event('input', { bubbles: true }));
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }
        };

        // ── Events ──
        button.addEventListener('click', () => (panel.hidden ? open() : close()));
        button.addEventListener('keydown', (event) => {
            if (['ArrowDown', 'ArrowUp'].includes(event.key) || (panel.hidden && [' ', 'Enter'].includes(event.key))) {
                event.preventDefault();
                if (panel.hidden) open();
                else move(event.key === 'ArrowUp' ? -1 : 1);
            } else if (!panel.hidden) {
                keys(event);
            }
        });
        search.addEventListener('input', filter);
        search.addEventListener('keydown', keys);
        function keys(event) {
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                move(event.key === 'ArrowUp' ? -1 : 1);
            } else if (event.key === 'Enter') {
                event.preventDefault();
                if (items[active]) choose(items[active].option);
            } else if (event.key === 'Escape') {
                event.preventDefault();
                event.stopPropagation(); // closes only the dropdown, not the filter panel around it
                close(true);
            } else if (event.key === 'Tab') {
                close();
            }
        }
        const outside = (event) => {
            if (!panel.hidden && !panel.contains(event.target) && !button.contains(event.target)) close();
        };
        document.addEventListener('pointerdown', outside);
        const follow = () => !panel.hidden && place();
        addEventListener('resize', follow);
        addEventListener('scroll', follow, { capture: true, passive: true }); // the page or a panel scrolls

        // Keep the button in step with the select: changes made by x-model, the page, or new options
        select.addEventListener('change', sync);
        const observer = new MutationObserver(() => {
            sync();
            if (!panel.hidden) {
                build();
                filter();
            }
        });
        observer.observe(select, { childList: true, subtree: true, attributes: true, attributeFilter: ['disabled', 'aria-invalid', 'selected'] });
        if (select._x_model) effect(() => {
            select._x_model.get();
            queueMicrotask(sync);
        });
        sync();

        cleanup(() => {
            observer.disconnect();
            document.removeEventListener('pointerdown', outside);
            removeEventListener('resize', follow);
            removeEventListener('scroll', follow, { capture: true });
            close();
            button.remove();
            panel.remove();
            select.classList.remove('combo-native');
            select.removeAttribute('tabindex');
            select.removeAttribute('aria-hidden');
        });
    });
}
