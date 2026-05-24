import { Component, Prop, h, State, Watch } from '@stencil/core';

@Component({
  tag: 'ez-collection-card',
  shadow: false,
})
export class EzCollectionCard {
  @Prop() collectionTitle: string;
  @Prop() likes: number = 0;
  @Prop() link: string;
  @Prop() images: string | string[] = [];

  @State() _images: string[] = [];

  componentWillLoad() {
    this.parseImages(this.images);
  }

  @Watch('images')
  parseImages(newValue: string | string[]) {
    if (typeof newValue === 'string') {
      try {
        this._images = JSON.parse(newValue);
      } catch (e) {
        this._images = [];
      }
    } else {
      this._images = Array.isArray(newValue) ? newValue : [];
    }
  }

  render() {
    const displayImages = this._images.slice(0, 6);
    return (
      <div class="h-full w-full">
        <div class="relative min-w-0 shrink-0 grow-0 basis-42 lg:basis-77 h-full">
          <a class="flex w-full h-full flex-col justify-between gap-2.5 overflow-hidden rounded-lg border border-slate-120 px-3 py-4 shadow-22 lg:gap-5 lg:rounded-3xl lg:px-5 lg:py-6 lg:border-none lg:bg-slate-700 lg:text-white lg:shadow-6 lg:[&>div]:border-none" href={this.link || '#'}>
            <div class="items-center justify-between text-2xs lg:flex">
              <h3 class="text-sm lg:text-lg">{this.collectionTitle}</h3>
            </div>
            <div class="grid min-w-28 grid-cols-3 gap-1 border-b border-t border-slate-100 px-2 lg:gap-3 grid-rows-2 max-lg:grid-rows-1">
              {displayImages.map((src: string, index: number) => (
                <div class="w-9 lg:w-[52px] lg:[&:last-of-type>div>div]:flex max-lg:[&:nth-child(n+4)]:hidden max-lg:[&:nth-of-type(3)>div>div]:flex">
                  <div class="relative overflow-hidden rounded-md shadow-2">
                    <img class="h-[66px] w-[52px] object-cover" src={src} loading="lazy" alt="" />
                    {index === 5 && this._images.length > 6 && (
                      <div class="absolute right-0 top-0 flex h-full w-full items-center justify-center bg-primary-500/80 text-white">
                        <span class="text-xs font-bold">+{this._images.length - 6}</span>
                      </div>
                    )}
                    {index === 2 && this._images.length > 3 && (
                      <div class="absolute right-0 top-0 hidden h-full w-full items-center justify-center bg-primary-500/80 text-white max-lg:flex">
                        <span class="text-xs font-bold">+{this._images.length - 3}</span>
                      </div>
                    )}
                  </div>
                </div>
              ))}
            </div>
          </a>
        </div>
      </div>
    );
  }
}
