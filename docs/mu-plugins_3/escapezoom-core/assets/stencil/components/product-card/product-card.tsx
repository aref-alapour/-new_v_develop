import { Component, h, Prop, Host } from '@stencil/core';

@Component({
  tag: 'ez-product-card',
  shadow: false,
})
export class EzProductCard {
  @Prop() productId?: string | number;
  @Prop() status?: string;
  @Prop() href?: string;
  @Prop() isSlide?: boolean = false;
  /** عنوان محصول (از سرور؛ در HTML به‌صورت product-title پاس داده می‌شود تا با titleٔ رزروشده تداخل نداشته باشد). */
  @Prop() productTitle?: string;
  @Prop() price?: string;
  @Prop() imageUrl?: string;
  @Prop() address?: string;

  render() {
    return (
      <Host
        class={{
          'embla__slide': !!this.isSlide,
          'font-[var(--font-yekan)]': true,
        }}
        role="article"
        data-product-id={this.productId}
        data-status={this.status}
        style={{ display: 'block' }}
      >
        <div class="relative overflow-hidden rounded-[var(--radius-ez)] lg:rounded-2xl lg:shadow-[var(--tw-shadow,0_4px_6px_-1px_rgba(0,0,0,.1),0_2px_4px_-2px_rgba(0,0,0,.1))]">
          <div class="relative">
            {this.imageUrl ? (
              <a href={this.href || '#'} class="block">
                <img
                  src={this.imageUrl}
                  alt={this.productTitle || ''}
                  class="w-full h-full object-cover"
                  loading="lazy"
                />
              </a>
            ) : (
              <slot name="media" />
            )}
            <slot name="badge" />
            <slot name="floating-action" />
          </div>
          <slot name="overlay-panel" />
        </div>
        <slot name="meta" />
        {this.productTitle ? (
          <a
            href={this.href || '#'}
            class="block font-semibold text-inherit hover:text-[var(--color-brand-primary)]"
          >
            {this.productTitle}
          </a>
        ) : (
          <slot name="title" />
        )}
        {this.address ? (
          <p class="text-sm text-gray-600">{this.address}</p>
        ) : (
          <slot name="address" />
        )}
        {this.price ? (
          <p class="text-[var(--color-brand-primary)] font-medium">{this.price}</p>
        ) : (
          <slot name="pricing" />
        )}
      </Host>
    );
  }
}
