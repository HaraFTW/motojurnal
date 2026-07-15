import { initDialogTriggers } from './dialogs.js';

const TYPE_DURATION_DEFAULTS = {
    RCA: '1_an',
    ITP: '2_ani',
    Rovinieta: '1_an',
};

function addDurationToDate(isoDate, duration) {
    if (! isoDate || ! duration) {
        return '';
    }

    const date = new Date(`${isoDate}T00:00:00`);

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    switch (duration) {
        case '30_zile':
            date.setDate(date.getDate() + 30);
            break;
        case '1_an':
            date.setFullYear(date.getFullYear() + 1);
            break;
        case '2_ani':
            date.setFullYear(date.getFullYear() + 2);
            break;
        default:
            return '';
    }

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

function initOptionButtons({ buttonSelector, inputId, onSelect }) {
    const buttons = document.querySelectorAll(buttonSelector);
    const input = document.getElementById(inputId);

    if (! buttons.length || ! input) {
        return { selectValue: () => {} };
    }

    const selectValue = (value, selectedButton = null) => {
        input.value = value;

        buttons.forEach((button) => {
            const isSelected = selectedButton
                ? button === selectedButton
                : button.dataset.value === value;

            button.classList.toggle('border-amber-500', isSelected);
            button.classList.toggle('bg-zinc-800', isSelected);
            button.classList.toggle('ring-2', isSelected);
            button.classList.toggle('ring-amber-500/30', isSelected);
            button.classList.toggle('border-zinc-800', ! isSelected);
            button.classList.toggle('bg-zinc-900', ! isSelected);
        });

        if (onSelect) {
            onSelect(value);
        }
    };

    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            selectValue(button.dataset.value, button);
        });
    });

    return { selectValue };
}

function initRemindersPage() {
    initDialogTriggers();

    const formPanel = document.getElementById('reminder-form-panel');
    const toggleButton = document.getElementById('reminder-form-toggle');
    const startingDateInput = document.getElementById('starting_date');
    const endingDateInput = document.getElementById('ending_date');

    if (toggleButton && formPanel) {
        toggleButton.addEventListener('click', () => {
            const isHidden = formPanel.classList.contains('hidden');

            formPanel.classList.toggle('hidden');

            if (isHidden) {
                formPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });

        if (formPanel.dataset.showForm === 'true') {
            formPanel.classList.remove('hidden');
        }
    }

    if (! startingDateInput || ! endingDateInput) {
        return;
    }

    let endingDateManuallyEdited = false;

    endingDateInput.addEventListener('input', () => {
        endingDateManuallyEdited = true;
    });

    const updateEndingDateFromDuration = () => {
        const duration = document.getElementById('duration')?.value;

        if (! duration || endingDateManuallyEdited) {
            return;
        }

        const calculated = addDurationToDate(startingDateInput.value, duration);

        if (calculated) {
            endingDateInput.value = calculated;
        }
    };

    const { selectValue: selectType } = initOptionButtons({
        buttonSelector: '[data-reminder-type-button]',
        inputId: 'type',
        onSelect: (type) => {
            const customTypeField = document.getElementById('custom-type-field');
            const customTypeInput = document.getElementById('custom_type');

            if (customTypeField) {
                customTypeField.classList.toggle('hidden', type !== 'Altele');
            }

            if (customTypeInput && type !== 'Altele') {
                customTypeInput.value = '';
            }

            const defaultDuration = TYPE_DURATION_DEFAULTS[type];

            if (defaultDuration) {
                selectDuration(defaultDuration);
            }

            endingDateManuallyEdited = false;
            updateEndingDateFromDuration();
        },
    });

    const { selectValue: selectDuration } = initOptionButtons({
        buttonSelector: '[data-reminder-duration-button]',
        inputId: 'duration',
        onSelect: () => {
            endingDateManuallyEdited = false;
            updateEndingDateFromDuration();
        },
    });

    startingDateInput.addEventListener('change', updateEndingDateFromDuration);

    const initialType = document.getElementById('type')?.value || 'RCA';
    const initialDuration = document.getElementById('duration')?.value || TYPE_DURATION_DEFAULTS.RCA;

    selectType(initialType);
    selectDuration(initialDuration);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRemindersPage);
} else {
    initRemindersPage();
}
