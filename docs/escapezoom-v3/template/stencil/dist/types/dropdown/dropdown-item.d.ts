import { EventEmitter } from '../stencil-public-runtime';
export declare class EzDropdownItem {
    el: HTMLElement;
    value: any;
    selected: boolean;
    optionSelect: EventEmitter<{
        value: any;
        text: string;
    }>;
    private handleClick;
    render(): any;
}
