import { Controller } from "@hotwired/stimulus";

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
var controller_embed = class extends Controller {

    /**
     * Resize iframes when is loaded and also when height of its content changes
     * @param {Event} e
     */
    resize(e) {
        const iframe = e.currentTarget;
        const buffer = 200;
        try {
            let target = iframe.contentWindow.document.getElementById('main') || iframe.contentWindow.document.body;
            const updateHeight = () => {
                iframe.style.height = `${target.scrollHeight + buffer}px`;
            }

            iframe._resizeObserver = new iframe.contentWindow.ResizeObserver(updateHeight);
            iframe._resizeObserver.observe(target); // react to a height changes in iframe
            iframe._mutationObserver = new iframe.contentWindow.MutationObserver(updateHeight);
            iframe._mutationObserver.observe(target, {
                childList: true,
                subtree: true,
                attributes: true
            }); // reacts to a changes in DOM (přidání/odebrání prvků)

            iframe.addEventListener('load', () => this.resize(iframe)); // resize after each reload or change of a content

        } catch (e) {
            console.warn('resize: unable to read content of an iframe (other domain?)', e);
        }
    }
}

export {
    controller_embed as default
};
