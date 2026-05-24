'use strict';

var index = require('./index-COnMUfPy.js');

const EzCommentItemPost = class {
    constructor(hostRef) {
        index.registerInstance(this, hostRef);
    }
    render() {
        return (index.h(index.Host, { key: 'b90b39af998f3474a6d46a167df59878c1b80b69', class: "block py-6 border-b border-gray-100 last:border-0" }, index.h("div", { key: '7e8495c7cee74045021fb1067bdb8ee529892130', class: "flex gap-4" }, index.h("div", { key: '845712346057134191dec0dd82b01245d2cdd33c', class: "flex-shrink-0" }, index.h("slot", { key: '36f1cd88e1830eb1048c1974afdcfccda39a9483', name: "avatar" })), index.h("div", { key: 'b25d7f4ae4b7c4e9562de2fbb24817984f28f8a1', class: "flex-grow" }, index.h("div", { key: '30c2376028cc923b7132cb652fa510735b5f1f7a', class: "flex items-center justify-between mb-2" }, index.h("div", { key: 'd4e97c89af2d54c065a750a685f1658cc2dd5f8c', class: "font-yekan-bold text-navyBlue text-lg" }, index.h("slot", { key: 'a382c510a694017750a1dea535847129fb6e9d0a', name: "author" })), index.h("div", { key: '2c95657e39f713661c9a6e12c0b9c62f6778f242', class: "text-sm text-gray-400" }, index.h("slot", { key: '69cae559028628acbf5fe0c0c9b9b1abdcb1cb6a', name: "date" }))), index.h("div", { key: 'd604b3a4b290c0bc26eb16489b65640e03182bfe', class: "text-gray-600 leading-7 text-sm text-justify" }, index.h("slot", { key: 'b995fcb260602e54737a9ca4436a3d8a53dcd1d0', name: "content" })), index.h("div", { key: '96b86389bd19809e6224da827609a00e72130b4f', class: "w-full" }, index.h("slot", { key: '3aca6df395c3b757e5fa17022191814a53353d84', name: "response" })), index.h("div", { key: 'bb5edfa35a6ec32538e48fdc7607754ed95d7df6', class: "mt-3" }, index.h("slot", { key: '8ea1f1db31bf46391de261562157b7e07b37a1e6', name: "actions" }))))));
    }
};

exports.ez_comment_item_post = EzCommentItemPost;
