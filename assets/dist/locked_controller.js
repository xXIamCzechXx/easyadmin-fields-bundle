import { Controller } from "@hotwired/stimulus";

var controller_locked = class extends Controller {

    connect() {
        this.element.addEventListener('click', this.unlock.bind(this));
    }

    /**
     * If data-unlock-group="groupName" is defined on input, then after confirmation all inputs with the same hroup and locked="locked" will be unlocked
     * @param {Event} e
     */
    unlock(e) {
        e.preventDefault();

        const confirmText = e.target.dataset.confirmText.replace('#newline#', '\n\n') || 'Opravdu chcete odemknout pole k úpravě?';
        const inputs = document.querySelectorAll(`[data-unlock-group="${this.element.dataset.unlockGroup}"][locked="locked"]`);
        if (inputs.length === 0) {
            return; // If input is already unlocked, then do nothing
        }

        const confirmed = window.confirm(confirmText);
        if (confirmed) {
            inputs.forEach(input => {
                input.removeAttribute('locked'); // Unlock them
            });
            this.element.focus(); // Focus on clicked input
        }
    }

    disconnect() {
        this.element.removeEventListener('click', this.handleClick);
    }
}

export {
    controller_locked as default
};
