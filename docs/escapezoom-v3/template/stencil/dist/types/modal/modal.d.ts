import { EventEmitter } from '../stencil-public-runtime';
export declare class EzModal {
    isOpen: boolean;
    modalTitle: string;
    size: 'sm' | 'md' | 'lg' | 'xl';
    closeOnOverlayClick: boolean;
    close: EventEmitter<void>;
    private handleClose;
    private handleOverlayClick;
    render(): any;
}
