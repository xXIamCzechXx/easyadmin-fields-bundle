import { Controller } from "@hotwired/stimulus";

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
var controller_locked = class extends Controller {

    static TEMPLATE = `
        <div class="modal fade" id="field-confirm-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body p-4">
                        <p class="fs-6 mb-0" data-modal-content></p>
                    </div>
                    <div class="modal-footer border-0 pt-2 mt-1">
                        <button type="button" class="btn btn-secondary" data-modal-cancel data-bs-dismiss="modal">Zrušit</button>
                        <button type="button" class="btn btn-primary" data-modal-confirm>Potvrdit</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    /**
     * Creates a modal element and adds it to the body
     */
    initialize() {
        if (this.constructor.modal) {
            return;
        }

        const wrapper = document.createElement('div');
        wrapper.innerHTML = this.constructor.TEMPLATE.trim();

        this.constructor.modal = wrapper.firstElementChild;
        this.constructor.bsModal = new bootstrap.Modal(this.constructor.modal);

        document.body.appendChild(this.constructor.modal);
    }

    /**
     * After confirmation all inputs with the same group and readonly="readonly" will be unlocked
     * @param {Event} e
     */
    async unlock(e) {
        e.preventDefault();

        let inputs = document.querySelectorAll(`[data-group="${this.element.dataset.group}"][readonly="readonly"]`);
        if (inputs.length === 0) {
            return; // If input is already unlocked, then do nothing
        }

        let confirmed = await this.showConfirm(this.element.dataset.text);
        if (confirmed) {
            inputs.forEach((input) => {
                input.removeAttribute('readonly')
            });
            this.element.focus();
        }
    }

    /**
     * Shows modal and returns Promise<boolean> whether a user confirmed or not
     * @param {string} message
     * @returns {Promise<boolean>}
     */
    showConfirm(message) {
        let content = this.constructor.modal.querySelector("[data-modal-content]");
        let confirm = this.constructor.modal.querySelector("[data-modal-confirm]");
        let cancel = this.constructor.modal.querySelector("[data-modal-cancel]");

        content.innerHTML = message;
        confirm.textContent = this.element.dataset.confirm;
        cancel.textContent = this.element.dataset.cancel;

        return new Promise((resolve) => {
            const cleanup = () => {
                confirm.removeEventListener('click', okHandler);
                this.constructor.modal.removeEventListener('hidden.bs.modal', cancelHandler);
            };
            const okHandler = () => {
                resolve(true);
                cleanup();
                this.constructor.bsModal.hide();
            };
            const cancelHandler = () => {
                resolve(false);
                cleanup();
            };

            confirm.addEventListener('click', okHandler);
            this.constructor.modal.addEventListener('hidden.bs.modal', cancelHandler);
            this.constructor.bsModal.show();
        });
    }
}

export {
    controller_locked as default
};
