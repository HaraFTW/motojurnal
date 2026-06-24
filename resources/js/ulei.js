import $ from 'jquery';
import select2 from 'select2/dist/js/select2.full.js';
import 'select2/dist/css/select2.css';
import '../css/select2-dark.css';
import { initDialog, initDialogTriggers, initEditingEntry } from './dialogs.js';

window.$ = window.jQuery = $;
select2(window, $);

const select2Options = {
    width: '100%',
    placeholder: 'Selectează tipul de ulei',
    allowClear: true,
    minimumResultsForSearch: 5,
};

function initOilTypeSelect() {
    const oilTypeSelect = document.querySelector('#oil_type_id');

    if (! oilTypeSelect) {
        return;
    }

    $(oilTypeSelect).select2(select2Options);
}

function initOilEditSelects() {
    const initSelectInDialog = (dialogId) => {
        const dialog = document.getElementById(dialogId);

        if (! dialog) {
            return;
        }

        const select = dialog.querySelector('.oil-type-select-edit');

        if (! select || $(select).hasClass('select2-hidden-accessible')) {
            return;
        }

        $(select).select2(select2Options);
    };

    document.querySelectorAll('[data-open-dialog^="oil-edit-"]').forEach((button) => {
        button.addEventListener('click', () => {
            initSelectInDialog(button.dataset.openDialog ?? '');
        });
    });

    const editingMarker = document.getElementById('oil-editing-entry');

    if (editingMarker?.dataset.editDialog) {
        initSelectInDialog(editingMarker.dataset.editDialog);
    }
}

function initOilPage() {
    initOilTypeSelect();
    initOilEditSelects();
    initDialog('oil-history-open', 'oil-history-dialog', 'data-oil-history-close');
    initDialogTriggers();
    initEditingEntry('oil-editing-entry');
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initOilPage);
} else {
    initOilPage();
}
