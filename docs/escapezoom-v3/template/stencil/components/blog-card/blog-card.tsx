import { Component, h, Prop, Host } from '@stencil/core';

@Component({
  tag: 'ez-blog-card',
  shadow: false,
})
export class EzBlogCard {
  @Prop() href?: string;
  @Prop() isSlide?: boolean = false;

  render() {
    return (
      <Host class={{
        'relative grow-0 shrink-0 w-d310 max-lg:h-d174 lg:h-d230 block': true,
        'embla__slide': !!this.isSlide
      }}>
        <a class="relative block overflow-hidden rounded-16 shadow-8 w-d310 max-lg:h-d174 lg:h-d230"
          href={this.href || '#'}>
          <slot name="image" />
          <div class="absolute right-0 top-0 flex h-full w-full flex-col justify-between bg-gradient-to-t from-textColor to-transparent max-lg:p-3 lg:p-6 text-white/90">
            <div class="ez-post-category">
              <slot name="category" />
            </div>
            <div>
              <slot name="title" />
              <div class="ez-post-info mt-4 flex items-center justify-between gap-5 text-xs lg:mt-6">
                <slot name="meta" />
              </div>
            </div>
          </div>
        </a>
      </Host>
    );
  }
}
