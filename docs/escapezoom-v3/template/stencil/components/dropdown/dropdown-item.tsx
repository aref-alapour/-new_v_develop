import { Component, h, Prop, Host, Element, Event, EventEmitter } from '@stencil/core';

@Component({
  tag: 'ez-dropdown-item',
  shadow: false,
})
export class EzDropdownItem {
  @Element() el: HTMLElement;
  @Prop() value: any;
  @Prop() selected: boolean = false;

  @Event() optionSelect: EventEmitter<{ value: any; text: string }>;

  private handleClick = (e: MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    const text = (this.el.textContent || '').trim();
    this.optionSelect.emit({ value: this.value, text });
  };

  render() {
    return (
      <Host class="block">
        <a
          href="#"
          class={{
            'block px-4 py-2 text-sm hover:bg-gray-100 transition-colors': true,
            'bg-blue-50 text-primary-600': this.selected,
            'text-gray-700': !this.selected
          }}
          onClick={this.handleClick}
        >
          <slot />
        </a>
      </Host>
    );
  }
}
