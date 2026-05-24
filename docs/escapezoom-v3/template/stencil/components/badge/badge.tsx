import { Component, h, Prop, Host } from '@stencil/core';

@Component({
  tag: 'ez-badge',
  shadow: false,
})
export class EzBadge {
  @Prop() variant: 'default' | 'success' | 'warning' | 'danger' | 'info' | 'custom' = 'default';
  @Prop() sysColor: string;
  @Prop() sysBg: string;

  render() {
    const variants = {
      default: 'text-gray-600 bg-gray-100',
      success: 'text-green-600 bg-green-100',
      warning: 'text-orange-600 bg-orange-100',
      danger: 'text-red-600 bg-red-100',
      info: 'text-blue-600 bg-blue-100',
    };
    const style = this.variant === 'custom' ? { color: this.sysColor, backgroundColor: this.sysBg } : {};
    const className = this.variant === 'custom' ? '' : variants[this.variant];
    return (
      <Host
        class={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold ${className}`}
        style={style}
      >
        <slot />
      </Host>
    );
  }
}
