import { h, Host } from "@stencil/core";
export class EzLoading {
    type = 'spinner';
    size = 'md';
    message;
    color = 'text-primary-500';
    getSizeClass() {
        switch (this.size) {
            case 'sm': return 'w-4 h-4';
            case 'lg': return 'w-12 h-12';
            case 'xl': return 'w-16 h-16';
            default: return 'w-8 h-8';
        }
    }
    renderSpinner() {
        return (h("svg", { class: `animate-spin ${this.getSizeClass()} ${this.color}`, xmlns: "http://www.w3.org/2000/svg", fill: "none", viewBox: "0 0 24 24" }, h("circle", { class: "opacity-25", cx: "12", cy: "12", r: "10", stroke: "currentColor", "stroke-width": "4" }), h("path", { class: "opacity-75", fill: "currentColor", d: "M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" })));
    }
    renderDots() {
        const sizeDot = this.size === 'sm' ? 'w-1.5 h-1.5' : this.size === 'lg' ? 'w-3 h-3' : 'w-2 h-2';
        return (h("div", { class: "flex items-center space-x-1 space-x-reverse" }, h("div", { class: `${sizeDot} ${this.color} rounded-full animate-bounce [animation-delay:-0.3s]` }), h("div", { class: `${sizeDot} ${this.color} rounded-full animate-bounce [animation-delay:-0.15s]` }), h("div", { class: `${sizeDot} ${this.color} rounded-full animate-bounce` })));
    }
    renderCircle() {
        return (h("div", { class: `${this.getSizeClass()} relative` }, h("div", { class: "absolute inset-0 rounded-full border-4 border-gray-200" }), h("div", { class: `absolute inset-0 rounded-full border-4 border-t-transparent ${this.color} animate-spin` })));
    }
    render() {
        const containerClass = this.message
            ? "flex flex-col items-center justify-center p-4"
            : "inline-flex items-center justify-center";
        return (h(Host, { key: '982a84fc4b458240e43ac9ad18dea42037e902b5', class: containerClass }, this.type === 'spinner' && this.renderSpinner(), this.type === 'dots' && this.renderDots(), this.type === 'circle' && this.renderCircle(), this.message && (h("p", { key: 'bb4415b33af50114311ed835689dd85dd24a10c0', class: "mt-3 text-sm text-gray-500 font-yekan-medium animate-pulse" }, this.message))));
    }
    static get is() { return "ez-loading"; }
    static get properties() {
        return {
            "type": {
                "type": "string",
                "mutable": false,
                "complexType": {
                    "original": "'spinner' | 'dots' | 'circle'",
                    "resolved": "\"circle\" | \"dots\" | \"spinner\"",
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
                "attribute": "type",
                "defaultValue": "'spinner'"
            },
            "size": {
                "type": "string",
                "mutable": false,
                "complexType": {
                    "original": "'sm' | 'md' | 'lg' | 'xl'",
                    "resolved": "\"lg\" | \"md\" | \"sm\" | \"xl\"",
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
                "attribute": "size",
                "defaultValue": "'md'"
            },
            "message": {
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
                "attribute": "message"
            },
            "color": {
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
                "attribute": "color",
                "defaultValue": "'text-primary-500'"
            }
        };
    }
}
