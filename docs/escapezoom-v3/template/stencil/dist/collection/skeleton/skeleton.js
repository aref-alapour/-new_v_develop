import { h, Host } from "@stencil/core";
/**
 * EZ Skeleton - Placeholder loader for content that's being fetched.
 * Use as a wrapper or with predefined type props for common patterns.
 */
export class EzSkeleton {
    /** Predefined skeleton type: card, list, text, avatar */
    type = 'custom';
    /** Number of items to show (for list/card types) */
    count = 3;
    /** Show animation pulse effect */
    animate = true;
    renderCard() {
        return (h("div", { class: "bg-white rounded-xl shadow-md overflow-hidden" }, h("div", { class: "aspect-video bg-slate-200" }), h("div", { class: "p-4 space-y-3" }, h("div", { class: "h-4 bg-slate-200 rounded w-3/4" }), h("div", { class: "h-3 bg-slate-200 rounded w-1/2" }), h("div", { class: "flex justify-between" }, h("div", { class: "h-3 bg-slate-200 rounded w-1/4" }), h("div", { class: "h-3 bg-slate-200 rounded w-1/3" })))));
    }
    renderList() {
        return (h("div", { class: "space-y-4" }, Array.from({ length: this.count }).map(() => (h("div", { class: "flex gap-4 items-center p-3 bg-white rounded-lg" }, h("div", { class: "w-12 h-12 bg-slate-200 rounded-lg flex-shrink-0" }), h("div", { class: "flex-1 space-y-2" }, h("div", { class: "h-4 bg-slate-200 rounded w-3/4" }), h("div", { class: "h-3 bg-slate-200 rounded w-1/2" })))))));
    }
    renderText() {
        return (h("div", { class: "space-y-3" }, h("div", { class: "h-4 bg-slate-200 rounded w-full" }), h("div", { class: "h-4 bg-slate-200 rounded w-5/6" }), h("div", { class: "h-4 bg-slate-200 rounded w-4/6" })));
    }
    renderAvatar() {
        return (h("div", { class: "flex items-center gap-3" }, h("div", { class: "w-10 h-10 bg-slate-200 rounded-full" }), h("div", { class: "space-y-2" }, h("div", { class: "h-3 bg-slate-200 rounded w-24" }), h("div", { class: "h-2 bg-slate-200 rounded w-16" }))));
    }
    render() {
        const animateClass = this.animate ? 'animate-pulse' : '';
        return (h(Host, { key: '7ce2dec26f35cf0a52a51a552029d3d1a2c338fe', class: `block ${animateClass}`, "aria-busy": "true", "aria-label": "Loading..." }, this.type === 'card' && (h("div", { key: '47fff1df258eabd702cc98dcbb0d5a966f6a3322', class: "grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" }, Array.from({ length: this.count }).map(() => this.renderCard()))), this.type === 'list' && this.renderList(), this.type === 'text' && this.renderText(), this.type === 'avatar' && this.renderAvatar(), this.type === 'custom' && h("slot", { key: '13aee29a10978bdd1f988955645eb3d1b0f1695b' })));
    }
    static get is() { return "ez-skeleton"; }
    static get properties() {
        return {
            "type": {
                "type": "string",
                "mutable": false,
                "complexType": {
                    "original": "'card' | 'list' | 'text' | 'avatar' | 'custom'",
                    "resolved": "\"avatar\" | \"card\" | \"custom\" | \"list\" | \"text\"",
                    "references": {}
                },
                "required": false,
                "optional": false,
                "docs": {
                    "tags": [],
                    "text": "Predefined skeleton type: card, list, text, avatar"
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "type",
                "defaultValue": "'custom'"
            },
            "count": {
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
                    "text": "Number of items to show (for list/card types)"
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "count",
                "defaultValue": "3"
            },
            "animate": {
                "type": "boolean",
                "mutable": false,
                "complexType": {
                    "original": "boolean",
                    "resolved": "boolean",
                    "references": {}
                },
                "required": false,
                "optional": false,
                "docs": {
                    "tags": [],
                    "text": "Show animation pulse effect"
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "animate",
                "defaultValue": "true"
            }
        };
    }
}
