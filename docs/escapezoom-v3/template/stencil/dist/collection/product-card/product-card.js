import { h, Host } from "@stencil/core";
export class EzProductCard {
    productId;
    status;
    href;
    isSlide = false;
    /** عنوان محصول (از سرور؛ در HTML به‌صورت product-title پاس داده می‌شود تا با titleٔ رزروشده تداخل نداشته باشد). */
    productTitle;
    price;
    imageUrl;
    address;
    render() {
        return (h(Host, { key: 'e1e54f3e2d1e376b4d3bdc9c6c16769c494410ae', class: {
                'embla__slide': !!this.isSlide,
                'font-[var(--font-yekan)]': true,
            }, role: "article", "data-product-id": this.productId, "data-status": this.status, style: { display: 'block' } }, h("div", { key: '60d5588fb0d7170024e755a0f12d5eb1d389b93b', class: "relative overflow-hidden rounded-[var(--radius-ez)] lg:rounded-2xl lg:shadow-[var(--tw-shadow,0_4px_6px_-1px_rgba(0,0,0,.1),0_2px_4px_-2px_rgba(0,0,0,.1))]" }, h("div", { key: '81c496465a78a8c0829ef6d229142466a5f004c8', class: "relative" }, this.imageUrl ? (h("a", { href: this.href || '#', class: "block" }, h("img", { src: this.imageUrl, alt: this.productTitle || '', class: "w-full h-full object-cover", loading: "lazy" }))) : (h("slot", { name: "media" })), h("slot", { key: '494c61cfe8c6469650c08a2729a94964e40e2de7', name: "badge" }), h("slot", { key: '30c1d26d47f13f9b8b0cc02eac26a30519a8f257', name: "floating-action" })), h("slot", { key: '9c82af5ab88d098a0f37ec01bcbb8a9e0bc475bb', name: "overlay-panel" })), h("slot", { key: 'a5212acc50486da67ff7c3307a703ff2d267be90', name: "meta" }), this.productTitle ? (h("a", { href: this.href || '#', class: "block font-semibold text-inherit hover:text-[var(--color-brand-primary)]" }, this.productTitle)) : (h("slot", { name: "title" })), this.address ? (h("p", { class: "text-sm text-gray-600" }, this.address)) : (h("slot", { name: "address" })), this.price ? (h("p", { class: "text-[var(--color-brand-primary)] font-medium" }, this.price)) : (h("slot", { name: "pricing" }))));
    }
    static get is() { return "ez-product-card"; }
    static get properties() {
        return {
            "productId": {
                "type": "any",
                "mutable": false,
                "complexType": {
                    "original": "string | number",
                    "resolved": "number | string",
                    "references": {}
                },
                "required": false,
                "optional": true,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "product-id"
            },
            "status": {
                "type": "string",
                "mutable": false,
                "complexType": {
                    "original": "string",
                    "resolved": "string",
                    "references": {}
                },
                "required": false,
                "optional": true,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "status"
            },
            "href": {
                "type": "string",
                "mutable": false,
                "complexType": {
                    "original": "string",
                    "resolved": "string",
                    "references": {}
                },
                "required": false,
                "optional": true,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "href"
            },
            "isSlide": {
                "type": "boolean",
                "mutable": false,
                "complexType": {
                    "original": "boolean",
                    "resolved": "boolean",
                    "references": {}
                },
                "required": false,
                "optional": true,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "is-slide",
                "defaultValue": "false"
            },
            "productTitle": {
                "type": "string",
                "mutable": false,
                "complexType": {
                    "original": "string",
                    "resolved": "string",
                    "references": {}
                },
                "required": false,
                "optional": true,
                "docs": {
                    "tags": [],
                    "text": "\u0639\u0646\u0648\u0627\u0646 \u0645\u062D\u0635\u0648\u0644 (\u0627\u0632 \u0633\u0631\u0648\u0631\u061B \u062F\u0631 HTML \u0628\u0647\u200C\u0635\u0648\u0631\u062A product-title \u067E\u0627\u0633 \u062F\u0627\u062F\u0647 \u0645\u06CC\u200C\u0634\u0648\u062F \u062A\u0627 \u0628\u0627 title\u0654 \u0631\u0632\u0631\u0648\u0634\u062F\u0647 \u062A\u062F\u0627\u062E\u0644 \u0646\u062F\u0627\u0634\u062A\u0647 \u0628\u0627\u0634\u062F)."
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "product-title"
            },
            "price": {
                "type": "string",
                "mutable": false,
                "complexType": {
                    "original": "string",
                    "resolved": "string",
                    "references": {}
                },
                "required": false,
                "optional": true,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "price"
            },
            "imageUrl": {
                "type": "string",
                "mutable": false,
                "complexType": {
                    "original": "string",
                    "resolved": "string",
                    "references": {}
                },
                "required": false,
                "optional": true,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "image-url"
            },
            "address": {
                "type": "string",
                "mutable": false,
                "complexType": {
                    "original": "string",
                    "resolved": "string",
                    "references": {}
                },
                "required": false,
                "optional": true,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "address"
            }
        };
    }
}
