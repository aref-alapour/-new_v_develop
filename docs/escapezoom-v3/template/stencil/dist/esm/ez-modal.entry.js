import { r as registerInstance, c as createEvent, h, H as Host } from './index-DclNjYd0.js';

const EzModal = class {
    constructor(hostRef) {
        registerInstance(this, hostRef);
        this.close = createEvent(this, "close");
    }
    isOpen = false;
    modalTitle;
    size = 'md';
    closeOnOverlayClick = true;
    close;
    handleClose = () => {
        this.isOpen = false;
        this.close.emit();
    };
    handleOverlayClick = (e) => {
        if (this.closeOnOverlayClick && e.target === e.currentTarget) {
            this.handleClose();
        }
    };
    render() {
        const sizeClasses = {
            'sm': 'max-w-sm',
            'md': 'max-w-md',
            'lg': 'max-w-lg',
            'xl': 'max-w-xl',
        };
        return (h(Host, { key: '77a2c43f08e49e46973a3f7751263e72d0858789', class: {
                'fixed inset-0 z-50 flex items-center justify-center': true,
                'hidden': !this.isOpen,
            }, onClick: this.handleOverlayClick }, h("div", { key: '4b79a1109a997b2f09cc544ec43a8a31c75dfdfd', class: "fixed inset-0 bg-black/50 backdrop-blur-sm -z-10", "aria-hidden": "true" }), h("div", { key: '126ca9f46d58c62cf1576c0963f6c3deed9d63e8', class: `bg-white rounded-xl shadow-xl w-full ${sizeClasses[this.size]} mx-4 flex flex-col max-h-modal` }, h("div", { key: '99a04e55462e3d176bf0ccc8b906e0f9f6f36e80', class: "flex justify-between items-center p-6 border-b border-slate-105" }, h("h2", { key: 'f9315391e94277c6c5ee6bdcc12f9f3427f6d368', class: "text-lg font-yekan-bold text-navyBlue" }, this.modalTitle, h("slot", { key: 'c267a82048dd222fcb664d6d7c73e069d43b32bf', name: "header" })), h("button", { key: 'fd9f5dc8cf50b49ac1eaa1c0308415c6d7976aa4', class: "text-gray-500 hover:text-gray-700 transition-colors", onClick: this.handleClose, type: "button" }, h("svg", { key: 'f9951d3495fe7cdf0d9cb5b5140d806f2e822f11', class: "w-6 h-6", fill: "none", stroke: "currentColor", viewBox: "0 0 24 24" }, h("path", { key: '792b07c81cf8b58e470e77f36031c9686b86951a', "stroke-linecap": "round", "stroke-linejoin": "round", "stroke-width": "2", d: "M6 18L18 6M6 6l12 12" })))), h("div", { key: '272a72099f32c8a29c3c3278464ed9a0cb08782e', class: "p-6 overflow-y-auto" }, h("slot", { key: '0ddf1ad2c7aa9b918871ff0d919b9181d78c191e' })), h("div", { key: '37bc8290ce1704833489500e1617e86f435d1983', class: "px-6 py-4 border-t border-slate-105 flex justify-end gap-3 bg-gray-50 rounded-b-xl" }, h("slot", { key: 'dcc9d5b1996da0d6d705dace556277d8e85d180f', name: "footer" })))));
    }
};

export { EzModal as ez_modal };
