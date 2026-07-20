export function formatBrazilianPhone(value) {
    const digits = String(value ?? '').replace(/\D/g, '').slice(0, 11);

    if (digits.length === 0) {
        return '';
    }

    const ddd = digits.slice(0, 2);
    const rest = digits.slice(2);

    if (digits.length <= 2) {
        return `(${ddd}`;
    }

    if (digits.length <= 6) {
        return `(${ddd}) ${rest}`;
    }

    if (digits.length <= 10) {
        return `(${ddd}) ${rest.slice(0, 4)}-${rest.slice(4)}`;
    }

    return `(${ddd}) ${rest.slice(0, 5)}-${rest.slice(5)}`;
}

function applyPhoneMask(event) {
    const input = event.target;

    if (! input.matches('[data-phone-mask]') || input.dataset.masking === 'true') {
        return;
    }

    const formatted = formatBrazilianPhone(input.value);

    if (input.value === formatted) {
        return;
    }

    input.dataset.masking = 'true';
    input.value = formatted;
    delete input.dataset.masking;
}

export function initPhoneMasks() {
    document.addEventListener('input', applyPhoneMask);
}