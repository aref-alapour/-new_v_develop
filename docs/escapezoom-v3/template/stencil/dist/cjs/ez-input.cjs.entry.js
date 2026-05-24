'use strict';

var index = require('./index-COnMUfPy.js');

const EzInput = class {
    constructor(hostRef) {
        index.registerInstance(this, hostRef);
    }
    label;
    type = 'text';
    name;
    placeholder;
    value;
    required = false;
    readonly = false;
    render() {
        return (index.h("div", { key: '9c127e4491583f29107f9152592438d6d023c0e1', class: "relative w-full text-right font-sans", dir: "rtl" }, this.label && index.h("label", { key: '570c45d5f453cc38245649c9f1aac05fe95f6b5f', class: "mb-2 block text-sm font-bold text-steel" }, this.label), index.h("input", { key: '8bb8cc0a2f3eff72516e8b9d19cb2c36b7ae716a', type: this.type, name: this.name, placeholder: this.placeholder, value: this.value, required: this.required, readOnly: this.readonly, class: "w-full bg-white border border-gray-100/80 rounded-lg max-lg:shadow-13 h-d48 px-4 py-2 text-ink-tab focus:outline-none focus:ring-1 focus:ring-primary-500 placeholder-gray-400" })));
    }
};

exports.ez_input = EzInput;
