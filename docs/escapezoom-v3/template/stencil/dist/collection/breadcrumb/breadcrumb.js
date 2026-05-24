import { h } from "@stencil/core";
export class EzBreadcrumb {
    items = [];
    _items = [];
    componentWillLoad() {
        this.parseItems(this.items);
    }
    parseItems(newValue) {
        if (typeof newValue === 'string') {
            try {
                this._items = JSON.parse(newValue);
            }
            catch (e) {
                this._items = [];
            }
        }
        else {
            this._items = Array.isArray(newValue) ? newValue : [];
        }
    }
    render() {
        if (this._items.length === 0)
            return null;
        return (h("nav", { class: "flex", "aria-label": "Breadcrumb" }, h("ol", { class: "inline-flex items-center" }, this._items.map((item, index) => (h("li", { class: "group" }, h("div", { class: "flex items-center" }, index > 0 && (h("div", { class: "mx-5 h-2 w-px bg-slate-110" })), item.url ? (h("a", { class: "text-2xs font-medium text-slate-310 hover:text-primary-600 transition-colors", href: item.url }, item.label)) : (h("span", { class: "text-2xs font-medium text-slate-310 cursor-text opacity-75" }, item.label)))))))));
    }
    static get is() { return "ez-breadcrumb"; }
    static get properties() {
        return {
            "items": {
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
                "attribute": "items",
                "defaultValue": "[]"
            }
        };
    }
    static get states() {
        return {
            "_items": {}
        };
    }
    static get watchers() {
        return [{
                "propName": "items",
                "methodName": "parseItems"
            }];
    }
}
