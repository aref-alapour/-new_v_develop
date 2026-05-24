import { r as registerInstance, g as getElement, h, H as Host } from './index-DclNjYd0.js';

const EzTable = class {
    constructor(hostRef) {
        registerInstance(this, hostRef);
    }
    get el() { return getElement(this); }
    columns = [];
    loading = false;
    skeletonCount = 5;
    caption;
    getColumns() {
        if (typeof this.columns === 'string') {
            try {
                return JSON.parse(this.columns);
            }
            catch (e) {
                return [];
            }
        }
        return Array.isArray(this.columns) ? this.columns : [];
    }
    render() {
        const cols = this.getColumns();
        const colCount = cols.length;
        return (h(Host, { key: '531421cba19f65584752c9073cde24183d8761de', class: "block w-full" }, h("div", { key: 'e44818ebe6e508737bb50699ffd847b7c9cd3c60', class: "bg-white rounded-xl border border-slate-105 overflow-hidden" }, this.caption && (h("div", { key: '18b6314f28c3c75bd7693f924f33e4993fa4c980', class: "px-6 py-4 border-b border-slate-105 bg-frost" }, h("h3", { key: '66038658858f10faf12c1b5fc92e93403daf188b', class: "font-yekan-bold text-navyBlue" }, this.caption))), h("div", { key: '01af9e8c6f8ef5f702c9dc3d0550960354e6bd26', class: "overflow-x-auto" }, h("div", { key: '43033921744f83dcdd1a6c509f27a606cc741627', class: "w-full table min-w-full" }, colCount > 0 && (h("div", { key: '4501810a4ef42192696da9e6fc6afad01591586c', class: "table-header-group bg-frost" }, h("div", { key: 'e9400f3bc64be447fb1cef508bd8362cbb6f8ab0', class: "table-row" }, cols.map((col) => (h("div", { class: "table-cell px-6 py-4 text-right text-xs font-yekan-bold text-navyBlue whitespace-nowrap align-middle" }, col)))))), h("div", { key: 'ae86b96c61c8600b5ece2a999a2f01186c6c309c', class: "table-row-group bg-white" }, this.loading ? (Array.from({ length: this.skeletonCount }).map((_, rowIndex) => (h("div", { class: "table-row border-b border-slate-105", key: `skeleton-${rowIndex}` }, Array.from({ length: colCount || 5 }).map((_, colIndex) => (h("div", { class: "table-cell px-6 py-4 align-middle", key: `skeleton-cell-${colIndex}` }, h("div", { class: "h-4 bg-gray-100 rounded animate-pulse" })))))))) : (h("slot", null))))))));
    }
};

export { EzTable as ez_table };
