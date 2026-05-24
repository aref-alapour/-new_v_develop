import { h } from "@stencil/core";
export class EzAccordion {
    el;
    render() {
        return (h("div", { key: 'b53f50cfe4a6cd303adc7d623d0cb6f9b58339a2', class: "w-full rounded-2xl border border-gray-100 bg-white px-4 shadow-sm lg:px-6" }, h("slot", { key: 'e1bf6061a59361bd7164407cfe3397162ee85e0b' })));
    }
    static get is() { return "ez-accordion"; }
    static get elementRef() { return "el"; }
}
