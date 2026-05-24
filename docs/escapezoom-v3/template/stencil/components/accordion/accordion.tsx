import { Component, h, Element } from '@stencil/core';

@Component({
  tag: 'ez-accordion',
  shadow: false,
})
export class EzAccordion {
  @Element() el: HTMLElement;

  render() {
    return (
      <div class="w-full rounded-2xl border border-gray-100 bg-white px-4 shadow-sm lg:px-6">
        <slot />
      </div>
    );
  }
}
