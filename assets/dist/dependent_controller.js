import { Controller } from "@hotwired/stimulus";

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
const controller_dependent = class extends Controller {

    connect() {
        let options = JSON.parse(this.element.getAttribute('data-dependent-field-options'));
        let type = this.element.getAttribute('data-dependent-field');

        this.root = this.element.closest(('form'));
        this.callbackUrl = options.callback_url;
        this.dependencies = options.dependencies;
        this.dependenciesFormGroup = [];

        this.dependencies.forEach(dependency => {
            let formGroup = getFieldFormGroup(dependency);
            if (!formGroup) {
                return;
            }

            if (type === 'hide') {
                formGroup.addEventListener('input', this.handleHide.bind(this));
            }
            if (type === 'adapt') {
                formGroup.addEventListener('input', this.handleAdapt.bind(this));
            }
            this.dependenciesFormGroup.push(formGroup);
        });

        this.input = getFormGroupField(this.element);

        if (options.fetch_on_init) {
            if (type === 'hide') {
                this.handleHide().catch(e => console.error(e));
            }
            if (type === 'adapt') {
                this.handleAdapt().catch(e => console.error(e));
            }
        }
    }

    async handleAdapt() {
        let hasEmptyOption = Array.from(this.input.options).some(option => option.value === "");
        let oldValue = this.input.value;
        let newOptions = await this.fetchOptions();

        this.clearOptions().setOptions(newOptions, hasEmptyOption, oldValue);
    }

    async handleHide() {
        let show = await this.fetchOptions();

        if (show === true) {
            showField(this.input);
        } else {
            hideField(this.input);
        }
    }

    async fetchOptions() {
        let params = new URLSearchParams();

        this.dependencies.forEach(dependency => {
            let formGroup = getFieldFormGroup(dependency);
            if (!formGroup) {
                return;
            }

            let field = getFormGroupField(formGroup);
            let value = getValue(field);

            if (Array.isArray(value)) {
                value.forEach(singleValue => {
                    params.append(dependency + "[]", singleValue);
                });
            } else {
                params.append(dependency, value);
            }
        });

        let query = new URLSearchParams(params).toString();
        let response = await fetch(`${this.callbackUrl}?${query}`);

        return await response.json();
    }

    clearOptions() {
        if (!isTomSelect(this.input)) {
            while (this.input.options.length > 0) {
                this.input.remove();
            }
            return;
        }

        this.input.tomselect.lock();
        this.input.tomselect.clear();
        this.input.tomselect.clearOptions();

        return this;
    }

    setOptions(options, hasEmptyOption, previousOption = null) {
        let input = this.input;

        if (!isTomSelect(input)) {
            if (hasEmptyOption) {
                input.options.add(new Option("", ""));
            }

            options.forEach(option => {
                const opt = new Option(option.text, option.value);

                if (previousOption && String(option.value) === String(previousOption)) {
                    opt.selected = true;
                }

                input.options.add(opt);
            });

            return;
        }

        let control = input.tomselect;
        let currentValue = previousOption || control.getValue();
        control.addOptions(options);

        if (currentValue && control.options[currentValue]) {
            control.setValue(currentValue);
        } else {
            control.clear();
        }

        control.refreshOptions(false);
        control.unlock();
    }

    disconnect() {
        this.dependenciesFormGroup.forEach(formGroup => {
            formGroup.removeEventListener('input', this.handleAdapt.bind(this));
            formGroup.removeEventListener('input', this.handleHide.bind(this));
        });
    }
}

export const getValue = (input) => {
    if (isTomSelect(input)) {
        return input.tomselect.getValue();
    }

    if (input.getAttribute('type') === 'checkbox') {
        return input.checked ? "true" : "false";
    }

    return input.value;
};

export const isTomSelect = (element) => {
    return element && element.tomselect !== undefined;
};

export const getFieldFormGroup = (field) => {
    let input = this.root.querySelector(`[name*="[${field}]"]`);
    if (!input) {
        return null;
    }

    return getInputClosestFormGroup(input);
};

export const getFormGroupField = (formGroup) => {
    return formGroup.querySelector('select, input, textarea');
};

export const getFormGroupFields = (formGroup) => {
    return formGroup.querySelectorAll('select, input, textarea');
};

export const getInputClosestFormGroup = (input) => {
    return input.closest('.js-form-group-override') || input.closest('.form-group');
};

export const hideField = (field) => {
    const formGroup = getInputClosestFormGroup(field);
    formGroup.style.display = "none";
};

export const showField = (field) => {
    const formGroup = getInputClosestFormGroup(field);
    formGroup.style.display = null;
};

export {
    controller_dependent as default
};
