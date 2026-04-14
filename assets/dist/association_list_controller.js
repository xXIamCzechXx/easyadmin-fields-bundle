import { Controller } from '@hotwired/stimulus';

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
const controller_association_list = class extends Controller {

    static targets = [
        'button',
        'field',
        'modal',
        'modalTitle',
        'modalBody',
        'modalFooter',
    ];

    static values = {
        url: String,
        filters: Object,
        columns: Object,
        showFilter: Boolean,
        showSearch: Boolean,
        cancelLabel: String,
        validateLabel: String,
        selectionClass: { type: String, default: 'table-primary' },
    };

    connect() {
        this.selection = [];
        this.modalInstance = new window.bootstrap.Modal(this.modalTarget);
    }

    open(e) {
        e.preventDefault();

        this.#syncInitialSelection();
        this.#setLoadingState(true);

        let url = this.urlValue;

        if (this.hasFiltersValue && this.filtersValue) {
            const query = this.serialize({ filters: this.filtersValue });
            url += `&${query}`;
        }

        this.#loadContent(url).finally(() => {
            this.#setLoadingState(false);
        });
    }

    cancel(e) {
        e.preventDefault();
        this.selection = [];
        this.modalInstance.hide();
    }

    confirm(e) {
        e.preventDefault();

        if (0 === this.selection.length) {
            this.modalInstance.hide();

            return;
        }

        this.fieldTarget.value = this.fieldTarget.hasAttribute('multiple') ? this.selection : this.selection[0];
        this.dispatchNativeChange(this.fieldTarget);

        for (const id of this.selection) {
            const row = this.modalBodyTarget.querySelector(`[data-id="${id}"]`);
            const text = this.#getRowName(row);
            this.applySelectedValue(id, text);

            if (!this.fieldTarget.hasAttribute('multiple')) {
                break;
            }
        }

        this.modalInstance.hide();
    }

    rowClick(e) {
        const row = e.target.closest('tr[data-id]');

        if (null === e) {
            return;
        }

        e.preventDefault();
        this.#toggleRow(row);
    }

    async modalClick(e) {
        const row = e.target.closest('tr[data-id]');

        if (null !== row && this.modalBodyTarget.contains(row)) {
            e.preventDefault();
            this.#toggleRow(row);

            return;
        }

        const link = e.target.closest('a');

        if (null === link || false === this.modalBodyTarget.contains(link)) {
            return;
        }

        if (link.dataset.modal) {
            return;
        }

        e.preventDefault();
        this.#loadBodyOnly(link.href);
    }

    async modalSubmit(e) {
        const form = e.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        if (!this.modalBodyTarget.contains(form)) {
            return;
        }

        e.preventDefault();

        const method = form.getAttribute('method') || 'get';
        const query = new URLSearchParams(new FormData(form)).toString();
        const url = `${this.urlValue || this.fieldTarget.dataset.eaAjaxIndexUrl}&${query}`;

        const response = await fetch(url, {
            method: method.toUpperCase(),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const html = await response.text();
        this.#populateFromResponse(html);
    }

    #toggleRow(row) {
        const id = String(row.dataset.id);

        if (!this.selection.includes(id)) {
            if (this.fieldTarget.hasAttribute('multiple')) {
                this.selection.push(id);
            } else {
                this.selection = [id];
            }
        } else {
            this.selection = this.selection.filter((selectedId) => selectedId !== id);
        }

        this.#refreshSelectionDisplay();
    }

    #syncInitialSelection() {
        const value = this.#getFieldValue();

        this.selection = [];
        if (Array.isArray(value)) {
            this.selection = value.filter((item) => '' !== String(item)).map((item) => String(item));

            return;
        }

        if (null !== value && '' !== String(value)) {
            this.selection = [String(value)];
        }
    }

    #getFieldValue() {
        if (this.fieldTarget.tomselect) {
            return this.fieldTarget.tomselect.getValue();
        }

        if (this.fieldTarget.multiple) {
            return Array.from(this.fieldTarget.selectedOptions).map((option) => option.value);
        }

        return this.fieldTarget.value;
    }

    async #loadContent(url) {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const html = await response.text();
        const documentFragment = this.parseHtml(html);

        documentFragment.querySelector('.content-header-title .title').style.display = 'none';
        const title = documentFragment.querySelector('.content-header-title .title')?.innerHTML ?? '';
        const content = documentFragment.querySelector('.content-body');

        this.modalTitleTarget.innerHTML = title;
        this.modalFooterTarget.innerHTML = this.#buildFooter();

        if (null !== content) {
            this.#populateModalBody(content);
        }

        this.modalInstance.show();
    }

    async #loadBodyOnly(url) {
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const html = await response.text();
        const documentFragment = this.parseHtml(html);
        const content = documentFragment.querySelector('.content-body');

        if (null !== content) {
            this.#populateModalBody(content);
        }
    }

    #populateFromResponse(html) {
        const documentFragment = this.parseHtml(html);
        const content = documentFragment.querySelector('.content-body');

        if (null !== content) {
            this.#populateModalBody(content);
        }
    }

    #populateModalBody(content) {
        const html = content.cloneNode(true);

        if (!this.showFilterValue) {
            html.querySelector('.datagrid-filters')?.remove();
        }

        if (!this.showSearchValue) {
            html.querySelector('.datagrid-search')?.remove();
        }

        html.querySelector('.form-batch-checkbox-all')?.remove();
        html.querySelectorAll('.batch-actions-selector').forEach((element) => {
            element.remove();
        });
        html.querySelectorAll('.global-actions a').forEach((element) => {
            element.remove();
        });
        this.#addSelectors(html);
        this.modalBodyTarget.innerHTML = '';
        this.modalBodyTarget.appendChild(html);
        this.#refreshSelectionDisplay();
    }

    #addSelectors(container) {
        const rows = container.querySelectorAll('tr');

        rows.forEach((row) => {
            const actionsCell = row.querySelector('.actions');

            if (null === actionsCell) {
                return;
            }

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'list-checkbox';
            checkbox.style.pointerEvents = 'none';

            actionsCell.innerHTML = '';
            actionsCell.appendChild(checkbox);
            row.style.cursor = 'pointer';
        });
    }

    #refreshSelectionDisplay() {
        const rows = this.modalBodyTarget.querySelectorAll('tr[data-id]');

        rows.forEach((row) => {
            row.classList.remove(this.selectionClassValue);

            const checkbox = row.querySelector('input.list-checkbox');
            if (null !== checkbox) {
                checkbox.checked = false;
            }
        });

        this.selection.forEach((id) => {
            const row = this.modalBodyTarget.querySelector(`[data-id="${id}"]`);

            if (null === row) {
                return;
            }

            row.classList.add(this.selectionClassValue);

            const checkbox = row.querySelector('input.list-checkbox');
            if (null !== checkbox) {
                checkbox.checked = true;
            }
        });
    }

    #getRowName(row) {
        if (null === row) {
            return '';
        }

        const columns = this.columnsValue?.columns;

        if (Array.isArray(columns)) {
            const values = [];
            const separator = this.columnsValue?.separator ?? ' - ';

            columns.forEach((columnIndex) => {
                const cell = row.querySelector(`td:nth-child(${columnIndex})`);
                const value = cell?.textContent?.trim();

                if (value) {
                    values.push(value);
                }
            });

            return values.join(separator);
        }

        return row.querySelector('td:first-child')?.textContent?.trim() ?? '';
    }

    applySelectedValue(id, text) {
        if ('ea-autocomplete' === this.fieldTarget.dataset.eaWidget && this.fieldTarget.tomselect) {
            const instance = this.fieldTarget.tomselect;

            instance.addOption({
                [instance.settings.valueField]: id,
                [instance.settings.labelField]: text || `#${id}`,
            });
            instance.refreshOptions(false);
            instance.addItem(id);
            instance.refreshItems();

            return;
        }

        if ('select2' === this.fieldTarget.dataset.widget) {
            const optionExists = this.fieldTarget.querySelector(`option[value="${CSS.escape(String(id))}"]`);

            if (null === optionExists) {
                const option = new Option(text, id, true, true);
                this.fieldTarget.appendChild(option);
            }

            this.dispatchNativeChange(this.fieldTarget);

            return;
        }

        const option = this.fieldTarget.querySelector(`option[value="${CSS.escape(String(id))}"]`);
        if (null !== option) {
            option.selected = true;
        }
    }

    dispatchNativeChange(element) {
        element.dispatchEvent(new Event('change', { bubbles: true }));
    }

    #setLoadingState(isLoading) {
        if (!this.hasButtonTarget) {
            return;
        }

        this.buttonTarget.disabled = isLoading;

        const icon = this.buttonTarget.querySelector('.fa');
        if (null === icon) {
            return;
        }

        if (isLoading) {
            icon.dataset.originalClass = icon.className;
            icon.className = 'fa fa-spinner fa-spin';
            return;
        }

        if (icon.dataset.originalClass) {
            icon.className = icon.dataset.originalClass;
        }
    }

    #buildFooter() {
        return `
            <button type="button"
                class="btn btn-secondary"
                data-action="click->iamczech--easyadmin-fields-bundle--association-list#cancel"
            >
                <span class="btn-label">${this.cancelLabelValue}</span>
            </button>
            <button type="button"
                class="btn btn-primary"
                data-action="click->iamczech--easyadmin-fields-bundle--association-list#confirm"
            >
                <span class="btn-label">${this.validateLabelValue}</span>
            </button>
        `;
    }

    parseHtml(html) {
        return new DOMParser().parseFromString(html, 'text/html');
    }

    serialize(obj, prefix = null) {
        const pairs = [];

        Object.keys(obj).forEach((key) => {
            const value = obj[key];
            const prefixedKey = prefix ? `${prefix}[${key}]` : key;

            if (null !== value && 'object' === typeof value && false === Array.isArray(value)) {
                pairs.push(this.serialize(value, prefixedKey));

                return;
            }

            if (Array.isArray(value)) {
                value.forEach((arrayValue, index) => {
                    pairs.push(this.serialize({ [index]: arrayValue }, prefixedKey));
                });

                return;
            }

            pairs.push(`${encodeURIComponent(prefixedKey)}=${encodeURIComponent(value)}`);
        });

        return pairs.join('&');
    }
}

export {
    controller_association_list as default
};
