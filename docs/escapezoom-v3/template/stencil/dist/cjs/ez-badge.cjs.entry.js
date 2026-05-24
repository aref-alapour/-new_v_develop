'use strict';

var index = require('./index-COnMUfPy.js');

const EzBadge = class {
    constructor(hostRef) {
        index.registerInstance(this, hostRef);
    }
    variant = 'default';
    sysColor;
    sysBg;
    render() {
        const variants = {
            default: 'text-gray-600 bg-gray-100',
            success: 'text-green-600 bg-green-100',
            warning: 'text-orange-600 bg-orange-100',
            danger: 'text-red-600 bg-red-100',
            info: 'text-blue-600 bg-blue-100',
        };
        const style = this.variant === 'custom' ? { color: this.sysColor, backgroundColor: this.sysBg } : {};
        const className = this.variant === 'custom' ? '' : variants[this.variant];
        return (index.h(index.Host, { key: 'a4efb38c67d39f0949f864bb668edba0b64a0c1b', class: `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold ${className}`, style: style }, index.h("slot", { key: '888aa14637c7a948ba4406e0a853708802e9aee3' })));
    }
};

exports.ez_badge = EzBadge;
