import { initDialog, initDialogTriggers, initEditingEntry } from './dialogs.js';

function initEventTypeSelection() {
    const buttons = document.querySelectorAll('[data-event-type-button]');
    const formPanel = document.getElementById('event-form-panel');
    const typeInput = document.getElementById('event_type_id');

    if (! buttons.length || ! formPanel || ! typeInput) {
        return;
    }

    const selectType = (typeId, selectedButton) => {
        typeInput.value = typeId;
        formPanel.classList.remove('hidden');

        buttons.forEach((button) => {
            const isSelected = button === selectedButton;

            button.classList.toggle('border-amber-500', isSelected);
            button.classList.toggle('bg-zinc-800', isSelected);
            button.classList.toggle('ring-2', isSelected);
            button.classList.toggle('ring-amber-500/30', isSelected);
            button.classList.toggle('border-zinc-800', ! isSelected);
            button.classList.toggle('bg-zinc-900', ! isSelected);
        });

        formPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    };

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            selectType(button.dataset.eventTypeId, button);
        });
    });

    const selectedTypeId = formPanel.dataset.selectedType;

    if (selectedTypeId) {
        const selectedButton = document.querySelector(`[data-event-type-id="${selectedTypeId}"]`);

        if (selectedButton) {
            selectType(selectedTypeId, selectedButton);
        }
    }
}

function initEventsPage() {
    initDialog('events-history-open', 'events-history-dialog', 'data-events-history-close');
    initDialogTriggers();
    initEditingEntry('events-editing-entry');
    initEventTypeSelection();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEventsPage);
} else {
    initEventsPage();
}
