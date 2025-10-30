import { Controller } from "@hotwired/stimulus";

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
var controller_locked = class extends Controller {

    /**
     * After confirmation all inputs with the same group and readonly="readonly" will be unlocked
     * @param {Event} e
     */
    unlock(e) {
        e.preventDefault();

        let text = e.target.dataset.confirmText.replace('#newline#', '\n\n') || 'Opravdu chcete odemknout pole k úpravě?';
        let inputs = document.querySelectorAll(`[data-unlock-group="${this.element.dataset.unlockGroup}"][readonly="readonly"]`);
        if (inputs.length === 0) {
            return; // If input is already unlocked, then do nothing
        }

        if (window.confirm(text)) {
            inputs.forEach(input => {
                input.removeAttribute('readonly'); // Unlock them
            });
            this.element.focus(); // Focus on clicked input
        }
    }
}

export {
    controller_locked as default
};
