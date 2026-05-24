import { EventEmitter } from '../stencil-public-runtime';
export declare class EzDropdown {
    el: HTMLElement;
    label: string;
    icon: string;
    isOpen: boolean;
    selectedLabel: string;
    selectionChange: EventEmitter<any>;
    componentWillLoad(): void;
    handleOptionSelect(e: CustomEvent<{
        value: any;
        text: string;
    }>): void;
    toggleDropdown: (e: MouseEvent) => void;
    closeDropdown: () => void;
    handleWindowClick(): void;
    render(): any;
}
