import { h, Host } from "@stencil/core";
export class EzDropdown {
    el;
    label = 'انتخاب کنید';
    icon = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6,9 12,15 18,9"></polyline></svg>`;
    isOpen = false;
    selectedLabel = '';
    selectionChange;
    componentWillLoad() {
        this.selectedLabel = this.label;
    }
    handleOptionSelect(e) {
        this.selectedLabel = e.detail.text;
        this.selectionChange.emit(e.detail);
        this.isOpen = false;
    }
    toggleDropdown = (e) => {
        e.stopPropagation();
        this.isOpen = !this.isOpen;
    };
    closeDropdown = () => {
        this.isOpen = false;
    };
    handleWindowClick() {
        if (this.isOpen) {
            this.closeDropdown();
        }
    }
    render() {
        return (h(Host, { key: '50b528af74edbb7467faf9394a3181cc4f99e7c1', class: "relative inline-block text-right" }, h("button", { key: '496e6178757ab45098b4f5911fb85fb8b126f4cc', class: "flex items-center gap-2 px-4 py-3 bg-white border border-slate-105 rounded-xl text-sm font-yekan-bold text-navyBlue hover:bg-gray-50 transition-colors w-full justify-between", onClick: this.toggleDropdown, type: "button" }, h("div", { key: 'eca0a17da18fd1c4bdbb8e73770bfae1841de09b', class: "flex items-center gap-2" }, h("span", { key: 'cf469836cfa58b2e5aee002de6eded77d00cb8c2', innerHTML: this.icon }), h("span", { key: '247bf25ed6ad4bc0635a2168a98d9a0892c6ab52' }, this.selectedLabel))), h("div", { key: '74e7678e67888291c6cf89c37b37f7b88a5122fe', class: {
                'absolute right-0 mt-2 w-full min-w-d200 bg-white border border-slate-105 rounded-xl shadow-lg z-20 origin-top-right transition-all duration-200': true,
                'opacity-0 invisible scale-95': !this.isOpen,
                'opacity-100 visible scale-100': this.isOpen
            } }, h("div", { key: '6951cadb02d4c5f243249f00a266965d1806b964', class: "py-2" }, h("slot", { key: '17b9bd5c630a3a99d7e5dda4547a3b2c223e7e2c' })))));
    }
    static get is() { return "ez-dropdown"; }
    static get properties() {
        return {
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
                "attribute": "label",
                "defaultValue": "'\u0627\u0646\u062A\u062E\u0627\u0628 \u06A9\u0646\u06CC\u062F'"
            },
            "icon": {
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
                "attribute": "icon",
                "defaultValue": "`<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"><polyline points=\"6,9 12,15 18,9\"></polyline></svg>`"
            }
        };
    }
    static get states() {
        return {
            "isOpen": {},
            "selectedLabel": {}
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
    static get listeners() {
        return [{
                "name": "optionSelect",
                "method": "handleOptionSelect",
                "target": undefined,
                "capture": false,
                "passive": false
            }, {
                "name": "click",
                "method": "handleWindowClick",
                "target": "window",
                "capture": false,
                "passive": false
            }];
    }
}
