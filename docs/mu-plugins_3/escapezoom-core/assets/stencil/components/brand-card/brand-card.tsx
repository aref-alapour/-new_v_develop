import { Component, Prop, h, Host, State } from '@stencil/core';

/**
 * EZ Brand Card — displays brand info with hover effects.
 * Used in brand archive and search results.
 * 
 * Props (from AJAX endpoint):
 *   - brandId: number - Brand ID
 *   - title: string - Brand name
 *   - slug: string - URL slug for /brand/{slug}
 *   - logo: string - Logo URL
 *   - score: number - Score (0-100)
 *   - reputation: number - Reputation (0-5 scale)
 *   - address: string - Brand address/location
 *   - gameCount: number - Number of games
 * 
 * @slot actions - Optional slot for action buttons
 */
@Component({
  tag: 'ez-brand-card',
  shadow: false,
})
export class EzBrandCard {
  /** Brand ID */
  @Prop() brandId: number;
  
  /** Brand title/name (primary) */
  @Prop() title: string;
  
  /** Brand slug for URL */
  @Prop() slug: string;
  
  /** Logo URL (primary) */
  @Prop() logo: string;
  
  /** Score (0-100 scale) */
  @Prop() score: number;
  
  /** Reputation (0-5 scale) */
  @Prop() reputation: number;
  
  /** Brand address/location */
  @Prop() address: string;
  
  /** Number of games/products */
  @Prop() gameCount: number;
  
  // Legacy props for backward compatibility
  /** @deprecated Use `title` instead */
  @Prop() brandName: string;
  
  /** @deprecated Use `logo` instead */
  @Prop() logoUrl: string;
  
  /** @deprecated Use slug-based link */
  @Prop() link: string;
  
  /** @deprecated Use `reputation` instead */
  @Prop() rating: number;

  /** @deprecated Use `title` */
  @Prop() name: string;
  
  /** @deprecated Use `logo` */
  @Prop() imageSrc: string;
  
  /** @deprecated Use `gameCount` */
  @Prop() count: number;

  /** Hover state for scale effect */
  @State() isHovered: boolean = false;

  private handleMouseEnter = () => {
    this.isHovered = true;
  };

  private handleMouseLeave = () => {
    this.isHovered = false;
  };

  private get displayName(): string {
    return this.title || this.brandName || this.name || '';
  }

  private get displayImage(): string {
    return this.logo || this.logoUrl || this.imageSrc || '';
  }

  private get displayCount(): number {
    return this.gameCount ?? this.count ?? 0;
  }

  private get displayLink(): string {
    if (this.slug) {
      return `/brand/${this.slug}`;
    }
    return this.link || '#';
  }
  
  private get displayRating(): number {
    return this.reputation ?? this.rating ?? 0;
  }
  
  private get displayAddress(): string {
    return this.address || '';
  }
  
  private get displayScore(): number {
    return this.score ?? 0;
  }

  private renderStars(rating: number) {
    const stars = [];
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 >= 0.5;
    
    for (let i = 0; i < 5; i++) {
      if (i < fullStars) {
        stars.push(
          <svg key={i} class="w-3 h-3 text-yellow-400 fill-current" viewBox="0 0 20 20">
            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
          </svg>
        );
      } else if (i === fullStars && hasHalfStar) {
        stars.push(
          <svg key={i} class="w-3 h-3 text-yellow-400" viewBox="0 0 20 20">
            <defs>
              <linearGradient id={`half-${i}`}>
                <stop offset="50%" stop-color="currentColor" />
                <stop offset="50%" stop-color="#e5e7eb" />
              </linearGradient>
            </defs>
            <path fill={`url(#half-${i})`} d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
          </svg>
        );
      } else {
        stars.push(
          <svg key={i} class="w-3 h-3 text-gray-300 fill-current" viewBox="0 0 20 20">
            <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z" />
          </svg>
        );
      }
    }
    return stars;
  }

  render() {
    const scaleClass = this.isHovered ? 'scale-105' : 'scale-100';
    
    return (
      <Host
        class="block"
        data-brand-id={this.brandId}
        data-brand-slug={this.slug}
        onMouseEnter={this.handleMouseEnter}
        onMouseLeave={this.handleMouseLeave}
      >
        <div class={`flex flex-col gap-3 max-lg:gap-2 transition-transform duration-300 ease-out ${scaleClass}`}>
          {/* Image / Logo */}
          <a href={this.displayLink} class="block relative group">
            {this.displayImage ? (
              <img 
                src={this.displayImage} 
                class="rounded-xl shadow-md aspect-square object-cover w-full transition-shadow duration-300 group-hover:shadow-lg" 
                loading="lazy" 
                alt={this.displayName}
              />
            ) : (
              <div class="rounded-xl shadow-md aspect-square bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center">
                <span class="text-slate-400 text-lg font-medium">{this.displayName?.charAt(0) || '?'}</span>
              </div>
            )}
            
            {/* Score badge - top right */}
            {this.displayScore > 0 && (
              <div class="absolute top-2 right-2 bg-emerald-500 text-white text-xs font-bold px-2 py-1 rounded-lg shadow-md">
                {this.displayScore}
              </div>
            )}
            
            {/* View Games Button - appears on hover */}
            <div class="absolute inset-0 flex items-center justify-center bg-black/40 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
              <span class="bg-white text-slate-800 px-4 py-2 rounded-lg text-sm font-medium shadow-lg">
                مشاهده بازی‌ها
              </span>
            </div>
          </a>

          {/* Brand Info */}
          <div class="flex flex-col gap-1.5">
            <a href={this.displayLink} class="flex justify-between items-center">
              <span class="text-sm font-semibold text-slate-800 line-clamp-1">{this.displayName}</span>
              
              {/* Game Count */}
              {this.displayCount > 0 && (
                <span class="flex items-center gap-1.5 max-lg:hidden text-xs text-slate-500 bg-slate-100 px-2 py-1 rounded-full">
                  {this.displayCount}
                  <svg xmlns="http://www.w3.org/2000/svg" width="12" height="13" viewBox="0 0 12 13" fill="none">
                    <path d="M3.55248 5.52134C3.55248 4.71176 3.42316 3.46277 3.92084 2.73396C5.02103 1.12537 7.35366 1.33882 8.1766 2.90895C8.58023 3.68007 8.42838 4.75791 8.447 5.52134M3.55248 5.52134C2.28182 5.52134 2.02221 6.23477 1.82823 6.79533C1.64894 7.42511 1.46574 8.92985 1.74593 10.5788C1.95559 11.6288 2.77363 12.0903 3.47704 12.149C4.15009 12.2047 6.99118 12.1836 7.81314 12.1836C9.08771 12.1836 9.88322 11.9086 10.2575 10.6481C10.4367 9.66828 10.4857 7.91547 10.1869 6.79533C9.79113 5.67518 8.9917 5.52134 8.447 5.52134M3.55248 5.52134C4.89857 5.46846 7.67696 5.47904 8.447 5.52134" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M6 7.71875V9.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                  </svg>
                </span>
              )}
            </a>
            
            {/* Address */}
            {this.displayAddress && (
              <div class="flex items-center gap-1 text-xs text-slate-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="line-clamp-1">{this.displayAddress}</span>
              </div>
            )}

            {/* Rating */}
            {this.displayRating > 0 && (
              <div class="flex items-center gap-1">
                {this.renderStars(this.displayRating)}
                <span class="text-xs text-slate-500 mr-1">{this.displayRating.toFixed(1)}</span>
              </div>
            )}
          </div>

          {/* Slot for custom actions */}
          <slot name="actions" />
        </div>
      </Host>
    );
  }
}
