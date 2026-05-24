import { EventEmitter } from '../stencil-public-runtime';
/** Response shape from shamsi-holidays API */
export interface HolidayEvent {
    description: string;
    is_holiday: boolean;
}
export interface HolidayData {
    date: string;
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
export declare class EzRangeDatepicker {
    el: HTMLElement;
    startDate?: string;
    endDate?: string;
    themeColor: string;
    placeholder: string;
    minDate?: string;
    maxDate?: string;
    disabledDates?: string[];
    disabled: boolean;
    required: boolean;
    name: string;
    persianNumbers: boolean;
    /** انتخاب بازه (true) یا تک روز (false). از HTML: allow-range یا allow-range="false" */
    allowRange: string | boolean;
    dateChanged: EventEmitter<DateChangeDetail>;
    dateCleared: EventEmitter<void>;
    calendarOpened: EventEmitter<void>;
    calendarClosed: EventEmitter<void>;
    internalStartDate: string | null;
    internalEndDate: string | null;
    errorMessage: string;
    calendarMode: 'gregorian' | 'jalali';
    jalaliCurrentYear: number;
    jalaliCurrentMonth: number;
    jalaliCalendarOpen: boolean;
    tooltipData: {
        x: number;
        y: number;
        content: string;
        isHoliday?: boolean;
    } | null;
    jalaliHoverDay: {
        year: number;
        month: number;
        day: number;
    } | null;
    holidaysLoading: boolean;
    holidaysVersion: number;
    /** انتخاب فعلی در تقویم (قبل از تأیید). فقط با دکمه تأیید به internal و form منتقل می‌شود */
    pendingStartDate: string | null;
    pendingEndDate: string | null;
    private get isRangeMode();
    private flatpickrInstance?;
    private inputElement?;
    private hiddenStartInput?;
    private hiddenEndInput?;
    /** Lookup: date key "1404-01-01" -> HolidayData (for current view) */
    private allHolidays;
    /** Cache: year -> HolidayData[] to avoid refetch when switching months */
    private holidayCache;
    private holidayFetchAbort;
    handlePropChange(): void;
    handleThemeColorChange(): void;
    componentWillLoad(): void;
    componentDidLoad(): void;
    disconnectedCallback(): void;
    /**
     * بارگذاری تعطیلات از API برای سال شمسی. با کش از درخواست تکراری جلوگیری می‌شود.
     * در صورت ۴۰۴ یا خطای شبکه تقویم بدون تعطیلات کار می‌کند (بدون کرش).
     */
    private fetchHolidays;
    private mergeHolidaysIntoMap;
    private validateAndSetDates;
    private isValidISODate;
    private isValidRange;
    private initializeFlatpickr;
    private fixRTLPositioning;
    private addCustomButtons;
    /** فقط هنگام کلیک تأیید در تقویم میلادی: مقدار نهایی را به internal منتقل و emit می‌کند */
    private syncFromFlatpickr;
    /** بعد از انتخاب در تقویم شمسی، تقویم میلادی را با pending همگام می‌کند */
    private syncFlatpickrFromPending;
    private switchToJalali;
    private switchToGregorian;
    private clearDates;
    private updateHiddenInputs;
    private emitDateChange;
    private toPersianDateString;
    private toPersianNumber;
    private getDisplayValue;
    private getJalaliMonthName;
    private getJalaliWeekDays;
    private getJalaliDaysInMonth;
    private getJalaliFirstDayOfMonth;
    private jalaliPrevMonth;
    private jalaliNextMonth;
    private jalaliSelectDay;
    private jalaliSelectToday;
    private jalaliClear;
    /** بستن تقویم شمسی بعد از تأیید: ثبت pending در internal و emit */
    private jalaliConfirm;
    private isJalaliDayInRange;
    /** روز در بازهٔ پیش‌نمایش (هاور) بین start و روز زیر موس */
    private isJalaliDayInPreviewRange;
    private isJalaliDayStart;
    private isJalaliDayEnd;
    private isJalaliDayToday;
    private getHolidayForJalaliDay;
    /** جمعه در تقویم میلادی: getDay() === 5 */
    private isJalaliDayFriday;
    /** کلاس‌های border برای روز: جمعه / تعطیل رسمی (غیرجمعه) / فقط مناسبت */
    private getJalaliDayBorderClass;
    /** روز خارج از minDate/maxDate غیرفعال است */
    private isJalaliDayDisabled;
    private showTooltip;
    private hideTooltip;
    private renderJalaliCalendar;
    render(): any;
}
