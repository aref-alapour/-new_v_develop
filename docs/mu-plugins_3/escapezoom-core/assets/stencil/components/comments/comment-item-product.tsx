import { Component, h, Prop, Host } from '@stencil/core';

@Component({
  tag: 'ez-comment-item-product',
  shadow: false,
})
export class EzCommentItemProduct {
  @Prop() rating: number = 0;
  @Prop() verified: boolean = false;

  renderStarRating(rating: number) {
    return (
      <div class="flex text-yellow-400 mb-2">
        {[1, 2, 3, 4, 5].map((star) => (
          <svg class={`w-4 h-4 ${star <= rating ? 'fill-current' : 'text-gray-300'}`} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
          </svg>
        ))}
      </div>
    );
  }

  render() {
    return (
      <Host class="block py-6 border-b border-gray-100 last:border-0">
        <div class="flex gap-4">
          <div class="flex-shrink-0">
            <slot name="avatar"></slot>
          </div>
          <div class="flex-grow">
            <div class="flex flex-wrap items-center justify-between mb-2 gap-2">
              <div class="flex items-center gap-2">
                <div class="font-yekan-bold text-navyBlue text-lg">
                  <slot name="author"></slot>
                </div>
                {this.verified && (
                  <span class="text-green-600 bg-green-50 px-2 py-0.5 rounded-full text-xs flex items-center gap-1 font-yekan-medium">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    خریدار
                  </span>
                )}
              </div>
              <div class="text-sm text-gray-400">
                <slot name="date"></slot>
              </div>
            </div>
            {this.rating > 0 && this.renderStarRating(this.rating)}
            <div class="text-gray-700 leading-7 text-sm text-justify">
              <slot name="content"></slot>
            </div>
            <div class="w-full">
              <slot name="response"></slot>
            </div>
            <div class="mt-3">
              <slot name="actions"></slot>
            </div>
          </div>
        </div>
      </Host>
    );
  }
}
