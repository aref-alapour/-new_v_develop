import { Component, h, Prop, State } from '@stencil/core';

@Component({
  tag: 'ez-sans-list',
  shadow: false,
})
export class EzSansList {
  @Prop() productId: string | number;
  /** API endpoint for fetching sessions (e.g. admin-ajax.php?action=ez_sanses or custom). */
  @Prop() apiEndpoint: string = '';

  @State() dates: any[] = [];
  @State() selectedDate: string = '';
  @State() sessions: any[] = [];
  @State() loading: boolean = true;
  @State() error: string = '';

  componentWillLoad() {
    this.fetchData();
  }

  fetchData = async () => {
    this.loading = true;
    this.error = '';
    try {
      if (this.apiEndpoint) {
        const formData = new FormData();
        formData.append('product_id', String(this.productId));
        const response = await fetch(this.apiEndpoint, {
          method: 'POST',
          body: formData,
        });
        const data = await response.json();
        if (data && data.dates) {
          this.dates = data.dates;
          this.selectedDate = data.dates[0]?.date || '';
          this.sessions = data.dates[0]?.sessions || [];
        } else {
          this.mockData();
        }
      } else {
        this.mockData();
      }
    } catch (e) {
      this.error = 'خطا در دریافت اطلاعات سانس‌ها';
      this.mockData();
    } finally {
      this.loading = false;
    }
  };

  mockData() {
    const today = new Date();
    const mockDates: any[] = [];
    const faDays = ['یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنج‌شنبه', 'جمعه', 'شنبه'];
    for (let i = 0; i < 7; i++) {
      const d = new Date(today);
      d.setDate(today.getDate() + i);
      const dateStr = d.toISOString().split('T')[0];
      const dayName = faDays[d.getDay()];
      const sessions: any[] = [];
      for (let j = 14; j < 23; j += 2) {
        sessions.push({
          id: `${dateStr}-${j}`,
          time: `${j}:00`,
          price: 150000,
          capacity: 5,
          is_booked: Math.random() > 0.7,
          discount_price: Math.random() > 0.8 ? 130000 : null
        });
      }
      mockDates.push({
        date: dateStr,
        day_name: dayName,
        date_formatted: new Intl.DateTimeFormat('fa-IR').format(d),
        sessions
      });
    }
    this.dates = mockDates;
    this.selectedDate = mockDates[0].date;
    this.sessions = mockDates[0].sessions;
  }

  handleDateSelect(dateObj: any) {
    this.selectedDate = dateObj.date;
    this.sessions = dateObj.sessions;
  }

  render() {
    if (this.loading) {
      return (
        <div class="w-full flex justify-center py-12">
          <ez-loading type="circle" size="lg" message="در حال دریافت سانس‌ها..."></ez-loading>
        </div>
      );
    }
    if (this.error) {
      return <div class="text-red-500 text-center py-8">{this.error}</div>;
    }
    return (
      <div class="block w-full bg-white rounded-2xl border border-slate-105 overflow-hidden">
        <div class="flex overflow-x-auto pb-2 scrollbar-hide border-b border-slate-105">
          {this.dates.map((date: any, index: number) => (
            <button
              class={`
                flex-shrink-0 flex flex-col items-center justify-center min-w-d80 p-4 gap-1 transition-all relative
                ${this.selectedDate === date.date ? 'text-primary-600 bg-primary-50' : 'text-gray-500 hover:bg-gray-50'}
                ${index !== this.dates.length - 1 ? 'border-l border-slate-105' : ''}
              `}
              onClick={() => this.handleDateSelect(date)}
            >
              <span class="text-sm font-yekan-medium">{date.day_name}</span>
              <span class="text-xs text-gray-400 font-yekan-regular">{date.date_formatted}</span>
              {this.selectedDate === date.date && (
                <div class="absolute bottom-0 left-0 right-0 h-1 bg-primary-500 rounded-t-md"></div>
              )}
            </button>
          ))}
        </div>
        <div class="p-6">
          <h3 class="text-gray-800 font-yekan-bold mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            سانس‌های قابل رزرو
          </h3>
          {this.sessions.length > 0 ? (
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
              {this.sessions.map((session: any) => (
                <ez-sans-item session={JSON.stringify(session)}></ez-sans-item>
              ))}
            </div>
          ) : (
            <div class="text-center py-8 text-gray-500 bg-gray-50 rounded-xl">
              برای این تاریخ سانسی تعریف نشده است.
            </div>
          )}
        </div>
      </div>
    );
  }
}
