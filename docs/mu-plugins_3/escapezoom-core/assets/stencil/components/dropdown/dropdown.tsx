import { Component, h, Prop, State, Element, Listen, Event, EventEmitter, Host } from '@stencil/core';

@Component({
  tag: 'ez-dropdown',
  shadow: false,
})
export class EzDropdown {
  @Element() el: HTMLElement;

  @Prop() label: string = 'انتخاب کنید';
  @Prop() icon: string = `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6,9 12,15 18,9"></polyline></svg>`;

  @State() isOpen: boolean = false;
  @State() selectedLabel: string = '';

  @Event() selectionChange: EventEmitter<any>;

  componentWillLoad() {
    this.selectedLabel = this.label;
  }

  @Listen('optionSelect')
  handleOptionSelect(e: CustomEvent<{ value: any; text: string }>) {
    this.selectedLabel = e.detail.text;
    this.selectionChange.emit(e.detail);
    this.isOpen = false;
  }

  toggleDropdown = (e: MouseEvent) => {
    e.stopPropagation();
    this.isOpen = !this.isOpen;
  };

  closeDropdown = () => {
    this.isOpen = false;
  };

  @Listen('click', { target: 'window' })
  handleWindowClick() {
    if (this.isOpen) {
      this.closeDropdown();
    }
  }

  render() {
    return (
      <Host class="relative inline-block text-right">
        <button
          class="flex items-center gap-2 px-4 py-3 bg-white border border-[#E4EBF0] rounded-xl text-sm font-yekan-bold text-navyBlue hover:bg-gray-50 transition-colors w-full justify-between"
          onClick={this.toggleDropdown}
          type="button"
        >
          <div class="flex items-center gap-2">
            <span innerHTML={this.icon}></span>
            <span>{this.selectedLabel}</span>
          </div>
        </button>
        <div class={{
          'absolute right-0 mt-2 w-full min-w-[200px] bg-white border border-[#E4EBF0] rounded-xl shadow-lg z-20 origin-top-right transition-all duration-200': true,
          'opacity-0 invisible scale-95': !this.isOpen,
          'opacity-100 visible scale-100': this.isOpen
        }}>
          <div class="py-2">
            <slot />
          </div>
        </div>
      </Host>
    );
  }
}
