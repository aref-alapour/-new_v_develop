import { r as registerInstance, c as createEvent, g as getElement, h, H as Host } from './index-DclNjYd0.js';

const EzDropdownItem = class {
    constructor(hostRef) {
        registerInstance(this, hostRef);
        this.optionSelect = createEvent(this, "optionSelect");
    }
    get el() { return getElement(this); }
    value;
    selected = false;
    optionSelect;
    handleClick = (e) => {
        e.preventDefault();
        e.stopPropagation();
        const text = (this.el.textContent || '').trim();
        this.optionSelect.emit({ value: this.value, text });
    };
    render() {
        return (h(Host, { key: 'e8f3c42f2e021b117721976645311b3cbcb3af20', class: "block" }, h("a", { key: 'd72672302505874f382d0401617c810ee0b1c13f', href: "#", class: {
                'block px-4 py-2 text-sm hover:bg-gray-100 transition-colors': true,
                'bg-blue-50 text-primary-600': this.selected,
                'text-gray-700': !this.selected
            }, onClick: this.handleClick }, h("slot", { key: '2dc5803e965b1e7ec30491c7b082ff8b52f955c3' }))));
    }
};

export { EzDropdownItem as ez_dropdown_item };
