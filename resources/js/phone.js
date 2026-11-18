/*
| x-phone: a country picker (flag + dialling code, searchable) in front of a phone number field.
|
| Built on intl-tel-input. Pakistan is picked by default, with the Gulf, UK, US and Canada at the top of the list
| (where most overseas buyers call from). The visitor types the local number (0300 1234567); the form sends the full
| international number (+923001234567) in a hidden field that takes over the input's name, so the server, x-model
| and the lead emails need no changes. Without JavaScript the plain phone field still works.
|
| Usage: <input type="tel" name="phone" x-phone ...> (inside an Alpine component).
| Don't put x-model on it: intl-tel-input fires its own "input" events whose detail x-model would store; read
| the field with @input="phone = $el.value" instead.
*/
import intlTelInput from 'intl-tel-input'; // its styles are imported in app.css, before ours

const TOP = ['pk', 'ae', 'sa', 'qa', 'om', 'kw', 'bh', 'gb', 'us', 'ca'];

export default function phone(Alpine) {
    Alpine.directive('phone', (input, _directive, { cleanup }) => {
        // The hidden field carries the name, so the form sends the full number
        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = input.name;
        input.removeAttribute('name');

        // Margins on the field (mt-2) move to the wrapper: intl-tel-input zeroes the input's own margin
        const margins = [...input.classList].filter((c) => /^(?:[a-z]+:)?-?m[trblxy]?-/.test(c));
        input.classList.remove(...margins);

        const iti = intlTelInput(input, {
            initialCountry: 'pk',
            countryOrder: TOP,
            separateDialCode: true,
            countrySearch: true,
            // A dropdown under the field on every screen (like the other dropdowns), so it also works inside pop-ups
            countrySelectorMode: 'DROPDOWN',
            containerClass: margins.join(' '),
            // The example number follows the chosen country (a UAE number looks different from a Pakistani one)
            placeholderNumberPolicy: 'AGGRESSIVE',
            // Formatting and per-country example numbers load only when a phone field is on the page
            loadUtils: () => import('intl-tel-input/utils'),
        });
        input.closest('.iti').after(hidden);

        // Full international number; before the formatting rules load, built from the dialling code by hand
        const sync = () => {
            const typed = input.value.trim();
            if (!typed) {
                hidden.value = '';
                return;
            }
            const digits = typed.replace(/\D/g, '');
            const dial = iti.getSelectedCountry()?.dialCode ?? '';
            let full = '';
            try {
                full = iti.getNumber(); // throws until the formatting rules have loaded
            } catch {}
            hidden.value = full || (typed.startsWith('+') ? `+${digits}` : `+${dial}${digits.replace(/^0+/, '')}`);
        };
        input.addEventListener('input', sync);
        input.addEventListener('change', sync);
        input.addEventListener('countrychange', sync);
        iti.promise.then(sync).catch(() => {});
        sync();

        // The field's <label> also holds the country button: point the label at the number field, not the button
        const label = input.closest('label');
        if (label) {
            input.id ||= `phone-${Math.random().toString(36).slice(2, 8)}`;
            label.htmlFor = input.id;
        }

        // A form reset (e.g. "Send another message") empties the number but keeps the chosen country
        const form = input.form;
        const reset = () => setTimeout(() => {
            iti.setNumber('');
            hidden.value = '';
        });
        form?.addEventListener('reset', reset);

        cleanup(() => {
            form?.removeEventListener('reset', reset);
            hidden.remove();
            iti.destroy();
        });
    });
}
