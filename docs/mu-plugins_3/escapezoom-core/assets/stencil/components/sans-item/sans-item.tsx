import { Component, h, Prop, Host } from '@stencil/core';

@Component({
  tag: 'ez-sans-item',
  shadow: false,
})
export class EzSansItem {
  @Prop() session: any;

  formatPrice(price: number) {
    return new Intl.NumberFormat('fa-IR').format(price);
  }

  render() {
    let session = this.session;
    if (typeof session === 'string') {
      try {
        session = JSON.parse(session);
      } catch {
        session = {};
      }
    }
    const { time = '', price = 0, is_booked = false, discount_price = null, capacity = 0 } = session || {};
    return (
      <Host class={`block ${is_booked ? 'opacity-60 pointer-events-none grayscale' : ''}`}>
        <div class={`
          relative flex flex-col items-center justify-center p-3 rounded-xl border-2 transition-all duration-200 group
          ${is_booked
            ? 'border-gray-100 bg-gray-50'
            : 'border-gray-200 bg-white hover:border-primary-500 hover:shadow-lg hover:-translate-y-1 cursor-pointer'}
        `}>
          {discount_price && !is_booked && (
            <div class="absolute -top-3 right-2 bg-red-500 text-white text-[10px] px-2 py-0.5 rounded-full shadow-sm font-yekan-bold">
              تخفیف ویژه
            </div>
          )}
          <div class={`text-lg font-yekan-black mb-1 ${is_booked ? 'text-gray-400' : 'text-navyBlue group-hover:text-primary-600'}`}>
            {time}
          </div>
          <div class="flex flex-col items-center gap-0.5">
            {discount_price && !is_booked ? (
              <div class="flex flex-col items-center">
                <span class="text-xs text-gray-400 line-through decoration-red-400 decoration-1">{this.formatPrice(price)}</span>
                <span class="text-sm font-yekan-bold text-green-600 flex items-center gap-1">
                  {this.formatPrice(discount_price)}
                  <span class="text-[10px] font-yekan-regular text-gray-500">تومان</span>
                </span>
              </div>
            ) : (
              <span class="text-sm font-yekan-bold text-gray-600 flex items-center gap-1">
                {is_booked ? 'رزرو شده' : (
                  <span>{this.formatPrice(price)} <span class="text-[10px] font-yekan-regular text-gray-400">تومان</span></span>
                )}
              </span>
            )}
          </div>
          {!is_booked && (
            <div class="mt-2 w-full pt-2 border-t border-dashed border-gray-100 flex justify-between items-center text-[10px] text-gray-400">
              <span>ظرفیت: {capacity}</span>
              <span class="text-primary-500 font-bold group-hover:block hidden">رزرو کنید</span>
            </div>
          )}
        </div>
      </Host>
    );
  }
}
