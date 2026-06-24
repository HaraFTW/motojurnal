export function initDialog(openButtonId, dialogId, closeAttribute) {
    const openButton = document.getElementById(openButtonId);
    const dialog = document.getElementById(dialogId);

    if (! openButton || ! dialog) {
        return;
    }

    openButton.addEventListener('click', () => dialog.showModal());

    dialog.querySelectorAll(`[${closeAttribute}]`).forEach((button) => {
        button.addEventListener('click', () => dialog.close());
    });

    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });
}

export function initDialogTriggers() {
    document.querySelectorAll('[data-open-dialog]').forEach((button) => {
        button.addEventListener('click', () => {
            document.getElementById(button.dataset.openDialog)?.showModal();
        });
    });

    document.querySelectorAll('[data-dialog-close]').forEach((button) => {
        button.addEventListener('click', () => {
            button.closest('dialog')?.close();
        });
    });

    document.querySelectorAll('dialog[data-close-on-backdrop]').forEach((dialog) => {
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close();
            }
        });
    });
}

export function openDialogOnLoad(dialogId) {
    const dialog = document.getElementById(dialogId);

    if (dialog) {
        dialog.showModal();
    }
}

export function initEditingEntry(markerId) {
    const marker = document.getElementById(markerId);

    if (! marker) {
        return;
    }

    const historyDialog = document.getElementById(marker.dataset.historyDialog ?? '');
    const editDialog = document.getElementById(marker.dataset.editDialog ?? '');

    historyDialog?.showModal();
    editDialog?.showModal();
}
