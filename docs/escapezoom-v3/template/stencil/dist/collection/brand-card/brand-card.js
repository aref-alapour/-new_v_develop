import { h, Host } from "@stencil/core";
/**
 * EZ Brand Card — entire card is one link (`shadow: false`; styles from global Tailwind/main.css).
 * SSR uses a real `<a class="ez-brand-card">` in `brand-card.php` so directories work without hydration.
 *
 * Slots (inside the main link):
 *   - `media` — logo / placeholder
 *   - `badge` — optional score (position absolute in slotted markup)
 *   - `title-row` — title + optional meta (no nested links)
 *   - `details` — e.g. address
 * Slot `actions` is **outside** the link (valid HTML if you need buttons).
 */
export class EzBrandCard {
    href = '#';
    brandId;
    brandSlug;
    render() {
        return (h(Host, { key: '9d11a007b487598e0e12bf1619c38c108a88b6d3', class: "block" }, h("a", { key: '0b80016dd987535f8f63e21dca5084132f371f8f', href: this.href, ...(this.href && this.href !== '#' ? { target: '_blank', rel: 'noopener noreferrer' } : {}), class: "ez-brand-card group block rounded-xl no-underline text-inherit outline-none ring-primary-600 transition-transform duration-300 ease-out focus-visible:ring-2 focus-visible:ring-offset-2", "data-brand-id": this.brandId, "data-brand-slug": this.brandSlug }, h("div", { key: '9f21a8be4bc8cf33691a26fa5c61f2c65d282346', class: "flex flex-col gap-5 max-lg:gap-4 pt-0.5 transition-transform duration-300 ease-out group-hover:scale-105" }, h("div", { key: 'a3cafface3343bcc15eaca23b0ab7841f7595f1f', class: "relative block" }, h("slot", { key: 'd3c1434161837ed81835732a1ebb8548b67a6139', name: "media" }), h("slot", { key: '9163ddde1ded0ec98745f093e6bf515b879201e9', name: "badge" })), h("div", { key: '2ac54a726b395c619c62ecabf5ddc9fb14392a46', class: "flex flex-col gap-1.5 pt-3" }, h("slot", { key: 'ac7af14d6b524b3f22acbd9384a4466f3ea250f0', name: "title-row" }), h("slot", { key: 'b26ac15716719227022c2d566a032e41d7e78d32', name: "details" })))), h("slot", { key: 'eb99ef019599be2908f63e874f0d74fa8b5d719f', name: "actions" })));
    }
    static get is() { return "ez-brand-card"; }
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
                "optional": false,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "href",
                "defaultValue": "'#'"
            },
            "brandId": {
                "type": "number",
                "mutable": false,
                "complexType": {
                    "original": "number",
                    "resolved": "number",
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
                "attribute": "brand-id"
            },
            "brandSlug": {
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
                "attribute": "brand-slug"
            }
        };
    }
}
