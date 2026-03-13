import { Controller } from "@hotwired/stimulus";

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
const controller_configurable = class extends Controller {

    static values = {
        url: String,
    };

    connect() {
        this.addCreateButton();
    }

    addCreateButton() {
        const button = document.createElement('button');
        button.setAttribute('type', 'button');
        button.className = 'btn btn-link field-collection-add-button';
        button.innerHTML =
            '<span class="icon pr-1">' +
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" fill="currentColor"><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 144L48 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l144 0 0 144c0 17.7 14.3 32 32 32s32-14.3 32-32l0-144 144 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-144 0 0-144z"></path></svg>' +
            '</span>Správa verzí';

        button.addEventListener('click', async (event) => {
            event.preventDefault();
            event.stopPropagation();

            await this.openCreateModal();
        });

        const wrapper = this.element.closest('.form-widget') || this.element.parentElement;
        if (!wrapper) {
            return;
        }

        wrapper.append(button);
    }

    async openCreateModal() {
        const modalElement = this.getOrCreateModal();
        const modal = window.bootstrap.Modal.getOrCreateInstance(modalElement);

        modal.show();
        await this.loadModalUrl(this.urlValue);
    }

    async loadModalUrl(url) {
        const modalElement = this.getOrCreateModal();
        const modalBody = modalElement.querySelector('.modal-body');
        const modalTitle = modalElement.querySelector('.modal-title');

        modalBody.innerHTML = `
            <div class='d-flex justify-content-center p-4'>
                <div class='spinner-border' role='status'>
                    <span class='visually-hidden'>Loading...</span>
                </div>
            </div>
        `;

        const response = await fetch(url, {
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

        const content = this.findModalContent(parsed);
        if (!content) {
            modalBody.innerHTML = '<div class="alert alert-danger">Unable to find modal content.</div>';

            return;
        }

        modalBody.innerHTML = '';
        modalBody.append(content);

        this.bindModalContent(modalBody);
    }

    bindModalContent(modalBody) {
        modalBody.querySelectorAll('a[href]').forEach((link) => {
            if (link.dataset.modalBound === 'true') {
                return;
            }

            link.dataset.modalBound = 'true';

            link.addEventListener('click', async (event) => {
                const href = link.getAttribute('href');

                if (
                    !href
                    || href.startsWith('#')
                    || link.target === '_blank'
                    || link.hasAttribute('download')
                    || link.dataset.bsToggle
                    || link.dataset.bsDismiss
                ) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();

                await this.loadModalUrl(href);
            });
        });

        modalBody.querySelectorAll('form').forEach((form) => {
            if (form.dataset.modalBound === 'true') {
                return;
            }

            form.dataset.modalBound = 'true';

            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                event.stopPropagation();

                await this.submitModalForm(form, event.submitter || null);
            });
        });
    }

    async submitModalForm(form, submitter = null) {
        const modalElement = this.getOrCreateModal();
        const modalBody = modalElement.querySelector('.modal-body');
        const action = form.getAttribute('action') || this.urlValue;
        const method = (form.getAttribute('method') || 'POST').toUpperCase();

        const formData = new FormData(form);

        if (submitter && submitter.name) {
            formData.append(submitter.name, submitter.value);
        }

        modalBody.classList.add('position-relative');

        const response = await fetch(action, {
            method,
            body: formData,
            redirect: 'follow',
        });

        if (response.redirected) {
            await this.loadModalUrl(response.url);

            return;
        }

        const html = await response.text();
        const parsed = new DOMParser().parseFromString(html, 'text/html');

        const content = this.findModalContent(parsed);
        if (!content) {
            modalBody.innerHTML = '<div class="alert alert-danger">The form submission failed.</div>';

            return;
        }

        const modalTitle = modalElement.querySelector('.modal-title');
        const title = parsed.querySelector('div.content-header-title h1.title');
        if (title) {
            modalTitle.textContent = title.textContent.trim();
        }

        modalBody.innerHTML = '';
        modalBody.append(content);

        this.bindModalContent(modalBody);
    }

    findModalContent(documentNode) {
        const selectors = [
            'article.content',
            '.content-panel',
            '.ea-content',
            '.table-responsive',
            'form',
        ];

        for (const selector of selectors) {
            const element = documentNode.querySelector(selector);

            if (element) {
                return element;
            }
        }

        console.error('Unable to find modal content.');

        return null;
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
            <div class='modal-dialog modal-xl'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title'>Správa verzí</h5>
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
    controller_configurable as default
};
