import { Controller } from "@hotwired/stimulus";

var controller_dependent = class extends Controller {
    connect() {
        const options = getOptions(this.element);
        this.callbackUrl = options.callback_url;
        this.dependencies = options.dependencies;
        this.dependenciesFormGroup = [];

        this.dependencies.forEach(dependency => {
            const formGroup = getFieldFormGroup(dependency);
            if (!formGroup) return;

            formGroup.addEventListener('input', this.handle.bind(this));
            this.dependenciesFormGroup.push(formGroup);
        });

        this.input = getFormGroupField(this.element);
        this.isTomselect = Boolean(this.element.querySelector('.tomselected'));

        if (options.fetch_on_init) {
            this.handle();
        }
    }

    disconnect() {
        this.dependenciesFormGroup.forEach(formGroup => {
            formGroup.removeEventListener('input', this.handle.bind(this));
        });
    }

    async handle() {
        const input = this.input;

        const oldValue = input.value
        const hasEmptyOption = Array.from(input.options).some(option => option.value === "");

        this.clearOptions();

        const newOptions = await this.fetchOptions();

        this.setOptions(newOptions, hasEmptyOption, oldValue);
    }

    async fetchOptions() {
        const params = new URLSearchParams();

        this.dependencies.forEach(dependency => {
            const formGroup = getFieldFormGroup(dependency);
            if (!formGroup) return;

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
                this.input.remove(0);
            }
            return;
        }

        const control = this.input.tomselect;
        control.lock();
        control.clear();
        control.clearOptions();
    }

    setOptions(options, hasEmptyOption, oldValue = null) {
        const input = this.input;

        if (!isTomSelect(input)) {
            if (hasEmptyOption) {
                input.options.add(new Option("", ""));
            }

            options.forEach(option => {
                const opt = new Option(option.text, option.value);

                // 🧩 pokud stará hodnota odpovídá, označ ji jako selected
                if (oldValue && String(option.value) === String(oldValue)) {
                    opt.selected = true;
                }

                input.options.add(opt);
            });

            // 🧩 pokud původní hodnota nebyla nalezena, vynuluj
            const stillValid = Array.from(input.options).some(opt => opt.value === oldValue);
            if (!stillValid) {
                input.value = "";
            }

            return;
        }

        const control = input.tomselect;
        const currentValue = oldValue || control.getValue();
        control.addOptions(options);

        // 🧩 Pokud stará hodnota je mezi novými options, vyber ji
        if (currentValue && control.options[currentValue]) {
            control.setValue(currentValue);
        } else {
            control.clear();
        }

        control.refreshOptions(false);
        control.unlock();
    }
}

export const getValue = (input) => {
    if (isTomSelect(input)) {
        return input.tomselect.getValue();
    }

    if (input.getAttribute('type') === 'checkbox') {
        // chceme vrátit string hodnotu, ne boolean
        return input.checked ? "true" : "false";
    }

    return input.value;
};

export const isTomSelect = (element) => {
    return element && element.tomselect !== undefined;
};

export const getFieldFormGroup = (field) => {
    // najdi odpovídající input
    const input = document.querySelector(`[name*="[${field}]"]`);
    if (!input) {
        return null;
    }

    return getInputClosestFormGroup(input);
};

export const getFieldFormGroups = (field) => {
    // nejprve zkus najít form group přímo (např. pro kolekce)
    const formGroup = document.querySelector(`[data-prototype*="_${field}__"]`);
    if (formGroup) {
        return [formGroup];
    }

    // najdi všechny odpovídající inputy
    const inputs = document.querySelectorAll(`[name*="[${field}]"]`);
    return Array.from(inputs).map(getInputClosestFormGroup);
};

export const getFormGroupField = (formGroup) => {
    return formGroup.querySelector('select, input, textarea');
};

export const getFormGroupFields = (formGroup) => {
    return formGroup.querySelectorAll('select, input, textarea');
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

export const getCallbackUrl = (input) => {
    return input.getAttribute('data-dependent-field-callback-url');
};

export {
    controller_dependent as default
};
