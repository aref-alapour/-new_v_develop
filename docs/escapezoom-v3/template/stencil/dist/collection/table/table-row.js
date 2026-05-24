import { h, Host } from "@stencil/core";
export class EzTableRow {
    render() {
        return (h(Host, { key: '75ead3bc0fe3b12a985a4e4ba41edc146eb8e23c', class: "table-row border-b border-slate-105 hover:bg-gray-50 transition-colors duration-200" }, h("slot", { key: '30ef671720316c2f54f293599793516340befd44' })));
    }
    static get is() { return "ez-table-row"; }
}
