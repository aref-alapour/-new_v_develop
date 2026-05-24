import { h } from "@stencil/core";
import EmblaCarousel from "embla-carousel";
import Autoplay from "embla-carousel-autoplay";
import Fade from "embla-carousel-fade";
export class EzBannerSlider {
    el;
    items = [];
    sliderModel = 'normal';
    _items = [];
    emblaApi;
    componentWillLoad() {
        this.parseItems();
    }
    componentDidLoad() {
        this.initEmbla();
    }
    parseItems() {
        if (typeof this.items === 'string') {
            try {
                this._items = JSON.parse(this.items);
            }
            catch (e) {
                this._items = [];
            }
        }
        else {
            this._items = Array.isArray(this.items) ? this.items : [];
        }
    }
    initEmbla() {
        const viewportNode = this.el.querySelector('.embla__viewport');
        if (!viewportNode)
            return;
        this.emblaApi = EmblaCarousel(viewportNode, { loop: true }, [Fade(), Autoplay({ delay: 5000 })]);
    }
    scrollPrev = () => {
        if (this.emblaApi)
            this.emblaApi.scrollPrev();
    };
    render() {
        if (!this._items || this._items.length === 0)
            return null;
        return (h("div", { class: `relative w-full overflow-hidden embla_fade rounded-14 lg:rounded-20 ${this.sliderModel === 'wide' ? 'mt-7.5 lg:mt-10' : ''}` }, h("div", { class: "embla__viewport relative min-h-d350 md:min-h-d500" }, h("div", { class: "embla__container relative w-full min-h-d350 md:min-h-d500 flex" }, this._items.map((item) => (h("div", { class: "embla__slide adv-banner relative w-full min-h-d350 md:min-h-d500 select-none shrink-0 grow-0 basis-full" }, h("a", { class: "h-full block w-full", href: item.link || '#' }, h("img", { class: "lg:hidden h-full w-full object-cover absolute top-0 left-0", src: item.srcMobile || item.srcDesktop, loading: "lazy", alt: "" }), h("img", { class: "max-lg:hidden h-full w-full object-cover absolute top-0 left-0", src: item.srcDesktop, loading: "lazy", alt: "" }))))))), this._items.length > 1 && (h("button", { class: "embla__button embla__button--prev absolute right-0 top-1/2 -translate-y-1/2 rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px max-md:hidden", type: "button", onClick: this.scrollPrev }, h("svg", { xmlns: "http://www.w3.org/2000/svg", width: "30", fill: "none", viewBox: "0 0 30 113" }, h("g", { "clip-path": "url(#arrow_aa)" }, h("path", { fill: "#BFCBD9", "fill-rule": "evenodd", d: "M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z", "clip-rule": "evenodd" }), h("path", { fill: "#fff", "fill-rule": "evenodd", d: "M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z", "clip-rule": "evenodd" }), h("path", { fill: "#9FB3CB", "fill-rule": "evenodd", d: "m13.815 50.977.125.142c.387.51.334 1.232-.124 1.677l-3.098 3.037 3.098 3.037.125.141a1.273 1.273 0 0 1-.128 1.68 1.286 1.286 0 0 1-1.804-.003l-4.025-3.946-.126-.142a1.276 1.276 0 0 1 .126-1.676l4.025-3.946.147-.124a1.29 1.29 0 0 1 1.659.123Z", "clip-rule": "evenodd" })), h("defs", null, h("clipPath", { id: "arrow_aa" }, h("path", { fill: "#fff", d: "M0 0h30v113H0z" }))))))));
    }
    static get is() { return "ez-banner-slider"; }
    static get properties() {
        return {
            "items": {
                "type": "string",
                "mutable": false,
                "complexType": {
                    "original": "string | any[]",
                    "resolved": "any[] | string",
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
                "attribute": "items",
                "defaultValue": "[]"
            },
            "sliderModel": {
                "type": "string",
                "mutable": false,
                "complexType": {
                    "original": "'wide' | 'normal'",
                    "resolved": "\"normal\" | \"wide\"",
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
                "attribute": "slider-model",
                "defaultValue": "'normal'"
            }
        };
    }
    static get states() {
        return {
            "_items": {}
        };
    }
    static get elementRef() { return "el"; }
}
