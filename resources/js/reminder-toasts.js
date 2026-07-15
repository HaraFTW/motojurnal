export function initReminderToasts() {
    const stack = document.getElementById('reminder-toast-stack');

    if (! stack) {
        return;
    }

    const items = JSON.parse(stack.dataset.reminders || '[]');
    const remindersUrl = stack.dataset.remindersUrl;

    if (! items.length || ! remindersUrl) {
        return;
    }

    stack.hidden = false;

    const toast = document.createElement('div');
    toast.dataset.reminderToast = '';
    toast.className = 'pointer-events-auto flex w-full items-start gap-2 rounded-xl border-4 border-amber-500/40 bg-zinc-900 px-3 py-3 text-sm leading-snug text-zinc-100 shadow-lg';
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(-6rem)';

    const openButton = document.createElement('button');
    openButton.type = 'button';
    openButton.className = 'min-w-0 flex-1 space-y-2 rounded-lg px-1 py-0.5 text-left transition focus:outline-none focus:ring-2 focus:ring-amber-500/40';

    items.forEach(({ message, expired }) => {
        const line = document.createElement('span');
        line.className = expired
            ? 'block rounded-lg bg-amber-500 px-2.5 py-2 font-medium text-zinc-950'
            : 'block text-zinc-100 transition hover:text-amber-300';
        line.textContent = message;
        openButton.appendChild(line);
    });

    openButton.addEventListener('click', () => {
        window.location.href = remindersUrl;
    });

    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'inline-flex shrink-0 items-center justify-center rounded-lg p-2 text-zinc-400 transition hover:bg-zinc-800 hover:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-amber-500/40';
    closeButton.setAttribute('aria-label', 'Închide');
    closeButton.innerHTML = `
        <svg class="size-4" aria-hidden="true" focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512">
            <path fill="currentColor" d="M342.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L192 210.7 86.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L146.7 256 41.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L192 301.3 297.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L237.3 256 342.6 150.6z"/>
        </svg>
    `;
    closeButton.addEventListener('click', (event) => {
        event.stopPropagation();
        toast.remove();
        stack.hidden = true;
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
