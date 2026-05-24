import { h } from "@stencil/core";
export class EzCollectionCard {
    collectionTitle;
    likes = 0;
    link;
    images = [];
    _images = [];
    componentWillLoad() {
        this.parseImages(this.images);
    }
    parseImages(newValue) {
        if (typeof newValue === 'string') {
            try {
                this._images = JSON.parse(newValue);
            }
            catch (e) {
                this._images = [];
            }
        }
        else {
            this._images = Array.isArray(newValue) ? newValue : [];
        }
    }
    render() {
        const displayImages = this._images.slice(0, 6);
        return (h("div", { key: 'a54d87728995dbca18345561da6fe46c50bed5ba', class: "h-full w-full" }, h("div", { key: 'ff9552c012ead851a9058ec20fc55afbdf43757b', class: "relative min-w-0 shrink-0 grow-0 basis-42 lg:basis-77 h-full" }, h("a", { key: 'e105de61c0c7faa1020eebb19a5a10033e73cdc4', class: "flex w-full h-full flex-col justify-between gap-2.5 overflow-hidden rounded-lg border border-slate-120 px-3 py-4 shadow-22 lg:gap-5 lg:rounded-3xl lg:px-5 lg:py-6 lg:border-none lg:bg-slate-700 lg:text-white lg:shadow-6 lg:[&>div]:border-none", href: this.link || '#' }, h("div", { key: 'be507b35d361476758e0171b53306a23e3387c56', class: "items-center justify-between text-2xs lg:flex" }, h("h3", { key: '2ddf8d196da177f238d03dc6cdce664dbee1c2f7', class: "text-sm lg:text-lg" }, this.collectionTitle)), h("div", { key: '74b0aec2fca68c896857e5ee43b59535f67f0fef', class: "grid min-w-28 grid-cols-3 gap-1 border-b border-t border-slate-100 px-2 lg:gap-3 grid-rows-2 max-lg:grid-rows-1" }, displayImages.map((src, index) => (h("div", { class: "w-9 lg:w-d52 lg:[&:last-of-type>div>div]:flex max-lg:[&:nth-child(n+4)]:hidden max-lg:[&:nth-of-type(3)>div>div]:flex" }, h("div", { class: "relative overflow-hidden rounded-md shadow-2" }, h("img", { class: "h-d66 w-d52 object-cover", src: src, loading: "lazy", alt: "" }), index === 5 && this._images.length > 6 && (h("div", { class: "absolute right-0 top-0 flex h-full w-full items-center justify-center bg-primary-500/80 text-white" }, h("span", { class: "text-xs font-bold" }, "+", this._images.length - 6))), index === 2 && this._images.length > 3 && (h("div", { class: "absolute right-0 top-0 hidden h-full w-full items-center justify-center bg-primary-500/80 text-white max-lg:flex" }, h("span", { class: "text-xs font-bold" }, "+", this._images.length - 3))))))))))));
    }
    static get is() { return "ez-collection-card"; }
    static get properties() {
        return {
            "collectionTitle": {
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
                "attribute": "collection-title"
            },
            "likes": {
                "type": "number",
                "mutable": false,
                "complexType": {
                    "original": "number",
                    "resolved": "number",
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
                "attribute": "likes",
                "defaultValue": "0"
            },
            "link": {
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
                "attribute": "link"
            },
            "images": {
                "type": "string",
                "mutable": false,
                "complexType": {
                    "original": "string | string[]",
                    "resolved": "string | string[]",
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
                "attribute": "images",
                "defaultValue": "[]"
            }
        };
    }
    static get states() {
        return {
            "_images": {}
        };
    }
    static get watchers() {
        return [{
                "propName": "images",
                "methodName": "parseImages"
            }];
    }
}
