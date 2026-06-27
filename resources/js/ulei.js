import $ from 'jquery';
import select2 from 'select2/dist/js/select2.full.js';
import 'select2/dist/css/select2.css';
import '../css/select2-dark.css';
import { initDialog, initDialogTriggers, initEditingEntry } from './dialogs.js';

window.$ = window.jQuery = $;
select2(window, $);

function matchesInOrder(text, term) {
    const haystack = text.toLowerCase();
    const needle = term.toLowerCase().trim();

    if (needle === '') {
        return true;
    }

    let startAt = 0;

    for (const char of needle) {
        const index = haystack.indexOf(char, startAt);

        if (index === -1) {
            return false;
        }

        startAt = index + 1;
    }

    return true;
}

const select2Options = {
    width: '100%',
    placeholder: 'Selectează tipul de ulei',
    allowClear: true,
    minimumResultsForSearch: 5,
    matcher(params, data) {
        if (params.term === undefined || params.term.trim() === '') {
            return data;
        }

        if (typeof data.text === 'undefined') {
            return null;
        }

        return matchesInOrder(data.text, params.term) ? data : null;
    },
};

function initOilTypeSelectInDialog(dialog) {
    const select = dialog.querySelector('.oil-type-select-edit');

    if (! select) {
        return;
    }

    const $select = $(select);

    if ($select.hasClass('select2-hidden-accessible')) {
        $select.select2('destroy');
    }

    $select.select2({
        ...select2Options,
        dropdownParent: $(dialog),
    });
}

function initOilTypeSelect() {
    const oilTypeSelect = document.querySelector('#oil_type_id');

    if (! oilTypeSelect) {
        return;
    }

    $(oilTypeSelect).select2(select2Options);
}

function initOilEditSelects() {
    document.querySelectorAll('dialog[id^="oil-edit-"]').forEach((dialog) => {
        dialog.addEventListener('toggle', () => {
            if (dialog.open) {
                initOilTypeSelectInDialog(dialog);
            }
        });
    });
}

function initOilPage() {
    initOilTypeSelect();
    initOilEditSelects();
    initDialog('oil-history-open', 'oil-history-dialog', 'data-oil-history-close');
    initDialogTriggers();
    initEditingEntry('oil-editing-entry');

    const editingMarker = document.getElementById('oil-editing-entry');

    if (editingMarker?.dataset.editDialog) {
        const dialog = document.getElementById(editingMarker.dataset.editDialog);

        if (dialog?.open) {
            initOilTypeSelectInDialog(dialog);
        }
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initOilPage);
} else {
    initOilPage();
}
