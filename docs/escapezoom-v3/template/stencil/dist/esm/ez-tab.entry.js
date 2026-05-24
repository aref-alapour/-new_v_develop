import { r as registerInstance, g as getElement, h } from './index-DclNjYd0.js';

const EzTab = class {
    constructor(hostRef) {
        registerInstance(this, hostRef);
    }
    get el() { return getElement(this); }
    tabId;
    label;
    active = false;
    activeChanged(newValue) {
        if (newValue) {
            this.el.style.display = 'block';
        }
        else {
            this.el.style.display = 'none';
        }
    }
    componentDidLoad() {
        this.activeChanged(this.active);
    }
    render() {
        return (h("div", { key: 'c54fe2579cf48166055d0d7af23a5b60de61adc1', class: `transition-opacity duration-300 ${this.active ? 'opacity-100' : 'opacity-0 h-0 overflow-hidden'}` }, h("slot", { key: '6b0db08e62892ace0de903b487efa0df969704a6' })));
    }
    static get watchers() { return {
        "active": [{
                "activeChanged": 0
            }]
    }; }
};

export { EzTab as ez_tab };
