import { Component, Prop, Element, h, State } from '@stencil/core';
import EmblaCarousel, { EmblaCarouselType } from 'embla-carousel';
import Autoplay from 'embla-carousel-autoplay';
import Fade from 'embla-carousel-fade';

@Component({
  tag: 'ez-banner-slider',
  shadow: false,
})
export class EzBannerSlider {
  @Element() el: HTMLElement;
  @Prop() items: string | any[] = [];
  @Prop() sliderModel: 'wide' | 'normal' = 'normal';

  @State() _items: any[] = [];

  private emblaApi: EmblaCarouselType;

  componentWillLoad() {
    this.parseItems();
  }

  componentDidLoad() {
    this.initEmbla();
  }

  private parseItems() {
    if (typeof this.items === 'string') {
      try {
        this._items = JSON.parse(this.items);
      } catch (e) {
        this._items = [];
      }
    } else {
      this._items = Array.isArray(this.items) ? this.items : [];
    }
  }

  private initEmbla() {
    const viewportNode = this.el.querySelector('.embla__viewport') as HTMLElement;
    if (!viewportNode) return;
    this.emblaApi = EmblaCarousel(viewportNode, { loop: true }, [Fade(), Autoplay({ delay: 5000 })]);
  }

  private scrollPrev = () => {
    if (this.emblaApi) this.emblaApi.scrollPrev();
  };

  render() {
    if (!this._items || this._items.length === 0) return null;
    return (
      <div class={`relative w-full overflow-hidden embla_fade rounded-[14px] lg:rounded-[20px] ${this.sliderModel === 'wide' ? 'mt-7.5 lg:mt-10' : ''}`}>
        <div class="embla__viewport relative min-h-[350px] md:min-h-[500px]">
          <div class="embla__container relative w-full min-h-[350px] md:min-h-[500px] flex">
            {this._items.map((item: any) => (
              <div class="embla__slide adv-banner relative w-full min-h-[350px] md:min-h-[500px] select-none flex-[0_0_100%]">
                <a class="h-full block w-full" href={item.link || '#'}>
                  <img class="lg:hidden h-full w-full object-cover absolute top-0 left-0" src={item.srcMobile || item.srcDesktop} loading="lazy" alt="" />
                  <img class="max-lg:hidden h-full w-full object-cover absolute top-0 left-0" src={item.srcDesktop} loading="lazy" alt="" />
                </a>
              </div>
            ))}
          </div>
        </div>
        {this._items.length > 1 && (
          <button class="embla__button embla__button--prev absolute right-0 top-1/2 -translate-y-1/2 rotate-180 z-50 cursor-pointer touch-manipulation appearance-none -mr-px max-md:hidden" type="button" onClick={this.scrollPrev}>
            <svg xmlns="http://www.w3.org/2000/svg" width="30" fill="none" viewBox="0 0 30 113">
              <g clip-path="url(#arrow_aa)">
                <path fill="#BFCBD9" fill-rule="evenodd" d="M0 3.75c0 28.814 30 32.928 30 52.823 0 21.023-30 26.414-30 56.595V3.75Z" clip-rule="evenodd"></path>
                <path fill="#fff" fill-rule="evenodd" d="M0 1c0 28.814 27 33.679 27 53.573 0 21.022-27 23.914-27 54.094V1Z" clip-rule="evenodd"></path>
                <path fill="#9FB3CB" fill-rule="evenodd" d="m13.815 50.977.125.142c.387.51.334 1.232-.124 1.677l-3.098 3.037 3.098 3.037.125.141a1.273 1.273 0 0 1-.128 1.68 1.286 1.286 0 0 1-1.804-.003l-4.025-3.946-.126-.142a1.276 1.276 0 0 1 .126-1.676l4.025-3.946.147-.124a1.29 1.29 0 0 1 1.659.123Z" clip-rule="evenodd"></path>
              </g>
              <defs>
                <clipPath id="arrow_aa">
                  <path fill="#fff" d="M0 0h30v113H0z"></path>
                </clipPath>
              </defs>
            </svg>
          </button>
        )}
      </div>
    );
  }
}
