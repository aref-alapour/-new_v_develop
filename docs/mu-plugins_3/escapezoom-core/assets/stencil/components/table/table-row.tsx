import { Component, h, Host } from '@stencil/core';

@Component({
  tag: 'ez-table-row',
  shadow: false,
})
export class EzTableRow {
  render() {
    return (
      <Host class="table-row border-b border-[#E4EBF0] hover:bg-gray-50 transition-colors duration-200">
        <slot />
      </Host>
    );
  }
}
