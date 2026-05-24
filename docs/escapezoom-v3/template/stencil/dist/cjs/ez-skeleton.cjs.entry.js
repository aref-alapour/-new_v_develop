'use strict';

var index = require('./index-COnMUfPy.js');

const EzSkeleton = class {
    constructor(hostRef) {
        index.registerInstance(this, hostRef);
    }
    /** Predefined skeleton type: card, list, text, avatar */
    type = 'custom';
    /** Number of items to show (for list/card types) */
    count = 3;
    /** Show animation pulse effect */
    animate = true;
    renderCard() {
        return (index.h("div", { class: "bg-white rounded-xl shadow-md overflow-hidden" }, index.h("div", { class: "aspect-video bg-slate-200" }), index.h("div", { class: "p-4 space-y-3" }, index.h("div", { class: "h-4 bg-slate-200 rounded w-3/4" }), index.h("div", { class: "h-3 bg-slate-200 rounded w-1/2" }), index.h("div", { class: "flex justify-between" }, index.h("div", { class: "h-3 bg-slate-200 rounded w-1/4" }), index.h("div", { class: "h-3 bg-slate-200 rounded w-1/3" })))));
    }
    renderList() {
        return (index.h("div", { class: "space-y-4" }, Array.from({ length: this.count }).map(() => (index.h("div", { class: "flex gap-4 items-center p-3 bg-white rounded-lg" }, index.h("div", { class: "w-12 h-12 bg-slate-200 rounded-lg flex-shrink-0" }), index.h("div", { class: "flex-1 space-y-2" }, index.h("div", { class: "h-4 bg-slate-200 rounded w-3/4" }), index.h("div", { class: "h-3 bg-slate-200 rounded w-1/2" })))))));
    }
    renderText() {
        return (index.h("div", { class: "space-y-3" }, index.h("div", { class: "h-4 bg-slate-200 rounded w-full" }), index.h("div", { class: "h-4 bg-slate-200 rounded w-5/6" }), index.h("div", { class: "h-4 bg-slate-200 rounded w-4/6" })));
    }
    renderAvatar() {
        return (index.h("div", { class: "flex items-center gap-3" }, index.h("div", { class: "w-10 h-10 bg-slate-200 rounded-full" }), index.h("div", { class: "space-y-2" }, index.h("div", { class: "h-3 bg-slate-200 rounded w-24" }), index.h("div", { class: "h-2 bg-slate-200 rounded w-16" }))));
    }
    render() {
        const animateClass = this.animate ? 'animate-pulse' : '';
        return (index.h(index.Host, { key: '7ce2dec26f35cf0a52a51a552029d3d1a2c338fe', class: `block ${animateClass}`, "aria-busy": "true", "aria-label": "Loading..." }, this.type === 'card' && (index.h("div", { key: '47fff1df258eabd702cc98dcbb0d5a966f6a3322', class: "grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4" }, Array.from({ length: this.count }).map(() => this.renderCard()))), this.type === 'list' && this.renderList(), this.type === 'text' && this.renderText(), this.type === 'avatar' && this.renderAvatar(), this.type === 'custom' && index.h("slot", { key: '13aee29a10978bdd1f988955645eb3d1b0f1695b' })));
    }
};

exports.ez_skeleton = EzSkeleton;
