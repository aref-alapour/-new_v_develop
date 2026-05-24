import { Component, h, Host, Prop } from '@stencil/core';

/**
 * EZ Skeleton - Placeholder loader for content that's being fetched.
 * Use as a wrapper or with predefined type props for common patterns.
 */
@Component({
  tag: 'ez-skeleton',
  shadow: false,
})
export class EzSkeleton {
  /** Predefined skeleton type: card, list, text, avatar */
  @Prop() type: 'card' | 'list' | 'text' | 'avatar' | 'custom' = 'custom';

  /** Number of items to show (for list/card types) */
  @Prop() count: number = 3;

  /** Show animation pulse effect */
  @Prop() animate: boolean = true;

  renderCard() {
    return (
      <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="aspect-video bg-slate-200"></div>
        <div class="p-4 space-y-3">
          <div class="h-4 bg-slate-200 rounded w-3/4"></div>
          <div class="h-3 bg-slate-200 rounded w-1/2"></div>
          <div class="flex justify-between">
            <div class="h-3 bg-slate-200 rounded w-1/4"></div>
            <div class="h-3 bg-slate-200 rounded w-1/3"></div>
          </div>
        </div>
      </div>
    );
  }

  renderList() {
    return (
      <div class="space-y-4">
        {Array.from({ length: this.count }).map(() => (
          <div class="flex gap-4 items-center p-3 bg-white rounded-lg">
            <div class="w-12 h-12 bg-slate-200 rounded-lg flex-shrink-0"></div>
            <div class="flex-1 space-y-2">
              <div class="h-4 bg-slate-200 rounded w-3/4"></div>
              <div class="h-3 bg-slate-200 rounded w-1/2"></div>
            </div>
          </div>
        ))}
      </div>
    );
  }

  renderText() {
    return (
      <div class="space-y-3">
        <div class="h-4 bg-slate-200 rounded w-full"></div>
        <div class="h-4 bg-slate-200 rounded w-5/6"></div>
        <div class="h-4 bg-slate-200 rounded w-4/6"></div>
      </div>
    );
  }

  renderAvatar() {
    return (
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-slate-200 rounded-full"></div>
        <div class="space-y-2">
          <div class="h-3 bg-slate-200 rounded w-24"></div>
          <div class="h-2 bg-slate-200 rounded w-16"></div>
        </div>
      </div>
    );
  }

  render() {
    const animateClass = this.animate ? 'animate-pulse' : '';

    return (
      <Host class={`block ${animateClass}`} aria-busy="true" aria-label="Loading...">
        {this.type === 'card' && (
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {Array.from({ length: this.count }).map(() => this.renderCard())}
          </div>
        )}
        {this.type === 'list' && this.renderList()}
        {this.type === 'text' && this.renderText()}
        {this.type === 'avatar' && this.renderAvatar()}
        {this.type === 'custom' && <slot />}
      </Host>
    );
  }
}
