function readCookie(name) {
    const match = document.cookie.match(new RegExp(`(?:^|; )${name}=([^;]*)`));

    return match ? decodeURIComponent(match[1]) : null;
}

function createToast({ items, url, stack, messageMapper, onClose }) {
    const toast = document.createElement('div');
    toast.dataset.reminderToast = '';
    toast.className = 'pointer-events-auto flex w-full items-end gap-2 rounded-xl border-4 border-amber-500/40 bg-zinc-900 px-3 py-3 text-sm leading-snug text-zinc-100 shadow-lg';
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(-6rem)';

    const openButton = document.createElement('button');
    openButton.type = 'button';
    openButton.className = 'min-w-0 flex-1 space-y-2 rounded-lg px-1 py-0.5 text-left transition focus:outline-none focus:ring-2 focus:ring-amber-500/40';

    items.forEach((item) => {
        const line = document.createElement('span');
        const mapped = messageMapper(item);
        line.className = mapped.className;
        line.textContent = mapped.text;
        openButton.appendChild(line);
    });

    openButton.addEventListener('click', () => {
        window.location.href = url;
    });

    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'inline-flex shrink-0 items-center justify-center rounded-lg p-2 text-amber-400 transition hover:bg-amber-500/20 hover:text-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-500/40';
    closeButton.setAttribute('aria-label', 'Închide');
    closeButton.innerHTML = `
        <svg class="size-4" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
            <path fill="currentColor" d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c12.5 12.5 12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/>
        </svg>
    `;
    closeButton.addEventListener('click', (event) => {
        event.stopPropagation();
        toast.remove();

        if (! stack.querySelector('[data-reminder-toast]')) {
            stack.hidden = true;
        }

        if (typeof onClose === 'function') {
            onClose();
        }
    });

    toast.append(openButton, closeButton);
    stack.appendChild(toast);

    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            toast.animate(
                [
                    { opacity: 0, transform: 'translateY(-6rem)' },
                    { opacity: 1, transform: 'translateY(0)' },
                ],
                {
                    duration: 1200,
                    easing: 'ease-out',
                    fill: 'forwards',
                },
            );
        });
    });
}

function dismissOilChangeToasts(url) {
    if (! url) {
        return;
    }

    const xsrfToken = readCookie('XSRF-TOKEN');

    fetch(url, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...(xsrfToken ? { 'X-XSRF-TOKEN': xsrfToken } : {}),
        },
        credentials: 'same-origin',
    }).catch(() => {
        // Ignore network errors; toast is already hidden for this page.
    });
}

export function initReminderToasts() {
    const stack = document.getElementById('reminder-toast-stack');

    if (! stack) {
        return;
    }

    const reminders = JSON.parse(stack.dataset.reminders || '[]');
    const remindersUrl = stack.dataset.remindersUrl;
    const oilMessages = JSON.parse(stack.dataset.oilMessages || '[]');
    const oilUrl = stack.dataset.oilUrl;
    const oilDismissUrl = stack.dataset.oilDismissUrl;

    const hasReminders = reminders.length && remindersUrl;
    const hasOil = oilMessages.length && oilUrl;

    if (! hasReminders && ! hasOil) {
        return;
    }

    stack.hidden = false;
    stack.classList.add('flex', 'flex-col', 'gap-2');

    if (hasOil) {
        createToast({
            items: oilMessages,
            url: oilUrl,
            stack,
            messageMapper: (message) => ({
                text: message,
                className: 'block text-zinc-100 transition hover:text-amber-300',
            }),
            onClose: () => dismissOilChangeToasts(oilDismissUrl),
        });
    }

    if (hasReminders) {
        createToast({
            items: reminders,
            url: remindersUrl,
            stack,
            messageMapper: ({ message, expired }) => ({
                text: message,
                className: expired
                    ? 'block rounded-lg bg-amber-500 px-2.5 py-2 font-medium text-zinc-950'
                    : 'block text-zinc-100 transition hover:text-amber-300',
            }),
        });
    }
}
