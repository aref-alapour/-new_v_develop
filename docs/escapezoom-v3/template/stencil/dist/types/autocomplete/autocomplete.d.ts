import { EventEmitter } from '../stencil-public-runtime';
export declare class EzAutocomplete {
    el: HTMLElement;
    placeholder: string;
    debounce: number;
    loading: boolean;
    results: string | any[];
    value: string;
    searchQuery: string;
    _results: any[];
    isOpen: boolean;
    search: EventEmitter<string>;
    selection: EventEmitter<any>;
    private timer;
    private inputRef;
    componentWillLoad(): void;
    componentDidLoad(): void;
    disconnectedCallback(): void;
    handleOutsideClick: (e: MouseEvent) => void;
    parseResults(val: string | any[]): void;
    handleInput: (e: InputEvent) => void;
    handleSelect(item: any): void;
    handleClear: () => void;
    render(): any;
}
