import { r as registerInstance, h } from './index-DclNjYd0.js';

const EzBreadcrumb = class {
    constructor(hostRef) {
        registerInstance(this, hostRef);
    }
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
    static get watchers() { return {
        "items": [{
                "parseItems": 0
            }]
    }; }
};

export { EzBreadcrumb as ez_breadcrumb };
