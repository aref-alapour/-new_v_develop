import { Component, h, Host, Prop } from '@stencil/core';

/**
 * EZ Brand Card — entire card is one link (`shadow: false`; styles from global Tailwind/main.css).
 * SSR uses a real `<a class="ez-brand-card">` in `brand-card.php` so directories work without hydration.
 *
 * Slots (inside the main link):
 *   - `media` — logo / placeholder
 *   - `badge` — optional score (position absolute in slotted markup)
 *   - `title-row` — title + optional meta (no nested links)
 *   - `details` — e.g. address
 * Slot `actions` is **outside** the link (valid HTML if you need buttons).
 */
@Component({
  tag: 'ez-brand-card',
  shadow: false,
})
export class EzBrandCard {
  @Prop() href: string = '#';

  @Prop() brandId?: number;

  @Prop() brandSlug?: string;

  render() {
    return (
      <Host class="block">
        <a
          href={this.href}
          {...(this.href && this.href !== '#' ? { target: '_blank', rel: 'noopener noreferrer' } : {})}
          class="ez-brand-card group block rounded-xl no-underline text-inherit outline-none ring-primary-600 transition-transform duration-300 ease-out focus-visible:ring-2 focus-visible:ring-offset-2"
          data-brand-id={this.brandId}
          data-brand-slug={this.brandSlug}
        >
          <div class="flex flex-col gap-5 max-lg:gap-4 pt-0.5 transition-transform duration-300 ease-out group-hover:scale-105">
            <div class="relative block">
              <slot name="media" />
              <slot name="badge" />
            </div>
            <div class="flex flex-col gap-1.5 pt-3">
              <slot name="title-row" />
              <slot name="details" />
            </div>
          </div>
        </a>
        <slot name="actions" />
      </Host>
    );
  }
}
