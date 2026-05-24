'use strict';

var index = require('./index-COnMUfPy.js');

const EzDropdownItem = class {
    constructor(hostRef) {
        index.registerInstance(this, hostRef);
        this.optionSelect = index.createEvent(this, "optionSelect");
    }
    get el() { return index.getElement(this); }
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
        return (index.h(index.Host, { key: 'e8f3c42f2e021b117721976645311b3cbcb3af20', class: "block" }, index.h("a", { key: 'd72672302505874f382d0401617c810ee0b1c13f', href: "#", class: {
                'block px-4 py-2 text-sm hover:bg-gray-100 transition-colors': true,
                'bg-blue-50 text-primary-600': this.selected,
                'text-gray-700': !this.selected
            }, onClick: this.handleClick }, index.h("slot", { key: '2dc5803e965b1e7ec30491c7b082ff8b52f955c3' }))));
    }
};

exports.ez_dropdown_item = EzDropdownItem;
