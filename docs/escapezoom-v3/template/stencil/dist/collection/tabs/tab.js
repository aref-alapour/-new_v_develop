import { h } from "@stencil/core";
export class EzTab {
    el;
    tabId;
    label;
    active = false;
    activeChanged(newValue) {
        if (newValue) {
            this.el.style.display = 'block';
        }
        else {
            this.el.style.display = 'none';
        }
    }
    componentDidLoad() {
        this.activeChanged(this.active);
    }
    render() {
        return (h("div", { key: 'c54fe2579cf48166055d0d7af23a5b60de61adc1', class: `transition-opacity duration-300 ${this.active ? 'opacity-100' : 'opacity-0 h-0 overflow-hidden'}` }, h("slot", { key: '6b0db08e62892ace0de903b487efa0df969704a6' })));
    }
    static get is() { return "ez-tab"; }
    static get properties() {
        return {
            "tabId": {
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
                "attribute": "tab-id"
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
            },
            "active": {
                "type": "boolean",
                "mutable": true,
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
                "attribute": "active",
                "defaultValue": "false"
            }
        };
    }
    static get elementRef() { return "el"; }
    static get watchers() {
        return [{
                "propName": "active",
                "methodName": "activeChanged"
            }];
    }
}
