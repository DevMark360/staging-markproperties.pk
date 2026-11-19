/*
| x-phone: a country picker (flag + dialling code, searchable) in front of a phone number field.
|
| Built on intl-tel-input. Pakistan is picked by default, with the Gulf, UK, US and Canada at the top of the list
| (where most overseas buyers call from). The visitor types the local number (0300 1234567); the form sends the full
| international number (+923001234567) in a hidden field that takes over the input's name, so the server, x-model
| and the lead emails need no changes. Without JavaScript the plain phone field still works.
|
| Usage: <input type="tel" name="phone" x-phone ...> (inside an Alpine component).
| Don't put x-model on it: intl-tel-input fires its own "input" events whose detail x-model would store. To show the
| number (e.g. in a thank-you message) use @phone="phone = $event.detail.readable" (+92 300 1234567).
|
| When the form is sent, the number is checked against the chosen country's rules (length and pattern); a wrong or
| incomplete number shows a message under the field and the form isn't sent.
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

        // Full international number; before the formatting rules load, built from the dialling code by hand.
        // The field also fires a "phone" event with the readable form (+92 300 1234567) for thank-you messages.
        const sync = () => {
            const typed = input.value.trim();
            let full = '';
            let readable = '';
            if (typed) {
                const digits = typed.replace(/\D/g, '');
                const dial = iti.getSelectedCountry()?.dialCode ?? '';
                try {
                    // These throw until the formatting rules have loaded
                    full = iti.getNumber();
                    readable = iti.getNumber('INTERNATIONAL');
                } catch {}
                full ||= typed.startsWith('+') ? `+${digits}` : `+${dial}${digits.replace(/^0+/, '')}`;
                readable ||= full;
            }
            hidden.value = full;
            input.dispatchEvent(new CustomEvent('phone', { detail: { number: full, readable } }));
        };
        input.addEventListener('input', (e) => {
            sync();
            if (e.isTrusted) showError(''); // typing again clears the message
        });
        input.addEventListener('change', sync);
        input.addEventListener('countrychange', () => {
            sync();
            showError('');
        });
        iti.promise.then(sync).catch(() => {});

        // Checking the number: a message under the field and a red border, shown when the form is sent
        const error = document.createElement('p');
        error.className = 'phone-error';
        error.id = `${(input.id ||= `phone-${Math.random().toString(36).slice(2, 8)}`)}-error`;
        error.setAttribute('role', 'alert');
        error.hidden = true;
        hidden.after(error);
        const showError = (message) => {
            error.textContent = message;
            error.hidden = !message;
            input.closest('.iti').classList.toggle('iti--invalid', !!message);
            if (message) {
                input.setAttribute('aria-invalid', 'true');
                input.setAttribute('aria-describedby', error.id);
            } else {
                input.removeAttribute('aria-invalid');
                input.removeAttribute('aria-describedby');
            }
        };
        const problem = () => {
            const typed = input.value.trim();
            if (!typed) return input.required ? 'Please enter your phone number.' : '';
            const country = iti.getSelectedCountry();
            if (iti.isValidNumber() === false) {
                // null: the rules haven't loaded, so the server checks it instead
                const example = input.placeholder ? `, e.g. ${input.placeholder}` : '';
                return country?.name ? `Please enter a complete ${country.name} number${example}.` : `Please enter a complete phone number${example}.`;
            }
            if (iti.isValidNumber() === null && typed.replace(/\D/g, '').length < 7) return 'Please enter a complete phone number.';
            return '';
        };

        // The field's <label> also holds the country button: point the label at the number field, not the button
        const label = input.closest('label');
        if (label) label.htmlFor = input.id;

        // A form reset (e.g. "Send another message") empties the number but keeps the chosen country
        const form = input.form;
        const reset = () => setTimeout(() => {
            iti.setNumber('');
            sync();
            showError('');
        });
        form?.addEventListener('reset', reset);

        // Runs before the form's own submit handler (capture phase): a wrong number stops the form being sent
        const check = (event) => {
            sync();
            const message = problem();
            showError(message);
            if (message) {
                event.preventDefault();
                event.stopImmediatePropagation();
                input.focus();
            }
        };
        form?.addEventListener('submit', check, { capture: true });

        cleanup(() => {
            form?.removeEventListener('reset', reset);
            form?.removeEventListener('submit', check, { capture: true });
            error.remove();
            hidden.remove();
            iti.destroy();
        });
    });
}
