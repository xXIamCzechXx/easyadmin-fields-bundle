import { Controller } from "@hotwired/stimulus";

/**
 * @author Ing. Dominik Mach <xXIamCzechXx@gmail.com>
 */
const controller_dependent = class extends Controller {

    connect() {
        this.hideOptions = JSON.parse(this.element.getAttribute('data-dependent-hide-options'));
        this.adaptOptions = JSON.parse(this.element.getAttribute('data-dependent-adapt-options'));

        this.root = this.element.closest('.field-collection-item') ?? this.element.closest('form') ?? document;
        this.dependenciesFormGroup = [];

        this.input = getFormGroupField(this.element);

        if (this.hideOptions != null) {
            this.hideOptions.dependencies.forEach(dependency => {
                let formGroup = getFieldFormGroup(dependency, this.root);
                if (formGroup) {
                    formGroup.addEventListener('input', this.handleHide.bind(this));
                    formGroup.addEventListener('change', this.handleHide.bind(this));
                    this.dependenciesFormGroup.push(formGroup);
                }

                if (this.hideOptions.fetch_on_init) {
                    this.handleHide().catch(e => console.error(e));
                }
            });
        }

        if (this.adaptOptions != null) {
            this.adaptOptions.dependencies.forEach(dependency => {
                let formGroup = getFieldFormGroup(dependency, this.root);
                if (formGroup) {
                    formGroup.addEventListener('input', this.handleAdapt.bind(this));
                    this.dependenciesFormGroup.push(formGroup);
                }
            });

            if (this.adaptOptions.fetch_on_init) {
                this.handleAdapt().catch(e => console.error(e));
            }
        }
    }

    async handleAdapt() {
        let hasEmptyOption = Array.from(this.input.options).some(option => option.value === "");
        let oldValue = this.input.value;
        let newOptions = await this.fetchOptions(this.adaptOptions);

        this.clearOptions().setOptions(newOptions, hasEmptyOption, oldValue);
    }

    async handleHide() {
        let show = await this.fetchOptions(this.hideOptions);

        if (show === true) {
            showField(this.input);
        } else {
            hideField(this.input);
        }
    }

    async fetchOptions(options) {
        let params = new URLSearchParams();

        options.dependencies.forEach(dependency => {
            let formGroup = getFieldFormGroup(dependency, this.root);
            if (!formGroup) {
                return;
            }

            let field = getFormGroupField(formGroup);
            let value = getValue(field);

            if (value) { // cannot send parameter with an empty value
                if (Array.isArray(value)) {
                    value.forEach(singleValue => {
                        params.append(dependency + "[]", singleValue);
                    });
                } else {
                    params.append(dependency, value);
                }
            }
        });

        let query = new URLSearchParams(params).toString();
        let response = await fetch(`${options.callback_url}?${query}`);

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

            options.forEach((option, index) => {
                const opt = new Option(option.text, option.value);

                if (previousOption && String(option.value) === String(previousOption)) {
                    opt.selected = true;
                } else if (!previousOption && !hasEmptyOption && index === 0) {
                    opt.selected = true;
                }

                input.options.add(opt);
            });

            return;
        }

        let control = input.tomselect;
        let currentValue = previousOption || control.getValue();

        control.settings.render.option = function(data, escape) {
            return `<div>${data.html ?? escape(data.text)}</div>`;
        };

        control.settings.render.item = function(data, escape) {
            return `<div>${data.html ?? escape(data.text)}</div>`;
        };
        control.addOptions(options);

        if (currentValue && control.options[currentValue]) {
            control.setValue(currentValue);
        } else if (options.length > 0) { // TODO::Can be configurable!!!
            control.setValue(String(options[0].value), true);
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

    if (input.getAttribute('type') === 'radio') {
        const checked = input.closest('.field-select').querySelector(`input[type="radio"][name="${CSS.escape(input.name)}"]:checked`);
        return checked ? checked.value : "0";
    }

    return input.value;
};

export const isTomSelect = (element) => {
    return element && element.tomselect !== undefined;
};

export const getFieldFormGroup = (field, root) => {
    // support for embedded crud controllers
    const isCollectionScope = root?.classList?.contains('field-collection-item') || !!root?.closest?.('.field-collection-item');

    const findCandidates = (scope) =>
        Array.from(scope.querySelectorAll(`[name$="[${field}]"], [name*="[${field}]"]`));

    let candidates = findCandidates(root);

    if (isCollectionScope) {
        const item = root.classList.contains('field-collection-item') ? root : root.closest('.field-collection-item');
        candidates = candidates.filter((el) => el.closest('.field-collection-item') === item);
    } else {
        candidates = candidates.filter((el) => !el.closest('.field-collection-item'));
    }

    if (!candidates.length) {
        return null;
    }

    const exact = candidates.find((el) => el.name?.endsWith(`[${field}]`));
    const input = exact ?? candidates[0];

    return getInputClosestFormGroup(input);
};

export const getFormGroupField = (formGroup) => {
    return formGroup.querySelector('select, input, textarea, button, a');
};

export const getFormGroupFields = (formGroup) => {
    return formGroup.querySelectorAll('select, input, textarea, button, a');
};

export const getInputClosestFormGroup = (input) => {
    return input.closest('.js-form-group-override') || input.closest('.form-group');
};

const DURATION = 360;

export const hideField = (field) => {
    const el = getInputClosestFormGroup(field);

    el.style.transition = `opacity ${DURATION}ms ease`;
    el.style.opacity = "0";

    window.setTimeout(() => {
        el.style.display = "none";
        if (el.parentElement) el.parentElement.style.display = "none";
    }, DURATION);
};

export const showField = (field) => {
    const el = getInputClosestFormGroup(field);

    if (el.parentElement) {
        el.parentElement.style.display = "";
    }
    el.style.display = "";

    // start from 0 then fade in
    el.style.transition = `opacity ${DURATION}ms ease`;
    el.style.opacity = "0";

    requestAnimationFrame(() => {
        el.style.opacity = "1";
    });
};

export {
    controller_dependent as default
};
