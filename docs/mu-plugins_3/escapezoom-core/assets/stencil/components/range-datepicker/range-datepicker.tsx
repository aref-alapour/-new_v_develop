import { Component, Prop, State, Event, EventEmitter, h, Element, Watch } from '@stencil/core';
import flatpickr from 'flatpickr';
import { Instance as FlatpickrInstance } from 'flatpickr/dist/types/instance';
import * as jalaali from 'jalaali-js';

/** Response shape from shamsi-holidays API */
export interface HolidayEvent {
  description: string;
  is_holiday: boolean;
}

export interface HolidayData {
  date: string; // "1404-01-01"
  events: HolidayEvent[];
  is_holiday: boolean;
}

export interface DateChangeDetail {
  startDate: string | null;
  endDate: string | null;
  startDatePersian: string;
  endDatePersian: string;
  isValid: boolean;
}

const HOLIDAYS_BASE_URL = 'https://raw.githubusercontent.com/hasan-ahani/shamsi-holidays/main/holidays';

/** تاریخ را در ظهر (۱۲:۰۰) برمی‌گرداند تا با تغییر timezone یک روز جابجا نشود */
function dateAtNoon(isoDate: string): Date {
  return new Date(isoDate + 'T12:00:00');
}

/** تاریخ محلی را به رشته ISO (YYYY-MM-DD) تبدیل می‌کند تا بر اساس timezone جابجا نشود */
function dateToISO(date: Date): string {
  const y = date.getFullYear();
  const m = String(date.getMonth() + 1).padStart(2, '0');
  const d = String(date.getDate()).padStart(2, '0');
  return `${y}-${m}-${d}`;
}

@Component({
  tag: 'ez-range-datepicker',
  styleUrl: 'range-datepicker.css',
  shadow: false,
})
export class EzRangeDatepicker {
  @Element() el!: HTMLElement;

  // Props
  @Prop() startDate?: string;
  @Prop() endDate?: string;
  @Prop() themeColor: string = '#3b82f6';
  @Prop() placeholder: string = 'انتخاب بازه تاریخ';
  @Prop() minDate?: string;
  @Prop() maxDate?: string;
  @Prop() disabledDates?: string[];
  @Prop() disabled: boolean = false;
  @Prop() required: boolean = false;
  @Prop() name: string = 'dateRange';
  @Prop() persianNumbers: boolean = false;
  /** انتخاب بازه (true) یا تک روز (false). از HTML: allow-range یا allow-range="false" */
  @Prop() allowRange: string | boolean = true;

  // Events
  @Event() dateChanged!: EventEmitter<DateChangeDetail>;
  @Event() dateCleared!: EventEmitter<void>;
  @Event() calendarOpened!: EventEmitter<void>;
  @Event() calendarClosed!: EventEmitter<void>;

  // State — committed (input + form + emit only on Confirm)
  @State() internalStartDate: string | null = null;
  @State() internalEndDate: string | null = null;
  @State() errorMessage: string = '';
  @State() calendarMode: 'gregorian' | 'jalali' = 'jalali';
  @State() jalaliCurrentYear: number = 1404;
  @State() jalaliCurrentMonth: number = 1;
  @State() jalaliCalendarOpen: boolean = false;
  @State() tooltipData: { x: number; y: number; content: string; isHoliday?: boolean } | null = null;
  @State() jalaliHoverDay: { year: number; month: number; day: number } | null = null;
  @State() holidaysLoading: boolean = false;
  @State() holidaysVersion: number = 0;
  /** انتخاب فعلی در تقویم (قبل از تأیید). فقط با دکمه تأیید به internal و form منتقل می‌شود */
  @State() pendingStartDate: string | null = null;
  @State() pendingEndDate: string | null = null;

  private get isRangeMode(): boolean {
    return this.allowRange !== false && this.allowRange !== 'false';
  }

  // Private
  private flatpickrInstance?: FlatpickrInstance;
  private inputElement?: HTMLInputElement;
  private hiddenStartInput?: HTMLInputElement;
  private hiddenEndInput?: HTMLInputElement;
  /** Lookup: date key "1404-01-01" -> HolidayData (for current view) */
  private allHolidays: Map<string, HolidayData> = new Map();
  /** Cache: year -> HolidayData[] to avoid refetch when switching months */
  private holidayCache: Map<number, HolidayData[]> = new Map();
  private holidayFetchAbort: AbortController | null = null;

  // Watchers
  @Watch('startDate')
  @Watch('endDate')
  handlePropChange() {
    this.validateAndSetDates();
  }

  @Watch('themeColor')
  handleThemeColorChange() {
    if (this.el) {
      this.el.style.setProperty('--datepicker-primary-color', this.themeColor);
      this.el.style.setProperty('--datepicker-selected-bg', this.themeColor);
    }
  }

  componentWillLoad() {
    this.validateAndSetDates();
    const today = new Date();
    const jToday = jalaali.toJalaali(today);
    this.jalaliCurrentYear = jToday.jy;
    this.jalaliCurrentMonth = jToday.jm;
    // Load holidays for current year on demand (async, no await)
    this.fetchHolidays(this.jalaliCurrentYear);
  }

  componentDidLoad() {
    // Apply theme color
    this.handleThemeColorChange();

    // Initialize Flatpickr (for Gregorian mode)
    this.initializeFlatpickr();
  }

  disconnectedCallback() {
    if (this.holidayFetchAbort) {
      this.holidayFetchAbort.abort();
      this.holidayFetchAbort = null;
    }
    if (this.flatpickrInstance) {
      this.flatpickrInstance.destroy();
      this.flatpickrInstance = undefined;
    }
  }

  /**
   * بارگذاری تعطیلات از API برای سال شمسی. با کش از درخواست تکراری جلوگیری می‌شود.
   * در صورت ۴۰۴ یا خطای شبکه تقویم بدون تعطیلات کار می‌کند (بدون کرش).
   */
  private async fetchHolidays(year: number): Promise<void> {
    if (this.holidayCache.has(year)) {
      this.mergeHolidaysIntoMap(this.holidayCache.get(year)!);
      return;
    }
    if (this.holidaysLoading) return;
    this.holidaysLoading = true;
    if (this.holidayFetchAbort) this.holidayFetchAbort.abort();
    const controller = new AbortController();
    this.holidayFetchAbort = controller;
    const url = `${HOLIDAYS_BASE_URL}/${year}.json`;
    try {
      const res = await fetch(url, { signal: controller.signal });
      if (!res.ok) {
        if (res.status === 404) {
          console.warn(`[my-range-datepicker] No holidays data for year ${year} (404).`);
        } else {
          console.warn(`[my-range-datepicker] Holidays fetch failed for ${year}: ${res.status}`);
        }
        return;
      }
      const data = (await res.json()) as HolidayData[];
      if (Array.isArray(data)) {
        this.holidayCache.set(year, data);
        this.mergeHolidaysIntoMap(data);
      }
    } catch (e) {
      if ((e as Error).name !== 'AbortError') {
        console.warn('[my-range-datepicker] Holidays fetch error:', (e as Error).message);
      }
    } finally {
      if (this.holidayFetchAbort === controller) {
        this.holidayFetchAbort = null;
      }
      this.holidaysLoading = false;
    }
  }

  private mergeHolidaysIntoMap(list: HolidayData[]): void {
    list.forEach(day => this.allHolidays.set(day.date, day));
    this.holidaysVersion += 1;
  }

  private validateAndSetDates() {
    // Validate and set start date
    if (this.startDate && this.isValidISODate(this.startDate)) {
      this.internalStartDate = this.startDate;
    } else if (this.startDate) {
      console.warn(`Invalid start date: ${this.startDate}`);
    }

    // Validate and set end date
    if (this.endDate && this.isValidISODate(this.endDate)) {
      this.internalEndDate = this.endDate;
    } else if (this.endDate) {
      console.warn(`Invalid end date: ${this.endDate}`);
    }

    if (!this.isRangeMode && this.internalStartDate) {
      this.internalEndDate = this.internalStartDate;
    }
    if (this.isRangeMode && this.internalStartDate && this.internalEndDate) {
      if (!this.isValidRange(this.internalStartDate, this.internalEndDate)) {
        this.errorMessage = 'تاریخ پایان نمی‌تواند قبل از تاریخ شروع باشد';
      } else {
        this.errorMessage = '';
      }
    }

    this.updateHiddenInputs();

    // Emit change event
    this.emitDateChange();
  }

  private isValidISODate(dateStr: string): boolean {
    const date = new Date(dateStr);
    return date instanceof Date && !isNaN(date.getTime());
  }

  private isValidRange(start: string, end: string): boolean {
    return dateAtNoon(start) <= dateAtNoon(end);
  }

  private initializeFlatpickr() {
    if (!this.inputElement) return;

    const defaultDates: Date[] = [];
    if (this.isRangeMode && this.internalStartDate && this.internalEndDate) {
      defaultDates.push(dateAtNoon(this.internalStartDate));
      defaultDates.push(dateAtNoon(this.internalEndDate));
    } else if (this.internalStartDate) {
      defaultDates.push(dateAtNoon(this.internalStartDate));
    } else if (!this.isRangeMode) {
      defaultDates.length = 0;
    } else {
      defaultDates.push(new Date());
    }

    this.flatpickrInstance = flatpickr(this.inputElement, {
      mode: this.isRangeMode ? 'range' : 'single',
      dateFormat: 'Y-m-d',
      defaultDate: defaultDates.length > 0 ? defaultDates : undefined,
      minDate: this.minDate,
      maxDate: this.maxDate,
      disable: this.disabledDates?.map(d => new Date(d)) || [],
      clickOpens: false,
      closeOnSelect: false,
      position: 'auto',
      appendTo: this.el.querySelector('.my-range-datepicker-wrapper') || this.el,
      static: false,
      onReady: (selectedDates, dateStr, instance) => {
        this.addCustomButtons(instance);
        this.fixRTLPositioning(instance);
      },

      onOpen: (selectedDates, dateStr, instance) => {
        this.pendingStartDate = this.internalStartDate;
        this.pendingEndDate = this.internalEndDate;
        this.fixRTLPositioning(instance);
        this.calendarOpened.emit();
      },

      onClose: () => {
        this.calendarClosed.emit();
      },

      onChange: (selectedDates) => {
        // فقط حالت pending را به‌روز می‌کنیم؛ تأیید نهایی با دکمه تأیید انجام می‌شود
        if (this.isRangeMode && selectedDates.length === 2) {
          this.pendingStartDate = dateToISO(selectedDates[0]);
          this.pendingEndDate = dateToISO(selectedDates[1]);
          this.errorMessage = '';
        } else if (selectedDates.length === 1) {
          const iso = dateToISO(selectedDates[0]);
          this.pendingStartDate = iso;
          this.pendingEndDate = this.isRangeMode ? null : iso;
          this.errorMessage = '';
        }
      },
    });
  }

  private fixRTLPositioning(instance: FlatpickrInstance) {
    const calendar = instance.calendarContainer;
    const input = this.inputElement;
    if (!calendar) return;
    setTimeout(() => {
      const parent = calendar.parentElement;
      if (parent?.classList.contains('my-range-datepicker-wrapper')) {
        calendar.style.position = 'absolute';
        calendar.style.top = 'calc(100% + 4px)';
        calendar.style.right = '0';
        calendar.style.left = 'auto';
        calendar.style.width = '100%';
        calendar.style.minWidth = '320px';
        calendar.style.maxWidth = '360px';
      } else if (input && this.el.getBoundingClientRect) {
        const hostRect = this.el.getBoundingClientRect();
        const inputRect = input.getBoundingClientRect();
        calendar.style.position = 'absolute';
        calendar.style.top = `${inputRect.bottom - hostRect.top + 4}px`;
        calendar.style.right = `${hostRect.right - inputRect.right}px`;
        calendar.style.left = 'auto';
      }
    }, 0);
  }

  private addCustomButtons(instance: FlatpickrInstance) {
    const calendar = instance.calendarContainer;
    if (!calendar) return;

    // Remove existing custom buttons if any
    const existingButtons = calendar.querySelector('.flatpickr-custom-buttons');
    if (existingButtons) {
      existingButtons.remove();
    }

    // Create button container
    const buttonContainer = document.createElement('div');
    buttonContainer.className = 'flatpickr-custom-buttons';

    // Calendar mode switch
    const switchContainer = document.createElement('div');
    switchContainer.className = 'flatpickr-calendar-switch';

    const jalaliBtn = document.createElement('button');
    jalaliBtn.className = 'flatpickr-switch-btn';
    jalaliBtn.textContent = 'شمسی';
    jalaliBtn.type = 'button';

    const gregorianBtn = document.createElement('button');
    gregorianBtn.className = 'flatpickr-switch-btn active';
    gregorianBtn.textContent = 'میلادی';
    gregorianBtn.type = 'button';

    jalaliBtn.addEventListener('click', () => {
      this.switchToJalali();
    });

    gregorianBtn.addEventListener('click', () => {
      this.switchToGregorian();
    });

    switchContainer.appendChild(jalaliBtn);
    switchContainer.appendChild(gregorianBtn);

    // Today button (بدون بستن)
    const todayBtn = document.createElement('button');
    todayBtn.className = 'flatpickr-today-btn';
    todayBtn.textContent = 'امروز';
    todayBtn.type = 'button';
    todayBtn.addEventListener('click', () => {
      const today = new Date();
      const todayIso = dateToISO(today);
      if (this.isRangeMode && this.pendingStartDate && !this.pendingEndDate) {
        this.pendingEndDate = todayIso;
        instance.setDate([dateAtNoon(this.pendingStartDate), today], true);
      } else {
        this.pendingStartDate = todayIso;
        this.pendingEndDate = this.isRangeMode ? null : todayIso;
        instance.setDate(this.isRangeMode ? [today] : [today], false);
      }
    });

    // Clear button (بدون بستن)
    const clearBtn = document.createElement('button');
    clearBtn.className = 'flatpickr-clear-btn';
    clearBtn.textContent = 'پاک کردن';
    clearBtn.type = 'button';
    clearBtn.addEventListener('click', () => {
      this.pendingStartDate = null;
      this.pendingEndDate = null;
      this.clearDates();
      instance.clear();
    });

    // تأیید — فقط این دکمه تقویم را می‌بندد
    const confirmBtn = document.createElement('button');
    confirmBtn.className = 'flatpickr-confirm-btn';
    confirmBtn.textContent = '✓ تأیید';
    confirmBtn.type = 'button';
    confirmBtn.setAttribute('aria-label', 'تأیید');
    confirmBtn.addEventListener('click', () => {
      this.syncFromFlatpickr(instance);
      instance.close();
    });

    buttonContainer.appendChild(switchContainer);
    buttonContainer.appendChild(todayBtn);
    buttonContainer.appendChild(clearBtn);
    buttonContainer.appendChild(confirmBtn);

    calendar.appendChild(buttonContainer);
  }

  /** فقط هنگام کلیک تأیید در تقویم میلادی: مقدار نهایی را به internal منتقل و emit می‌کند */
  private syncFromFlatpickr(instance: FlatpickrInstance) {
    const dates = instance.selectedDates;
    if (this.isRangeMode && dates.length === 2) {
      this.internalStartDate = dateToISO(dates[0]);
      this.internalEndDate = dateToISO(dates[1]);
    } else if (dates.length === 1) {
      const iso = dateToISO(dates[0]);
      this.internalStartDate = iso;
      this.internalEndDate = this.isRangeMode ? null : iso;
    }
    this.errorMessage = '';
    this.updateHiddenInputs();
    this.emitDateChange();
  }

  /** بعد از انتخاب در تقویم شمسی، تقویم میلادی را با pending همگام می‌کند */
  private syncFlatpickrFromPending() {
    if (!this.flatpickrInstance) return;
    const dates: Date[] = [];
    if (this.pendingStartDate) dates.push(dateAtNoon(this.pendingStartDate));
    if (this.isRangeMode && this.pendingEndDate && this.pendingEndDate !== this.pendingStartDate) {
      dates.push(dateAtNoon(this.pendingEndDate));
    }
    this.flatpickrInstance.setDate(dates, false);
  }

  private switchToJalali() {
    this.calendarMode = 'jalali';
    this.jalaliCalendarOpen = true;
    // نمایش ماه/سال بر اساس انتخاب فعلی (pending یا internal)
    const refStart = this.pendingStartDate || this.internalStartDate;
    if (refStart) {
      const startDate = dateAtNoon(refStart);
      const jDate = jalaali.toJalaali(startDate);
      this.jalaliCurrentYear = jDate.jy;
      this.jalaliCurrentMonth = jDate.jm;
    }
    if (this.flatpickrInstance) {
      this.flatpickrInstance.close();
    }
  }

  private switchToGregorian() {
    this.calendarMode = 'gregorian';
    this.jalaliCalendarOpen = false;
    
    if (this.flatpickrInstance) {
      this.flatpickrInstance.open();
    }
  }

  private clearDates() {
    this.pendingStartDate = null;
    this.pendingEndDate = null;
    this.internalStartDate = null;
    this.internalEndDate = null;
    this.errorMessage = '';
    this.updateHiddenInputs();
    this.emitDateChange();
    this.dateCleared.emit();
    if (this.flatpickrInstance) {
      this.flatpickrInstance.clear();
    }
  }

  private updateHiddenInputs() {
    if (this.hiddenStartInput) {
      this.hiddenStartInput.value = this.internalStartDate || '';
    }
    if (this.hiddenEndInput) {
      this.hiddenEndInput.value = this.internalEndDate || '';
    }
  }

  private emitDateChange() {
    const detail: DateChangeDetail = {
      startDate: this.internalStartDate,
      endDate: this.isRangeMode ? this.internalEndDate : (this.internalStartDate || null),
      startDatePersian: this.internalStartDate ? this.toPersianDateString(dateAtNoon(this.internalStartDate)) : '',
      endDatePersian: this.internalEndDate ? this.toPersianDateString(dateAtNoon(this.internalEndDate)) : '',
      isValid: this.isRangeMode
        ? !!(this.internalStartDate && this.internalEndDate && !this.errorMessage)
        : !!(this.internalStartDate && !this.errorMessage),
    };
    this.dateChanged.emit(detail);
  }

  private toPersianDateString(date: Date): string {
    const j = jalaali.toJalaali(date);
    const year = this.persianNumbers ? this.toPersianNumber(j.jy) : j.jy;
    const month = this.persianNumbers ? this.toPersianNumber(j.jm) : String(j.jm).padStart(2, '0');
    const day = this.persianNumbers ? this.toPersianNumber(j.jd) : String(j.jd).padStart(2, '0');
    return `${year}/${month}/${day}`;
  }

  private toPersianNumber(num: number): string {
    const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return String(num).replace(/\d/g, (d) => persianDigits[parseInt(d)]);
  }

  private getDisplayValue(): string {
    if (!this.internalStartDate) return '';
    const start = this.toPersianDateString(dateAtNoon(this.internalStartDate));
    if (!this.isRangeMode || !this.internalEndDate || this.internalEndDate === this.internalStartDate) {
      return start;
    }
    const end = this.toPersianDateString(dateAtNoon(this.internalEndDate));
    return `${start} - ${end}`;
  }

  // Jalali Calendar Methods
  private getJalaliMonthName(month: number): string {
    const months = [
      'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
      'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
    ];
    return months[month - 1] || '';
  }

  private getJalaliWeekDays(): string[] {
    return ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'];
  }

  private getJalaliDaysInMonth(year: number, month: number): number {
    return jalaali.jalaaliMonthLength(year, month);
  }

  private getJalaliFirstDayOfMonth(year: number, month: number): number {
    const gDate = jalaali.toGregorian(year, month, 1);
    const date = new Date(gDate.gy, gDate.gm - 1, gDate.gd);
    return (date.getDay() + 1) % 7; // Convert to Persian week (Saturday = 0)
  }

  private jalaliPrevMonth = () => {
    if (this.jalaliCurrentMonth === 1) {
      this.jalaliCurrentMonth = 12;
      this.jalaliCurrentYear--;
      this.fetchHolidays(this.jalaliCurrentYear);
    } else {
      this.jalaliCurrentMonth--;
    }
  }

  private jalaliNextMonth = () => {
    if (this.jalaliCurrentMonth === 12) {
      this.jalaliCurrentMonth = 1;
      this.jalaliCurrentYear++;
      this.fetchHolidays(this.jalaliCurrentYear);
    } else {
      this.jalaliCurrentMonth++;
    }
  }

  private jalaliSelectDay = (day: number) => {
    const gDate = jalaali.toGregorian(this.jalaliCurrentYear, this.jalaliCurrentMonth, day);
    const selectedDate = new Date(gDate.gy, gDate.gm - 1, gDate.gd);
    const isoDate = dateToISO(selectedDate);

    if (!this.isRangeMode) {
      this.pendingStartDate = isoDate;
      this.pendingEndDate = isoDate;
      this.jalaliHoverDay = null;
    } else if (!this.pendingStartDate || (this.pendingStartDate && this.pendingEndDate)) {
      this.pendingStartDate = isoDate;
      this.pendingEndDate = null;
    } else {
      if (dateAtNoon(isoDate) < dateAtNoon(this.pendingStartDate)) {
        this.pendingEndDate = this.pendingStartDate;
        this.pendingStartDate = isoDate;
      } else {
        this.pendingEndDate = isoDate;
      }
      this.jalaliHoverDay = null;
    }

    this.errorMessage = '';
    this.syncFlatpickrFromPending();
  }

  private jalaliSelectToday = () => {
    const today = new Date();
    const todayIso = dateToISO(today);
    const jToday = jalaali.toJalaali(today);
    this.jalaliCurrentYear = jToday.jy;
    this.jalaliCurrentMonth = jToday.jm;

    if (this.isRangeMode && this.pendingStartDate && !this.pendingEndDate) {
      this.pendingEndDate = todayIso;
    } else if (!this.pendingStartDate) {
      this.pendingStartDate = todayIso;
      this.pendingEndDate = this.isRangeMode ? null : todayIso;
    } else {
      this.pendingStartDate = todayIso;
      this.pendingEndDate = this.isRangeMode ? null : todayIso;
    }
    this.jalaliHoverDay = null;
    this.errorMessage = '';
    this.syncFlatpickrFromPending();
  }

  private jalaliClear = () => {
    this.clearDates();
    this.jalaliHoverDay = null;
  }

  /** بستن تقویم شمسی بعد از تأیید: ثبت pending در internal و emit */
  private jalaliConfirm = () => {
    this.internalStartDate = this.pendingStartDate;
    this.internalEndDate = this.pendingEndDate;
    this.updateHiddenInputs();
    this.emitDateChange();
    this.jalaliHoverDay = null;
    this.jalaliCalendarOpen = false;
  }

  private isJalaliDayInRange(year: number, month: number, day: number): boolean {
    if (!this.pendingStartDate || !this.pendingEndDate) return false;

    const gDate = jalaali.toGregorian(year, month, day);
    const dayDate = new Date(gDate.gy, gDate.gm - 1, gDate.gd);
    const start = dateAtNoon(this.pendingStartDate);
    const end = dateAtNoon(this.pendingEndDate);

    return dayDate >= start && dayDate <= end;
  }

  /** روز در بازهٔ پیش‌نمایش (هاور) بین start و روز زیر موس */
  private isJalaliDayInPreviewRange(year: number, month: number, day: number): boolean {
    if (!this.pendingStartDate || this.pendingEndDate || !this.jalaliHoverDay) return false;

    const gDate = jalaali.toGregorian(year, month, day);
    const dayDate = new Date(gDate.gy, gDate.gm - 1, gDate.gd);
    const start = dateAtNoon(this.pendingStartDate);
    const h = jalaali.toGregorian(this.jalaliHoverDay.year, this.jalaliHoverDay.month, this.jalaliHoverDay.day);
    const hoverDate = new Date(h.gy, h.gm - 1, h.gd);
    const [from, to] = start <= hoverDate ? [start, hoverDate] : [hoverDate, start];
    return dayDate >= from && dayDate <= to;
  }

  private isJalaliDayStart(year: number, month: number, day: number): boolean {
    if (!this.pendingStartDate) return false;

    const gDate = jalaali.toGregorian(year, month, day);
    const dayDate = new Date(gDate.gy, gDate.gm - 1, gDate.gd);
    const start = dateAtNoon(this.pendingStartDate);

    return dayDate.toDateString() === start.toDateString();
  }

  private isJalaliDayEnd(year: number, month: number, day: number): boolean {
    if (!this.pendingEndDate) return false;

    const gDate = jalaali.toGregorian(year, month, day);
    const dayDate = new Date(gDate.gy, gDate.gm - 1, gDate.gd);
    const end = dateAtNoon(this.pendingEndDate);

    return dayDate.toDateString() === end.toDateString();
  }

  private isJalaliDayToday(year: number, month: number, day: number): boolean {
    const gDate = jalaali.toGregorian(year, month, day);
    const dayDate = new Date(gDate.gy, gDate.gm - 1, gDate.gd);
    const today = new Date();

    return dayDate.toDateString() === today.toDateString();
  }

  private getHolidayForJalaliDay(year: number, month: number, day: number): HolidayData | null {
    const dateKey = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    return this.allHolidays.get(dateKey) || null;
  }

  /** جمعه در تقویم میلادی: getDay() === 5 */
  private isJalaliDayFriday(year: number, month: number, day: number): boolean {
    const g = jalaali.toGregorian(year, month, day);
    const d = new Date(g.gy, g.gm - 1, g.gd);
    return d.getDay() === 5;
  }

  /** کلاس‌های border برای روز: جمعه / تعطیل رسمی (غیرجمعه) / فقط مناسبت */
  private getJalaliDayBorderClass(year: number, month: number, day: number, holiday: HolidayData | null): string | null {
    if (!holiday?.events?.length) return null;
    const isFriday = this.isJalaliDayFriday(year, month, day);
    if (isFriday) return 'jalali-day-friday';
    if (holiday.is_holiday) return 'jalali-day-holiday-official';
    return 'jalali-day-event';
  }

  /** روز خارج از minDate/maxDate غیرفعال است */
  private isJalaliDayDisabled(year: number, month: number, day: number): boolean {
    const gDate = jalaali.toGregorian(year, month, day);
    const d = new Date(gDate.gy, gDate.gm - 1, gDate.gd);
    if (this.minDate && d < new Date(this.minDate)) return true;
    if (this.maxDate && d > new Date(this.maxDate)) return true;
    return false;
  }

  private showTooltip = (event: MouseEvent, holiday: HolidayData) => {
    const rect = (event.target as HTMLElement).getBoundingClientRect();
    const content = holiday.events.map(e => e.description).join(' • ');
    this.tooltipData = {
      x: rect.left + rect.width / 2,
      y: rect.top - 8,
      content,
      isHoliday: holiday.is_holiday,
    };
  }

  private hideTooltip = () => {
    this.tooltipData = null;
  }

  private renderJalaliCalendar() {
    const daysInMonth = this.getJalaliDaysInMonth(this.jalaliCurrentYear, this.jalaliCurrentMonth);
    const firstDay = this.getJalaliFirstDayOfMonth(this.jalaliCurrentYear, this.jalaliCurrentMonth);
    
    const days: any[] = [];

    // Empty cells before first day
    for (let i = 0; i < firstDay; i++) {
      days.push(<div class="jalali-day-empty"></div>);
    }

    // Days of the month
    for (let day = 1; day <= daysInMonth; day++) {
      const disabled = this.isJalaliDayDisabled(this.jalaliCurrentYear, this.jalaliCurrentMonth, day);
      const isInRange = this.isJalaliDayInRange(this.jalaliCurrentYear, this.jalaliCurrentMonth, day);
      const isPreview = this.isJalaliDayInPreviewRange(this.jalaliCurrentYear, this.jalaliCurrentMonth, day);
      const isStart = this.isJalaliDayStart(this.jalaliCurrentYear, this.jalaliCurrentMonth, day);
      const isEnd = this.isJalaliDayEnd(this.jalaliCurrentYear, this.jalaliCurrentMonth, day);
      const isToday = this.isJalaliDayToday(this.jalaliCurrentYear, this.jalaliCurrentMonth, day);
      const holiday = this.getHolidayForJalaliDay(this.jalaliCurrentYear, this.jalaliCurrentMonth, day);
      const borderClass = this.getJalaliDayBorderClass(this.jalaliCurrentYear, this.jalaliCurrentMonth, day, holiday);

      days.push(
        <button
          type="button"
          class={{
            'jalali-day': true,
            'jalali-day-disabled': disabled,
            'jalali-day-in-range': isInRange && !isStart && !isEnd,
            'jalali-day-preview': isPreview && !isStart && !isEnd,
            'jalali-day-start': isStart,
            'jalali-day-end': isEnd,
            'jalali-day-today': isToday,
            'jalali-day-friday': borderClass === 'jalali-day-friday',
            'jalali-day-holiday-official': borderClass === 'jalali-day-holiday-official',
            'jalali-day-event': borderClass === 'jalali-day-event',
          }}
          disabled={disabled}
          onClick={() => !disabled && this.jalaliSelectDay(day)}
          onMouseEnter={(e) => {
            this.jalaliHoverDay = { year: this.jalaliCurrentYear, month: this.jalaliCurrentMonth, day };
            if (holiday) this.showTooltip(e, holiday);
          }}
          onMouseLeave={() => {
            this.jalaliHoverDay = null;
            this.hideTooltip();
          }}
        >
          {this.toPersianNumber(day)}
        </button>
      );
    }

    return days;
  }

  render() {
    return (
      <div class="my-range-datepicker-wrapper">
        {/* Main Input */}
        <input
          ref={(el) => (this.inputElement = el)}
          type="text"
          class="my-range-datepicker-input"
          placeholder={this.placeholder}
          value={this.getDisplayValue()}
          disabled={this.disabled}
          required={this.required}
          readonly
          onClick={() => {
            if (this.calendarMode === 'jalali') {
              if (!this.jalaliCalendarOpen) {
                this.jalaliHoverDay = null;
                this.pendingStartDate = this.internalStartDate;
                this.pendingEndDate = this.internalEndDate;
              }
              this.jalaliCalendarOpen = !this.jalaliCalendarOpen;
            } else if (this.flatpickrInstance) {
              this.pendingStartDate = this.internalStartDate;
              this.pendingEndDate = this.internalEndDate;
              this.flatpickrInstance.open();
            }
          }}
          aria-label={this.placeholder}
          aria-expanded={this.jalaliCalendarOpen.toString()}
          aria-haspopup="dialog"
          aria-invalid={!!this.errorMessage}
          aria-describedby={this.errorMessage ? 'error-message' : undefined}
        />

        {/* Hidden inputs for HTMX */}
        <input
          ref={(el) => (this.hiddenStartInput = el)}
          type="hidden"
          name={`${this.name}_start`}
          value={this.internalStartDate || ''}
        />
        <input
          ref={(el) => (this.hiddenEndInput = el)}
          type="hidden"
          name={`${this.name}_end`}
          value={this.internalEndDate || ''}
        />

        {/* Error Message */}
        {this.errorMessage && (
          <div id="error-message" class="error-message" role="alert" aria-live="polite">
            {this.errorMessage}
          </div>
        )}

        {/* Jalali Calendar */}
        {this.jalaliCalendarOpen && (
          <div class={{ 'jalali-calendar': true, 'jalali-calendar-loading': this.holidaysLoading }}>
            {this.holidaysLoading && (
              <div class="jalali-calendar-loading-overlay" aria-hidden="true">
                <span class="jalali-calendar-spinner" />
              </div>
            )}
            {/* Header: ترتیب برای RTL — Prev راست، Next چپ */}
            <div class="jalali-calendar-header">
              <button type="button" class="jalali-calendar-prev" onClick={this.jalaliPrevMonth} aria-label="ماه قبل">
                <svg viewBox="0 0 24 24" fill="currentColor">
                  <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z" />
                </svg>
              </button>
              <select
                class="jalali-calendar-select jalali-calendar-select-month"
                onChange={(e) => {
                  this.jalaliCurrentMonth = parseInt((e.target as HTMLSelectElement).value, 10);
                }}
                aria-label="ماه"
              >
                {[1,2,3,4,5,6,7,8,9,10,11,12].map(m => (
                  <option value={m} selected={m === this.jalaliCurrentMonth}>{this.getJalaliMonthName(m)}</option>
                ))}
              </select>
              <select
                class="jalali-calendar-select jalali-calendar-select-year"
                onChange={(e) => {
                  const y = parseInt((e.target as HTMLSelectElement).value, 10);
                  this.jalaliCurrentYear = y;
                  this.fetchHolidays(y);
                }}
                aria-label="سال"
              >
                {(() => {
                  const jToday = jalaali.toJalaali(new Date());
                  const from = jToday.jy - 5;
                  const to = jToday.jy + 5;
                  const years: number[] = [];
                  for (let y = from; y <= to; y++) years.push(y);
                  return years.map(y => (
                    <option value={y} selected={y === this.jalaliCurrentYear}>{this.persianNumbers ? this.toPersianNumber(y) : y}</option>
                  ));
                })()}
              </select>
              <button type="button" class="jalali-calendar-next" onClick={this.jalaliNextMonth} aria-label="ماه بعد">
                <svg viewBox="0 0 24 24" fill="currentColor">
                  <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z" />
                </svg>
              </button>
            </div>

            {/* سوئیچ شمسی / میلادی */}
            <div class="jalali-calendar-mode-switch">
              <button
                type="button"
                class="jalali-mode-btn active"
                aria-pressed="true"
              >
                شمسی
              </button>
              <button
                type="button"
                class="jalali-mode-btn"
                aria-pressed="false"
                onClick={() => this.switchToGregorian()}
              >
                میلادی
              </button>
            </div>

            {/* Weekdays */}
            <div class="jalali-calendar-weekdays">
              {this.getJalaliWeekDays().map(day => (
                <div>{day}</div>
              ))}
            </div>

            {/* Days */}
            <div class="jalali-calendar-days">
              {this.renderJalaliCalendar()}
            </div>

            {/* Buttons */}
            <div class="jalali-calendar-footer">
              <button type="button" class="jalali-today-btn" onClick={this.jalaliSelectToday}>
                امروز
              </button>
              <button type="button" class="jalali-clear-btn" onClick={this.jalaliClear}>
                پاک کردن
              </button>
              <button type="button" class="jalali-confirm-btn" onClick={this.jalaliConfirm} aria-label="تأیید">
                ✓ تأیید
              </button>
            </div>
          </div>
        )}

        {/* Tooltip تعطیلات / مناسبت (position: fixed تا بریده نشود) */}
        {this.tooltipData && (
          <div
            class="holiday-tooltip"
            style={{
              left: `${this.tooltipData.x}px`,
              top: `${this.tooltipData.y}px`,
              transform: 'translate(-50%, -100%)',
            }}
            role="tooltip"
          >
            <span class="holiday-tooltip-label">
              {this.tooltipData.isHoliday ? 'تعطیلی رسمی' : 'مناسبت'}
            </span>
            <p class="holiday-tooltip-text">{this.tooltipData.content}</p>
          </div>
        )}
      </div>
    );
  }
}
