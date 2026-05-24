'use strict';

var index = require('./index-COnMUfPy.js');

const EzTableRow = class {
    constructor(hostRef) {
        index.registerInstance(this, hostRef);
    }
    render() {
        return (index.h(index.Host, { key: '75ead3bc0fe3b12a985a4e4ba41edc146eb8e23c', class: "table-row border-b border-slate-105 hover:bg-gray-50 transition-colors duration-200" }, index.h("slot", { key: '30ef671720316c2f54f293599793516340befd44' })));
    }
};

exports.ez_table_row = EzTableRow;
