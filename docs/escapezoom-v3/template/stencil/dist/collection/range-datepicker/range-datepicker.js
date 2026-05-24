import { h } from "@stencil/core";
import flatpickr from "flatpickr";
import * as jalaali from "jalaali-js";
const HOLIDAYS_BASE_URL = 'https://raw.githubusercontent.com/hasan-ahani/shamsi-holidays/main/holidays';
/** تاریخ را در ظهر (۱۲:۰۰) برمی‌گرداند تا با تغییر timezone یک روز جابجا نشود */
function dateAtNoon(isoDate) {
    return new Date(isoDate + 'T12:00:00');
}
/** تاریخ محلی را به رشته ISO (YYYY-MM-DD) تبدیل می‌کند تا بر اساس timezone جابجا نشود */
function dateToISO(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}
export class EzRangeDatepicker {
    el;
    // Props
    startDate;
    endDate;
    themeColor = '#3b82f6';
    placeholder = 'انتخاب بازه تاریخ';
    minDate;
    maxDate;
    disabledDates;
    disabled = false;
    required = false;
    name = 'dateRange';
    persianNumbers = false;
    /** انتخاب بازه (true) یا تک روز (false). از HTML: allow-range یا allow-range="false" */
    allowRange = true;
    // Events
    dateChanged;
    dateCleared;
    calendarOpened;
    calendarClosed;
    // State — committed (input + form + emit only on Confirm)
    internalStartDate = null;
    internalEndDate = null;
    errorMessage = '';
    calendarMode = 'jalali';
    jalaliCurrentYear = 1404;
    jalaliCurrentMonth = 1;
    jalaliCalendarOpen = false;
    tooltipData = null;
    jalaliHoverDay = null;
    holidaysLoading = false;
    holidaysVersion = 0;
    /** انتخاب فعلی در تقویم (قبل از تأیید). فقط با دکمه تأیید به internal و form منتقل می‌شود */
    pendingStartDate = null;
    pendingEndDate = null;
    get isRangeMode() {
        return this.allowRange !== false && this.allowRange !== 'false';
    }
    // Private
    flatpickrInstance;
    inputElement;
    hiddenStartInput;
    hiddenEndInput;
    /** Lookup: date key "1404-01-01" -> HolidayData (for current view) */
    allHolidays = new Map();
    /** Cache: year -> HolidayData[] to avoid refetch when switching months */
    holidayCache = new Map();
    holidayFetchAbort = null;
    // Watchers
    handlePropChange() {
        this.validateAndSetDates();
    }
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
    async fetchHolidays(year) {
        if (this.holidayCache.has(year)) {
            this.mergeHolidaysIntoMap(this.holidayCache.get(year));
            return;
        }
        if (this.holidaysLoading)
            return;
        this.holidaysLoading = true;
        if (this.holidayFetchAbort)
            this.holidayFetchAbort.abort();
        const controller = new AbortController();
        this.holidayFetchAbort = controller;
        const url = `${HOLIDAYS_BASE_URL}/${year}.json`;
        try {
            const res = await fetch(url, { signal: controller.signal });
            if (!res.ok) {
                if (res.status === 404) {
                    console.warn(`[my-range-datepicker] No holidays data for year ${year} (404).`);
                }
                else {
                    console.warn(`[my-range-datepicker] Holidays fetch failed for ${year}: ${res.status}`);
                }
                return;
            }
            const data = (await res.json());
            if (Array.isArray(data)) {
                this.holidayCache.set(year, data);
                this.mergeHolidaysIntoMap(data);
            }
        }
        catch (e) {
            if (e.name !== 'AbortError') {
                console.warn('[my-range-datepicker] Holidays fetch error:', e.message);
            }
        }
        finally {
            if (this.holidayFetchAbort === controller) {
                this.holidayFetchAbort = null;
            }
            this.holidaysLoading = false;
        }
    }
    mergeHolidaysIntoMap(list) {
        list.forEach(day => this.allHolidays.set(day.date, day));
        this.holidaysVersion += 1;
    }
    validateAndSetDates() {
        // Validate and set start date
        if (this.startDate && this.isValidISODate(this.startDate)) {
            this.internalStartDate = this.startDate;
        }
        else if (this.startDate) {
            console.warn(`Invalid start date: ${this.startDate}`);
        }
        // Validate and set end date
        if (this.endDate && this.isValidISODate(this.endDate)) {
            this.internalEndDate = this.endDate;
        }
        else if (this.endDate) {
            console.warn(`Invalid end date: ${this.endDate}`);
        }
        if (!this.isRangeMode && this.internalStartDate) {
            this.internalEndDate = this.internalStartDate;
        }
        if (this.isRangeMode && this.internalStartDate && this.internalEndDate) {
            if (!this.isValidRange(this.internalStartDate, this.internalEndDate)) {
                this.errorMessage = 'تاریخ پایان نمی‌تواند قبل از تاریخ شروع باشد';
            }
            else {
                this.errorMessage = '';
            }
        }
        this.updateHiddenInputs();
        // Emit change event
        this.emitDateChange();
    }
    isValidISODate(dateStr) {
        const date = new Date(dateStr);
        return date instanceof Date && !isNaN(date.getTime());
    }
    isValidRange(start, end) {
        return dateAtNoon(start) <= dateAtNoon(end);
    }
    initializeFlatpickr() {
        if (!this.inputElement)
            return;
        const defaultDates = [];
        if (this.isRangeMode && this.internalStartDate && this.internalEndDate) {
            defaultDates.push(dateAtNoon(this.internalStartDate));
            defaultDates.push(dateAtNoon(this.internalEndDate));
        }
        else if (this.internalStartDate) {
            defaultDates.push(dateAtNoon(this.internalStartDate));
        }
        else if (!this.isRangeMode) {
            defaultDates.length = 0;
        }
        else {
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
                }
                else if (selectedDates.length === 1) {
                    const iso = dateToISO(selectedDates[0]);
                    this.pendingStartDate = iso;
                    this.pendingEndDate = this.isRangeMode ? null : iso;
                    this.errorMessage = '';
                }
            },
        });
    }
    fixRTLPositioning(instance) {
        const calendar = instance.calendarContainer;
        const input = this.inputElement;
        if (!calendar)
            return;
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
            }
            else if (input && this.el.getBoundingClientRect) {
                const hostRect = this.el.getBoundingClientRect();
                const inputRect = input.getBoundingClientRect();
                calendar.style.position = 'absolute';
                calendar.style.top = `${inputRect.bottom - hostRect.top + 4}px`;
                calendar.style.right = `${hostRect.right - inputRect.right}px`;
                calendar.style.left = 'auto';
            }
        }, 0);
    }
    addCustomButtons(instance) {
        const calendar = instance.calendarContainer;
        if (!calendar)
            return;
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
            }
            else {
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
    syncFromFlatpickr(instance) {
        const dates = instance.selectedDates;
        if (this.isRangeMode && dates.length === 2) {
            this.internalStartDate = dateToISO(dates[0]);
            this.internalEndDate = dateToISO(dates[1]);
        }
        else if (dates.length === 1) {
            const iso = dateToISO(dates[0]);
            this.internalStartDate = iso;
            this.internalEndDate = this.isRangeMode ? null : iso;
        }
        this.errorMessage = '';
        this.updateHiddenInputs();
        this.emitDateChange();
    }
    /** بعد از انتخاب در تقویم شمسی، تقویم میلادی را با pending همگام می‌کند */
    syncFlatpickrFromPending() {
        if (!this.flatpickrInstance)
            return;
        const dates = [];
        if (this.pendingStartDate)
            dates.push(dateAtNoon(this.pendingStartDate));
        if (this.isRangeMode && this.pendingEndDate && this.pendingEndDate !== this.pendingStartDate) {
            dates.push(dateAtNoon(this.pendingEndDate));
        }
        this.flatpickrInstance.setDate(dates, false);
    }
    switchToJalali() {
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
    switchToGregorian() {
        this.calendarMode = 'gregorian';
        this.jalaliCalendarOpen = false;
        if (this.flatpickrInstance) {
            this.flatpickrInstance.open();
        }
    }
    clearDates() {
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
    updateHiddenInputs() {
        if (this.hiddenStartInput) {
            this.hiddenStartInput.value = this.internalStartDate || '';
        }
        if (this.hiddenEndInput) {
            this.hiddenEndInput.value = this.internalEndDate || '';
        }
    }
    emitDateChange() {
        const detail = {
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
    toPersianDateString(date) {
        const j = jalaali.toJalaali(date);
        const year = this.persianNumbers ? this.toPersianNumber(j.jy) : j.jy;
        const month = this.persianNumbers ? this.toPersianNumber(j.jm) : String(j.jm).padStart(2, '0');
        const day = this.persianNumbers ? this.toPersianNumber(j.jd) : String(j.jd).padStart(2, '0');
        return `${year}/${month}/${day}`;
    }
    toPersianNumber(num) {
        const persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return String(num).replace(/\d/g, (d) => persianDigits[parseInt(d)]);
    }
    getDisplayValue() {
        if (!this.internalStartDate)
            return '';
        const start = this.toPersianDateString(dateAtNoon(this.internalStartDate));
        if (!this.isRangeMode || !this.internalEndDate || this.internalEndDate === this.internalStartDate) {
            return start;
        }
        const end = this.toPersianDateString(dateAtNoon(this.internalEndDate));
        return `${start} - ${end}`;
    }
    // Jalali Calendar Methods
    getJalaliMonthName(month) {
        const months = [
            'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
            'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
        ];
        return months[month - 1] || '';
    }
    getJalaliWeekDays() {
        return ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'];
    }
    getJalaliDaysInMonth(year, month) {
        return jalaali.jalaaliMonthLength(year, month);
    }
    getJalaliFirstDayOfMonth(year, month) {
        const gDate = jalaali.toGregorian(year, month, 1);
        const date = new Date(gDate.gy, gDate.gm - 1, gDate.gd);
        return (date.getDay() + 1) % 7; // Convert to Persian week (Saturday = 0)
    }
    jalaliPrevMonth = () => {
        if (this.jalaliCurrentMonth === 1) {
            this.jalaliCurrentMonth = 12;
            this.jalaliCurrentYear--;
            this.fetchHolidays(this.jalaliCurrentYear);
        }
        else {
            this.jalaliCurrentMonth--;
        }
    };
    jalaliNextMonth = () => {
        if (this.jalaliCurrentMonth === 12) {
            this.jalaliCurrentMonth = 1;
            this.jalaliCurrentYear++;
            this.fetchHolidays(this.jalaliCurrentYear);
        }
        else {
            this.jalaliCurrentMonth++;
        }
    };
    jalaliSelectDay = (day) => {
        const gDate = jalaali.toGregorian(this.jalaliCurrentYear, this.jalaliCurrentMonth, day);
        const selectedDate = new Date(gDate.gy, gDate.gm - 1, gDate.gd);
        const isoDate = dateToISO(selectedDate);
        if (!this.isRangeMode) {
            this.pendingStartDate = isoDate;
            this.pendingEndDate = isoDate;
            this.jalaliHoverDay = null;
        }
        else if (!this.pendingStartDate || (this.pendingStartDate && this.pendingEndDate)) {
            this.pendingStartDate = isoDate;
            this.pendingEndDate = null;
        }
        else {
            if (dateAtNoon(isoDate) < dateAtNoon(this.pendingStartDate)) {
                this.pendingEndDate = this.pendingStartDate;
                this.pendingStartDate = isoDate;
            }
            else {
                this.pendingEndDate = isoDate;
            }
            this.jalaliHoverDay = null;
        }
        this.errorMessage = '';
        this.syncFlatpickrFromPending();
    };
    jalaliSelectToday = () => {
        const today = new Date();
        const todayIso = dateToISO(today);
        const jToday = jalaali.toJalaali(today);
        this.jalaliCurrentYear = jToday.jy;
        this.jalaliCurrentMonth = jToday.jm;
        if (this.isRangeMode && this.pendingStartDate && !this.pendingEndDate) {
            this.pendingEndDate = todayIso;
        }
        else if (!this.pendingStartDate) {
            this.pendingStartDate = todayIso;
            this.pendingEndDate = this.isRangeMode ? null : todayIso;
        }
        else {
            this.pendingStartDate = todayIso;
            this.pendingEndDate = this.isRangeMode ? null : todayIso;
        }
        this.jalaliHoverDay = null;
        this.errorMessage = '';
        this.syncFlatpickrFromPending();
    };
    jalaliClear = () => {
        this.clearDates();
        this.jalaliHoverDay = null;
    };
    /** بستن تقویم شمسی بعد از تأیید: ثبت pending در internal و emit */
    jalaliConfirm = () => {
        this.internalStartDate = this.pendingStartDate;
        this.internalEndDate = this.pendingEndDate;
        this.updateHiddenInputs();
        this.emitDateChange();
        this.jalaliHoverDay = null;
        this.jalaliCalendarOpen = false;
    };
    isJalaliDayInRange(year, month, day) {
        if (!this.pendingStartDate || !this.pendingEndDate)
            return false;
        const gDate = jalaali.toGregorian(year, month, day);
        const dayDate = new Date(gDate.gy, gDate.gm - 1, gDate.gd);
        const start = dateAtNoon(this.pendingStartDate);
        const end = dateAtNoon(this.pendingEndDate);
        return dayDate >= start && dayDate <= end;
    }
    /** روز در بازهٔ پیش‌نمایش (هاور) بین start و روز زیر موس */
    isJalaliDayInPreviewRange(year, month, day) {
        if (!this.pendingStartDate || this.pendingEndDate || !this.jalaliHoverDay)
            return false;
        const gDate = jalaali.toGregorian(year, month, day);
        const dayDate = new Date(gDate.gy, gDate.gm - 1, gDate.gd);
        const start = dateAtNoon(this.pendingStartDate);
        const h = jalaali.toGregorian(this.jalaliHoverDay.year, this.jalaliHoverDay.month, this.jalaliHoverDay.day);
        const hoverDate = new Date(h.gy, h.gm - 1, h.gd);
        const [from, to] = start <= hoverDate ? [start, hoverDate] : [hoverDate, start];
        return dayDate >= from && dayDate <= to;
    }
    isJalaliDayStart(year, month, day) {
        if (!this.pendingStartDate)
            return false;
        const gDate = jalaali.toGregorian(year, month, day);
        const dayDate = new Date(gDate.gy, gDate.gm - 1, gDate.gd);
        const start = dateAtNoon(this.pendingStartDate);
        return dayDate.toDateString() === start.toDateString();
    }
    isJalaliDayEnd(year, month, day) {
        if (!this.pendingEndDate)
            return false;
        const gDate = jalaali.toGregorian(year, month, day);
        const dayDate = new Date(gDate.gy, gDate.gm - 1, gDate.gd);
        const end = dateAtNoon(this.pendingEndDate);
        return dayDate.toDateString() === end.toDateString();
    }
    isJalaliDayToday(year, month, day) {
        const gDate = jalaali.toGregorian(year, month, day);
        const dayDate = new Date(gDate.gy, gDate.gm - 1, gDate.gd);
        const today = new Date();
        return dayDate.toDateString() === today.toDateString();
    }
    getHolidayForJalaliDay(year, month, day) {
        const dateKey = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        return this.allHolidays.get(dateKey) || null;
    }
    /** جمعه در تقویم میلادی: getDay() === 5 */
    isJalaliDayFriday(year, month, day) {
        const g = jalaali.toGregorian(year, month, day);
        const d = new Date(g.gy, g.gm - 1, g.gd);
        return d.getDay() === 5;
    }
    /** کلاس‌های border برای روز: جمعه / تعطیل رسمی (غیرجمعه) / فقط مناسبت */
    getJalaliDayBorderClass(year, month, day, holiday) {
        if (!holiday?.events?.length)
            return null;
        const isFriday = this.isJalaliDayFriday(year, month, day);
        if (isFriday)
            return 'jalali-day-friday';
        if (holiday.is_holiday)
            return 'jalali-day-holiday-official';
        return 'jalali-day-event';
    }
    /** روز خارج از minDate/maxDate غیرفعال است */
    isJalaliDayDisabled(year, month, day) {
        const gDate = jalaali.toGregorian(year, month, day);
        const d = new Date(gDate.gy, gDate.gm - 1, gDate.gd);
        if (this.minDate && d < new Date(this.minDate))
            return true;
        if (this.maxDate && d > new Date(this.maxDate))
            return true;
        return false;
    }
    showTooltip = (event, holiday) => {
        const rect = event.target.getBoundingClientRect();
        const content = holiday.events.map(e => e.description).join(' • ');
        this.tooltipData = {
            x: rect.left + rect.width / 2,
            y: rect.top - 8,
            content,
            isHoliday: holiday.is_holiday,
        };
    };
    hideTooltip = () => {
        this.tooltipData = null;
    };
    renderJalaliCalendar() {
        const daysInMonth = this.getJalaliDaysInMonth(this.jalaliCurrentYear, this.jalaliCurrentMonth);
        const firstDay = this.getJalaliFirstDayOfMonth(this.jalaliCurrentYear, this.jalaliCurrentMonth);
        const days = [];
        // Empty cells before first day
        for (let i = 0; i < firstDay; i++) {
            days.push(h("div", { class: "jalali-day-empty" }));
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
            days.push(h("button", { type: "button", class: {
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
                }, disabled: disabled, onClick: () => !disabled && this.jalaliSelectDay(day), onMouseEnter: (e) => {
                    this.jalaliHoverDay = { year: this.jalaliCurrentYear, month: this.jalaliCurrentMonth, day };
                    if (holiday)
                        this.showTooltip(e, holiday);
                }, onMouseLeave: () => {
                    this.jalaliHoverDay = null;
                    this.hideTooltip();
                } }, this.toPersianNumber(day)));
        }
        return days;
    }
    render() {
        return (h("div", { key: 'b558a8967fbedb5de7cb5e46d6cf8fd9d2ed54d9', class: "my-range-datepicker-wrapper" }, h("input", { key: '9c99a6a7afae04bd1a8660597656973d37cae6cc', ref: (el) => (this.inputElement = el), type: "text", class: "my-range-datepicker-input", placeholder: this.placeholder, value: this.getDisplayValue(), disabled: this.disabled, required: this.required, readonly: true, onClick: () => {
                if (this.calendarMode === 'jalali') {
                    if (!this.jalaliCalendarOpen) {
                        this.jalaliHoverDay = null;
                        this.pendingStartDate = this.internalStartDate;
                        this.pendingEndDate = this.internalEndDate;
                    }
                    this.jalaliCalendarOpen = !this.jalaliCalendarOpen;
                }
                else if (this.flatpickrInstance) {
                    this.pendingStartDate = this.internalStartDate;
                    this.pendingEndDate = this.internalEndDate;
                    this.flatpickrInstance.open();
                }
            }, "aria-label": this.placeholder, "aria-expanded": this.jalaliCalendarOpen.toString(), "aria-haspopup": "dialog", "aria-invalid": !!this.errorMessage, "aria-describedby": this.errorMessage ? 'error-message' : undefined }), h("input", { key: 'adcea022416bc4518edc1c61056d036dff95a378', ref: (el) => (this.hiddenStartInput = el), type: "hidden", name: `${this.name}_start`, value: this.internalStartDate || '' }), h("input", { key: '05183e2d5f22d776ce3ea0afa2dcd00e189273c2', ref: (el) => (this.hiddenEndInput = el), type: "hidden", name: `${this.name}_end`, value: this.internalEndDate || '' }), this.errorMessage && (h("div", { key: '45911213353ca0aee348777558f50797e5df2b4b', id: "error-message", class: "error-message", role: "alert", "aria-live": "polite" }, this.errorMessage)), this.jalaliCalendarOpen && (h("div", { key: 'e9f177109f6ea684e1f287ce2e965997ea770cef', class: { 'jalali-calendar': true, 'jalali-calendar-loading': this.holidaysLoading } }, this.holidaysLoading && (h("div", { key: 'da7d7fd7e193aaa5dffafa938e738182cd170ff2', class: "jalali-calendar-loading-overlay", "aria-hidden": "true" }, h("span", { key: '562c3f555f11a4de1449f48ba062a3b7437389fb', class: "jalali-calendar-spinner" }))), h("div", { key: 'c7897a59c2c5f2c8e6e6c908c503973ff26196d5', class: "jalali-calendar-header" }, h("button", { key: '3be21ece57afaec8ab578655fab0887e09ccd57b', type: "button", class: "jalali-calendar-prev", onClick: this.jalaliPrevMonth, "aria-label": "\u0645\u0627\u0647 \u0642\u0628\u0644" }, h("svg", { key: '294d2335120de372b26a1ef4e6f2d0e00a6b03f3', viewBox: "0 0 24 24", fill: "currentColor" }, h("path", { key: '0600cd474d5d97632feee247e961bea768f5f9af', d: "M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z" }))), h("select", { key: '0a149318bb8ae685399d338e80f1df94dd1b4889', class: "jalali-calendar-select jalali-calendar-select-month", onChange: (e) => {
                this.jalaliCurrentMonth = parseInt(e.target.value, 10);
            }, "aria-label": "\u0645\u0627\u0647" }, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12].map(m => (h("option", { value: m, selected: m === this.jalaliCurrentMonth }, this.getJalaliMonthName(m))))), h("select", { key: '22add8d359ad197396b9f022bc833d866c2134d9', class: "jalali-calendar-select jalali-calendar-select-year", onChange: (e) => {
                const y = parseInt(e.target.value, 10);
                this.jalaliCurrentYear = y;
                this.fetchHolidays(y);
            }, "aria-label": "\u0633\u0627\u0644" }, (() => {
            const jToday = jalaali.toJalaali(new Date());
            const from = jToday.jy - 5;
            const to = jToday.jy + 5;
            const years = [];
            for (let y = from; y <= to; y++)
                years.push(y);
            return years.map(y => (h("option", { value: y, selected: y === this.jalaliCurrentYear }, this.persianNumbers ? this.toPersianNumber(y) : y)));
        })()), h("button", { key: '80bdeec9ee203d3f669c1f5beeb45f06686fad85', type: "button", class: "jalali-calendar-next", onClick: this.jalaliNextMonth, "aria-label": "\u0645\u0627\u0647 \u0628\u0639\u062F" }, h("svg", { key: '45bce1e70311db82d38c578c60ada4bda1dddf6b', viewBox: "0 0 24 24", fill: "currentColor" }, h("path", { key: 'd9cfabd41b7b854493399a1aeea1ad5b241916f8', d: "M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z" })))), h("div", { key: '9b4df6a86f93a0d2bb9dc0c327884e3f2d0522bf', class: "jalali-calendar-mode-switch" }, h("button", { key: 'ebdefc220eae18517a0d17ce87476774bae998fd', type: "button", class: "jalali-mode-btn active", "aria-pressed": "true" }, "\u0634\u0645\u0633\u06CC"), h("button", { key: '4f458f8d0ece5968e352ca1c1a1bfb5912920ef3', type: "button", class: "jalali-mode-btn", "aria-pressed": "false", onClick: () => this.switchToGregorian() }, "\u0645\u06CC\u0644\u0627\u062F\u06CC")), h("div", { key: 'b9d951f256bb4e9d95f30029c6437fd6df9b31d1', class: "jalali-calendar-weekdays" }, this.getJalaliWeekDays().map(day => (h("div", null, day)))), h("div", { key: '4e62e620a51700c4f1232bbc99ef70805c6ef44e', class: "jalali-calendar-days" }, this.renderJalaliCalendar()), h("div", { key: 'f393c0d3a7a58ae9d6acf7f0d8d7969a58e9df9c', class: "jalali-calendar-footer" }, h("button", { key: '6b745f7317490b9eb6790bfbe170be86587b1a6f', type: "button", class: "jalali-today-btn", onClick: this.jalaliSelectToday }, "\u0627\u0645\u0631\u0648\u0632"), h("button", { key: 'd42dbe31c770b81d91957f772e4347f97b276d8a', type: "button", class: "jalali-clear-btn", onClick: this.jalaliClear }, "\u067E\u0627\u06A9 \u06A9\u0631\u062F\u0646"), h("button", { key: '0de13584cb9307fdac8f2101dd6fc85e3dd3c180', type: "button", class: "jalali-confirm-btn", onClick: this.jalaliConfirm, "aria-label": "\u062A\u0623\u06CC\u06CC\u062F" }, "\u2713 \u062A\u0623\u06CC\u06CC\u062F")))), this.tooltipData && (h("div", { key: '68d12fc08172cb746ed64e94a39fdb1be2f8a49b', class: "holiday-tooltip", style: {
                left: `${this.tooltipData.x}px`,
                top: `${this.tooltipData.y}px`,
                transform: 'translate(-50%, -100%)',
            }, role: "tooltip" }, h("span", { key: '8b0e84f3018118abc8a57f803297d6c20ff07ff3', class: "holiday-tooltip-label" }, this.tooltipData.isHoliday ? 'تعطیلی رسمی' : 'مناسبت'), h("p", { key: 'dcf2a4645248152edbeffcd464e03ea4f82cd51e', class: "holiday-tooltip-text" }, this.tooltipData.content)))));
    }
    static get is() { return "ez-range-datepicker"; }
    static get originalStyleUrls() {
        return {
            "$": ["range-datepicker.css"]
        };
    }
    static get styleUrls() {
        return {
            "$": ["range-datepicker.css"]
        };
    }
    static get properties() {
        return {
            "startDate": {
                "type": "string",
                "mutable": false,
                "complexType": {
                    "original": "string",
                    "resolved": "string",
                    "references": {}
                },
                "required": false,
                "optional": true,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "start-date"
            },
            "endDate": {
                "type": "string",
                "mutable": false,
                "complexType": {
                    "original": "string",
                    "resolved": "string",
                    "references": {}
                },
                "required": false,
                "optional": true,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "end-date"
            },
            "themeColor": {
                "type": "string",
                "mutable": false,
                "complexType": {
                    "original": "string",
                    "resolved": "string",
                    "references": {}
                },
                "required": false,
                "optional": false,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "theme-color",
                "defaultValue": "'#3b82f6'"
            },
            "placeholder": {
                "type": "string",
                "mutable": false,
                "complexType": {
                    "original": "string",
                    "resolved": "string",
                    "references": {}
                },
                "required": false,
                "optional": false,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "placeholder",
                "defaultValue": "'\u0627\u0646\u062A\u062E\u0627\u0628 \u0628\u0627\u0632\u0647 \u062A\u0627\u0631\u06CC\u062E'"
            },
            "minDate": {
                "type": "string",
                "mutable": false,
                "complexType": {
                    "original": "string",
                    "resolved": "string",
                    "references": {}
                },
                "required": false,
                "optional": true,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "min-date"
            },
            "maxDate": {
                "type": "string",
                "mutable": false,
                "complexType": {
                    "original": "string",
                    "resolved": "string",
                    "references": {}
                },
                "required": false,
                "optional": true,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "max-date"
            },
            "disabledDates": {
                "type": "unknown",
                "mutable": false,
                "complexType": {
                    "original": "string[]",
                    "resolved": "string[]",
                    "references": {}
                },
                "required": false,
                "optional": true,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "getter": false,
                "setter": false
            },
            "disabled": {
                "type": "boolean",
                "mutable": false,
                "complexType": {
                    "original": "boolean",
                    "resolved": "boolean",
                    "references": {}
                },
                "required": false,
                "optional": false,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "disabled",
                "defaultValue": "false"
            },
            "required": {
                "type": "boolean",
                "mutable": false,
                "complexType": {
                    "original": "boolean",
                    "resolved": "boolean",
                    "references": {}
                },
                "required": false,
                "optional": false,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "required",
                "defaultValue": "false"
            },
            "name": {
                "type": "string",
                "mutable": false,
                "complexType": {
                    "original": "string",
                    "resolved": "string",
                    "references": {}
                },
                "required": false,
                "optional": false,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "name",
                "defaultValue": "'dateRange'"
            },
            "persianNumbers": {
                "type": "boolean",
                "mutable": false,
                "complexType": {
                    "original": "boolean",
                    "resolved": "boolean",
                    "references": {}
                },
                "required": false,
                "optional": false,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "persian-numbers",
                "defaultValue": "false"
            },
            "allowRange": {
                "type": "any",
                "mutable": false,
                "complexType": {
                    "original": "string | boolean",
                    "resolved": "boolean | string",
                    "references": {}
                },
                "required": false,
                "optional": false,
                "docs": {
                    "tags": [],
                    "text": "\u0627\u0646\u062A\u062E\u0627\u0628 \u0628\u0627\u0632\u0647 (true) \u06CC\u0627 \u062A\u06A9 \u0631\u0648\u0632 (false). \u0627\u0632 HTML: allow-range \u06CC\u0627 allow-range=\"false\""
                },
                "getter": false,
                "setter": false,
                "reflect": false,
                "attribute": "allow-range",
                "defaultValue": "true"
            }
        };
    }
    static get states() {
        return {
            "internalStartDate": {},
            "internalEndDate": {},
            "errorMessage": {},
            "calendarMode": {},
            "jalaliCurrentYear": {},
            "jalaliCurrentMonth": {},
            "jalaliCalendarOpen": {},
            "tooltipData": {},
            "jalaliHoverDay": {},
            "holidaysLoading": {},
            "holidaysVersion": {},
            "pendingStartDate": {},
            "pendingEndDate": {}
        };
    }
    static get events() {
        return [{
                "method": "dateChanged",
                "name": "dateChanged",
                "bubbles": true,
                "cancelable": true,
                "composed": true,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "complexType": {
                    "original": "DateChangeDetail",
                    "resolved": "DateChangeDetail",
                    "references": {
                        "DateChangeDetail": {
                            "location": "local",
                            "path": "C:/Users/jobal/docker/escapezoom_wp/wp-content/themes/escapezoom-v2/template/stencil/components/range-datepicker/range-datepicker.tsx",
                            "id": "components/range-datepicker/range-datepicker.tsx::DateChangeDetail"
                        }
                    }
                }
            }, {
                "method": "dateCleared",
                "name": "dateCleared",
                "bubbles": true,
                "cancelable": true,
                "composed": true,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "complexType": {
                    "original": "void",
                    "resolved": "void",
                    "references": {}
                }
            }, {
                "method": "calendarOpened",
                "name": "calendarOpened",
                "bubbles": true,
                "cancelable": true,
                "composed": true,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "complexType": {
                    "original": "void",
                    "resolved": "void",
                    "references": {}
                }
            }, {
                "method": "calendarClosed",
                "name": "calendarClosed",
                "bubbles": true,
                "cancelable": true,
                "composed": true,
                "docs": {
                    "tags": [],
                    "text": ""
                },
                "complexType": {
                    "original": "void",
                    "resolved": "void",
                    "references": {}
                }
            }];
    }
    static get elementRef() { return "el"; }
    static get watchers() {
        return [{
                "propName": "startDate",
                "methodName": "handlePropChange"
            }, {
                "propName": "endDate",
                "methodName": "handlePropChange"
            }, {
                "propName": "themeColor",
                "methodName": "handleThemeColorChange"
            }];
    }
}
