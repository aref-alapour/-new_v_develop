import { h } from "@stencil/core";
export class EzSelect {
    el;
    name;
    placeholder = 'انتخاب کنید';
    options = [];
    value;
    mode = 'single';
    searchable = false;
    label;
    isOpen = false;
    searchQuery = '';
    _options = [];
    internalValue = '';
    displayValue = '';
    selectionChange;
    componentWillLoad() {
        this.parseOptions(this.options);
        this.syncInternalValue(this.value);
    }
    parseOptions(newValue) {
        if (typeof newValue === 'string') {
            try {
                this._options = JSON.parse(newValue);
            }
            catch (e) {
                this._options = [];
            }
        }
        else {
            this._options = Array.isArray(newValue) ? [...newValue] : [];
        }
        this.updateDisplayValue();
    }
    syncInternalValue(newValue) {
        if (this.mode === 'multiple') {
            this.internalValue = Array.isArray(newValue) ? newValue : (newValue ? [newValue] : []);
        }
        else {
            this.internalValue = newValue ?? '';
        }
        this.updateDisplayValue();
    }
    updateDisplayValue() {
        if (this.mode === 'multiple') {
            const vals = this.internalValue;
            if (!vals || vals.length === 0) {
                this.displayValue = '';
                return;
            }
            const labels = this._options.filter(opt => vals.includes(opt.value)).map(opt => opt.label);
            this.displayValue = labels.join(', ');
        }
        else {
            const val = this.internalValue;
            const found = this._options.find(opt => opt.value == val);
            this.displayValue = found ? found.label : '';
        }
    }
    toggleDropdown = (e) => {
        e.stopPropagation();
        if (!this.isOpen)
            this.searchQuery = '';
        this.isOpen = !this.isOpen;
    };
    closeDropdown = () => {
        this.isOpen = false;
    };
    handleWindowClick() {
        if (this.isOpen)
            this.closeDropdown();
    }
    handleOptionClick(e, option) {
        e.stopPropagation();
        if (this.mode === 'multiple') {
            const currentVals = [...this.internalValue];
            const index = currentVals.indexOf(option.value);
            if (index > -1) {
                currentVals.splice(index, 1);
            }
            else {
                currentVals.push(option.value);
            }
            this.internalValue = currentVals;
        }
        else {
            this.internalValue = option.value;
            this.closeDropdown();
        }
        this.updateDisplayValue();
        this.selectionChange.emit(this.internalValue);
    }
    handleSearchInput = (e) => {
        this.searchQuery = e.target.value.toLowerCase();
    };
    getFilteredOptions() {
        if (!this.searchQuery)
            return this._options;
        return this._options.filter(opt => opt.label.toLowerCase().includes(this.searchQuery));
    }
    render() {
        const filteredOptions = this.getFilteredOptions();
        return (h("div", { key: 'd0eedf95a53d10e7faffcd7db59c0726fe91b8fc', class: "relative w-full max-w-xs font-sans text-right", dir: "rtl" }, this.label && h("label", { key: '94d9bd000ff1f53367dc12338fa974961ff7b556', class: "mb-2 block text-sm font-bold text-steel" }, this.label), this.mode === 'single' ? (h("input", { type: "hidden", name: this.name, value: this.internalValue })) : (this.internalValue.map((val) => (h("input", { type: "hidden", name: `${this.name}[]`, value: val })))), h("button", { key: '8c412a4f7ed6cb9b0bb9d45a9daf536c0c75c943', type: "button", class: "w-full bg-white border border-gray-100/80 rounded-lg max-lg:shadow-13 h-d48 px-4 py-2 text-right flex items-center justify-between focus:outline-none focus:ring-1 focus:ring-primary-500", onClick: this.toggleDropdown }, h("span", { key: '4ae5bcfa9570244b1d250de7285b745f03ae4280', class: "text-ink-tab font-extrabold truncate" }, this.displayValue || h("span", { key: '4dd98fb08a3f9db417350646314a1298dc835fd1', class: "text-gray-400 font-normal" }, this.placeholder)), h("svg", { key: '3e25868fef14f5c4fe5ad73a13e7affa8093049b', class: `w-4 h-4 text-gray-400 m-0 transition-transform ${this.isOpen ? 'rotate-180' : ''}`, fill: "none", stroke: "currentColor", viewBox: "0 0 24 24" }, h("path", { key: '3186569ef53313117eb33a28da6f0ea312e37765', "stroke-linecap": "round", "stroke-linejoin": "round", "stroke-width": "2", d: "M19 9l-7 7-7-7" }))), this.isOpen && (h("div", { key: 'cdfa8b76cd9204d584902698213a083d4352410f', class: "absolute w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-50 overflow-hidden" }, this.searchable && (h("div", { key: '64eda421e4b608848a6376aed2547c25e9d91e5f', class: "p-2 border-b border-gray-100" }, h("input", { key: 'd1c3b4ddb35204341bcbd92ce177491deec9f30b', type: "text", class: "w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:border-primary-500", placeholder: "\u062C\u0633\u062A\u062C\u0648...", onInput: this.handleSearchInput, onClick: (e) => e.stopPropagation() }))), h("div", { key: '0bec52d1f42edbcc5fd77be653d5c276a71f38f9', class: "max-h-60 overflow-y-auto custom-scrollbar" }, filteredOptions.length > 0 ? (filteredOptions.map(opt => {
            const isSelected = this.mode === 'multiple'
                ? this.internalValue.includes(opt.value)
                : this.internalValue == opt.value;
            return (h("div", { class: `block w-full px-4 py-2 cursor-pointer transition flex items-center justify-between
                        ${isSelected ? 'bg-primary-50 text-primary-600' : 'hover:bg-gray-100 hover:text-primary-500 text-gray-700'}`, onClick: (e) => this.handleOptionClick(e, opt) }, h("span", null, opt.label), this.mode === 'multiple' && (h("input", { type: "checkbox", checked: isSelected, class: "bg-primary-600 border-gray-300 rounded focus:ring-primary-500", readOnly: true })), this.mode === 'single' && isSelected && (h("svg", { class: "w-4 h-4 text-primary-600", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor" }, h("path", { "stroke-linecap": "round", "stroke-linejoin": "round", "stroke-width": "2", d: "M5 13l4 4L19 7" })))));
        })) : (h("div", { class: "px-4 py-3 text-sm text-gray-400 text-center" }, "\u0645\u0648\u0631\u062F\u06CC \u06CC\u0627\u0641\u062A \u0646\u0634\u062F")))))));
    }
    static get is() { return "ez-select"; }
    static get originalStyleUrls() {
        return {
            "$": ["select.css"]
        };
    }
    static get styleUrls() {
        return {
            "$": ["select.css"]
        };
    }
    static get properties() {
        return {
            "name": {
                "type": "string",
                "mutable": false,
                "complexType": {
                    "original": "string",
                    "resolved": "string",
                    "references": {}
                },
                "required": false,
                "optional": false,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "name"
            },
            "placeholder": {
                "type": "string",
                "mutable": false,
                "complexType": {
                    "original": "string",
                    "resolved": "string",
                    "references": {}
                },
                "required": false,
                "optional": false,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "placeholder",
                "defaultValue": "'\u0627\u0646\u062A\u062E\u0627\u0628 \u06A9\u0646\u06CC\u062F'"
            },
            "options": {
                "type": "string",
                "mutable": false,
                "complexType": {
                    "original": "string | any[]",
                    "resolved": "any[] | string",
                    "references": {}
                },
                "required": false,
                "optional": false,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "options",
                "defaultValue": "[]"
            },
            "value": {
                "type": "string",
                "mutable": false,
                "complexType": {
                    "original": "string | string[]",
                    "resolved": "string | string[]",
                    "references": {}
                },
                "required": false,
                "optional": false,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "value"
            },
            "mode": {
                "type": "string",
                "mutable": false,
                "complexType": {
                    "original": "'single' | 'multiple'",
                    "resolved": "\"multiple\" | \"single\"",
                    "references": {}
                },
                "required": false,
                "optional": false,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "mode",
                "defaultValue": "'single'"
            },
            "searchable": {
                "type": "boolean",
                "mutable": false,
                "complexType": {
                    "original": "boolean",
                    "resolved": "boolean",
                    "references": {}
                },
                "required": false,
                "optional": false,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "searchable",
                "defaultValue": "false"
            },
            "label": {
                "type": "string",
                "mutable": false,
                "complexType": {
                    "original": "string",
                    "resolved": "string",
                    "references": {}
                },
                "required": false,
                "optional": false,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "label"
            }
        };
    }
    static get states() {
        return {
            "isOpen": {},
            "searchQuery": {},
            "_options": {},
            "internalValue": {},
            "displayValue": {}
        };
    }
    static get events() {
        return [{
                "method": "selectionChange",
                "name": "selectionChange",
                "bubbles": true,
                "cancelable": true,
                "composed": true,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "complexType": {
                    "original": "any",
                    "resolved": "any",
                    "references": {}
                }
            }];
    }
    static get elementRef() { return "el"; }
    static get watchers() {
        return [{
                "propName": "options",
                "methodName": "parseOptions"
            }, {
                "propName": "value",
                "methodName": "syncInternalValue"
            }];
    }
    static get listeners() {
        return [{
                "name": "click",
                "method": "handleWindowClick",
                "target": "window",
                "capture": false,
                "passive": false
            }];
    }
}
