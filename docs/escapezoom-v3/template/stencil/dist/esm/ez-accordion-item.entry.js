import { r as registerInstance, h } from './index-DclNjYd0.js';

const EzAccordionItem = class {
    constructor(hostRef) {
        registerInstance(this, hostRef);
    }
    label;
    open = false;
    isOpen = false;
    componentWillLoad() {
        this.isOpen = this.open;
    }
    toggle = () => {
        this.isOpen = !this.isOpen;
    };
    render() {
        return (h("div", { key: '3efa0b75587a75d9bac19e7c0abfeb99d9f66196', class: "border-b border-gray-100 last:border-0" }, h("button", { key: 'f212c593ffc971ec0f970d596cd04757609f1eb4', type: "button", class: "flex w-full items-center justify-between py-4 text-right text-sm font-bold text-slate-800 hover:text-primary-600 transition-colors bg-white focus:outline-none", onClick: this.toggle, "aria-expanded": this.isOpen.toString() }, h("span", { key: '68d7aa6b0f0de7790e2c29fe8c9ae9625579e91b' }, this.label), h("svg", { key: '4f78b74044caf6b6fae972c7f5cdd23cb2945d89', class: `h-5 w-5 transform text-gray-400 transition-transform duration-200 ${this.isOpen ? 'rotate-180' : ''}`, fill: "none", viewBox: "0 0 24 24", stroke: "currentColor" }, h("path", { key: 'bb68729cf3c97cc9e62d6d5c256724350e82b6a4', "stroke-linecap": "round", "stroke-linejoin": "round", "stroke-width": "2", d: "M19 9l-7 7-7-7" }))), h("div", { key: '90f6d7dceca13d93e2ac7c42248e36d9a4c28263', class: `overflow-hidden transition-all duration-300 ease-in-out ${this.isOpen ? 'max-h-96 opacity-100 mb-4' : 'max-h-0 opacity-0'}` }, h("div", { key: '21ad6209dd8a520d7ef1cd32cb822fcae83eb444', class: "text-sm text-gray-600 leading-relaxed px-1" }, h("slot", { key: 'ee91d150c535485dbc9bf694cd06c13e5af205f5' })))));
    }
};

export { EzAccordionItem as ez_accordion_item };
