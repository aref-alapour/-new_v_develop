'use strict';

var index = require('./index-COnMUfPy.js');

const EzAccordion = class {
    constructor(hostRef) {
        index.registerInstance(this, hostRef);
    }
    get el() { return index.getElement(this); }
    render() {
        return (index.h("div", { key: 'b53f50cfe4a6cd303adc7d623d0cb6f9b58339a2', class: "w-full rounded-2xl border border-gray-100 bg-white px-4 shadow-sm lg:px-6" }, index.h("slot", { key: 'e1bf6061a59361bd7164407cfe3397162ee85e0b' })));
    }
};

exports.ez_accordion = EzAccordion;
