'use strict';

var index = require('./index-COnMUfPy.js');

const EzButton = class {
    constructor(hostRef) {
        index.registerInstance(this, hostRef);
    }
    variant = 'primary';
    size = 'md';
    disabled = false;
    loading = false;
    type = 'button';
    wFull = false;
    render() {
        const variants = {
            primary: 'bg-primary-500 hover:bg-primary-600 text-white shadow-sm',
            secondary: 'bg-gray-100 hover:bg-gray-200 text-navyBlue',
            outline: 'border border-slate-105 bg-white hover:bg-gray-50 text-navyBlue',
            ghost: 'bg-transparent hover:bg-gray-100 text-navyBlue',
            danger: 'bg-red-500 hover:bg-red-600 text-white',
        };
        const sizes = {
            sm: 'h-9 px-3 text-xs',
            md: 'h-11 px-4 text-sm',
            lg: 'h-12.5 px-6 text-base',
        };
        const widthClass = this.wFull ? 'w-full' : '';
        const disabledClass = (this.disabled || this.loading) ? 'opacity-60 cursor-not-allowed' : '';
        return (index.h(index.Host, { key: '758eedaa565843f595de276b0272abaf7c1d158a', class: { 'block w-full': this.wFull, 'inline-block': !this.wFull } }, index.h("button", { key: '415903629f19dd28078771eb9daddc0367ac79ba', type: this.type, disabled: this.disabled || this.loading, class: `
            flex items-center justify-center gap-2 rounded-xl font-yekan-bold transition-all duration-200
            ${variants[this.variant]}
            ${sizes[this.size]}
            ${widthClass}
            ${disabledClass}
          ` }, this.loading && (index.h("ez-loading", { key: 'c30e164d77241bd22ee51bc0b452ce039c999e7a', type: "spinner", size: "sm", color: "text-current", class: "mr-2 -ml-1" })), index.h("slot", { key: '50b7c5186537c981288e92d799d79ec857e3c90c', name: "icon-left" }), index.h("slot", { key: '569d8d297e96efc6f4f53dab93f5accf667a9589' }), index.h("slot", { key: '5235eb2df9877e4e6da32b5662fb6eba0449a2ca', name: "icon-right" }))));
    }
};

exports.ez_button = EzButton;
