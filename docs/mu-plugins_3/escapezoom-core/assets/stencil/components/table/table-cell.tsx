import { Component, h, Host } from '@stencil/core';

@Component({
  tag: 'ez-table-cell',
  shadow: false,
})
export class EzTableCell {
  render() {
    return (
      <Host class="table-cell px-6 py-4 align-middle text-sm text-navyBlue">
        <slot />
      </Host>
    );
  }
}
