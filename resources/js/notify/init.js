import Swal from 'sweetalert2';

const BRAND = '#245a3f';
const DANGER = '#dc2626';

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3500,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    },
});

// Show flash messages (server-set session status/error) as premium toasts.
function initFlashToasts() {
    const el = document.getElementById('flash-data');
    if (!el) return;

    let flash;
    try {
        flash = JSON.parse(el.textContent);
    } catch {
        return;
    }

    if (flash.status) {
        Toast.fire({ icon: 'success', title: flash.status });
    }
    if (flash.error) {
        Toast.fire({ icon: 'error', title: flash.error });
    }
}

// Any <form data-confirm="message"> asks for confirmation via SweetAlert2 before
// submitting — replaces native confirm() everywhere with a consistent premium dialog.
function initConfirmForms() {
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.dataset.confirm || form.dataset.confirmed === 'true') {
            return;
        }

        event.preventDefault();

        const danger = form.hasAttribute('data-confirm-danger');

        Swal.fire({
            title: form.dataset.confirmTitle || 'Are you sure?',
            text: form.dataset.confirm,
            icon: danger ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: danger ? DANGER : BRAND,
            cancelButtonColor: '#94a3b8',
            confirmButtonText: form.dataset.confirmButton || (danger ? 'Yes, delete' : 'Confirm'),
            cancelButtonText: 'Cancel',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                form.dataset.confirmed = 'true';
                form.submit();
            }
        });
    });
}

export function initNotify() {
    initFlashToasts();
    initConfirmForms();
}
