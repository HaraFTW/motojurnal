import { initReminderToasts } from './reminder-toasts.js';

function initApp() {
    initReminderToasts();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initApp);
} else {
    initApp();
}
