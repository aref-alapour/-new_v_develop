import { Component, h, Host } from '@stencil/core';

@Component({
  tag: 'ez-comment-item-post',
  shadow: false,
})
export class EzCommentItemPost {
  render() {
    return (
      <Host class="block py-6 border-b border-gray-100 last:border-0">
        <div class="flex gap-4">
          <div class="flex-shrink-0">
            <slot name="avatar"></slot>
          </div>
          <div class="flex-grow">
            <div class="flex items-center justify-between mb-2">
              <div class="font-yekan-bold text-navyBlue text-lg">
                <slot name="author"></slot>
              </div>
              <div class="text-sm text-gray-400">
                <slot name="date"></slot>
              </div>
            </div>
            <div class="text-gray-600 leading-7 text-sm text-justify">
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
