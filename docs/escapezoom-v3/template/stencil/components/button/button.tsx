import { Component, h, Prop, Host } from '@stencil/core';

@Component({
  tag: 'ez-button',
  shadow: false,
})
export class EzButton {
  @Prop() variant: 'primary' | 'secondary' | 'outline' | 'ghost' | 'danger' = 'primary';
  @Prop() size: 'sm' | 'md' | 'lg' = 'md';
  @Prop() disabled: boolean = false;
  @Prop() loading: boolean = false;
  @Prop() type: 'button' | 'submit' | 'reset' = 'button';
  @Prop() wFull: boolean = false;

  render() {
    const variants = {
      primary: 'bg-primary-500 hover:bg-primary-600 text-white shadow-sm',
      secondary: 'bg-gray-100 hover:bg-gray-200 text-navyBlue',
      outline: 'border border-slate-105 bg-white hover:bg-gray-50 text-navyBlue',
      ghost: 'bg-transparent hover:bg-gray-100 text-navyBlue',
      danger: 'bg-red-500 hover:bg-red-600 text-white',
    };
    const sizes = {
      sm: 'h-9 px-3 text-xs',
      md: 'h-11 px-4 text-sm',
      lg: 'h-12.5 px-6 text-base',
    };
    const widthClass = this.wFull ? 'w-full' : '';
    const disabledClass = (this.disabled || this.loading) ? 'opacity-60 cursor-not-allowed' : '';
    return (
      <Host class={{ 'block w-full': this.wFull, 'inline-block': !this.wFull }}>
        <button
          type={this.type}
          disabled={this.disabled || this.loading}
          class={`
            flex items-center justify-center gap-2 rounded-xl font-yekan-bold transition-all duration-200
            ${variants[this.variant]}
            ${sizes[this.size]}
            ${widthClass}
            ${disabledClass}
          `}
        >
          {this.loading && (
            <ez-loading type="spinner" size="sm" color="text-current" class="mr-2 -ml-1"></ez-loading>
          )}
          <slot name="icon-left" />
          <slot />
          <slot name="icon-right" />
        </button>
      </Host>
    );
  }
}
