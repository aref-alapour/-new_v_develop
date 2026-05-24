import { r as registerInstance, c as createEvent, g as getElement, h } from './index-DclNjYd0.js';

const autocompleteCss = () => `:host{display:block;width:100%}`;

const EzAutocomplete = class {
    constructor(hostRef) {
        registerInstance(this, hostRef);
        this.search = createEvent(this, "search");
        this.selection = createEvent(this, "selection");
    }
    get el() { return getElement(this); }
    placeholder = 'جستجو...';
    debounce = 300;
    loading = false;
    results = [];
    value = '';
    searchQuery = '';
    _results = [];
    isOpen = false;
    search;
    selection;
    timer;
    inputRef;
    componentWillLoad() {
        this.searchQuery = this.value;
        this.parseResults(this.results);
    }
    componentDidLoad() {
        document.addEventListener('click', this.handleOutsideClick);
    }
    disconnectedCallback() {
        document.removeEventListener('click', this.handleOutsideClick);
    }
    handleOutsideClick = (e) => {
        if (!this.el.contains(e.target)) {
            this.isOpen = false;
        }
    };
    parseResults(val) {
        if (typeof val === 'string') {
            try {
                this._results = JSON.parse(val);
            }
            catch (e) {
                this._results = [];
            }
        }
        else {
            this._results = val || [];
        }
    }
    handleInput = (e) => {
        const val = e.target.value;
        this.searchQuery = val;
        this.isOpen = val.length > 0;
        if (this.timer)
            clearTimeout(this.timer);
        this.timer = setTimeout(() => {
            this.search.emit(val);
        }, this.debounce);
    };
    handleSelect(item) {
        this.selection.emit(item);
        this.isOpen = false;
    }
    handleClear = () => {
        this.searchQuery = '';
        this.isOpen = false;
        this.search.emit('');
        if (this.inputRef)
            this.inputRef.focus();
    };
    render() {
        return (h("div", { key: '68d289f68c9469c9a290365c4222128eff1bcef8', class: "relative w-full" }, h("div", { key: '042118c090b439437a3b1d8cccacb95d3366a1a1', class: "relative" }, h("input", { key: '7929942018235b5740f35e9a9ce693bcd07655b9', ref: el => this.inputRef = el, type: "text", class: "w-full h-12 px-4 rounded-lg text-right border border-slate-105 placeholder:text-gray-400 focus:outline-none focus:ring-1 focus:ring-primary-500 bg-white", placeholder: this.placeholder, value: this.searchQuery, onInput: this.handleInput }), h("div", { key: 'b3a816e221846c0756861c6f0c35981949613b5f', class: "absolute left-3 top-1/2 transform -translate-y-1/2 flex items-center" }, this.loading ? (h("div", { class: "w-5 h-5 border-2 border-gray-200 border-t-primary-500 rounded-full animate-spin" })) : this.searchQuery ? (h("button", { type: "button", onClick: this.handleClear, class: "text-gray-400 hover:text-gray-600" }, h("svg", { xmlns: "http://www.w3.org/2000/svg", width: "20", height: "20", viewBox: "0 0 24 24", fill: "none", stroke: "currentColor", "stroke-width": "2", "stroke-linecap": "round", "stroke-linejoin": "round" }, h("line", { x1: "18", y1: "6", x2: "6", y2: "18" }), h("line", { x1: "6", y1: "6", x2: "18", y2: "18" })))) : (h("button", { type: "button", class: "text-gray-400" }, h("svg", { xmlns: "http://www.w3.org/2000/svg", width: "20", height: "20", viewBox: "0 0 24 24", fill: "none", stroke: "currentColor", "stroke-width": "2", "stroke-linecap": "round", "stroke-linejoin": "round" }, h("circle", { cx: "11", cy: "11", r: "8" }), h("line", { x1: "21", y1: "21", x2: "16.65", y2: "16.65" })))))), this.isOpen && (h("div", { key: '83a18ddbff17aff7f26b0dde009eb9394b38d6cf', class: "absolute w-full mt-2 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-[60]" }, this.loading ? (h("div", { class: "p-4 text-center text-sm text-gray-400" }, "\u062F\u0631 \u062D\u0627\u0644 \u062C\u0633\u062A\u062C\u0648...")) : this._results.length > 0 ? (h("ul", { class: "max-h-d300 overflow-y-auto" }, this._results.map(item => (h("li", null, h("a", { href: item.link || '#', class: "flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition-colors border-b border-gray-50 last:border-0", onClick: (e) => { e.preventDefault(); this.handleSelect(item); } }, item.image && (h("img", { src: item.image, class: "w-10 h-10 rounded-lg object-cover", alt: "" })), h("div", { class: "flex flex-col" }, h("span", { class: "text-sm font-bold text-gray-800" }, item.title), item.subtitle && h("span", { class: "text-xs text-gray-500" }, item.subtitle)))))))) : (h("div", { class: "p-4 text-center text-sm text-gray-400" }, this.searchQuery.length < 2 ? 'لطفا بیشتر تایپ کنید...' : 'موردی یافت نشد'))))));
    }
};
EzAutocomplete.style = autocompleteCss();

export { EzAutocomplete as ez_autocomplete };
