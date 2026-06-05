import './bootstrap';
import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

const defaultConfirmMessage = 'Apakah Anda yakin ingin melanjutkan aksi ini?';
const destructivePattern = /\b(hapus|delete|remove|destroy|batalkan|cancel)\b/i;

function buildConfirmOptions(message, trigger) {
    const text = message || defaultConfirmMessage;
    const isDestructive = destructivePattern.test(text) || trigger?.dataset.confirmVariant === 'danger';

    return {
        title: trigger?.dataset.confirmTitle || (isDestructive ? 'Konfirmasi Hapus' : 'Konfirmasi'),
        text,
        icon: isDestructive ? 'warning' : 'question',
        showCancelButton: true,
        reverseButtons: true,
        focusCancel: true,
        confirmButtonText: trigger?.dataset.confirmYes || (isDestructive ? 'Ya, Hapus' : 'Ya'),
        cancelButtonText: trigger?.dataset.confirmNo || 'Tidak',
        buttonsStyling: false,
        customClass: {
            confirmButton: isDestructive ? 'btn btn-error text-white' : 'btn btn-primary',
            cancelButton: 'btn btn-ghost',
        },
    };
}

async function askForConfirmation(message, trigger) {
    const result = await Swal.fire(buildConfirmOptions(message, trigger));

    return Boolean(result.isConfirmed);
}

document.addEventListener(
    'submit',
    (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const message = form.dataset.confirm;
        if (!message) {
            return;
        }

        if (form.dataset.confirmed === '1') {
            delete form.dataset.confirmed;
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        void askForConfirmation(message, form).then((confirmed) => {
            if (!confirmed) {
                return;
            }

            form.dataset.confirmed = '1';

            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();

                return;
            }

            form.submit();
        });
    },
    true,
);

document.addEventListener(
    'click',
    (event) => {
        const target = event.target;

        if (!(target instanceof Element)) {
            return;
        }

        const trigger = target.closest('[data-confirm]');
        if (!trigger || trigger instanceof HTMLFormElement) {
            return;
        }

        // Let form-level confirmation handle submit buttons.
        if (trigger.closest('form[data-confirm]')) {
            return;
        }

        const message = trigger.getAttribute('data-confirm');

        if (trigger.dataset.confirmed === '1') {
            delete trigger.dataset.confirmed;
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        void askForConfirmation(message, trigger).then((confirmed) => {
            if (!confirmed) {
                return;
            }

            trigger.dataset.confirmed = '1';
            trigger.click();
        });
    },
    true,
);
