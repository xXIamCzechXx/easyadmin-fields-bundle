import { Controller } from "@hotwired/stimulus";

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */

const controller_switchable = class extends Controller {

    static values = {
        show: { type: Number, default: 0 }, // which index to show initially
    };

    connect() {
        this.onCollectionItemAdded = this.onCollectionItemAdded.bind(this);
        this.onCollectionItemRemoved = this.onCollectionItemRemoved.bind(this);

        document.addEventListener('ea.collection.item-added', this.onCollectionItemAdded);
        document.addEventListener('ea.collection.item-removed', this.onCollectionItemRemoved);

        this.refreshSelect();
    }

    onCollectionItemAdded(event) {
        this.refreshSelect();

        const newIndex = Math.max(0, this.items.length - 1);
        this.showValue = newIndex;
        this.setSelectOption(newIndex);
    }

    onCollectionItemRemoved(event) {
        this.refreshSelect();
    }

    refreshSelect() {
        this.items = Array.from(this.element.querySelectorAll('.field-collection-item'));

        if (this.selectWrapper) {
            this.selectWrapper.remove();
            this.selectWrapper = null;
            this.select = null;
        }

        if (!this.items.length || 1 === this.items.length) {
            this.items.forEach((item) => {
                item.style.display = '';
            });

            return;
        }

        this.renderSelect();

        let index = this.showValue;

        if (this.select) {
            const currentValue = parseInt(this.select.value, 10);

            if (Number.isFinite(currentValue)) {
                index = currentValue;
            }
        }

        this.setSelectOption(index);
    }

    renderSelect() {
        const select = document.createElement('select');
        select.className = 'iamczech-switchable-select form-select w-auto';
        select.addEventListener('change', () => this.setSelectOption(parseInt(select.value, 10)));

        this.items.forEach((item, index) => {
            const option = document.createElement('option');
            const headerText = item.querySelector('.accordion-button')?.textContent?.trim() || null;

            option.textContent = headerText && headerText.length ? headerText : `Nový záznam #${index + 1}`;
            option.value = String(index);

            select.appendChild(option);
        });

        let wrapper;

        const existingSelect = this.element.querySelector('.iamczech-switchable-select');
        if (existingSelect) {
            wrapper = existingSelect.parentNode;
            existingSelect.remove();
        } else {
            wrapper = document.createElement('div');
            wrapper.className = 'mb-3 d-flex align-items-center gap-2';

            const legend = this.element.querySelector('legend');
            if (legend) {
                legend.classList.add('w-auto');
                wrapper.appendChild(legend);
            }
        }

        wrapper.appendChild(select);

        const list = this.element.querySelector('.ea-form-collection-items') || this.element;
        list.parentNode.insertBefore(wrapper, list);

        this.select = select;
    }

    setSelectOption(index) {
        if (!Number.isFinite(index) || index < 0 || index >= this.items.length) {
            index = 0;
        }

        this.items.forEach((item, i) => {
            item.style.display = (i === index) ? '' : 'none';
        });

        if (this.select) {
            this.select.value = String(index);
        }
    }

    disconnect() {
        super.disconnect();
    }
}

export {
    controller_switchable as default
};
