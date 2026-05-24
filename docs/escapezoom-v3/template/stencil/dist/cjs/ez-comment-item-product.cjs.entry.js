'use strict';

var index = require('./index-COnMUfPy.js');

const EzCommentItemProduct = class {
    constructor(hostRef) {
        index.registerInstance(this, hostRef);
    }
    rating = 0;
    verified = false;
    renderStarRating(rating) {
        return (index.h("div", { class: "flex text-yellow-400 mb-2" }, [1, 2, 3, 4, 5].map((star) => (index.h("svg", { class: `w-4 h-4 ${star <= rating ? 'fill-current' : 'text-gray-300'}`, xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 20 20", fill: "currentColor" }, index.h("path", { d: "M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" }))))));
    }
    render() {
        return (index.h(index.Host, { key: '6eb0d983a0c85101c034fd93e1e1af2bcb97972d', class: "block py-6 border-b border-gray-100 last:border-0" }, index.h("div", { key: '3ff885d81f748989097db6c83487a1f213865b85', class: "flex gap-4" }, index.h("div", { key: '2405b109c6664d931779c38cc22b6f046a2be017', class: "flex-shrink-0" }, index.h("slot", { key: '5cd1e1cbc8967428146e28a56586203335a37e0d', name: "avatar" })), index.h("div", { key: 'c4cf98af1b7ba498c12292fcba6e9b0710f055f3', class: "flex-grow" }, index.h("div", { key: '38a299cf1d9622df12a88427996933b7e8da3a9c', class: "flex flex-wrap items-center justify-between mb-2 gap-2" }, index.h("div", { key: 'da9457cddb5d2fd74ff99705ffa18f52c3c2eb1b', class: "flex items-center gap-2" }, index.h("div", { key: '5bbe538d4874e31680c58cc5fe348bc6316d1293', class: "font-yekan-bold text-navyBlue text-lg" }, index.h("slot", { key: 'a8fcad77e19a9390cfca420db1ec5677d49ad5ea', name: "author" })), this.verified && (index.h("span", { key: 'eb720c5c1a54799b734964f588ed4946c4a3029d', class: "text-green-600 bg-green-50 px-2 py-0.5 rounded-full text-xs flex items-center gap-1 font-yekan-medium" }, index.h("svg", { key: '444ac721bbc6d0ee39210044def26b120005994b', class: "w-3 h-3", fill: "none", viewBox: "0 0 24 24", stroke: "currentColor" }, index.h("path", { key: 'e6f1df40b3cd3827eaa5a7a5451be561bcc85d59', "stroke-linecap": "round", "stroke-linejoin": "round", "stroke-width": "2", d: "M5 13l4 4L19 7" })), "\u062E\u0631\u06CC\u062F\u0627\u0631"))), index.h("div", { key: '9984e26cd9b4509283a95fb558220da96aad2851', class: "text-sm text-gray-400" }, index.h("slot", { key: 'f664818516b574c6d31302c822420ba1e0703fa1', name: "date" }))), this.rating > 0 && this.renderStarRating(this.rating), index.h("div", { key: '092df4052116fd1e644b9322e77cac9c0464d4a6', class: "text-gray-700 leading-7 text-sm text-justify" }, index.h("slot", { key: '4601cb2764b3185b67bd77d18eddcf9863e7f931', name: "content" })), index.h("div", { key: '49249a712d5ea37d6a8f094c04bf65c08b10c54f', class: "w-full" }, index.h("slot", { key: 'b9549927d8fe3f3042f32662f7128045d16172aa', name: "response" })), index.h("div", { key: '1ae2e5baf7b3044789d12093d0a38de775948214', class: "mt-3" }, index.h("slot", { key: '7656e1f57e13a81441d7c74ce95ab109f8a74710', name: "actions" }))))));
    }
};

exports.ez_comment_item_product = EzCommentItemProduct;
