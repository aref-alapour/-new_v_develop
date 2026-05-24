import { Component, Prop, State, h, Event, EventEmitter, Element } from '@stencil/core';

@Component({
  tag: 'ez-tabs',
  shadow: false,
})
export class EzTabs {
  @Element() el: HTMLElement;
  @Prop() activeTab: string;

  @State() tabs: Array<{ id: string; label: string }> = [];
  @State() currentTab: string = '';

  @Event() tabChange: EventEmitter<string>;

  componentDidLoad() {
    const tabElements = Array.from(this.el.querySelectorAll('ez-tab'));
    this.tabs = tabElements.map((tab: any) => ({
      id: tab.tabId,
      label: tab.label
    }));
    if (!this.currentTab && this.tabs.length > 0) {
      this.currentTab = this.activeTab || this.tabs[0].id;
    } else if (this.activeTab) {
      this.currentTab = this.activeTab;
    }
  }

  handleTabClick(id: string) {
    this.currentTab = id;
    this.tabChange.emit(id);
    const tabElements = Array.from(this.el.querySelectorAll('ez-tab'));
    tabElements.forEach((tab: any) => {
      tab.active = (tab.tabId === id);
    });
  }

  render() {
    return (
      <div class="w-full">
        <div class="flex border-b border-gray-200 gap-6 overflow-x-auto no-scrollbar mb-6">
          {this.tabs.map(tab => (
            <button
              type="button"
              class={`pb-3 text-sm font-bold whitespace-nowrap border-b-2 transition-colors duration-200 px-1
                ${this.currentTab === tab.id
                  ? 'border-primary-500 text-primary-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'}`}
              onClick={() => this.handleTabClick(tab.id)}
            >
              {tab.label}
            </button>
          ))}
        </div>
        <div>
          <slot />
        </div>
      </div>
    );
  }
}
