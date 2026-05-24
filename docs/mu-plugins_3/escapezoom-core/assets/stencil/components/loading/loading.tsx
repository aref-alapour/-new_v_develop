import { Component, h, Prop, Host } from '@stencil/core';

@Component({
  tag: 'ez-loading',
  shadow: false,
})
export class EzLoading {
  @Prop() type: 'spinner' | 'dots' | 'circle' = 'spinner';
  @Prop() size: 'sm' | 'md' | 'lg' | 'xl' = 'md';
  @Prop() message?: string;
  @Prop() color: string = 'text-primary-500';

  private getSizeClass() {
    switch (this.size) {
      case 'sm': return 'w-4 h-4';
      case 'lg': return 'w-12 h-12';
      case 'xl': return 'w-16 h-16';
      default: return 'w-8 h-8';
    }
  }

  renderSpinner() {
    return (
      <svg class={`animate-spin ${this.getSizeClass()} ${this.color}`} xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
    );
  }

  renderDots() {
    const sizeDot = this.size === 'sm' ? 'w-1.5 h-1.5' : this.size === 'lg' ? 'w-3 h-3' : 'w-2 h-2';
    return (
      <div class="flex items-center space-x-1 space-x-reverse">
        <div class={`${sizeDot} ${this.color} rounded-full animate-bounce [animation-delay:-0.3s]`}></div>
        <div class={`${sizeDot} ${this.color} rounded-full animate-bounce [animation-delay:-0.15s]`}></div>
        <div class={`${sizeDot} ${this.color} rounded-full animate-bounce`}></div>
      </div>
    );
  }

  renderCircle() {
    return (
      <div class={`${this.getSizeClass()} relative`}>
        <div class="absolute inset-0 rounded-full border-4 border-gray-200"></div>
        <div class={`absolute inset-0 rounded-full border-4 border-t-transparent ${this.color} animate-spin`}></div>
      </div>
    );
  }

  render() {
    const containerClass = this.message
      ? "flex flex-col items-center justify-center p-4"
      : "inline-flex items-center justify-center";
    return (
      <Host class={containerClass}>
        {this.type === 'spinner' && this.renderSpinner()}
        {this.type === 'dots' && this.renderDots()}
        {this.type === 'circle' && this.renderCircle()}
        {this.message && (
          <p class="mt-3 text-sm text-gray-500 font-yekan-medium animate-pulse">
            {this.message}
          </p>
        )}
      </Host>
    );
  }
}
