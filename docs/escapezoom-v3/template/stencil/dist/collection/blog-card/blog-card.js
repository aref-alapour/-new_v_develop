import { h, Host } from "@stencil/core";
export class EzBlogCard {
    href;
    isSlide = false;
    render() {
        return (h(Host, { key: 'be274aad499596b98edd9bb5d393c1fe593bce9b', class: {
                'relative grow-0 shrink-0 w-d310 max-lg:h-d174 lg:h-d230 block': true,
                'embla__slide': !!this.isSlide
            } }, h("a", { key: 'dffb0624da2a8482de8b0416c02ed8f4d581b4d9', class: "relative block overflow-hidden rounded-16 shadow-8 w-d310 max-lg:h-d174 lg:h-d230", href: this.href || '#' }, h("slot", { key: 'e1e4042babb40ace9ae10f47149e7315f715c219', name: "image" }), h("div", { key: '24f1994710a75e883f62feec0e38d755cd267d20', class: "absolute right-0 top-0 flex h-full w-full flex-col justify-between bg-gradient-to-t from-textColor to-transparent max-lg:p-3 lg:p-6 text-white/90" }, h("div", { key: '1485512d3bdbef351753d8778231d6566bba424e', class: "ez-post-category" }, h("slot", { key: '7bdbebe1c41c3f057663f61d9308c003cbf467c8', name: "category" })), h("div", { key: 'e12eb3db019fb14e034f73b9981b86fb9128dfb9' }, h("slot", { key: '57a4d32047dcc776151ce9800835f11722742276', name: "title" }), h("div", { key: '5955bd17c2dcb218b832216b78cdeb1319dc9744', class: "ez-post-info mt-4 flex items-center justify-between gap-5 text-xs lg:mt-6" }, h("slot", { key: '7c0c2fc710944392657bd778a03608945ada0bfd', name: "meta" })))))));
    }
    static get is() { return "ez-blog-card"; }
    static get properties() {
        return {
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
            }
        };
    }
}
