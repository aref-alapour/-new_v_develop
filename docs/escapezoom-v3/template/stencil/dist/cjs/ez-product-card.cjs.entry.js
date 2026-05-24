'use strict';

var index = require('./index-COnMUfPy.js');

const EzProductCard = class {
    constructor(hostRef) {
        index.registerInstance(this, hostRef);
    }
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
        return (index.h(index.Host, { key: 'e1e54f3e2d1e376b4d3bdc9c6c16769c494410ae', class: {
                'embla__slide': !!this.isSlide,
                'font-[var(--font-yekan)]': true,
            }, role: "article", "data-product-id": this.productId, "data-status": this.status, style: { display: 'block' } }, index.h("div", { key: '60d5588fb0d7170024e755a0f12d5eb1d389b93b', class: "relative overflow-hidden rounded-[var(--radius-ez)] lg:rounded-2xl lg:shadow-[var(--tw-shadow,0_4px_6px_-1px_rgba(0,0,0,.1),0_2px_4px_-2px_rgba(0,0,0,.1))]" }, index.h("div", { key: '81c496465a78a8c0829ef6d229142466a5f004c8', class: "relative" }, this.imageUrl ? (index.h("a", { href: this.href || '#', class: "block" }, index.h("img", { src: this.imageUrl, alt: this.productTitle || '', class: "w-full h-full object-cover", loading: "lazy" }))) : (index.h("slot", { name: "media" })), index.h("slot", { key: '494c61cfe8c6469650c08a2729a94964e40e2de7', name: "badge" }), index.h("slot", { key: '30c1d26d47f13f9b8b0cc02eac26a30519a8f257', name: "floating-action" })), index.h("slot", { key: '9c82af5ab88d098a0f37ec01bcbb8a9e0bc475bb', name: "overlay-panel" })), index.h("slot", { key: 'a5212acc50486da67ff7c3307a703ff2d267be90', name: "meta" }), this.productTitle ? (index.h("a", { href: this.href || '#', class: "block font-semibold text-inherit hover:text-[var(--color-brand-primary)]" }, this.productTitle)) : (index.h("slot", { name: "title" })), this.address ? (index.h("p", { class: "text-sm text-gray-600" }, this.address)) : (index.h("slot", { name: "address" })), this.price ? (index.h("p", { class: "text-[var(--color-brand-primary)] font-medium" }, this.price)) : (index.h("slot", { name: "pricing" }))));
    }
};

exports.ez_product_card = EzProductCard;
