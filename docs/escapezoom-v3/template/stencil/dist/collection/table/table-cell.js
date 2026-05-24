import { h, Host } from "@stencil/core";
export class EzTableCell {
    render() {
        return (h(Host, { key: '6a49f5d07db16b1946b943caac31a4d15834dc55', class: "table-cell px-6 py-4 align-middle text-sm text-navyBlue" }, h("slot", { key: 'bb2ad282a02c4d80800dc4cd9bd3c000587c0b18' })));
    }
    static get is() { return "ez-table-cell"; }
}
