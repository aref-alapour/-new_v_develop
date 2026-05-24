import { h, Host } from "@stencil/core";
export class EzSansItem {
    session;
    formatPrice(price) {
        return new Intl.NumberFormat('fa-IR').format(price);
    }
    render() {
        let session = this.session;
        if (typeof session === 'string') {
            try {
                session = JSON.parse(session);
            }
            catch {
                session = {};
            }
        }
        const { time = '', price = 0, is_booked = false, discount_price = null, capacity = 0 } = session || {};
        return (h(Host, { key: 'de053aee7e72c8d773fae7fa87fb84ca6ef04da3', class: `block ${is_booked ? 'opacity-60 pointer-events-none grayscale' : ''}` }, h("div", { key: '33f6f00706c08b2517ebe5d6502d6db302561594', class: `
          relative flex flex-col items-center justify-center p-3 rounded-xl border-2 transition-all duration-200 group
          ${is_booked
                ? 'border-gray-100 bg-gray-50'
                : 'border-gray-200 bg-white hover:border-primary-500 hover:shadow-lg hover:-translate-y-1 cursor-pointer'}
        ` }, discount_price && !is_booked && (h("div", { key: 'd5030da2555028410016938a0f116221f62d6460', class: "absolute -top-3 right-2 bg-red-500 text-white text-10 px-2 py-0.5 rounded-full shadow-sm font-yekan-bold" }, "\u062A\u062E\u0641\u06CC\u0641 \u0648\u06CC\u0698\u0647")), h("div", { key: '44334cd67e696d1b720fa8e45236b2c745dbef72', class: `text-lg font-yekan-black mb-1 ${is_booked ? 'text-gray-400' : 'text-navyBlue group-hover:text-primary-600'}` }, time), h("div", { key: '1801b8433d393251610e449d3a60b63617fac5ea', class: "flex flex-col items-center gap-0.5" }, discount_price && !is_booked ? (h("div", { class: "flex flex-col items-center" }, h("span", { class: "text-xs text-gray-400 line-through decoration-red-400 decoration-1" }, this.formatPrice(price)), h("span", { class: "text-sm font-yekan-bold text-green-600 flex items-center gap-1" }, this.formatPrice(discount_price), h("span", { class: "text-10 font-yekan-regular text-gray-500" }, "\u062A\u0648\u0645\u0627\u0646")))) : (h("span", { class: "text-sm font-yekan-bold text-gray-600 flex items-center gap-1" }, is_booked ? 'رزرو شده' : (h("span", null, this.formatPrice(price), " ", h("span", { class: "text-10 font-yekan-regular text-gray-400" }, "\u062A\u0648\u0645\u0627\u0646")))))), !is_booked && (h("div", { key: '637f97edfd218c6f21447d174437179483830f29', class: "mt-2 w-full pt-2 border-t border-dashed border-gray-100 flex justify-between items-center text-10 text-gray-400" }, h("span", { key: 'd04a1af28ed6822d6509260e85ef086780a410b7' }, "\u0638\u0631\u0641\u06CC\u062A: ", capacity), h("span", { key: '0a47781ceb998f24d151043a4f993f3242cdefb9', class: "text-primary-500 font-bold group-hover:block hidden" }, "\u0631\u0632\u0631\u0648 \u06A9\u0646\u06CC\u062F"))))));
    }
    static get is() { return "ez-sans-item"; }
    static get properties() {
        return {
            "session": {
                "type": "any",
                "mutable": false,
                "complexType": {
                    "original": "any",
                    "resolved": "any",
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
                "attribute": "session"
            }
        };
    }
}
