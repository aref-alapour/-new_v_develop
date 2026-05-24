import { h } from "@stencil/core";
export class EzTabs {
    el;
    activeTab;
    tabs = [];
    currentTab = '';
    tabChange;
    componentDidLoad() {
        const tabElements = Array.from(this.el.querySelectorAll('ez-tab'));
        this.tabs = tabElements.map((tab) => ({
            id: tab.tabId,
            label: tab.label
        }));
        if (!this.currentTab && this.tabs.length > 0) {
            this.currentTab = this.activeTab || this.tabs[0].id;
        }
        else if (this.activeTab) {
            this.currentTab = this.activeTab;
        }
    }
    handleTabClick(id) {
        this.currentTab = id;
        this.tabChange.emit(id);
        const tabElements = Array.from(this.el.querySelectorAll('ez-tab'));
        tabElements.forEach((tab) => {
            tab.active = (tab.tabId === id);
        });
    }
    render() {
        return (h("div", { key: 'f237fee578d301baea9212db9407d174e67304c7', class: "w-full" }, h("div", { key: '534fa35b0175ca840415c0a27245ef1c3fbc6719', class: "flex border-b border-gray-200 gap-6 overflow-x-auto no-scrollbar mb-6" }, this.tabs.map(tab => (h("button", { type: "button", class: `pb-3 text-sm font-bold whitespace-nowrap border-b-2 transition-colors duration-200 px-1
                ${this.currentTab === tab.id
                ? 'border-primary-500 text-primary-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'}`, onClick: () => this.handleTabClick(tab.id) }, tab.label)))), h("div", { key: '7e21a8cc56e7c02141fbbfcbcb9618b2e8c0b83a' }, h("slot", { key: 'bf3aac6b6ec4b6948886bfb0ca7b33e5e4374541' }))));
    }
    static get is() { return "ez-tabs"; }
    static get properties() {
        return {
            "activeTab": {
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
                "attribute": "active-tab"
            }
        };
    }
    static get states() {
        return {
            "tabs": {},
            "currentTab": {}
        };
    }
    static get events() {
        return [{
                "method": "tabChange",
                "name": "tabChange",
                "bubbles": true,
                "cancelable": true,
                "composed": true,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "complexType": {
                    "original": "string",
                    "resolved": "string",
                    "references": {}
                }
            }];
    }
    static get elementRef() { return "el"; }
}
