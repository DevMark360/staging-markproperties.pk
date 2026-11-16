// "Squeeze" carousel (home page areas), after 21st.dev's carousel-squeeze, rebuilt for Alpine.
//
// The row is four columns and a tail of thin slats, and it is a strip that slides rather than a ring that turns.
// Column 0 is the open panel (a 16:9 block, give or take); columns 1-3 share out what's left; anything else is a
// slat. Opening a panel adds cards to the end of the strip and slides it left, so the old open panel narrows to a
// slat on its way out of the left edge. Once the movement ends, the strip is trimmed back to what's on show.
// Widths are worked out in CSS (container query units), so nothing is measured in JavaScript.

const SHARES = [-0.06, 0.61, 0.3, 0.15]; // resting share of the spare room per column
const STRETCHED = [0, 0.71, 0.4, 0.25]; // the column under the pointer takes more
const SQUEEZED = [-0.12, 0.59, 0.28, 0.13]; // and the others give a little up

const SLAT = 8; // px
const SLAT_GAP = 8; // px
const GAP = 16; // px

const reducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches;

export default (slides, duration = 1000) => ({
    slides,
    count: slides.length,
    ms: reducedMotion() ? 0 : duration,
    slats: Math.max(1, Math.min(3, slides.length - 4)),
    seed: 0,
    cards: [],
    column: 0, // column of the strip's first card; goes negative while a step forward is under way
    slid: 0, // how far the strip is moved, in slats
    still: false, // true for the frame where the strip changes without being seen to
    hover: -1,
    forward: true,
    timers: [],

    init() {
        const visible = 4 + this.slats;
        this.cards = Array.from({ length: visible }, (_, p) => ({ key: this.seed++, slide: this.wrap(p) }));
    },

    wrap(i) {
        return ((i % this.count) + this.count) % this.count;
    },

    get open() {
        return this.cards[-this.column]?.slide ?? 0;
    },

    col(place) {
        return place + this.column;
    },

    share(col) {
        if (this.hover < 0 || this.hover > 3 || !this.ms) return SHARES[col];
        return this.hover === col ? STRETCHED[col] : SQUEEZED[col];
    },

    width(col) {
        if (col < 0 || col > 3) return `${SLAT}px`;
        if (col === 0) return `calc(var(--sq-hero) + var(--sq-room) * ${this.share(0)})`;
        return `calc(var(--sq-room) * ${this.share(col)})`;
    },

    vars() {
        return {
            '--sq-ms': `${this.ms}ms`,
            '--sq-room': `calc(100cqi - var(--sq-hero) - ${this.slats * (SLAT_GAP + SLAT)}px - ${3 * GAP}px)`,
        };
    },

    stripStyle() {
        return {
            transform: `translateX(${this.slid * (SLAT + GAP)}px)`,
            transition: this.still ? 'none' : 'transform var(--sq-ms) var(--ease-out-expo)',
        };
    },

    cardStyle(place) {
        const col = this.col(place);
        const width = this.width(col);

        return {
            width,
            marginLeft: place === 0 ? '0px' : col < 4 ? `${GAP}px` : `${SLAT_GAP}px`,
            borderRadius: `min(var(--sq-radius), calc(${width} / 2))`,
            transitionProperty: 'width, margin-left, border-radius',
            transitionDuration: this.still ? '0s' : 'var(--sq-ms)',
            transitionTimingFunction: 'var(--ease-out-expo)',
        };
    },

    // Hold transitions for two frames, so a change that must not be seen isn't animated
    freeze(then) {
        this.still = true;
        requestAnimationFrame(() =>
            requestAnimationFrame(() => {
                this.still = false;
                then?.();
            }),
        );
    },

    step(by) {
        if (this.count < 2 || !by) return;
        this.timers.forEach(clearTimeout);
        this.timers = [];

        if (by > 0) {
            this.forward = true;
            // The incoming panels join the tail first, so the end of the row is never a slat short
            const last = this.cards[this.cards.length - 1].slide;
            for (let k = 0; k < by; k++) this.cards.push({ key: this.seed++, slide: this.wrap(last + 1 + k) });
            this.column -= by;
            this.slid -= by;
        } else {
            // Going back the strip grows at the front, which shoves everything right: move it left by the same
            // amount with no transition, then let it ease home. Finish any step forward still under way first.
            if (this.column !== 0 || this.slid !== 0) this.settle(false);
            this.forward = false;
            const first = this.cards[0].slide;
            const added = Array.from({ length: -by }, (_, k) => ({ key: this.seed++, slide: this.wrap(first - (-by - k)) }));
            this.cards = [...added, ...this.cards];
            this.slid = by;
            this.freeze(() => (this.slid = 0));
        }

        this.timers.push(setTimeout(() => this.settle(), this.ms + 20));
    },

    // Cut the strip back to the cards on show and reset the counters: the same picture, so nothing animates
    settle(thaw = true) {
        const visible = 4 + this.slats;
        this.cards = this.forward ? this.cards.slice(-visible) : this.cards.slice(0, visible);
        this.column = 0;
        this.slid = 0;
        thaw ? this.freeze() : (this.still = true);
    },

    // A narrow panel opens; the open one follows its link (areas with published projects only)
    pick(place) {
        const col = this.col(place);
        if (col > 0) return this.step(col);
        const href = this.slides[this.cards[place].slide].href;
        if (col === 0 && href) window.location.href = href;
    },
});
