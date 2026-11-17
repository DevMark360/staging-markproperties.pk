// Property finder: the home page pop-up. After a spell with no scrolling, clicking or typing it asks the admin's
// questions one at a time, then for a name and number. Shown at most once every few days per visitor, and never
// again once they have sent their details. The questions and options come from the admin (FinderQuestion).

const KEY = 'mp-finder';
const WAKE = ['scroll', 'wheel', 'pointerdown', 'pointermove', 'keydown', 'touchstart'];

const remembered = () => {
    try {
        return JSON.parse(localStorage.getItem(KEY)) ?? {};
    } catch {
        return {};
    }
};
const remember = (value) => {
    try {
        localStorage.setItem(KEY, JSON.stringify(value));
    } catch {
        // Private windows can refuse storage: the pop-up may then show again on the next visit, which is harmless
    }
};

export default (questions, { delay = 30, days = 7 } = {}) => ({
    questions,
    open: false,
    step: 0, // 0 … questions.length - 1: a question; questions.length: the contact step
    forward: true, // which way the steps slide
    picks: Object.fromEntries(questions.map((q) => [q.id, []])),
    hint: '',
    idle: null,
    wake: null,

    init() {
        const seen = remembered();
        if (!questions.length || seen.done || (seen.until && Date.now() < seen.until)) return;

        // Any activity restarts the countdown; the pop-up opens once the visitor has been still for `delay` seconds
        this.wake = () => {
            clearTimeout(this.idle);
            this.idle = setTimeout(() => this.show(), delay * 1000);
        };
        WAKE.forEach((e) => window.addEventListener(e, this.wake, { passive: true }));
        this.wake();
    },

    stopWatching() {
        clearTimeout(this.idle);
        WAKE.forEach((e) => window.removeEventListener(e, this.wake));
    },

    show() {
        this.stopWatching();
        // Not over another pop-up (search, menu, gallery…) that the visitor left open
        const others = document.querySelectorAll('[aria-modal="true"]:not([data-finder])');
        if ([...others].some((el) => el.getClientRects().length)) return;
        this.open = true;
    },

    close() {
        this.open = false;
        this.stopWatching();
        remember(this.done ? { done: true } : { until: Date.now() + days * 864e5 });
    },

    total() {
        return this.questions.length;
    },

    // 0% on the first question, 90% on the contact step, 100% once sent
    percent() {
        if (this.done) return 100;
        return Math.round((this.step / this.total()) * 90);
    },

    isPicked(id, label) {
        return this.picks[id].includes(label);
    },

    choose(question, label) {
        this.hint = '';
        if (question.multiple) {
            const list = this.picks[question.id];
            this.picks[question.id] = list.includes(label) ? list.filter((l) => l !== label) : [...list, label];
            return;
        }
        this.picks[question.id] = [label];
        // One answer only: move on by itself, after a beat so the choice is seen
        setTimeout(() => this.step < this.total() && this.questions[this.step].id === question.id && this.next(), 280);
    },

    next() {
        if (this.step >= this.total()) return;
        if (!this.picks[this.questions[this.step].id].length) {
            this.hint = 'Please choose an option to continue.';
            return;
        }
        this.hint = '';
        this.forward = true;
        this.step++;
    },

    back() {
        if (this.step === 0) return;
        this.hint = '';
        this.forward = false;
        this.step--;
    },

    // Enter moves on from a question; on the contact step it sends the form as usual
    enter(event) {
        if (this.step < this.total() && event.target.tagName !== 'BUTTON') {
            event.preventDefault();
            this.next();
        }
    },

    async send(form) {
        await this.submit(form); // backgroundForm (app.js)
        if (this.done) remember({ done: true });
    },
});
