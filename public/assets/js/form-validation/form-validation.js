(() => {
    'use strict';

    const form = document.getElementById('contact-form');
    if (!(form instanceof HTMLFormElement)) return;

    const locale = document.documentElement.lang;
    const copy = {
        en: {
            required: 'This field is required.',
            email: 'Enter a valid email address.',
            minlength: (count) => `Enter at least ${count} characters.`,
            maxlength: (count) => `Use no more than ${count} characters.`,
            invalid: 'Check this field and try again.',
        },
        fr: {
            required: 'Ce champ est obligatoire.',
            email: 'Saisissez une adresse e-mail valide.',
            minlength: (count) => `Saisissez au moins ${count} caractères.`,
            maxlength: (count) => `Utilisez ${count} caractères au maximum.`,
            invalid: 'Vérifiez ce champ puis réessayez.',
        },
        es: {
            required: 'Este campo es obligatorio.',
            email: 'Introduce una dirección de correo válida.',
            minlength: (count) => `Escribe al menos ${count} caracteres.`,
            maxlength: (count) => `Usa un máximo de ${count} caracteres.`,
            invalid: 'Revisa este campo e inténtalo de nuevo.',
        },
    };
    const text = copy[locale] || copy.en;

    const errorFor = (field) => document.getElementById(`${field.name}-error`);
    const messageFor = (field) => {
        if (field.validity.valueMissing) return text.required;
        if (field.validity.typeMismatch || field.validity.patternMismatch) return text.email;
        if (field.validity.tooShort) return text.minlength(field.minLength);
        if (field.validity.tooLong) return text.maxlength(field.maxLength);
        return text.invalid;
    };
    const showError = (field) => {
        const error = errorFor(field);
        if (!error) return;
        field.setAttribute('aria-invalid', 'true');
        error.textContent = messageFor(field);
    };
    const clearError = (field) => {
        field.removeAttribute('aria-invalid');
        const error = errorFor(field);
        if (error) error.textContent = '';
    };
    const fields = Array.from(form.querySelectorAll('input[name], textarea[name], select[name]'))
        .filter((field) => field.name !== 'csrf_token' && field.name !== 'website');

    fields.forEach((field) => {
        field.addEventListener('blur', () => {
            if (!field.validity.valid) showError(field);
        });
        field.addEventListener('input', () => {
            if (field.validity.valid) clearError(field);
            else if (field.getAttribute('aria-invalid') === 'true') showError(field);
        });
    });

    form.addEventListener('invalid', (event) => {
        const field = event.target;
        if (field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement || field instanceof HTMLSelectElement) {
            showError(field);
        }
    }, true);
})();