import { initDialogTriggers, openDialogOnLoad } from './dialogs.js';

function initAdminFilesPage() {
    initDialogTriggers();

    const reopenId = document.getElementById('admin-files-page')?.dataset.reopenDialog;

    if (reopenId) {
        openDialogOnLoad(reopenId);
    }

    document.querySelectorAll('[data-admin-delete-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            document.getElementById(button.dataset.adminDeleteToggle)?.classList.toggle('hidden');
        });
    });

    document.querySelectorAll('[data-admin-delete-form]').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': token,
                    },
                    body: new FormData(form),
                });

                if (response.ok) {
                    window.location.reload();
                }
            } catch {
                // Stay on the page with no message, same as a wrong password.
            }
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAdminFilesPage);
} else {
    initAdminFilesPage();
}
