import { Controller } from "@hotwired/stimulus";

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
const controller_copy = class extends Controller {

    connect() {
        this.field = this.element.querySelector('input');
        this.setTargetElement();
    }

    updateValue(e) {
        for (const target of this.targetElements) {
            this.field.value = this.targetElements.map((target) => target.value).join('-');
        }
    }

    setTargetElement() {
        const fieldNames = JSON.parse(this.field.dataset.target);
        this.targetElements = [];

        for (let name of fieldNames) {
            let target = document.getElementById(name);

            if (null === target) {
                console.error(`Wrong target specified for copy widget ("${name}").`);
            }

            this.targetElements.push(target);
        }
    }

    disconnect() {

    }
}

export {
    controller_copy as default
};
