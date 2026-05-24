import { EventEmitter } from '../stencil-public-runtime';
export declare class EzSelect {
    el: HTMLElement;
    name: string;
    placeholder: string;
    options: string | any[];
    value: string | string[];
    mode: 'single' | 'multiple';
    searchable: boolean;
    label: string;
    isOpen: boolean;
    searchQuery: string;
    _options: Array<{
        label: string;
        value: string;
        selected?: boolean;
    }>;
    internalValue: string | string[];
    displayValue: string;
    selectionChange: EventEmitter<any>;
    componentWillLoad(): void;
    parseOptions(newValue: string | any[]): void;
    syncInternalValue(newValue: string | string[] | undefined): void;
    updateDisplayValue(): void;
    toggleDropdown: (e: MouseEvent) => void;
    closeDropdown: () => void;
    handleWindowClick(): void;
    handleOptionClick(e: MouseEvent, option: any): void;
    handleSearchInput: (e: InputEvent) => void;
    getFilteredOptions(): {
        label: string;
        value: string;
        selected?: boolean;
    }[];
    render(): any;
}
