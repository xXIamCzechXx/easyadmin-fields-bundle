import { Controller } from "@hotwired/stimulus";

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */

const controller_creatable = class extends Controller {

    connect() {
        if (this.element.dataset.creatableEnhanced === 'true' || !this.element.tomselect) {
            return;
        }

        this.element.dataset.creatableEnhanced = 'true';
        this.addCreateButton(this.element);
    }

    addCreateButton(field) {
        if (field.tomselect.wrapper.querySelector('[data-creatable-plus]')) {
            return;
        }

        const button = document.createElement('div');
        button.className = 'btn btn-secondary ts-create-btn';
        button.setAttribute('data-creatable-plus', 'true');
        button.setAttribute('data-ts-item', '');
        button.setAttribute('aria-label', 'Add related item');
        button.setAttribute('style', 'height: 22px; font-size: 13px; padding: 1px 8px;');
        button.innerHTML = '<i class="fas fa-plus" aria-hidden="true"></i>';

        button.addEventListener('click', async (e) => {
            e.preventDefault();
            e.stopPropagation();

            await this.openCreateModal(field);
        });

        const control = field.tomselect.control;
        const items = control.querySelectorAll('.item');
        const lastItem = items[items.length - 1];

        if (lastItem) {
            lastItem.insertAdjacentElement('afterend', button);
        } else {
            control.prepend(button);
        }
    }

    async openCreateModal(field) {
        const autocompleteUrl = field.dataset.eaAutocompleteEndpointUrl;
        if (!autocompleteUrl) {
            return;
        }

        const createUrl = autocompleteUrl.replace('autocomplete?', 'new?');

        const modalElement = this.getOrCreateModal();
        const modalBody = modalElement.querySelector('.modal-body');
        const modalTitle = modalElement.querySelector('.modal-title');
        const modal = window.bootstrap.Modal.getOrCreateInstance(modalElement);
        modal.show();

        const response = await fetch(createUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const html = await response.text();
        const parsed = new DOMParser().parseFromString(html, 'text/html');

        const title = parsed.querySelector('div.content-header-title h1.title');
        if (title) {
            modalTitle.textContent = title.textContent.trim();
        }

        const form = this.findCreateForm(parsed, field);
        if (!form) {
            modalBody.innerHTML = '<div class="alert alert-danger">Unable to find the creation form.</div>';
            return;
        }

        if (!this.submit) {
            this.submit = this.findSubmitButton(parsed);
            if (!this.submit) {
                modalBody.innerHTML = '<div class="alert alert-danger">Unable to find the Submit button.</div>';
                return;
            }
        }

        modalBody.innerHTML = '';
        modalBody.append(form);
        document.dispatchEvent(new CustomEvent('ea.collection.item-added', { bubbles: true }));
        document.dispatchEvent(new CustomEvent('ea.form.field-added', { bubbles: true }));

        this.prepareFormForModalSubmit(form, createUrl, field, modal);
    }

    findCreateForm(documentNode, field) {
        const association = this.getAssociationName(field.id);
        const selectors = [
            `form#new-${association.toLowerCase()}-form`,
            `form#new-${association}-form`,
            `form#new-${association.charAt(0).toUpperCase() + association.slice(1)}-form`,
            'form',
        ];

        for (const selector of selectors) {
            const element = documentNode.querySelector(selector);

            if (element) {
                return element;
            }
        }

        console.error('Unable to find the add form! In Crud::PAGE_ADD of your associated CrudController');
    }

    findSubmitButton(documentNode) {
        const selectors = [
            'button.action-saveAndContinue',
            'button.action-saveAndReturn',
            'button.action-saveAndAddAnother',
            'button.action-save',
            'button[type="submit"]',
            'input[type="submit"]',
        ];

        for (const selector of selectors) {
            const element = documentNode.querySelector(selector);

            if (element) {
                return element;
            }
        }

        console.error('Unable to find the submit button! In Crud::PAGE_ADD of your associated CrudController');
    }

    prepareFormForModalSubmit(form, createUrl, field, modal) {
        const footer = document.createElement('div');
        footer.className = 'mt-3 d-flex justify-content-end';
        footer.append(this.submit);

        form.append(footer);

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const modalBody = modal._element.querySelector('.modal-body');
            modalBody.classList.add('position-relative');

            const formData = new FormData(form);
            const submitter = event.submitter;

            if (submitter && submitter.name) {
                formData.append(submitter.name, submitter.value);
            }

            const action = form.getAttribute('action') || createUrl;
            formData.set('ea[newForm][btn]', 'saveAndContinue');

            const response = await fetch(action, {
                method: 'POST',
                body: formData,
                redirect: 'follow',
            });

            if (response.redirected) {
                const redirectedUrl = new URL(response.url, window.location.origin);
                const match = redirectedUrl.pathname.match(/\/(\d+)\/edit$/);
                const entityId = match ? match[1] : null;

                if (entityId) {
                    await this.attachCreatedEntityToField(field, entityId);
                    modal.hide();

                    return;
                }
            }

            const html = await response.text();
            const parsed = new DOMParser().parseFromString(html, 'text/html');
            const newForm = this.findCreateForm(parsed, field);

            if (!newForm) {
                modalBody.innerHTML = '<div class="alert alert-danger">The form submission failed.</div>';

                return;
            }

            modalBody.innerHTML = '';
            modalBody.append(newForm);
            this.prepareFormForModalSubmit(newForm, createUrl, field, modal);
        }, { once: true });
    }

    async attachCreatedEntityToField(field, entityId) {
        const autocompleteUrl = field.dataset.eaAutocompleteEndpointUrl;
        const url = new URL(autocompleteUrl, window.location.origin);
        url.searchParams.set('query', entityId);

        const response = await fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        const data = await response.json();
        const entry = data.results.find((item) => String(item.entityId) === String(entityId));

        if (!entry) {
            console.log('No entry found for entityId', entityId, data);
            return;
        }

        const tomselect = field.tomselect;
        const option = {
            ['entityId']: String(entry.entityId),
            ['entityAsString']: String(entry.entityAsString),
        };

        tomselect.addOption(option);
        tomselect.addItem(String(entry.entityId));
        tomselect.refreshItems();
        tomselect.refreshOptions(false);
        tomselect.sync();
        this.addCreateButton(this.element);
    }

    getAssociationName(identifier) {
        let segment = 1;
        const parts = identifier.split('_');

        if (4 === parts.length && !Number.isNaN(Number(parts[2]))) {
            segment = 3;
        }

        if (5 === parts.length && parts[3]) {
            segment = 3;
        }

        return this.singularize(parts[segment]);
    }

    singularize(value) {
        if (value.endsWith('ies') && value.length > 3) {
            return `${value.slice(0, -3)}y`;
        }

        if (value.endsWith('sses') || value.endsWith('shes') || value.endsWith('ches') || value.endsWith('xes') || value.endsWith('zes')) {
            return value.slice(0, -2);
        }

        if (value.endsWith('s') && !value.endsWith('ss')) {
            return value.slice(0, -1);
        }

        return value;
    }

    getOrCreateModal() {
        let modal = document.getElementById('ea-creatable-association-modal');

        if (modal) {
            return modal;
        }

        modal = document.createElement('div');
        modal.id = 'ea-creatable-association-modal';
        modal.className = 'modal fade';
        modal.tabIndex = -1;
        modal.setAttribute('aria-hidden', 'true');

        modal.innerHTML = `
            <div class='modal-dialog modal-lg'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title'>Add related item</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                    </div>
                    <div class='modal-body'></div>
                </div>
            </div>
        `;

        document.body.append(modal);

        return modal;
    }

    disconnect() {
        super.disconnect();
    }
}

export {
    controller_creatable as default
};
