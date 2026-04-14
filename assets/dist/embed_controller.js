import { Controller } from "@hotwired/stimulus";

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
const controller_embed = class extends Controller {

    /**
     * Resize iframes when is loaded and also when the height of its content changes
     */
    connect() {
        if (this.element.dataset.baseSrc) {
            const embedContainer = this.element.closest('[data-embed-id]');
            const embedId = embedContainer?.dataset.embedId;

            const url = new URL(this.element.dataset.baseSrc, window.location.origin);

            if (embedId) {
                url.searchParams.set(this.element.dataset.propertyAlias, embedId);
            }

            this.element.src = url.toString();
        }

        const buffer = 160;
        try {
            const handle = () => {
                let target = this.element.contentWindow.document.getElementById('main') || this.element.contentWindow.document.body;
                const updateHeight = () => {
                    this.element.style.height = `${target.scrollHeight + buffer}px`;
                }

                this.element._resizeObserver = new this.element.contentWindow.ResizeObserver(updateHeight);
                this.element._resizeObserver.observe(target); // react to the height changes in an iframe
                this.element._mutationObserver = new this.element.contentWindow.MutationObserver(updateHeight);
                this.element._mutationObserver.observe(target, {
                    childList: true,
                    subtree: true,
                    attributes: true
                }); // reacts to changes in DOM

            };
            this.element.addEventListener('load', () => handle(this.element)); // resize after each reload or change of a content

        } catch (e) {
            console.warn('resize: unable to read content of an iframe (other domain?)', e);
        }
    }

    disconnect() {
        if (this.element._resizeObserver) {
            this.element._resizeObserver.disconnect();
        }
        if (this.element._mutationObserver) {
            this.element._mutationObserver.disconnect();
        }
    }
}

export {
    controller_embed as default
};
