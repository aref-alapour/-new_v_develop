import { Component, Prop, State, h } from '@stencil/core';

@Component({
  tag: 'ez-accordion-item',
  shadow: false,
})
export class EzAccordionItem {
  @Prop() label: string;
  @Prop() open: boolean = false;

  @State() isOpen: boolean = false;

  componentWillLoad() {
    this.isOpen = this.open;
  }

  toggle = () => {
    this.isOpen = !this.isOpen;
  }

  render() {
    return (
      <div class="border-b border-gray-100 last:border-0">
        <button
          type="button"
          class="flex w-full items-center justify-between py-4 text-right text-sm font-bold text-slate-800 hover:text-primary-600 transition-colors bg-white focus:outline-none"
          onClick={this.toggle}
          aria-expanded={this.isOpen.toString()}
        >
          <span>{this.label}</span>
          <svg
            class={`h-5 w-5 transform text-gray-400 transition-transform duration-200 ${this.isOpen ? 'rotate-180' : ''}`}
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </button>
        <div
          class={`overflow-hidden transition-all duration-300 ease-in-out ${this.isOpen ? 'max-h-96 opacity-100 mb-4' : 'max-h-0 opacity-0'}`}
        >
          <div class="text-sm text-gray-600 leading-relaxed px-1">
            <slot />
          </div>
        </div>
      </div>
    );
  }
}
