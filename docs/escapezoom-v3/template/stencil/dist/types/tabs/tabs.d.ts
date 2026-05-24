import { EventEmitter } from '../stencil-public-runtime';
export declare class EzTabs {
    el: HTMLElement;
    activeTab: string;
    tabs: Array<{
        id: string;
        label: string;
    }>;
    currentTab: string;
    tabChange: EventEmitter<string>;
    componentDidLoad(): void;
    handleTabClick(id: string): void;
    render(): any;
}
