import { Controller } from "@hotwired/stimulus";

var controller_locked = class extends Controller {

    /**
     * Resize iframes when height of its content changes TODO::Rewrite into iamczech/easyadmin-fields-bundle
     * @param {Event} e
     */
    resizeIframe(e) {
        const iframe = e.currentTarget;
        try {
            let target = iframe.contentWindow.document.getElementById('main') || iframe.contentWindow.document.body;
            const updateHeight = () => {
                iframe.style.height = `${target.scrollHeight + 200}px`;
            }

            iframe._resizeObserver = new iframe.contentWindow.ResizeObserver(updateHeight);
            iframe._resizeObserver.observe(target); // Reakce na změnu velikosti

            iframe._mutationObserver = new iframe.contentWindow.MutationObserver(updateHeight);
            iframe._mutationObserver.observe(target, { childList: true, subtree: true, attributes: true }); // reacts to a changes in DOM (přidání/odebrání prvků)

            iframe.addEventListener('load', () => this.resizeIframe(iframe)); // resize after each reload or change of a content

        } catch (e) {
            console.warn('resizeIframe: nelze přistoupit k obsahu iframe (jiná doména?)', e);
        }
    }
}

export {
    controller_locked as default
};
