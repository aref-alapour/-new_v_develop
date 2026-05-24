/**
 * Persian Calendar Module
 * 
 * This module provides Persian calendar functionality with date range selection.
 * It can be used in any page that needs calendar functionality.
 */
class PersianCalendar {
    constructor(options = {}) {
        this.currentCalendarDate = new Date();
        this.selectedStartDate = null;
        this.selectedEndDate = null;
        this.renderCalendarTimeout = null;
        this.onDateRangeSelected = options.onDateRangeSelected || null;
        this.onDateRangeCleared = options.onDateRangeCleared || null;
        
        this.init();
    }
    init() {
        this.bindEvents();
    }
    bindEvents() {
        // Calendar button click handler
        const calendarBtn = document.getElementById('calendar-btn');
        if (calendarBtn) {
            calendarBtn.addEventListener('click', () => this.openCalendarModal());
        }
        // Calendar modal event listeners - prevent duplicate listeners
        if (!window.calendarListenersAdded) {
            const closeBtn = document.getElementById('close-calendar');
            const prevBtn = document.getElementById('prev-month');
            const nextBtn = document.getElementById('next-month');
            const clearBtn = document.getElementById('clear-selection');
            const applyBtn = document.getElementById('apply-date-range');
            if (closeBtn) closeBtn.addEventListener('click', () => this.closeCalendarModal());
            if (prevBtn) prevBtn.addEventListener('click', () => this.previousMonth());
            if (nextBtn) nextBtn.addEventListener('click', () => this.nextMonth());
            if (clearBtn) clearBtn.addEventListener('click', () => this.clearSelection());
            if (applyBtn) applyBtn.addEventListener('click', () => this.applyDateRange());
            // Mark listeners as added
            window.calendarListenersAdded = true;
        }
    }
    openCalendarModal() {
        const modal = document.getElementById('calendar-modal');
        if (modal) {
            modal.classList.remove('hidden');
            this.debouncedRenderCalendar();
        }
    }
    closeCalendarModal() {
        const modal = document.getElementById('calendar-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }
    
    // --- اصلاح شده: تغییر ماه بر اساس تقویم شمسی ---
    previousMonth() {
        // تاریخ فعلی را به شمسی تبدیل کن
        const persianDate = this.gregorianToPersianAccurate(this.currentCalendarDate);
        
        let pYear = persianDate.year;
        let pMonth = persianDate.month - 1; // یک ماه کم کن
        
        // اگر به قبل از فروردین رفتیم، برو به اسفند سال قبل
        if (pMonth < 1) {
            pMonth = 12;
            pYear--;
        }
        
        // تاریخ شمسی جدید را به میلادی تبدیل کن و در متغیر اصلی ذخیره کن
        this.currentCalendarDate = this.persianToGregorianAccurate({
            year: pYear,
            month: pMonth,
            day: 1
        });
        
        this.debouncedRenderCalendar();
    }
    
    nextMonth() {
        // تاریخ فعلی را به شمسی تبدیل کن
        const persianDate = this.gregorianToPersianAccurate(this.currentCalendarDate);
        
        let pYear = persianDate.year;
        let pMonth = persianDate.month + 1; // یک ماه اضافه کن
        
        // اگر از اسفند رد شدیم، برو به فروردین سال بعد
        if (pMonth > 12) {
            pMonth = 1;
            pYear++;
        }
        
        // تاریخ شمسی جدید را به میلادی تبدیل کن و در متغیر اصلی ذخیره کن
        this.currentCalendarDate = this.persianToGregorianAccurate({
            year: pYear,
            month: pMonth,
            day: 1
        });
        
        this.debouncedRenderCalendar();
    }

    debouncedRenderCalendar() {
        if (this.renderCalendarTimeout) {
            clearTimeout(this.renderCalendarTimeout);
        }
        this.renderCalendarTimeout = setTimeout(() => {
            this.renderCalendar();
        }, 50); // 50ms debounce
    }
    renderCalendar() {
        const grid = document.getElementById('calendar-grid');
        const monthYear = document.getElementById('current-month-year');
        if (!grid || !monthYear) return;
        // Clear existing days (keep header)
        while (grid.children.length > 7) {
            grid.removeChild(grid.lastChild);
        }
        // Get current Persian date using accurate conversion
        const persianDate = this.gregorianToPersianAccurate(this.currentCalendarDate);
        const persianMonths = [
            'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
            'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
        ];
        // Update month/year display
        monthYear.textContent = `${persianMonths[persianDate.month - 1]} ${persianDate.year}`;
        // Get first day of month and days in month
        const firstDayOfMonth = this.persianToGregorianAccurate({
            year: persianDate.year,
            month: persianDate.month,
            day: 1
        });
        const firstDayOfWeek = this.getPersianDayOfWeek(firstDayOfMonth);
        const daysInMonth = this.getDaysInPersianMonth(persianDate.month, persianDate.year);
        // Add empty cells for days before month starts
        for (let i = 0; i < firstDayOfWeek; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.className = 'calendar-day other-month';
            emptyCell.textContent = '';
            grid.appendChild(emptyCell);
        }
        // Add days of the month
        // FIX: استفاده از let برای جلوگیری از تداخل با متغیرهای سراسری
        for (let day = 1; day <= daysInMonth; day++) {
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day';
            dayElement.textContent = day;
            dayElement.dataset.day = day;
            // Check if this is today
            const today = this.gregorianToPersianAccurate(new Date());
            if (day === today.day && persianDate.month === today.month && persianDate.year === today.year) {
                dayElement.classList.add('today');
            }
            // Check if this day is selected
            const currentDay = {
                year: persianDate.year,
                month: persianDate.month,
                day: day
            };
            if (this.selectedStartDate && !this.selectedEndDate) {
                if (this.isSameDate(currentDay, this.selectedStartDate)) {
                    dayElement.classList.add('selected');
                }
            }
            if (this.selectedStartDate && this.selectedEndDate) {
                if (this.isSameDate(currentDay, this.selectedStartDate)) {
                    dayElement.classList.add('selected-start');
                } else if (this.isSameDate(currentDay, this.selectedEndDate)) {
                    dayElement.classList.add('selected-end');
                } else if (this.isDateInRange(currentDay, this.selectedStartDate, this.selectedEndDate)) {
                    dayElement.classList.add('in-range');
                }
            }
            dayElement.addEventListener('click', () => this.selectDate(day));
            grid.appendChild(dayElement);
        }
    }
    
    // --- اصلاح شده: محاسبه دقیق روزهای ماه با الگوریتم کبیسه ---
    getDaysInPersianMonth(month, year) {
        const monthLengths = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];
        
        // الگوریتم تشخیص سال کبیسه شمسی (دقیق و بدون نیاز به لیست دستی)
        // اگر باقیمانده تقسیم سال بر ۳۳ برابر یکی از اعداد ۱، ۵، ۹، ۱۳، ۱۷، ۲۲، ۲۶، ۳۰ باشد، سال کبیسه است
        const remainder = year % 33;
        const isLeap = [1, 5, 9, 13, 17, 22, 26, 30].includes(remainder);
        
        if (month === 12 && isLeap) {
            return 30; // اسفند در سال کبیسه ۳۰ روز است
        }
        return monthLengths[month - 1];
    }

    isSameDate(date1, date2) {
        return date1.year === date2.year &&
            date1.month === date2.month &&
            date1.day === date2.day;
    }
    isDateInRange(date, startDate, endDate) {
        const dateGregorian = this.persianToGregorianAccurate(date);
        const startGregorian = this.persianToGregorianAccurate(startDate);
        const endGregorian = this.persianToGregorianAccurate(endDate);
        return dateGregorian >= startGregorian && dateGregorian <= endGregorian;
    }
    selectDate(day) {
        const persianDate = this.gregorianToPersianAccurate(this.currentCalendarDate);
        const selectedDate = {
            year: persianDate.year,
            month: persianDate.month,
            day: day
        };
        if (!this.selectedStartDate || (this.selectedStartDate && this.selectedEndDate)) {
            // Start new selection
            this.selectedStartDate = selectedDate;
            this.selectedEndDate = null;
        } else if (this.selectedStartDate && !this.selectedEndDate) {
            // Complete the range
            const startDate = this.persianToGregorianAccurate(this.selectedStartDate);
            const endDate = this.persianToGregorianAccurate(selectedDate);
            if (endDate >= startDate) {
                this.selectedEndDate = selectedDate;
            } else {
                this.selectedEndDate = this.selectedStartDate;
                this.selectedStartDate = selectedDate;
            }
        }
        this.updateSelectedRangeDisplay();
        this.debouncedRenderCalendar();
    }
    updateSelectedRangeDisplay() {
        const display = document.getElementById('selected-range');
        const applyBtn = document.getElementById('apply-date-range');
        if (!display || !applyBtn) return;
        if (this.selectedStartDate && this.selectedEndDate) {
            const persianMonths = [
                'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
                'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
            ];
            const startStr = `${this.selectedStartDate.day} ${persianMonths[this.selectedStartDate.month - 1]} ${this.selectedStartDate.year}`;
            const endStr = `${this.selectedEndDate.day} ${persianMonths[this.selectedEndDate.month - 1]} ${this.selectedEndDate.year}`;
            display.innerHTML = `${startStr}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;تا&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;${endStr}`;
            applyBtn.disabled = false;
        } else if (this.selectedStartDate) {
            const persianMonths = [
                'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
                'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
            ];
            display.textContent = `${this.selectedStartDate.day} ${persianMonths[this.selectedStartDate.month - 1]} ${this.selectedStartDate.year}`;
            applyBtn.disabled = true;
        } else {
            display.textContent = 'تاریخی انتخاب نشده';
            applyBtn.disabled = true;
        }
    }
    clearSelection() {
        this.selectedStartDate = null;
        this.selectedEndDate = null;
        this.updateSelectedRangeDisplay();
        this.debouncedRenderCalendar();
        
        if (this.onDateRangeCleared) {
            this.onDateRangeCleared();
        }
    }
    applyDateRange() {
        if (this.selectedStartDate && this.selectedEndDate) {
            this.closeCalendarModal();
            
            if (this.onDateRangeSelected) {
                this.onDateRangeSelected({
                    startDate: this.selectedStartDate,
                    endDate: this.selectedEndDate,
                    startGregorian: this.persianToGregorianAccurate(this.selectedStartDate),
                    endGregorian: this.persianToGregorianAccurate(this.selectedEndDate)
                });
            }
        }
    }
    // Helper function to get Persian day of week (0 = Saturday, 1 = Sunday, ..., 6 = Friday)
    getPersianDayOfWeek(date) {
        const gregorianDay = date.getDay(); // 0 = Sunday, 1 = Monday, ..., 6 = Saturday
        // Adjust to Persian week: 0 = Saturday, 1 = Sunday, ..., 6 = Friday
        return (gregorianDay + 1) % 7;
    }
    
    // --- اصلاح شده: الگوریتم دقیق تبدیل میلادی به شمسی ---
    gregorianToPersianAccurate(date) {
        // FIX: استفاده از let برای gy تا بتوانیم مقدار آن را تغییر دهیم
        let gy = date.getFullYear();
        const gm = date.getMonth() + 1; // Input months are 1-based
        const gd = date.getDate();

        const g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
        let jy = (gy <= 1600) ? 0 : 979;
        
        // این خط دیگر ارور نمی‌دهد چون gy دیگر const نیست
        gy -= (gy <= 1600) ? 621 : 1600;
        
        const gy2 = (gm > 2) ? (gy + 1) : gy;
        let days = (365 * gy) + parseInt((gy2 + 3) / 4) - parseInt((gy2 + 99) / 100) + parseInt((gy2 + 399) / 400) - 80 + gd + g_d_m[gm - 1];
        
        jy += 33 * parseInt(days / 12053);
        days %= 12053;
        jy += 4 * parseInt(days / 1461);
        days %= 1461;
        jy += parseInt((days - 1) / 365);
        
        if (days > 365) days = (days - 1) % 365;
        
        let jm = (days < 186) ? 1 + parseInt(days / 31) : 7 + parseInt((days - 186) / 30);
        let jd = 1 + ((days < 186) ? (days % 31) : ((days - 186) % 30));
        
        return {
            year: jy,
            month: jm,
            day: jd
        };
    }

    // Convert Persian date to Gregorian date using improved algorithm
    persianToGregorianAccurate(persianDate) {
        let jy = persianDate.year;
        const jm = persianDate.month;
        const jd = persianDate.day;
        // Use the improved conversion algorithm from the sample code
        let gy;
        if (jy > 979) {
            gy = 1600;
            jy -= 979;
        } else {
            gy = 621;
        }
        let days = (365 * jy) + Math.floor(jy / 33) * 8 + Math.floor(((jy % 33) + 3) / 4) + 78 + jd + ((jm < 7) ? (jm - 1) * 31 : ((jm - 7) * 30) + 186);
        gy += 400 * Math.floor(days / 146097);
        days %= 146097;
        if (days > 36524) {
            gy += 100 * Math.floor((days - 1) / 36524);
            days = (days - 1) % 36524;
            if (days >= 365) {
                days = days + 1;
            }
        }
        gy += 4 * Math.floor(days / 1461);
        days %= 1461;
        gy += Math.floor((days - 1) / 365);
        if (days > 365) days = (days - 365) % 365;
        let gd = days;
        const sal_a = [0, 31, ((gy % 4 === 0 && gy % 100 !== 0) || (gy % 400 === 0)) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        let gm = 0;
        while (gm < 13 && gd > sal_a[gm]) {
            gd -= sal_a[gm];
            gm++;
        }
        return new Date(gy, gm - 1, gd); // JavaScript months are 0-based
    }
    // Format date to Persian calendar using accurate conversion
    formatPersianDate(date) {
        const persianMonths = [
            'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
            'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
        ];
        // Convert Gregorian to Persian date using accurate algorithm
        const persianDate = this.gregorianToPersianAccurate(date);
        const day = persianDate.day;
        const month = persianMonths[persianDate.month - 1];
        const year = persianDate.year;
        return `${day} ${month} ${year}`;
    }
    // Get current selected date range
    getSelectedDateRange() {
        return {
            startDate: this.selectedStartDate,
            endDate: this.selectedEndDate,
            startGregorian: this.selectedStartDate ? this.persianToGregorianAccurate(this.selectedStartDate) : null,
            endGregorian: this.selectedEndDate ? this.persianToGregorianAccurate(this.selectedEndDate) : null
        };
    }
    // Set date range programmatically
    setDateRange(startDate, endDate) {
        this.selectedStartDate = startDate;
        this.selectedEndDate = endDate;
        this.updateSelectedRangeDisplay();
        this.debouncedRenderCalendar();
    }
    // Clear all selections
    reset() {
        this.selectedStartDate = null;
        this.selectedEndDate = null;
        this.currentCalendarDate = new Date();
        this.updateSelectedRangeDisplay();
        this.debouncedRenderCalendar();
    }
}
// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PersianCalendar;
} else if (typeof window !== 'undefined') {
    window.PersianCalendar = PersianCalendar;
}