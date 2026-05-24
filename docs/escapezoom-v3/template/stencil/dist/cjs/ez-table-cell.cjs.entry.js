'use strict';

var index = require('./index-COnMUfPy.js');

const EzTableCell = class {
    constructor(hostRef) {
        index.registerInstance(this, hostRef);
    }
    render() {
        return (index.h(index.Host, { key: '6a49f5d07db16b1946b943caac31a4d15834dc55', class: "table-cell px-6 py-4 align-middle text-sm text-navyBlue" }, index.h("slot", { key: 'bb2ad282a02c4d80800dc4cd9bd3c000587c0b18' })));
    }
};

exports.ez_table_cell = EzTableCell;
