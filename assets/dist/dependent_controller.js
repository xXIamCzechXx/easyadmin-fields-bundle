import { Controller } from "@hotwired/stimulus";

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
var controller_dependent = class extends Controller {

    connect() {
        const options = getOptions(this.element);
        this.callbackUrl = options.callback_url;
        this.dependencies = options.dependencies;
        this.dependenciesFormGroup = [];

        this.dependencies.forEach(dependency => {
            const formGroup = getFieldFormGroup(dependency);
            if (!formGroup) {
                return;
            }

            formGroup.addEventListener('input', this.handle.bind(this));
            this.dependenciesFormGroup.push(formGroup);
        });

        this.input = getFormGroupField(this.element);

        if (options.fetch_on_init) {
            this.handle().then(r => console.log('initialize dependent field'));
        }
    }

    async handle() {
        const hasEmptyOption = Array.from(this.input.options).some(option => option.value === "");
        const oldValue = this.input.value;
        const newOptions = await this.fetchOptions();

        this.clearOptions();
        this.setOptions(newOptions, hasEmptyOption, oldValue);
    }

    async fetchOptions() {
        const params = new URLSearchParams();

        this.dependencies.forEach(dependency => {
            const formGroup = getFieldFormGroup(dependency);
            if (!formGroup) {
                return;
            }

            const field = getFormGroupField(formGroup);
            const value = getValue(field);

            if (Array.isArray(value)) {
                value.forEach(singleValue => {
                    params.append(dependency + "[]", singleValue);
                });
            } else {
                params.append(dependency, value);
            }
        });

        const queryParams = new URLSearchParams(params).toString();
        const response = await fetch(`${this.callbackUrl}?${queryParams}`);

        return await response.json();
    }

    clearOptions() {
        if (!isTomSelect(this.input)) {
            while (this.input.options.length > 0) {
                this.input.remove();
            }
            return;
        }

        const control = this.input.tomselect;
        control.lock();
        control.clear();
        control.clearOptions();
    }

    setOptions(options, hasEmptyOption, previousOption = null) {
        const input = this.input;

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

        const control = input.tomselect;
        const currentValue = previousOption || control.getValue();
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
            formGroup.removeEventListener('input', this.handle.bind(this));
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
    const input = document.querySelector(`[name*="[${field}]"]`);
    if (!input) {
        return null;
    }

    return getInputClosestFormGroup(input);
};

export const getFieldFormGroups = (field) => {
    const formGroup = document.querySelector(`[data-prototype*="_${field}__"]`);
    if (formGroup) {
        return [formGroup];
    }

    const inputs = document.querySelectorAll(`[name*="[${field}]"]`);
    return Array.from(inputs).map(getInputClosestFormGroup);
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

export const getOptions = (input) => {
    const data = input.getAttribute('data-dependent-field-options');

    if (!data) {
        return {
            callback_url: "",
            dependencies: [],
            fetch_on_init: false
        };
    }

    return JSON.parse(data);
};

export const hideField = (field) => {
    const formGroups = getFieldFormGroups(field);

    formGroups.forEach(formGroup => {
        formGroup.style.display = "none";

        const inputs = getFormGroupFields(formGroup);
        inputs.forEach(input => {
            input.setAttribute('disabled', 'mask');
        });
    });
};

export const showField = (field) => {
    const formGroups = getFieldFormGroups(field);

    formGroups.forEach(formGroup => {
        formGroup.style.display = null;

        const inputs = getFormGroupFields(formGroup);
        inputs.forEach(input => {
            if (input.getAttribute('disabled') === 'mask') {
                input.removeAttribute('disabled');
            }
        });
    });
};

export {
    controller_dependent as default
};
