import { Component, h, Prop, Event, EventEmitter, Host } from '@stencil/core';

@Component({
  tag: 'ez-modal',
  shadow: false,
})
export class EzModal {
  @Prop({ mutable: true, reflect: true }) isOpen: boolean = false;
  @Prop() modalTitle: string;
  @Prop() size: 'sm' | 'md' | 'lg' | 'xl' = 'md';
  @Prop() closeOnOverlayClick: boolean = true;

  @Event() close: EventEmitter<void>;

  private handleClose = () => {
    this.isOpen = false;
    this.close.emit();
  };

  private handleOverlayClick = (e: MouseEvent) => {
    if (this.closeOnOverlayClick && e.target === e.currentTarget) {
      this.handleClose();
    }
  };

  render() {
    const sizeClasses = {
      'sm': 'max-w-sm',
      'md': 'max-w-md',
      'lg': 'max-w-lg',
      'xl': 'max-w-xl',
    };
    return (
      <Host
        class={{
          'fixed inset-0 z-50 flex items-center justify-center': true,
          'hidden': !this.isOpen,
        }}
        onClick={this.handleOverlayClick}
      >
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm -z-10" aria-hidden="true" />
        <div class={`bg-white rounded-xl shadow-xl w-full ${sizeClasses[this.size]} mx-4 flex flex-col max-h-[90vh]`}>
          <div class="flex justify-between items-center p-6 border-b border-[#E4EBF0]">
            <h2 class="text-lg font-yekan-bold text-navyBlue">
              {this.modalTitle}
              <slot name="header" />
            </h2>
            <button class="text-gray-500 hover:text-gray-700 transition-colors" onClick={this.handleClose} type="button">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          <div class="p-6 overflow-y-auto">
            <slot />
          </div>
          <div class="px-6 py-4 border-t border-[#E4EBF0] flex justify-end gap-3 bg-gray-50 rounded-b-xl">
            <slot name="footer" />
          </div>
        </div>
      </Host>
    );
  }
}
