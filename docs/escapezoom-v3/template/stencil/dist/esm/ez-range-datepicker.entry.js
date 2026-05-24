import { r as registerInstance, c as createEvent, g as getElement, h } from './index-DclNjYd0.js';

var HOOKS = [
    "onChange",
    "onClose",
    "onDayCreate",
    "onDestroy",
    "onKeyDown",
    "onMonthChange",
    "onOpen",
    "onParseConfig",
    "onReady",
    "onValueUpdate",
    "onYearChange",
    "onPreCalendarPosition",
];
var defaults = {
    _disable: [],
    allowInput: false,
    allowInvalidPreload: false,
    altFormat: "F j, Y",
    altInput: false,
    altInputClass: "form-control input",
    animate: typeof window === "object" &&
        window.navigator.userAgent.indexOf("MSIE") === -1,
    ariaDateFormat: "F j, Y",
    autoFillDefaultTime: true,
    clickOpens: true,
    closeOnSelect: true,
    conjunction: ", ",
    dateFormat: "Y-m-d",
    defaultHour: 12,
    defaultMinute: 0,
    defaultSeconds: 0,
    disable: [],
    disableMobile: false,
    enableSeconds: false,
    enableTime: false,
    errorHandler: function (err) {
        return typeof console !== "undefined" && console.warn(err);
    },
    getWeek: function (givenDate) {
        var date = new Date(givenDate.getTime());
        date.setHours(0, 0, 0, 0);
        date.setDate(date.getDate() + 3 - ((date.getDay() + 6) % 7));
        var week1 = new Date(date.getFullYear(), 0, 4);
        return (1 +
            Math.round(((date.getTime() - week1.getTime()) / 86400000 -
                3 +
                ((week1.getDay() + 6) % 7)) /
                7));
    },
    hourIncrement: 1,
    ignoredFocusElements: [],
    inline: false,
    locale: "default",
    minuteIncrement: 5,
    mode: "single",
    monthSelectorType: "dropdown",
    nextArrow: "<svg version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' viewBox='0 0 17 17'><g></g><path d='M13.207 8.472l-7.854 7.854-0.707-0.707 7.146-7.146-7.146-7.148 0.707-0.707 7.854 7.854z' /></svg>",
    noCalendar: false,
    now: new Date(),
    onChange: [],
    onClose: [],
    onDayCreate: [],
    onDestroy: [],
    onKeyDown: [],
    onMonthChange: [],
    onOpen: [],
    onParseConfig: [],
    onReady: [],
    onValueUpdate: [],
    onYearChange: [],
    onPreCalendarPosition: [],
    plugins: [],
    position: "auto",
    positionElement: undefined,
    prevArrow: "<svg version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' viewBox='0 0 17 17'><g></g><path d='M5.207 8.471l7.146 7.147-0.707 0.707-7.853-7.854 7.854-7.853 0.707 0.707-7.147 7.146z' /></svg>",
    shorthandCurrentMonth: false,
    showMonths: 1,
    static: false,
    time_24hr: false,
    weekNumbers: false,
    wrap: false,
};

var english = {
    weekdays: {
        shorthand: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
        longhand: [
            "Sunday",
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday",
            "Saturday",
        ],
    },
    months: {
        shorthand: [
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "May",
            "Jun",
            "Jul",
            "Aug",
            "Sep",
            "Oct",
            "Nov",
            "Dec",
        ],
        longhand: [
            "January",
            "February",
            "March",
            "April",
            "May",
            "June",
            "July",
            "August",
            "September",
            "October",
            "November",
            "December",
        ],
    },
    daysInMonth: [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31],
    firstDayOfWeek: 0,
    ordinal: function (nth) {
        var s = nth % 100;
        if (s > 3 && s < 21)
            return "th";
        switch (s % 10) {
            case 1:
                return "st";
            case 2:
                return "nd";
            case 3:
                return "rd";
            default:
                return "th";
        }
    },
    rangeSeparator: " to ",
    weekAbbreviation: "Wk",
    scrollTitle: "Scroll to increment",
    toggleTitle: "Click to toggle",
    amPM: ["AM", "PM"],
    yearAriaLabel: "Year",
    monthAriaLabel: "Month",
    hourAriaLabel: "Hour",
    minuteAriaLabel: "Minute",
    time_24hr: false,
};

var pad = function (number, length) {
    if (length === void 0) { length = 2; }
    return ("000" + number).slice(length * -1);
};
var int = function (bool) { return (bool === true ? 1 : 0); };
function debounce(fn, wait) {
    var t;
    return function () {
        var _this = this;
        var args = arguments;
        clearTimeout(t);
        t = setTimeout(function () { return fn.apply(_this, args); }, wait);
    };
}
var arrayify = function (obj) {
    return obj instanceof Array ? obj : [obj];
};

function toggleClass(elem, className, bool) {
    if (bool === true)
        return elem.classList.add(className);
    elem.classList.remove(className);
}
function createElement(tag, className, content) {
    var e = window.document.createElement(tag);
    className = className || "";
    content = content || "";
    e.className = className;
    if (content !== undefined)
        e.textContent = content;
    return e;
}
function clearNode(node) {
    while (node.firstChild)
        node.removeChild(node.firstChild);
}
function findParent(node, condition) {
    if (condition(node))
        return node;
    else if (node.parentNode)
        return findParent(node.parentNode, condition);
    return undefined;
}
function createNumberInput(inputClassName, opts) {
    var wrapper = createElement("div", "numInputWrapper"), numInput = createElement("input", "numInput " + inputClassName), arrowUp = createElement("span", "arrowUp"), arrowDown = createElement("span", "arrowDown");
    if (navigator.userAgent.indexOf("MSIE 9.0") === -1) {
        numInput.type = "number";
    }
    else {
        numInput.type = "text";
        numInput.pattern = "\\d*";
    }
    if (opts !== undefined)
        for (var key in opts)
            numInput.setAttribute(key, opts[key]);
    wrapper.appendChild(numInput);
    wrapper.appendChild(arrowUp);
    wrapper.appendChild(arrowDown);
    return wrapper;
}
function getEventTarget(event) {
    try {
        if (typeof event.composedPath === "function") {
            var path = event.composedPath();
            return path[0];
        }
        return event.target;
    }
    catch (error) {
        return event.target;
    }
}

var doNothing = function () { return undefined; };
var monthToStr = function (monthNumber, shorthand, locale) { return locale.months[shorthand ? "shorthand" : "longhand"][monthNumber]; };
var revFormat = {
    D: doNothing,
    F: function (dateObj, monthName, locale) {
        dateObj.setMonth(locale.months.longhand.indexOf(monthName));
    },
    G: function (dateObj, hour) {
        dateObj.setHours((dateObj.getHours() >= 12 ? 12 : 0) + parseFloat(hour));
    },
    H: function (dateObj, hour) {
        dateObj.setHours(parseFloat(hour));
    },
    J: function (dateObj, day) {
        dateObj.setDate(parseFloat(day));
    },
    K: function (dateObj, amPM, locale) {
        dateObj.setHours((dateObj.getHours() % 12) +
            12 * int(new RegExp(locale.amPM[1], "i").test(amPM)));
    },
    M: function (dateObj, shortMonth, locale) {
        dateObj.setMonth(locale.months.shorthand.indexOf(shortMonth));
    },
    S: function (dateObj, seconds) {
        dateObj.setSeconds(parseFloat(seconds));
    },
    U: function (_, unixSeconds) { return new Date(parseFloat(unixSeconds) * 1000); },
    W: function (dateObj, weekNum, locale) {
        var weekNumber = parseInt(weekNum);
        var date = new Date(dateObj.getFullYear(), 0, 2 + (weekNumber - 1) * 7, 0, 0, 0, 0);
        date.setDate(date.getDate() - date.getDay() + locale.firstDayOfWeek);
        return date;
    },
    Y: function (dateObj, year) {
        dateObj.setFullYear(parseFloat(year));
    },
    Z: function (_, ISODate) { return new Date(ISODate); },
    d: function (dateObj, day) {
        dateObj.setDate(parseFloat(day));
    },
    h: function (dateObj, hour) {
        dateObj.setHours((dateObj.getHours() >= 12 ? 12 : 0) + parseFloat(hour));
    },
    i: function (dateObj, minutes) {
        dateObj.setMinutes(parseFloat(minutes));
    },
    j: function (dateObj, day) {
        dateObj.setDate(parseFloat(day));
    },
    l: doNothing,
    m: function (dateObj, month) {
        dateObj.setMonth(parseFloat(month) - 1);
    },
    n: function (dateObj, month) {
        dateObj.setMonth(parseFloat(month) - 1);
    },
    s: function (dateObj, seconds) {
        dateObj.setSeconds(parseFloat(seconds));
    },
    u: function (_, unixMillSeconds) {
        return new Date(parseFloat(unixMillSeconds));
    },
    w: doNothing,
    y: function (dateObj, year) {
        dateObj.setFullYear(2000 + parseFloat(year));
    },
};
var tokenRegex = {
    D: "",
    F: "",
    G: "(\\d\\d|\\d)",
    H: "(\\d\\d|\\d)",
    J: "(\\d\\d|\\d)\\w+",
    K: "",
    M: "",
    S: "(\\d\\d|\\d)",
    U: "(.+)",
    W: "(\\d\\d|\\d)",
    Y: "(\\d{4})",
    Z: "(.+)",
    d: "(\\d\\d|\\d)",
    h: "(\\d\\d|\\d)",
    i: "(\\d\\d|\\d)",
    j: "(\\d\\d|\\d)",
    l: "",
    m: "(\\d\\d|\\d)",
    n: "(\\d\\d|\\d)",
    s: "(\\d\\d|\\d)",
    u: "(.+)",
    w: "(\\d\\d|\\d)",
    y: "(\\d{2})",
};
var formats = {
    Z: function (date) { return date.toISOString(); },
    D: function (date, locale, options) {
        return locale.weekdays.shorthand[formats.w(date, locale, options)];
    },
    F: function (date, locale, options) {
        return monthToStr(formats.n(date, locale, options) - 1, false, locale);
    },
    G: function (date, locale, options) {
        return pad(formats.h(date, locale, options));
    },
    H: function (date) { return pad(date.getHours()); },
    J: function (date, locale) {
        return locale.ordinal !== undefined
            ? date.getDate() + locale.ordinal(date.getDate())
            : date.getDate();
    },
    K: function (date, locale) { return locale.amPM[int(date.getHours() > 11)]; },
    M: function (date, locale) {
        return monthToStr(date.getMonth(), true, locale);
    },
    S: function (date) { return pad(date.getSeconds()); },
    U: function (date) { return date.getTime() / 1000; },
    W: function (date, _, options) {
        return options.getWeek(date);
    },
    Y: function (date) { return pad(date.getFullYear(), 4); },
    d: function (date) { return pad(date.getDate()); },
    h: function (date) { return (date.getHours() % 12 ? date.getHours() % 12 : 12); },
    i: function (date) { return pad(date.getMinutes()); },
    j: function (date) { return date.getDate(); },
    l: function (date, locale) {
        return locale.weekdays.longhand[date.getDay()];
    },
    m: function (date) { return pad(date.getMonth() + 1); },
    n: function (date) { return date.getMonth() + 1; },
    s: function (date) { return date.getSeconds(); },
    u: function (date) { return date.getTime(); },
    w: function (date) { return date.getDay(); },
    y: function (date) { return String(date.getFullYear()).substring(2); },
};

var createDateFormatter = function (_a) {
    var _b = _a.config, config = _b === void 0 ? defaults : _b, _c = _a.l10n, l10n = _c === void 0 ? english : _c, _d = _a.isMobile, isMobile = _d === void 0 ? false : _d;
    return function (dateObj, frmt, overrideLocale) {
        var locale = overrideLocale || l10n;
        if (config.formatDate !== undefined && !isMobile) {
            return config.formatDate(dateObj, frmt, locale);
        }
        return frmt
            .split("")
            .map(function (c, i, arr) {
            return formats[c] && arr[i - 1] !== "\\"
                ? formats[c](dateObj, locale, config)
                : c !== "\\"
                    ? c
                    : "";
        })
            .join("");
    };
};
var createDateParser = function (_a) {
    var _b = _a.config, config = _b === void 0 ? defaults : _b, _c = _a.l10n, l10n = _c === void 0 ? english : _c;
    return function (date, givenFormat, timeless, customLocale) {
        if (date !== 0 && !date)
            return undefined;
        var locale = customLocale || l10n;
        var parsedDate;
        var dateOrig = date;
        if (date instanceof Date)
            parsedDate = new Date(date.getTime());
        else if (typeof date !== "string" &&
            date.toFixed !== undefined)
            parsedDate = new Date(date);
        else if (typeof date === "string") {
            var format = givenFormat || (config || defaults).dateFormat;
            var datestr = String(date).trim();
            if (datestr === "today") {
                parsedDate = new Date();
                timeless = true;
            }
            else if (config && config.parseDate) {
                parsedDate = config.parseDate(date, format);
            }
            else if (/Z$/.test(datestr) ||
                /GMT$/.test(datestr)) {
                parsedDate = new Date(date);
            }
            else {
                var matched = void 0, ops = [];
                for (var i = 0, matchIndex = 0, regexStr = ""; i < format.length; i++) {
                    var token = format[i];
                    var isBackSlash = token === "\\";
                    var escaped = format[i - 1] === "\\" || isBackSlash;
                    if (tokenRegex[token] && !escaped) {
                        regexStr += tokenRegex[token];
                        var match = new RegExp(regexStr).exec(date);
                        if (match && (matched = true)) {
                            ops[token !== "Y" ? "push" : "unshift"]({
                                fn: revFormat[token],
                                val: match[++matchIndex],
                            });
                        }
                    }
                    else if (!isBackSlash)
                        regexStr += ".";
                }
                parsedDate =
                    !config || !config.noCalendar
                        ? new Date(new Date().getFullYear(), 0, 1, 0, 0, 0, 0)
                        : new Date(new Date().setHours(0, 0, 0, 0));
                ops.forEach(function (_a) {
                    var fn = _a.fn, val = _a.val;
                    return (parsedDate = fn(parsedDate, val, locale) || parsedDate);
                });
                parsedDate = matched ? parsedDate : undefined;
            }
        }
        if (!(parsedDate instanceof Date && !isNaN(parsedDate.getTime()))) {
            config.errorHandler(new Error("Invalid date provided: " + dateOrig));
            return undefined;
        }
        if (timeless === true)
            parsedDate.setHours(0, 0, 0, 0);
        return parsedDate;
    };
};
function compareDates(date1, date2, timeless) {
    if (timeless === void 0) { timeless = true; }
    if (timeless !== false) {
        return (new Date(date1.getTime()).setHours(0, 0, 0, 0) -
            new Date(date2.getTime()).setHours(0, 0, 0, 0));
    }
    return date1.getTime() - date2.getTime();
}
var isBetween = function (ts, ts1, ts2) {
    return ts > Math.min(ts1, ts2) && ts < Math.max(ts1, ts2);
};
var calculateSecondsSinceMidnight = function (hours, minutes, seconds) {
    return hours * 3600 + minutes * 60 + seconds;
};
var parseSeconds = function (secondsSinceMidnight) {
    var hours = Math.floor(secondsSinceMidnight / 3600), minutes = (secondsSinceMidnight - hours * 3600) / 60;
    return [hours, minutes, secondsSinceMidnight - hours * 3600 - minutes * 60];
};
var duration = {
    DAY: 86400000,
};
function getDefaultHours(config) {
    var hours = config.defaultHour;
    var minutes = config.defaultMinute;
    var seconds = config.defaultSeconds;
    if (config.minDate !== undefined) {
        var minHour = config.minDate.getHours();
        var minMinutes = config.minDate.getMinutes();
        var minSeconds = config.minDate.getSeconds();
        if (hours < minHour) {
            hours = minHour;
        }
        if (hours === minHour && minutes < minMinutes) {
            minutes = minMinutes;
        }
        if (hours === minHour && minutes === minMinutes && seconds < minSeconds)
            seconds = config.minDate.getSeconds();
    }
    if (config.maxDate !== undefined) {
        var maxHr = config.maxDate.getHours();
        var maxMinutes = config.maxDate.getMinutes();
        hours = Math.min(hours, maxHr);
        if (hours === maxHr)
            minutes = Math.min(maxMinutes, minutes);
        if (hours === maxHr && minutes === maxMinutes)
            seconds = config.maxDate.getSeconds();
    }
    return { hours: hours, minutes: minutes, seconds: seconds };
}

if (typeof Object.assign !== "function") {
    Object.assign = function (target) {
        var args = [];
        for (var _i = 1; _i < arguments.length; _i++) {
            args[_i - 1] = arguments[_i];
        }
        if (!target) {
            throw TypeError("Cannot convert undefined or null to object");
        }
        var _loop_1 = function (source) {
            if (source) {
                Object.keys(source).forEach(function (key) { return (target[key] = source[key]); });
            }
        };
        for (var _a = 0, args_1 = args; _a < args_1.length; _a++) {
            var source = args_1[_a];
            _loop_1(source);
        }
        return target;
    };
}

var __assign = (undefined && undefined.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
var __spreadArrays = (undefined && undefined.__spreadArrays) || function () {
    for (var s = 0, i = 0, il = arguments.length; i < il; i++) s += arguments[i].length;
    for (var r = Array(s), k = 0, i = 0; i < il; i++)
        for (var a = arguments[i], j = 0, jl = a.length; j < jl; j++, k++)
            r[k] = a[j];
    return r;
};
var DEBOUNCED_CHANGE_MS = 300;
function FlatpickrInstance(element, instanceConfig) {
    var self = {
        config: __assign(__assign({}, defaults), flatpickr.defaultConfig),
        l10n: english,
    };
    self.parseDate = createDateParser({ config: self.config, l10n: self.l10n });
    self._handlers = [];
    self.pluginElements = [];
    self.loadedPlugins = [];
    self._bind = bind;
    self._setHoursFromDate = setHoursFromDate;
    self._positionCalendar = positionCalendar;
    self.changeMonth = changeMonth;
    self.changeYear = changeYear;
    self.clear = clear;
    self.close = close;
    self.onMouseOver = onMouseOver;
    self._createElement = createElement;
    self.createDay = createDay;
    self.destroy = destroy;
    self.isEnabled = isEnabled;
    self.jumpToDate = jumpToDate;
    self.updateValue = updateValue;
    self.open = open;
    self.redraw = redraw;
    self.set = set;
    self.setDate = setDate;
    self.toggle = toggle;
    function setupHelperFunctions() {
        self.utils = {
            getDaysInMonth: function (month, yr) {
                if (month === void 0) { month = self.currentMonth; }
                if (yr === void 0) { yr = self.currentYear; }
                if (month === 1 && ((yr % 4 === 0 && yr % 100 !== 0) || yr % 400 === 0))
                    return 29;
                return self.l10n.daysInMonth[month];
            },
        };
    }
    function init() {
        self.element = self.input = element;
        self.isOpen = false;
        parseConfig();
        setupLocale();
        setupInputs();
        setupDates();
        setupHelperFunctions();
        if (!self.isMobile)
            build();
        bindEvents();
        if (self.selectedDates.length || self.config.noCalendar) {
            if (self.config.enableTime) {
                setHoursFromDate(self.config.noCalendar ? self.latestSelectedDateObj : undefined);
            }
            updateValue(false);
        }
        setCalendarWidth();
        var isSafari = /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
        if (!self.isMobile && isSafari) {
            positionCalendar();
        }
        triggerEvent("onReady");
    }
    function getClosestActiveElement() {
        var _a;
        return (((_a = self.calendarContainer) === null || _a === void 0 ? void 0 : _a.getRootNode())
            .activeElement || document.activeElement);
    }
    function bindToInstance(fn) {
        return fn.bind(self);
    }
    function setCalendarWidth() {
        var config = self.config;
        if (config.weekNumbers === false && config.showMonths === 1) {
            return;
        }
        else if (config.noCalendar !== true) {
            window.requestAnimationFrame(function () {
                if (self.calendarContainer !== undefined) {
                    self.calendarContainer.style.visibility = "hidden";
                    self.calendarContainer.style.display = "block";
                }
                if (self.daysContainer !== undefined) {
                    var daysWidth = (self.days.offsetWidth + 1) * config.showMonths;
                    self.daysContainer.style.width = daysWidth + "px";
                    self.calendarContainer.style.width =
                        daysWidth +
                            (self.weekWrapper !== undefined
                                ? self.weekWrapper.offsetWidth
                                : 0) +
                            "px";
                    self.calendarContainer.style.removeProperty("visibility");
                    self.calendarContainer.style.removeProperty("display");
                }
            });
        }
    }
    function updateTime(e) {
        if (self.selectedDates.length === 0) {
            var defaultDate = self.config.minDate === undefined ||
                compareDates(new Date(), self.config.minDate) >= 0
                ? new Date()
                : new Date(self.config.minDate.getTime());
            var defaults = getDefaultHours(self.config);
            defaultDate.setHours(defaults.hours, defaults.minutes, defaults.seconds, defaultDate.getMilliseconds());
            self.selectedDates = [defaultDate];
            self.latestSelectedDateObj = defaultDate;
        }
        if (e !== undefined && e.type !== "blur") {
            timeWrapper(e);
        }
        var prevValue = self._input.value;
        setHoursFromInputs();
        updateValue();
        if (self._input.value !== prevValue) {
            self._debouncedChange();
        }
    }
    function ampm2military(hour, amPM) {
        return (hour % 12) + 12 * int(amPM === self.l10n.amPM[1]);
    }
    function military2ampm(hour) {
        switch (hour % 24) {
            case 0:
            case 12:
                return 12;
            default:
                return hour % 12;
        }
    }
    function setHoursFromInputs() {
        if (self.hourElement === undefined || self.minuteElement === undefined)
            return;
        var hours = (parseInt(self.hourElement.value.slice(-2), 10) || 0) % 24, minutes = (parseInt(self.minuteElement.value, 10) || 0) % 60, seconds = self.secondElement !== undefined
            ? (parseInt(self.secondElement.value, 10) || 0) % 60
            : 0;
        if (self.amPM !== undefined) {
            hours = ampm2military(hours, self.amPM.textContent);
        }
        var limitMinHours = self.config.minTime !== undefined ||
            (self.config.minDate &&
                self.minDateHasTime &&
                self.latestSelectedDateObj &&
                compareDates(self.latestSelectedDateObj, self.config.minDate, true) ===
                    0);
        var limitMaxHours = self.config.maxTime !== undefined ||
            (self.config.maxDate &&
                self.maxDateHasTime &&
                self.latestSelectedDateObj &&
                compareDates(self.latestSelectedDateObj, self.config.maxDate, true) ===
                    0);
        if (self.config.maxTime !== undefined &&
            self.config.minTime !== undefined &&
            self.config.minTime > self.config.maxTime) {
            var minBound = calculateSecondsSinceMidnight(self.config.minTime.getHours(), self.config.minTime.getMinutes(), self.config.minTime.getSeconds());
            var maxBound = calculateSecondsSinceMidnight(self.config.maxTime.getHours(), self.config.maxTime.getMinutes(), self.config.maxTime.getSeconds());
            var currentTime = calculateSecondsSinceMidnight(hours, minutes, seconds);
            if (currentTime > maxBound && currentTime < minBound) {
                var result = parseSeconds(minBound);
                hours = result[0];
                minutes = result[1];
                seconds = result[2];
            }
        }
        else {
            if (limitMaxHours) {
                var maxTime = self.config.maxTime !== undefined
                    ? self.config.maxTime
                    : self.config.maxDate;
                hours = Math.min(hours, maxTime.getHours());
                if (hours === maxTime.getHours())
                    minutes = Math.min(minutes, maxTime.getMinutes());
                if (minutes === maxTime.getMinutes())
                    seconds = Math.min(seconds, maxTime.getSeconds());
            }
            if (limitMinHours) {
                var minTime = self.config.minTime !== undefined
                    ? self.config.minTime
                    : self.config.minDate;
                hours = Math.max(hours, minTime.getHours());
                if (hours === minTime.getHours() && minutes < minTime.getMinutes())
                    minutes = minTime.getMinutes();
                if (minutes === minTime.getMinutes())
                    seconds = Math.max(seconds, minTime.getSeconds());
            }
        }
        setHours(hours, minutes, seconds);
    }
    function setHoursFromDate(dateObj) {
        var date = dateObj || self.latestSelectedDateObj;
        if (date && date instanceof Date) {
            setHours(date.getHours(), date.getMinutes(), date.getSeconds());
        }
    }
    function setHours(hours, minutes, seconds) {
        if (self.latestSelectedDateObj !== undefined) {
            self.latestSelectedDateObj.setHours(hours % 24, minutes, seconds || 0, 0);
        }
        if (!self.hourElement || !self.minuteElement || self.isMobile)
            return;
        self.hourElement.value = pad(!self.config.time_24hr
            ? ((12 + hours) % 12) + 12 * int(hours % 12 === 0)
            : hours);
        self.minuteElement.value = pad(minutes);
        if (self.amPM !== undefined)
            self.amPM.textContent = self.l10n.amPM[int(hours >= 12)];
        if (self.secondElement !== undefined)
            self.secondElement.value = pad(seconds);
    }
    function onYearInput(event) {
        var eventTarget = getEventTarget(event);
        var year = parseInt(eventTarget.value) + (event.delta || 0);
        if (year / 1000 > 1 ||
            (event.key === "Enter" && !/[^\d]/.test(year.toString()))) {
            changeYear(year);
        }
    }
    function bind(element, event, handler, options) {
        if (event instanceof Array)
            return event.forEach(function (ev) { return bind(element, ev, handler, options); });
        if (element instanceof Array)
            return element.forEach(function (el) { return bind(el, event, handler, options); });
        element.addEventListener(event, handler, options);
        self._handlers.push({
            remove: function () { return element.removeEventListener(event, handler, options); },
        });
    }
    function triggerChange() {
        triggerEvent("onChange");
    }
    function bindEvents() {
        if (self.config.wrap) {
            ["open", "close", "toggle", "clear"].forEach(function (evt) {
                Array.prototype.forEach.call(self.element.querySelectorAll("[data-" + evt + "]"), function (el) {
                    return bind(el, "click", self[evt]);
                });
            });
        }
        if (self.isMobile) {
            setupMobile();
            return;
        }
        var debouncedResize = debounce(onResize, 50);
        self._debouncedChange = debounce(triggerChange, DEBOUNCED_CHANGE_MS);
        if (self.daysContainer && !/iPhone|iPad|iPod/i.test(navigator.userAgent))
            bind(self.daysContainer, "mouseover", function (e) {
                if (self.config.mode === "range")
                    onMouseOver(getEventTarget(e));
            });
        bind(self._input, "keydown", onKeyDown);
        if (self.calendarContainer !== undefined) {
            bind(self.calendarContainer, "keydown", onKeyDown);
        }
        if (!self.config.inline && !self.config.static)
            bind(window, "resize", debouncedResize);
        if (window.ontouchstart !== undefined)
            bind(window.document, "touchstart", documentClick);
        else
            bind(window.document, "mousedown", documentClick);
        bind(window.document, "focus", documentClick, { capture: true });
        if (self.config.clickOpens === true) {
            bind(self._input, "focus", self.open);
            bind(self._input, "click", self.open);
        }
        if (self.daysContainer !== undefined) {
            bind(self.monthNav, "click", onMonthNavClick);
            bind(self.monthNav, ["keyup", "increment"], onYearInput);
            bind(self.daysContainer, "click", selectDate);
        }
        if (self.timeContainer !== undefined &&
            self.minuteElement !== undefined &&
            self.hourElement !== undefined) {
            var selText = function (e) {
                return getEventTarget(e).select();
            };
            bind(self.timeContainer, ["increment"], updateTime);
            bind(self.timeContainer, "blur", updateTime, { capture: true });
            bind(self.timeContainer, "click", timeIncrement);
            bind([self.hourElement, self.minuteElement], ["focus", "click"], selText);
            if (self.secondElement !== undefined)
                bind(self.secondElement, "focus", function () { return self.secondElement && self.secondElement.select(); });
            if (self.amPM !== undefined) {
                bind(self.amPM, "click", function (e) {
                    updateTime(e);
                });
            }
        }
        if (self.config.allowInput) {
            bind(self._input, "blur", onBlur);
        }
    }
    function jumpToDate(jumpDate, triggerChange) {
        var jumpTo = jumpDate !== undefined
            ? self.parseDate(jumpDate)
            : self.latestSelectedDateObj ||
                (self.config.minDate && self.config.minDate > self.now
                    ? self.config.minDate
                    : self.config.maxDate && self.config.maxDate < self.now
                        ? self.config.maxDate
                        : self.now);
        var oldYear = self.currentYear;
        var oldMonth = self.currentMonth;
        try {
            if (jumpTo !== undefined) {
                self.currentYear = jumpTo.getFullYear();
                self.currentMonth = jumpTo.getMonth();
            }
        }
        catch (e) {
            e.message = "Invalid date supplied: " + jumpTo;
            self.config.errorHandler(e);
        }
        if (triggerChange && self.currentYear !== oldYear) {
            triggerEvent("onYearChange");
            buildMonthSwitch();
        }
        if (triggerChange &&
            (self.currentYear !== oldYear || self.currentMonth !== oldMonth)) {
            triggerEvent("onMonthChange");
        }
        self.redraw();
    }
    function timeIncrement(e) {
        var eventTarget = getEventTarget(e);
        if (~eventTarget.className.indexOf("arrow"))
            incrementNumInput(e, eventTarget.classList.contains("arrowUp") ? 1 : -1);
    }
    function incrementNumInput(e, delta, inputElem) {
        var target = e && getEventTarget(e);
        var input = inputElem ||
            (target && target.parentNode && target.parentNode.firstChild);
        var event = createEvent("increment");
        event.delta = delta;
        input && input.dispatchEvent(event);
    }
    function build() {
        var fragment = window.document.createDocumentFragment();
        self.calendarContainer = createElement("div", "flatpickr-calendar");
        self.calendarContainer.tabIndex = -1;
        if (!self.config.noCalendar) {
            fragment.appendChild(buildMonthNav());
            self.innerContainer = createElement("div", "flatpickr-innerContainer");
            if (self.config.weekNumbers) {
                var _a = buildWeeks(), weekWrapper = _a.weekWrapper, weekNumbers = _a.weekNumbers;
                self.innerContainer.appendChild(weekWrapper);
                self.weekNumbers = weekNumbers;
                self.weekWrapper = weekWrapper;
            }
            self.rContainer = createElement("div", "flatpickr-rContainer");
            self.rContainer.appendChild(buildWeekdays());
            if (!self.daysContainer) {
                self.daysContainer = createElement("div", "flatpickr-days");
                self.daysContainer.tabIndex = -1;
            }
            buildDays();
            self.rContainer.appendChild(self.daysContainer);
            self.innerContainer.appendChild(self.rContainer);
            fragment.appendChild(self.innerContainer);
        }
        if (self.config.enableTime) {
            fragment.appendChild(buildTime());
        }
        toggleClass(self.calendarContainer, "rangeMode", self.config.mode === "range");
        toggleClass(self.calendarContainer, "animate", self.config.animate === true);
        toggleClass(self.calendarContainer, "multiMonth", self.config.showMonths > 1);
        self.calendarContainer.appendChild(fragment);
        var customAppend = self.config.appendTo !== undefined &&
            self.config.appendTo.nodeType !== undefined;
        if (self.config.inline || self.config.static) {
            self.calendarContainer.classList.add(self.config.inline ? "inline" : "static");
            if (self.config.inline) {
                if (!customAppend && self.element.parentNode)
                    self.element.parentNode.insertBefore(self.calendarContainer, self._input.nextSibling);
                else if (self.config.appendTo !== undefined)
                    self.config.appendTo.appendChild(self.calendarContainer);
            }
            if (self.config.static) {
                var wrapper = createElement("div", "flatpickr-wrapper");
                if (self.element.parentNode)
                    self.element.parentNode.insertBefore(wrapper, self.element);
                wrapper.appendChild(self.element);
                if (self.altInput)
                    wrapper.appendChild(self.altInput);
                wrapper.appendChild(self.calendarContainer);
            }
        }
        if (!self.config.static && !self.config.inline)
            (self.config.appendTo !== undefined
                ? self.config.appendTo
                : window.document.body).appendChild(self.calendarContainer);
    }
    function createDay(className, date, _dayNumber, i) {
        var dateIsEnabled = isEnabled(date, true), dayElement = createElement("span", className, date.getDate().toString());
        dayElement.dateObj = date;
        dayElement.$i = i;
        dayElement.setAttribute("aria-label", self.formatDate(date, self.config.ariaDateFormat));
        if (className.indexOf("hidden") === -1 &&
            compareDates(date, self.now) === 0) {
            self.todayDateElem = dayElement;
            dayElement.classList.add("today");
            dayElement.setAttribute("aria-current", "date");
        }
        if (dateIsEnabled) {
            dayElement.tabIndex = -1;
            if (isDateSelected(date)) {
                dayElement.classList.add("selected");
                self.selectedDateElem = dayElement;
                if (self.config.mode === "range") {
                    toggleClass(dayElement, "startRange", self.selectedDates[0] &&
                        compareDates(date, self.selectedDates[0], true) === 0);
                    toggleClass(dayElement, "endRange", self.selectedDates[1] &&
                        compareDates(date, self.selectedDates[1], true) === 0);
                    if (className === "nextMonthDay")
                        dayElement.classList.add("inRange");
                }
            }
        }
        else {
            dayElement.classList.add("flatpickr-disabled");
        }
        if (self.config.mode === "range") {
            if (isDateInRange(date) && !isDateSelected(date))
                dayElement.classList.add("inRange");
        }
        if (self.weekNumbers &&
            self.config.showMonths === 1 &&
            className !== "prevMonthDay" &&
            i % 7 === 6) {
            self.weekNumbers.insertAdjacentHTML("beforeend", "<span class='flatpickr-day'>" + self.config.getWeek(date) + "</span>");
        }
        triggerEvent("onDayCreate", dayElement);
        return dayElement;
    }
    function focusOnDayElem(targetNode) {
        targetNode.focus();
        if (self.config.mode === "range")
            onMouseOver(targetNode);
    }
    function getFirstAvailableDay(delta) {
        var startMonth = delta > 0 ? 0 : self.config.showMonths - 1;
        var endMonth = delta > 0 ? self.config.showMonths : -1;
        for (var m = startMonth; m != endMonth; m += delta) {
            var month = self.daysContainer.children[m];
            var startIndex = delta > 0 ? 0 : month.children.length - 1;
            var endIndex = delta > 0 ? month.children.length : -1;
            for (var i = startIndex; i != endIndex; i += delta) {
                var c = month.children[i];
                if (c.className.indexOf("hidden") === -1 && isEnabled(c.dateObj))
                    return c;
            }
        }
        return undefined;
    }
    function getNextAvailableDay(current, delta) {
        var givenMonth = current.className.indexOf("Month") === -1
            ? current.dateObj.getMonth()
            : self.currentMonth;
        var endMonth = delta > 0 ? self.config.showMonths : -1;
        var loopDelta = delta > 0 ? 1 : -1;
        for (var m = givenMonth - self.currentMonth; m != endMonth; m += loopDelta) {
            var month = self.daysContainer.children[m];
            var startIndex = givenMonth - self.currentMonth === m
                ? current.$i + delta
                : delta < 0
                    ? month.children.length - 1
                    : 0;
            var numMonthDays = month.children.length;
            for (var i = startIndex; i >= 0 && i < numMonthDays && i != (delta > 0 ? numMonthDays : -1); i += loopDelta) {
                var c = month.children[i];
                if (c.className.indexOf("hidden") === -1 &&
                    isEnabled(c.dateObj) &&
                    Math.abs(current.$i - i) >= Math.abs(delta))
                    return focusOnDayElem(c);
            }
        }
        self.changeMonth(loopDelta);
        focusOnDay(getFirstAvailableDay(loopDelta), 0);
        return undefined;
    }
    function focusOnDay(current, offset) {
        var activeElement = getClosestActiveElement();
        var dayFocused = isInView(activeElement || document.body);
        var startElem = current !== undefined
            ? current
            : dayFocused
                ? activeElement
                : self.selectedDateElem !== undefined && isInView(self.selectedDateElem)
                    ? self.selectedDateElem
                    : self.todayDateElem !== undefined && isInView(self.todayDateElem)
                        ? self.todayDateElem
                        : getFirstAvailableDay(offset > 0 ? 1 : -1);
        if (startElem === undefined) {
            self._input.focus();
        }
        else if (!dayFocused) {
            focusOnDayElem(startElem);
        }
        else {
            getNextAvailableDay(startElem, offset);
        }
    }
    function buildMonthDays(year, month) {
        var firstOfMonth = (new Date(year, month, 1).getDay() - self.l10n.firstDayOfWeek + 7) % 7;
        var prevMonthDays = self.utils.getDaysInMonth((month - 1 + 12) % 12, year);
        var daysInMonth = self.utils.getDaysInMonth(month, year), days = window.document.createDocumentFragment(), isMultiMonth = self.config.showMonths > 1, prevMonthDayClass = isMultiMonth ? "prevMonthDay hidden" : "prevMonthDay", nextMonthDayClass = isMultiMonth ? "nextMonthDay hidden" : "nextMonthDay";
        var dayNumber = prevMonthDays + 1 - firstOfMonth, dayIndex = 0;
        for (; dayNumber <= prevMonthDays; dayNumber++, dayIndex++) {
            days.appendChild(createDay("flatpickr-day " + prevMonthDayClass, new Date(year, month - 1, dayNumber), dayNumber, dayIndex));
        }
        for (dayNumber = 1; dayNumber <= daysInMonth; dayNumber++, dayIndex++) {
            days.appendChild(createDay("flatpickr-day", new Date(year, month, dayNumber), dayNumber, dayIndex));
        }
        for (var dayNum = daysInMonth + 1; dayNum <= 42 - firstOfMonth &&
            (self.config.showMonths === 1 || dayIndex % 7 !== 0); dayNum++, dayIndex++) {
            days.appendChild(createDay("flatpickr-day " + nextMonthDayClass, new Date(year, month + 1, dayNum % daysInMonth), dayNum, dayIndex));
        }
        var dayContainer = createElement("div", "dayContainer");
        dayContainer.appendChild(days);
        return dayContainer;
    }
    function buildDays() {
        if (self.daysContainer === undefined) {
            return;
        }
        clearNode(self.daysContainer);
        if (self.weekNumbers)
            clearNode(self.weekNumbers);
        var frag = document.createDocumentFragment();
        for (var i = 0; i < self.config.showMonths; i++) {
            var d = new Date(self.currentYear, self.currentMonth, 1);
            d.setMonth(self.currentMonth + i);
            frag.appendChild(buildMonthDays(d.getFullYear(), d.getMonth()));
        }
        self.daysContainer.appendChild(frag);
        self.days = self.daysContainer.firstChild;
        if (self.config.mode === "range" && self.selectedDates.length === 1) {
            onMouseOver();
        }
    }
    function buildMonthSwitch() {
        if (self.config.showMonths > 1 ||
            self.config.monthSelectorType !== "dropdown")
            return;
        var shouldBuildMonth = function (month) {
            if (self.config.minDate !== undefined &&
                self.currentYear === self.config.minDate.getFullYear() &&
                month < self.config.minDate.getMonth()) {
                return false;
            }
            return !(self.config.maxDate !== undefined &&
                self.currentYear === self.config.maxDate.getFullYear() &&
                month > self.config.maxDate.getMonth());
        };
        self.monthsDropdownContainer.tabIndex = -1;
        self.monthsDropdownContainer.innerHTML = "";
        for (var i = 0; i < 12; i++) {
            if (!shouldBuildMonth(i))
                continue;
            var month = createElement("option", "flatpickr-monthDropdown-month");
            month.value = new Date(self.currentYear, i).getMonth().toString();
            month.textContent = monthToStr(i, self.config.shorthandCurrentMonth, self.l10n);
            month.tabIndex = -1;
            if (self.currentMonth === i) {
                month.selected = true;
            }
            self.monthsDropdownContainer.appendChild(month);
        }
    }
    function buildMonth() {
        var container = createElement("div", "flatpickr-month");
        var monthNavFragment = window.document.createDocumentFragment();
        var monthElement;
        if (self.config.showMonths > 1 ||
            self.config.monthSelectorType === "static") {
            monthElement = createElement("span", "cur-month");
        }
        else {
            self.monthsDropdownContainer = createElement("select", "flatpickr-monthDropdown-months");
            self.monthsDropdownContainer.setAttribute("aria-label", self.l10n.monthAriaLabel);
            bind(self.monthsDropdownContainer, "change", function (e) {
                var target = getEventTarget(e);
                var selectedMonth = parseInt(target.value, 10);
                self.changeMonth(selectedMonth - self.currentMonth);
                triggerEvent("onMonthChange");
            });
            buildMonthSwitch();
            monthElement = self.monthsDropdownContainer;
        }
        var yearInput = createNumberInput("cur-year", { tabindex: "-1" });
        var yearElement = yearInput.getElementsByTagName("input")[0];
        yearElement.setAttribute("aria-label", self.l10n.yearAriaLabel);
        if (self.config.minDate) {
            yearElement.setAttribute("min", self.config.minDate.getFullYear().toString());
        }
        if (self.config.maxDate) {
            yearElement.setAttribute("max", self.config.maxDate.getFullYear().toString());
            yearElement.disabled =
                !!self.config.minDate &&
                    self.config.minDate.getFullYear() === self.config.maxDate.getFullYear();
        }
        var currentMonth = createElement("div", "flatpickr-current-month");
        currentMonth.appendChild(monthElement);
        currentMonth.appendChild(yearInput);
        monthNavFragment.appendChild(currentMonth);
        container.appendChild(monthNavFragment);
        return {
            container: container,
            yearElement: yearElement,
            monthElement: monthElement,
        };
    }
    function buildMonths() {
        clearNode(self.monthNav);
        self.monthNav.appendChild(self.prevMonthNav);
        if (self.config.showMonths) {
            self.yearElements = [];
            self.monthElements = [];
        }
        for (var m = self.config.showMonths; m--;) {
            var month = buildMonth();
            self.yearElements.push(month.yearElement);
            self.monthElements.push(month.monthElement);
            self.monthNav.appendChild(month.container);
        }
        self.monthNav.appendChild(self.nextMonthNav);
    }
    function buildMonthNav() {
        self.monthNav = createElement("div", "flatpickr-months");
        self.yearElements = [];
        self.monthElements = [];
        self.prevMonthNav = createElement("span", "flatpickr-prev-month");
        self.prevMonthNav.innerHTML = self.config.prevArrow;
        self.nextMonthNav = createElement("span", "flatpickr-next-month");
        self.nextMonthNav.innerHTML = self.config.nextArrow;
        buildMonths();
        Object.defineProperty(self, "_hidePrevMonthArrow", {
            get: function () { return self.__hidePrevMonthArrow; },
            set: function (bool) {
                if (self.__hidePrevMonthArrow !== bool) {
                    toggleClass(self.prevMonthNav, "flatpickr-disabled", bool);
                    self.__hidePrevMonthArrow = bool;
                }
            },
        });
        Object.defineProperty(self, "_hideNextMonthArrow", {
            get: function () { return self.__hideNextMonthArrow; },
            set: function (bool) {
                if (self.__hideNextMonthArrow !== bool) {
                    toggleClass(self.nextMonthNav, "flatpickr-disabled", bool);
                    self.__hideNextMonthArrow = bool;
                }
            },
        });
        self.currentYearElement = self.yearElements[0];
        updateNavigationCurrentMonth();
        return self.monthNav;
    }
    function buildTime() {
        self.calendarContainer.classList.add("hasTime");
        if (self.config.noCalendar)
            self.calendarContainer.classList.add("noCalendar");
        var defaults = getDefaultHours(self.config);
        self.timeContainer = createElement("div", "flatpickr-time");
        self.timeContainer.tabIndex = -1;
        var separator = createElement("span", "flatpickr-time-separator", ":");
        var hourInput = createNumberInput("flatpickr-hour", {
            "aria-label": self.l10n.hourAriaLabel,
        });
        self.hourElement = hourInput.getElementsByTagName("input")[0];
        var minuteInput = createNumberInput("flatpickr-minute", {
            "aria-label": self.l10n.minuteAriaLabel,
        });
        self.minuteElement = minuteInput.getElementsByTagName("input")[0];
        self.hourElement.tabIndex = self.minuteElement.tabIndex = -1;
        self.hourElement.value = pad(self.latestSelectedDateObj
            ? self.latestSelectedDateObj.getHours()
            : self.config.time_24hr
                ? defaults.hours
                : military2ampm(defaults.hours));
        self.minuteElement.value = pad(self.latestSelectedDateObj
            ? self.latestSelectedDateObj.getMinutes()
            : defaults.minutes);
        self.hourElement.setAttribute("step", self.config.hourIncrement.toString());
        self.minuteElement.setAttribute("step", self.config.minuteIncrement.toString());
        self.hourElement.setAttribute("min", self.config.time_24hr ? "0" : "1");
        self.hourElement.setAttribute("max", self.config.time_24hr ? "23" : "12");
        self.hourElement.setAttribute("maxlength", "2");
        self.minuteElement.setAttribute("min", "0");
        self.minuteElement.setAttribute("max", "59");
        self.minuteElement.setAttribute("maxlength", "2");
        self.timeContainer.appendChild(hourInput);
        self.timeContainer.appendChild(separator);
        self.timeContainer.appendChild(minuteInput);
        if (self.config.time_24hr)
            self.timeContainer.classList.add("time24hr");
        if (self.config.enableSeconds) {
            self.timeContainer.classList.add("hasSeconds");
            var secondInput = createNumberInput("flatpickr-second");
            self.secondElement = secondInput.getElementsByTagName("input")[0];
            self.secondElement.value = pad(self.latestSelectedDateObj
                ? self.latestSelectedDateObj.getSeconds()
                : defaults.seconds);
            self.secondElement.setAttribute("step", self.minuteElement.getAttribute("step"));
            self.secondElement.setAttribute("min", "0");
            self.secondElement.setAttribute("max", "59");
            self.secondElement.setAttribute("maxlength", "2");
            self.timeContainer.appendChild(createElement("span", "flatpickr-time-separator", ":"));
            self.timeContainer.appendChild(secondInput);
        }
        if (!self.config.time_24hr) {
            self.amPM = createElement("span", "flatpickr-am-pm", self.l10n.amPM[int((self.latestSelectedDateObj
                ? self.hourElement.value
                : self.config.defaultHour) > 11)]);
            self.amPM.title = self.l10n.toggleTitle;
            self.amPM.tabIndex = -1;
            self.timeContainer.appendChild(self.amPM);
        }
        return self.timeContainer;
    }
    function buildWeekdays() {
        if (!self.weekdayContainer)
            self.weekdayContainer = createElement("div", "flatpickr-weekdays");
        else
            clearNode(self.weekdayContainer);
        for (var i = self.config.showMonths; i--;) {
            var container = createElement("div", "flatpickr-weekdaycontainer");
            self.weekdayContainer.appendChild(container);
        }
        updateWeekdays();
        return self.weekdayContainer;
    }
    function updateWeekdays() {
        if (!self.weekdayContainer) {
            return;
        }
        var firstDayOfWeek = self.l10n.firstDayOfWeek;
        var weekdays = __spreadArrays(self.l10n.weekdays.shorthand);
        if (firstDayOfWeek > 0 && firstDayOfWeek < weekdays.length) {
            weekdays = __spreadArrays(weekdays.splice(firstDayOfWeek, weekdays.length), weekdays.splice(0, firstDayOfWeek));
        }
        for (var i = self.config.showMonths; i--;) {
            self.weekdayContainer.children[i].innerHTML = "\n      <span class='flatpickr-weekday'>\n        " + weekdays.join("</span><span class='flatpickr-weekday'>") + "\n      </span>\n      ";
        }
    }
    function buildWeeks() {
        self.calendarContainer.classList.add("hasWeeks");
        var weekWrapper = createElement("div", "flatpickr-weekwrapper");
        weekWrapper.appendChild(createElement("span", "flatpickr-weekday", self.l10n.weekAbbreviation));
        var weekNumbers = createElement("div", "flatpickr-weeks");
        weekWrapper.appendChild(weekNumbers);
        return {
            weekWrapper: weekWrapper,
            weekNumbers: weekNumbers,
        };
    }
    function changeMonth(value, isOffset) {
        if (isOffset === void 0) { isOffset = true; }
        var delta = isOffset ? value : value - self.currentMonth;
        if ((delta < 0 && self._hidePrevMonthArrow === true) ||
            (delta > 0 && self._hideNextMonthArrow === true))
            return;
        self.currentMonth += delta;
        if (self.currentMonth < 0 || self.currentMonth > 11) {
            self.currentYear += self.currentMonth > 11 ? 1 : -1;
            self.currentMonth = (self.currentMonth + 12) % 12;
            triggerEvent("onYearChange");
            buildMonthSwitch();
        }
        buildDays();
        triggerEvent("onMonthChange");
        updateNavigationCurrentMonth();
    }
    function clear(triggerChangeEvent, toInitial) {
        if (triggerChangeEvent === void 0) { triggerChangeEvent = true; }
        if (toInitial === void 0) { toInitial = true; }
        self.input.value = "";
        if (self.altInput !== undefined)
            self.altInput.value = "";
        if (self.mobileInput !== undefined)
            self.mobileInput.value = "";
        self.selectedDates = [];
        self.latestSelectedDateObj = undefined;
        if (toInitial === true) {
            self.currentYear = self._initialDate.getFullYear();
            self.currentMonth = self._initialDate.getMonth();
        }
        if (self.config.enableTime === true) {
            var _a = getDefaultHours(self.config), hours = _a.hours, minutes = _a.minutes, seconds = _a.seconds;
            setHours(hours, minutes, seconds);
        }
        self.redraw();
        if (triggerChangeEvent)
            triggerEvent("onChange");
    }
    function close() {
        self.isOpen = false;
        if (!self.isMobile) {
            if (self.calendarContainer !== undefined) {
                self.calendarContainer.classList.remove("open");
            }
            if (self._input !== undefined) {
                self._input.classList.remove("active");
            }
        }
        triggerEvent("onClose");
    }
    function destroy() {
        if (self.config !== undefined)
            triggerEvent("onDestroy");
        for (var i = self._handlers.length; i--;) {
            self._handlers[i].remove();
        }
        self._handlers = [];
        if (self.mobileInput) {
            if (self.mobileInput.parentNode)
                self.mobileInput.parentNode.removeChild(self.mobileInput);
            self.mobileInput = undefined;
        }
        else if (self.calendarContainer && self.calendarContainer.parentNode) {
            if (self.config.static && self.calendarContainer.parentNode) {
                var wrapper = self.calendarContainer.parentNode;
                wrapper.lastChild && wrapper.removeChild(wrapper.lastChild);
                if (wrapper.parentNode) {
                    while (wrapper.firstChild)
                        wrapper.parentNode.insertBefore(wrapper.firstChild, wrapper);
                    wrapper.parentNode.removeChild(wrapper);
                }
            }
            else
                self.calendarContainer.parentNode.removeChild(self.calendarContainer);
        }
        if (self.altInput) {
            self.input.type = "text";
            if (self.altInput.parentNode)
                self.altInput.parentNode.removeChild(self.altInput);
            delete self.altInput;
        }
        if (self.input) {
            self.input.type = self.input._type;
            self.input.classList.remove("flatpickr-input");
            self.input.removeAttribute("readonly");
        }
        [
            "_showTimeInput",
            "latestSelectedDateObj",
            "_hideNextMonthArrow",
            "_hidePrevMonthArrow",
            "__hideNextMonthArrow",
            "__hidePrevMonthArrow",
            "isMobile",
            "isOpen",
            "selectedDateElem",
            "minDateHasTime",
            "maxDateHasTime",
            "days",
            "daysContainer",
            "_input",
            "_positionElement",
            "innerContainer",
            "rContainer",
            "monthNav",
            "todayDateElem",
            "calendarContainer",
            "weekdayContainer",
            "prevMonthNav",
            "nextMonthNav",
            "monthsDropdownContainer",
            "currentMonthElement",
            "currentYearElement",
            "navigationCurrentMonth",
            "selectedDateElem",
            "config",
        ].forEach(function (k) {
            try {
                delete self[k];
            }
            catch (_) { }
        });
    }
    function isCalendarElem(elem) {
        return self.calendarContainer.contains(elem);
    }
    function documentClick(e) {
        if (self.isOpen && !self.config.inline) {
            var eventTarget_1 = getEventTarget(e);
            var isCalendarElement = isCalendarElem(eventTarget_1);
            var isInput = eventTarget_1 === self.input ||
                eventTarget_1 === self.altInput ||
                self.element.contains(eventTarget_1) ||
                (e.path &&
                    e.path.indexOf &&
                    (~e.path.indexOf(self.input) ||
                        ~e.path.indexOf(self.altInput)));
            var lostFocus = !isInput &&
                !isCalendarElement &&
                !isCalendarElem(e.relatedTarget);
            var isIgnored = !self.config.ignoredFocusElements.some(function (elem) {
                return elem.contains(eventTarget_1);
            });
            if (lostFocus && isIgnored) {
                if (self.config.allowInput) {
                    self.setDate(self._input.value, false, self.config.altInput
                        ? self.config.altFormat
                        : self.config.dateFormat);
                }
                if (self.timeContainer !== undefined &&
                    self.minuteElement !== undefined &&
                    self.hourElement !== undefined &&
                    self.input.value !== "" &&
                    self.input.value !== undefined) {
                    updateTime();
                }
                self.close();
                if (self.config &&
                    self.config.mode === "range" &&
                    self.selectedDates.length === 1)
                    self.clear(false);
            }
        }
    }
    function changeYear(newYear) {
        if (!newYear ||
            (self.config.minDate && newYear < self.config.minDate.getFullYear()) ||
            (self.config.maxDate && newYear > self.config.maxDate.getFullYear()))
            return;
        var newYearNum = newYear, isNewYear = self.currentYear !== newYearNum;
        self.currentYear = newYearNum || self.currentYear;
        if (self.config.maxDate &&
            self.currentYear === self.config.maxDate.getFullYear()) {
            self.currentMonth = Math.min(self.config.maxDate.getMonth(), self.currentMonth);
        }
        else if (self.config.minDate &&
            self.currentYear === self.config.minDate.getFullYear()) {
            self.currentMonth = Math.max(self.config.minDate.getMonth(), self.currentMonth);
        }
        if (isNewYear) {
            self.redraw();
            triggerEvent("onYearChange");
            buildMonthSwitch();
        }
    }
    function isEnabled(date, timeless) {
        var _a;
        if (timeless === void 0) { timeless = true; }
        var dateToCheck = self.parseDate(date, undefined, timeless);
        if ((self.config.minDate &&
            dateToCheck &&
            compareDates(dateToCheck, self.config.minDate, timeless !== undefined ? timeless : !self.minDateHasTime) < 0) ||
            (self.config.maxDate &&
                dateToCheck &&
                compareDates(dateToCheck, self.config.maxDate, timeless !== undefined ? timeless : !self.maxDateHasTime) > 0))
            return false;
        if (!self.config.enable && self.config.disable.length === 0)
            return true;
        if (dateToCheck === undefined)
            return false;
        var bool = !!self.config.enable, array = (_a = self.config.enable) !== null && _a !== void 0 ? _a : self.config.disable;
        for (var i = 0, d = void 0; i < array.length; i++) {
            d = array[i];
            if (typeof d === "function" &&
                d(dateToCheck))
                return bool;
            else if (d instanceof Date &&
                dateToCheck !== undefined &&
                d.getTime() === dateToCheck.getTime())
                return bool;
            else if (typeof d === "string") {
                var parsed = self.parseDate(d, undefined, true);
                return parsed && parsed.getTime() === dateToCheck.getTime()
                    ? bool
                    : !bool;
            }
            else if (typeof d === "object" &&
                dateToCheck !== undefined &&
                d.from &&
                d.to &&
                dateToCheck.getTime() >= d.from.getTime() &&
                dateToCheck.getTime() <= d.to.getTime())
                return bool;
        }
        return !bool;
    }
    function isInView(elem) {
        if (self.daysContainer !== undefined)
            return (elem.className.indexOf("hidden") === -1 &&
                elem.className.indexOf("flatpickr-disabled") === -1 &&
                self.daysContainer.contains(elem));
        return false;
    }
    function onBlur(e) {
        var isInput = e.target === self._input;
        var valueChanged = self._input.value.trimEnd() !== getDateStr();
        if (isInput &&
            valueChanged &&
            !(e.relatedTarget && isCalendarElem(e.relatedTarget))) {
            self.setDate(self._input.value, true, e.target === self.altInput
                ? self.config.altFormat
                : self.config.dateFormat);
        }
    }
    function onKeyDown(e) {
        var eventTarget = getEventTarget(e);
        var isInput = self.config.wrap
            ? element.contains(eventTarget)
            : eventTarget === self._input;
        var allowInput = self.config.allowInput;
        var allowKeydown = self.isOpen && (!allowInput || !isInput);
        var allowInlineKeydown = self.config.inline && isInput && !allowInput;
        if (e.keyCode === 13 && isInput) {
            if (allowInput) {
                self.setDate(self._input.value, true, eventTarget === self.altInput
                    ? self.config.altFormat
                    : self.config.dateFormat);
                self.close();
                return eventTarget.blur();
            }
            else {
                self.open();
            }
        }
        else if (isCalendarElem(eventTarget) ||
            allowKeydown ||
            allowInlineKeydown) {
            var isTimeObj = !!self.timeContainer &&
                self.timeContainer.contains(eventTarget);
            switch (e.keyCode) {
                case 13:
                    if (isTimeObj) {
                        e.preventDefault();
                        updateTime();
                        focusAndClose();
                    }
                    else
                        selectDate(e);
                    break;
                case 27:
                    e.preventDefault();
                    focusAndClose();
                    break;
                case 8:
                case 46:
                    if (isInput && !self.config.allowInput) {
                        e.preventDefault();
                        self.clear();
                    }
                    break;
                case 37:
                case 39:
                    if (!isTimeObj && !isInput) {
                        e.preventDefault();
                        var activeElement = getClosestActiveElement();
                        if (self.daysContainer !== undefined &&
                            (allowInput === false ||
                                (activeElement && isInView(activeElement)))) {
                            var delta_1 = e.keyCode === 39 ? 1 : -1;
                            if (!e.ctrlKey)
                                focusOnDay(undefined, delta_1);
                            else {
                                e.stopPropagation();
                                changeMonth(delta_1);
                                focusOnDay(getFirstAvailableDay(1), 0);
                            }
                        }
                    }
                    else if (self.hourElement)
                        self.hourElement.focus();
                    break;
                case 38:
                case 40:
                    e.preventDefault();
                    var delta = e.keyCode === 40 ? 1 : -1;
                    if ((self.daysContainer &&
                        eventTarget.$i !== undefined) ||
                        eventTarget === self.input ||
                        eventTarget === self.altInput) {
                        if (e.ctrlKey) {
                            e.stopPropagation();
                            changeYear(self.currentYear - delta);
                            focusOnDay(getFirstAvailableDay(1), 0);
                        }
                        else if (!isTimeObj)
                            focusOnDay(undefined, delta * 7);
                    }
                    else if (eventTarget === self.currentYearElement) {
                        changeYear(self.currentYear - delta);
                    }
                    else if (self.config.enableTime) {
                        if (!isTimeObj && self.hourElement)
                            self.hourElement.focus();
                        updateTime(e);
                        self._debouncedChange();
                    }
                    break;
                case 9:
                    if (isTimeObj) {
                        var elems = [
                            self.hourElement,
                            self.minuteElement,
                            self.secondElement,
                            self.amPM,
                        ]
                            .concat(self.pluginElements)
                            .filter(function (x) { return x; });
                        var i = elems.indexOf(eventTarget);
                        if (i !== -1) {
                            var target = elems[i + (e.shiftKey ? -1 : 1)];
                            e.preventDefault();
                            (target || self._input).focus();
                        }
                    }
                    else if (!self.config.noCalendar &&
                        self.daysContainer &&
                        self.daysContainer.contains(eventTarget) &&
                        e.shiftKey) {
                        e.preventDefault();
                        self._input.focus();
                    }
                    break;
            }
        }
        if (self.amPM !== undefined && eventTarget === self.amPM) {
            switch (e.key) {
                case self.l10n.amPM[0].charAt(0):
                case self.l10n.amPM[0].charAt(0).toLowerCase():
                    self.amPM.textContent = self.l10n.amPM[0];
                    setHoursFromInputs();
                    updateValue();
                    break;
                case self.l10n.amPM[1].charAt(0):
                case self.l10n.amPM[1].charAt(0).toLowerCase():
                    self.amPM.textContent = self.l10n.amPM[1];
                    setHoursFromInputs();
                    updateValue();
                    break;
            }
        }
        if (isInput || isCalendarElem(eventTarget)) {
            triggerEvent("onKeyDown", e);
        }
    }
    function onMouseOver(elem, cellClass) {
        if (cellClass === void 0) { cellClass = "flatpickr-day"; }
        if (self.selectedDates.length !== 1 ||
            (elem &&
                (!elem.classList.contains(cellClass) ||
                    elem.classList.contains("flatpickr-disabled"))))
            return;
        var hoverDate = elem
            ? elem.dateObj.getTime()
            : self.days.firstElementChild.dateObj.getTime(), initialDate = self.parseDate(self.selectedDates[0], undefined, true).getTime(), rangeStartDate = Math.min(hoverDate, self.selectedDates[0].getTime()), rangeEndDate = Math.max(hoverDate, self.selectedDates[0].getTime());
        var containsDisabled = false;
        var minRange = 0, maxRange = 0;
        for (var t = rangeStartDate; t < rangeEndDate; t += duration.DAY) {
            if (!isEnabled(new Date(t), true)) {
                containsDisabled =
                    containsDisabled || (t > rangeStartDate && t < rangeEndDate);
                if (t < initialDate && (!minRange || t > minRange))
                    minRange = t;
                else if (t > initialDate && (!maxRange || t < maxRange))
                    maxRange = t;
            }
        }
        var hoverableCells = Array.from(self.rContainer.querySelectorAll("*:nth-child(-n+" + self.config.showMonths + ") > ." + cellClass));
        hoverableCells.forEach(function (dayElem) {
            var date = dayElem.dateObj;
            var timestamp = date.getTime();
            var outOfRange = (minRange > 0 && timestamp < minRange) ||
                (maxRange > 0 && timestamp > maxRange);
            if (outOfRange) {
                dayElem.classList.add("notAllowed");
                ["inRange", "startRange", "endRange"].forEach(function (c) {
                    dayElem.classList.remove(c);
                });
                return;
            }
            else if (containsDisabled && !outOfRange)
                return;
            ["startRange", "inRange", "endRange", "notAllowed"].forEach(function (c) {
                dayElem.classList.remove(c);
            });
            if (elem !== undefined) {
                elem.classList.add(hoverDate <= self.selectedDates[0].getTime()
                    ? "startRange"
                    : "endRange");
                if (initialDate < hoverDate && timestamp === initialDate)
                    dayElem.classList.add("startRange");
                else if (initialDate > hoverDate && timestamp === initialDate)
                    dayElem.classList.add("endRange");
                if (timestamp >= minRange &&
                    (maxRange === 0 || timestamp <= maxRange) &&
                    isBetween(timestamp, initialDate, hoverDate))
                    dayElem.classList.add("inRange");
            }
        });
    }
    function onResize() {
        if (self.isOpen && !self.config.static && !self.config.inline)
            positionCalendar();
    }
    function open(e, positionElement) {
        if (positionElement === void 0) { positionElement = self._positionElement; }
        if (self.isMobile === true) {
            if (e) {
                e.preventDefault();
                var eventTarget = getEventTarget(e);
                if (eventTarget) {
                    eventTarget.blur();
                }
            }
            if (self.mobileInput !== undefined) {
                self.mobileInput.focus();
                self.mobileInput.click();
            }
            triggerEvent("onOpen");
            return;
        }
        else if (self._input.disabled || self.config.inline) {
            return;
        }
        var wasOpen = self.isOpen;
        self.isOpen = true;
        if (!wasOpen) {
            self.calendarContainer.classList.add("open");
            self._input.classList.add("active");
            triggerEvent("onOpen");
            positionCalendar(positionElement);
        }
        if (self.config.enableTime === true && self.config.noCalendar === true) {
            if (self.config.allowInput === false &&
                (e === undefined ||
                    !self.timeContainer.contains(e.relatedTarget))) {
                setTimeout(function () { return self.hourElement.select(); }, 50);
            }
        }
    }
    function minMaxDateSetter(type) {
        return function (date) {
            var dateObj = (self.config["_" + type + "Date"] = self.parseDate(date, self.config.dateFormat));
            var inverseDateObj = self.config["_" + (type === "min" ? "max" : "min") + "Date"];
            if (dateObj !== undefined) {
                self[type === "min" ? "minDateHasTime" : "maxDateHasTime"] =
                    dateObj.getHours() > 0 ||
                        dateObj.getMinutes() > 0 ||
                        dateObj.getSeconds() > 0;
            }
            if (self.selectedDates) {
                self.selectedDates = self.selectedDates.filter(function (d) { return isEnabled(d); });
                if (!self.selectedDates.length && type === "min")
                    setHoursFromDate(dateObj);
                updateValue();
            }
            if (self.daysContainer) {
                redraw();
                if (dateObj !== undefined)
                    self.currentYearElement[type] = dateObj.getFullYear().toString();
                else
                    self.currentYearElement.removeAttribute(type);
                self.currentYearElement.disabled =
                    !!inverseDateObj &&
                        dateObj !== undefined &&
                        inverseDateObj.getFullYear() === dateObj.getFullYear();
            }
        };
    }
    function parseConfig() {
        var boolOpts = [
            "wrap",
            "weekNumbers",
            "allowInput",
            "allowInvalidPreload",
            "clickOpens",
            "time_24hr",
            "enableTime",
            "noCalendar",
            "altInput",
            "shorthandCurrentMonth",
            "inline",
            "static",
            "enableSeconds",
            "disableMobile",
        ];
        var userConfig = __assign(__assign({}, JSON.parse(JSON.stringify(element.dataset || {}))), instanceConfig);
        var formats = {};
        self.config.parseDate = userConfig.parseDate;
        self.config.formatDate = userConfig.formatDate;
        Object.defineProperty(self.config, "enable", {
            get: function () { return self.config._enable; },
            set: function (dates) {
                self.config._enable = parseDateRules(dates);
            },
        });
        Object.defineProperty(self.config, "disable", {
            get: function () { return self.config._disable; },
            set: function (dates) {
                self.config._disable = parseDateRules(dates);
            },
        });
        var timeMode = userConfig.mode === "time";
        if (!userConfig.dateFormat && (userConfig.enableTime || timeMode)) {
            var defaultDateFormat = flatpickr.defaultConfig.dateFormat || defaults.dateFormat;
            formats.dateFormat =
                userConfig.noCalendar || timeMode
                    ? "H:i" + (userConfig.enableSeconds ? ":S" : "")
                    : defaultDateFormat + " H:i" + (userConfig.enableSeconds ? ":S" : "");
        }
        if (userConfig.altInput &&
            (userConfig.enableTime || timeMode) &&
            !userConfig.altFormat) {
            var defaultAltFormat = flatpickr.defaultConfig.altFormat || defaults.altFormat;
            formats.altFormat =
                userConfig.noCalendar || timeMode
                    ? "h:i" + (userConfig.enableSeconds ? ":S K" : " K")
                    : defaultAltFormat + (" h:i" + (userConfig.enableSeconds ? ":S" : "") + " K");
        }
        Object.defineProperty(self.config, "minDate", {
            get: function () { return self.config._minDate; },
            set: minMaxDateSetter("min"),
        });
        Object.defineProperty(self.config, "maxDate", {
            get: function () { return self.config._maxDate; },
            set: minMaxDateSetter("max"),
        });
        var minMaxTimeSetter = function (type) { return function (val) {
            self.config[type === "min" ? "_minTime" : "_maxTime"] = self.parseDate(val, "H:i:S");
        }; };
        Object.defineProperty(self.config, "minTime", {
            get: function () { return self.config._minTime; },
            set: minMaxTimeSetter("min"),
        });
        Object.defineProperty(self.config, "maxTime", {
            get: function () { return self.config._maxTime; },
            set: minMaxTimeSetter("max"),
        });
        if (userConfig.mode === "time") {
            self.config.noCalendar = true;
            self.config.enableTime = true;
        }
        Object.assign(self.config, formats, userConfig);
        for (var i = 0; i < boolOpts.length; i++)
            self.config[boolOpts[i]] =
                self.config[boolOpts[i]] === true ||
                    self.config[boolOpts[i]] === "true";
        HOOKS.filter(function (hook) { return self.config[hook] !== undefined; }).forEach(function (hook) {
            self.config[hook] = arrayify(self.config[hook] || []).map(bindToInstance);
        });
        self.isMobile =
            !self.config.disableMobile &&
                !self.config.inline &&
                self.config.mode === "single" &&
                !self.config.disable.length &&
                !self.config.enable &&
                !self.config.weekNumbers &&
                /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        for (var i = 0; i < self.config.plugins.length; i++) {
            var pluginConf = self.config.plugins[i](self) || {};
            for (var key in pluginConf) {
                if (HOOKS.indexOf(key) > -1) {
                    self.config[key] = arrayify(pluginConf[key])
                        .map(bindToInstance)
                        .concat(self.config[key]);
                }
                else if (typeof userConfig[key] === "undefined")
                    self.config[key] = pluginConf[key];
            }
        }
        if (!userConfig.altInputClass) {
            self.config.altInputClass =
                getInputElem().className + " " + self.config.altInputClass;
        }
        triggerEvent("onParseConfig");
    }
    function getInputElem() {
        return self.config.wrap
            ? element.querySelector("[data-input]")
            : element;
    }
    function setupLocale() {
        if (typeof self.config.locale !== "object" &&
            typeof flatpickr.l10ns[self.config.locale] === "undefined")
            self.config.errorHandler(new Error("flatpickr: invalid locale " + self.config.locale));
        self.l10n = __assign(__assign({}, flatpickr.l10ns.default), (typeof self.config.locale === "object"
            ? self.config.locale
            : self.config.locale !== "default"
                ? flatpickr.l10ns[self.config.locale]
                : undefined));
        tokenRegex.D = "(" + self.l10n.weekdays.shorthand.join("|") + ")";
        tokenRegex.l = "(" + self.l10n.weekdays.longhand.join("|") + ")";
        tokenRegex.M = "(" + self.l10n.months.shorthand.join("|") + ")";
        tokenRegex.F = "(" + self.l10n.months.longhand.join("|") + ")";
        tokenRegex.K = "(" + self.l10n.amPM[0] + "|" + self.l10n.amPM[1] + "|" + self.l10n.amPM[0].toLowerCase() + "|" + self.l10n.amPM[1].toLowerCase() + ")";
        var userConfig = __assign(__assign({}, instanceConfig), JSON.parse(JSON.stringify(element.dataset || {})));
        if (userConfig.time_24hr === undefined &&
            flatpickr.defaultConfig.time_24hr === undefined) {
            self.config.time_24hr = self.l10n.time_24hr;
        }
        self.formatDate = createDateFormatter(self);
        self.parseDate = createDateParser({ config: self.config, l10n: self.l10n });
    }
    function positionCalendar(customPositionElement) {
        if (typeof self.config.position === "function") {
            return void self.config.position(self, customPositionElement);
        }
        if (self.calendarContainer === undefined)
            return;
        triggerEvent("onPreCalendarPosition");
        var positionElement = customPositionElement || self._positionElement;
        var calendarHeight = Array.prototype.reduce.call(self.calendarContainer.children, (function (acc, child) { return acc + child.offsetHeight; }), 0), calendarWidth = self.calendarContainer.offsetWidth, configPos = self.config.position.split(" "), configPosVertical = configPos[0], configPosHorizontal = configPos.length > 1 ? configPos[1] : null, inputBounds = positionElement.getBoundingClientRect(), distanceFromBottom = window.innerHeight - inputBounds.bottom, showOnTop = configPosVertical === "above" ||
            (configPosVertical !== "below" &&
                distanceFromBottom < calendarHeight &&
                inputBounds.top > calendarHeight);
        var top = window.pageYOffset +
            inputBounds.top +
            (!showOnTop ? positionElement.offsetHeight + 2 : -calendarHeight - 2);
        toggleClass(self.calendarContainer, "arrowTop", !showOnTop);
        toggleClass(self.calendarContainer, "arrowBottom", showOnTop);
        if (self.config.inline)
            return;
        var left = window.pageXOffset + inputBounds.left;
        var isCenter = false;
        var isRight = false;
        if (configPosHorizontal === "center") {
            left -= (calendarWidth - inputBounds.width) / 2;
            isCenter = true;
        }
        else if (configPosHorizontal === "right") {
            left -= calendarWidth - inputBounds.width;
            isRight = true;
        }
        toggleClass(self.calendarContainer, "arrowLeft", !isCenter && !isRight);
        toggleClass(self.calendarContainer, "arrowCenter", isCenter);
        toggleClass(self.calendarContainer, "arrowRight", isRight);
        var right = window.document.body.offsetWidth -
            (window.pageXOffset + inputBounds.right);
        var rightMost = left + calendarWidth > window.document.body.offsetWidth;
        var centerMost = right + calendarWidth > window.document.body.offsetWidth;
        toggleClass(self.calendarContainer, "rightMost", rightMost);
        if (self.config.static)
            return;
        self.calendarContainer.style.top = top + "px";
        if (!rightMost) {
            self.calendarContainer.style.left = left + "px";
            self.calendarContainer.style.right = "auto";
        }
        else if (!centerMost) {
            self.calendarContainer.style.left = "auto";
            self.calendarContainer.style.right = right + "px";
        }
        else {
            var doc = getDocumentStyleSheet();
            if (doc === undefined)
                return;
            var bodyWidth = window.document.body.offsetWidth;
            var centerLeft = Math.max(0, bodyWidth / 2 - calendarWidth / 2);
            var centerBefore = ".flatpickr-calendar.centerMost:before";
            var centerAfter = ".flatpickr-calendar.centerMost:after";
            var centerIndex = doc.cssRules.length;
            var centerStyle = "{left:" + inputBounds.left + "px;right:auto;}";
            toggleClass(self.calendarContainer, "rightMost", false);
            toggleClass(self.calendarContainer, "centerMost", true);
            doc.insertRule(centerBefore + "," + centerAfter + centerStyle, centerIndex);
            self.calendarContainer.style.left = centerLeft + "px";
            self.calendarContainer.style.right = "auto";
        }
    }
    function getDocumentStyleSheet() {
        var editableSheet = null;
        for (var i = 0; i < document.styleSheets.length; i++) {
            var sheet = document.styleSheets[i];
            if (!sheet.cssRules)
                continue;
            editableSheet = sheet;
            break;
        }
        return editableSheet != null ? editableSheet : createStyleSheet();
    }
    function createStyleSheet() {
        var style = document.createElement("style");
        document.head.appendChild(style);
        return style.sheet;
    }
    function redraw() {
        if (self.config.noCalendar || self.isMobile)
            return;
        buildMonthSwitch();
        updateNavigationCurrentMonth();
        buildDays();
    }
    function focusAndClose() {
        self._input.focus();
        if (window.navigator.userAgent.indexOf("MSIE") !== -1 ||
            navigator.msMaxTouchPoints !== undefined) {
            setTimeout(self.close, 0);
        }
        else {
            self.close();
        }
    }
    function selectDate(e) {
        e.preventDefault();
        e.stopPropagation();
        var isSelectable = function (day) {
            return day.classList &&
                day.classList.contains("flatpickr-day") &&
                !day.classList.contains("flatpickr-disabled") &&
                !day.classList.contains("notAllowed");
        };
        var t = findParent(getEventTarget(e), isSelectable);
        if (t === undefined)
            return;
        var target = t;
        var selectedDate = (self.latestSelectedDateObj = new Date(target.dateObj.getTime()));
        var shouldChangeMonth = (selectedDate.getMonth() < self.currentMonth ||
            selectedDate.getMonth() >
                self.currentMonth + self.config.showMonths - 1) &&
            self.config.mode !== "range";
        self.selectedDateElem = target;
        if (self.config.mode === "single")
            self.selectedDates = [selectedDate];
        else if (self.config.mode === "multiple") {
            var selectedIndex = isDateSelected(selectedDate);
            if (selectedIndex)
                self.selectedDates.splice(parseInt(selectedIndex), 1);
            else
                self.selectedDates.push(selectedDate);
        }
        else if (self.config.mode === "range") {
            if (self.selectedDates.length === 2) {
                self.clear(false, false);
            }
            self.latestSelectedDateObj = selectedDate;
            self.selectedDates.push(selectedDate);
            if (compareDates(selectedDate, self.selectedDates[0], true) !== 0)
                self.selectedDates.sort(function (a, b) { return a.getTime() - b.getTime(); });
        }
        setHoursFromInputs();
        if (shouldChangeMonth) {
            var isNewYear = self.currentYear !== selectedDate.getFullYear();
            self.currentYear = selectedDate.getFullYear();
            self.currentMonth = selectedDate.getMonth();
            if (isNewYear) {
                triggerEvent("onYearChange");
                buildMonthSwitch();
            }
            triggerEvent("onMonthChange");
        }
        updateNavigationCurrentMonth();
        buildDays();
        updateValue();
        if (!shouldChangeMonth &&
            self.config.mode !== "range" &&
            self.config.showMonths === 1)
            focusOnDayElem(target);
        else if (self.selectedDateElem !== undefined &&
            self.hourElement === undefined) {
            self.selectedDateElem && self.selectedDateElem.focus();
        }
        if (self.hourElement !== undefined)
            self.hourElement !== undefined && self.hourElement.focus();
        if (self.config.closeOnSelect) {
            var single = self.config.mode === "single" && !self.config.enableTime;
            var range = self.config.mode === "range" &&
                self.selectedDates.length === 2 &&
                !self.config.enableTime;
            if (single || range) {
                focusAndClose();
            }
        }
        triggerChange();
    }
    var CALLBACKS = {
        locale: [setupLocale, updateWeekdays],
        showMonths: [buildMonths, setCalendarWidth, buildWeekdays],
        minDate: [jumpToDate],
        maxDate: [jumpToDate],
        positionElement: [updatePositionElement],
        clickOpens: [
            function () {
                if (self.config.clickOpens === true) {
                    bind(self._input, "focus", self.open);
                    bind(self._input, "click", self.open);
                }
                else {
                    self._input.removeEventListener("focus", self.open);
                    self._input.removeEventListener("click", self.open);
                }
            },
        ],
    };
    function set(option, value) {
        if (option !== null && typeof option === "object") {
            Object.assign(self.config, option);
            for (var key in option) {
                if (CALLBACKS[key] !== undefined)
                    CALLBACKS[key].forEach(function (x) { return x(); });
            }
        }
        else {
            self.config[option] = value;
            if (CALLBACKS[option] !== undefined)
                CALLBACKS[option].forEach(function (x) { return x(); });
            else if (HOOKS.indexOf(option) > -1)
                self.config[option] = arrayify(value);
        }
        self.redraw();
        updateValue(true);
    }
    function setSelectedDate(inputDate, format) {
        var dates = [];
        if (inputDate instanceof Array)
            dates = inputDate.map(function (d) { return self.parseDate(d, format); });
        else if (inputDate instanceof Date || typeof inputDate === "number")
            dates = [self.parseDate(inputDate, format)];
        else if (typeof inputDate === "string") {
            switch (self.config.mode) {
                case "single":
                case "time":
                    dates = [self.parseDate(inputDate, format)];
                    break;
                case "multiple":
                    dates = inputDate
                        .split(self.config.conjunction)
                        .map(function (date) { return self.parseDate(date, format); });
                    break;
                case "range":
                    dates = inputDate
                        .split(self.l10n.rangeSeparator)
                        .map(function (date) { return self.parseDate(date, format); });
                    break;
            }
        }
        else
            self.config.errorHandler(new Error("Invalid date supplied: " + JSON.stringify(inputDate)));
        self.selectedDates = (self.config.allowInvalidPreload
            ? dates
            : dates.filter(function (d) { return d instanceof Date && isEnabled(d, false); }));
        if (self.config.mode === "range")
            self.selectedDates.sort(function (a, b) { return a.getTime() - b.getTime(); });
    }
    function setDate(date, triggerChange, format) {
        if (triggerChange === void 0) { triggerChange = false; }
        if (format === void 0) { format = self.config.dateFormat; }
        if ((date !== 0 && !date) || (date instanceof Array && date.length === 0))
            return self.clear(triggerChange);
        setSelectedDate(date, format);
        self.latestSelectedDateObj =
            self.selectedDates[self.selectedDates.length - 1];
        self.redraw();
        jumpToDate(undefined, triggerChange);
        setHoursFromDate();
        if (self.selectedDates.length === 0) {
            self.clear(false);
        }
        updateValue(triggerChange);
        if (triggerChange)
            triggerEvent("onChange");
    }
    function parseDateRules(arr) {
        return arr
            .slice()
            .map(function (rule) {
            if (typeof rule === "string" ||
                typeof rule === "number" ||
                rule instanceof Date) {
                return self.parseDate(rule, undefined, true);
            }
            else if (rule &&
                typeof rule === "object" &&
                rule.from &&
                rule.to)
                return {
                    from: self.parseDate(rule.from, undefined),
                    to: self.parseDate(rule.to, undefined),
                };
            return rule;
        })
            .filter(function (x) { return x; });
    }
    function setupDates() {
        self.selectedDates = [];
        self.now = self.parseDate(self.config.now) || new Date();
        var preloadedDate = self.config.defaultDate ||
            ((self.input.nodeName === "INPUT" ||
                self.input.nodeName === "TEXTAREA") &&
                self.input.placeholder &&
                self.input.value === self.input.placeholder
                ? null
                : self.input.value);
        if (preloadedDate)
            setSelectedDate(preloadedDate, self.config.dateFormat);
        self._initialDate =
            self.selectedDates.length > 0
                ? self.selectedDates[0]
                : self.config.minDate &&
                    self.config.minDate.getTime() > self.now.getTime()
                    ? self.config.minDate
                    : self.config.maxDate &&
                        self.config.maxDate.getTime() < self.now.getTime()
                        ? self.config.maxDate
                        : self.now;
        self.currentYear = self._initialDate.getFullYear();
        self.currentMonth = self._initialDate.getMonth();
        if (self.selectedDates.length > 0)
            self.latestSelectedDateObj = self.selectedDates[0];
        if (self.config.minTime !== undefined)
            self.config.minTime = self.parseDate(self.config.minTime, "H:i");
        if (self.config.maxTime !== undefined)
            self.config.maxTime = self.parseDate(self.config.maxTime, "H:i");
        self.minDateHasTime =
            !!self.config.minDate &&
                (self.config.minDate.getHours() > 0 ||
                    self.config.minDate.getMinutes() > 0 ||
                    self.config.minDate.getSeconds() > 0);
        self.maxDateHasTime =
            !!self.config.maxDate &&
                (self.config.maxDate.getHours() > 0 ||
                    self.config.maxDate.getMinutes() > 0 ||
                    self.config.maxDate.getSeconds() > 0);
    }
    function setupInputs() {
        self.input = getInputElem();
        if (!self.input) {
            self.config.errorHandler(new Error("Invalid input element specified"));
            return;
        }
        self.input._type = self.input.type;
        self.input.type = "text";
        self.input.classList.add("flatpickr-input");
        self._input = self.input;
        if (self.config.altInput) {
            self.altInput = createElement(self.input.nodeName, self.config.altInputClass);
            self._input = self.altInput;
            self.altInput.placeholder = self.input.placeholder;
            self.altInput.disabled = self.input.disabled;
            self.altInput.required = self.input.required;
            self.altInput.tabIndex = self.input.tabIndex;
            self.altInput.type = "text";
            self.input.setAttribute("type", "hidden");
            if (!self.config.static && self.input.parentNode)
                self.input.parentNode.insertBefore(self.altInput, self.input.nextSibling);
        }
        if (!self.config.allowInput)
            self._input.setAttribute("readonly", "readonly");
        updatePositionElement();
    }
    function updatePositionElement() {
        self._positionElement = self.config.positionElement || self._input;
    }
    function setupMobile() {
        var inputType = self.config.enableTime
            ? self.config.noCalendar
                ? "time"
                : "datetime-local"
            : "date";
        self.mobileInput = createElement("input", self.input.className + " flatpickr-mobile");
        self.mobileInput.tabIndex = 1;
        self.mobileInput.type = inputType;
        self.mobileInput.disabled = self.input.disabled;
        self.mobileInput.required = self.input.required;
        self.mobileInput.placeholder = self.input.placeholder;
        self.mobileFormatStr =
            inputType === "datetime-local"
                ? "Y-m-d\\TH:i:S"
                : inputType === "date"
                    ? "Y-m-d"
                    : "H:i:S";
        if (self.selectedDates.length > 0) {
            self.mobileInput.defaultValue = self.mobileInput.value = self.formatDate(self.selectedDates[0], self.mobileFormatStr);
        }
        if (self.config.minDate)
            self.mobileInput.min = self.formatDate(self.config.minDate, "Y-m-d");
        if (self.config.maxDate)
            self.mobileInput.max = self.formatDate(self.config.maxDate, "Y-m-d");
        if (self.input.getAttribute("step"))
            self.mobileInput.step = String(self.input.getAttribute("step"));
        self.input.type = "hidden";
        if (self.altInput !== undefined)
            self.altInput.type = "hidden";
        try {
            if (self.input.parentNode)
                self.input.parentNode.insertBefore(self.mobileInput, self.input.nextSibling);
        }
        catch (_a) { }
        bind(self.mobileInput, "change", function (e) {
            self.setDate(getEventTarget(e).value, false, self.mobileFormatStr);
            triggerEvent("onChange");
            triggerEvent("onClose");
        });
    }
    function toggle(e) {
        if (self.isOpen === true)
            return self.close();
        self.open(e);
    }
    function triggerEvent(event, data) {
        if (self.config === undefined)
            return;
        var hooks = self.config[event];
        if (hooks !== undefined && hooks.length > 0) {
            for (var i = 0; hooks[i] && i < hooks.length; i++)
                hooks[i](self.selectedDates, self.input.value, self, data);
        }
        if (event === "onChange") {
            self.input.dispatchEvent(createEvent("change"));
            self.input.dispatchEvent(createEvent("input"));
        }
    }
    function createEvent(name) {
        var e = document.createEvent("Event");
        e.initEvent(name, true, true);
        return e;
    }
    function isDateSelected(date) {
        for (var i = 0; i < self.selectedDates.length; i++) {
            var selectedDate = self.selectedDates[i];
            if (selectedDate instanceof Date &&
                compareDates(selectedDate, date) === 0)
                return "" + i;
        }
        return false;
    }
    function isDateInRange(date) {
        if (self.config.mode !== "range" || self.selectedDates.length < 2)
            return false;
        return (compareDates(date, self.selectedDates[0]) >= 0 &&
            compareDates(date, self.selectedDates[1]) <= 0);
    }
    function updateNavigationCurrentMonth() {
        if (self.config.noCalendar || self.isMobile || !self.monthNav)
            return;
        self.yearElements.forEach(function (yearElement, i) {
            var d = new Date(self.currentYear, self.currentMonth, 1);
            d.setMonth(self.currentMonth + i);
            if (self.config.showMonths > 1 ||
                self.config.monthSelectorType === "static") {
                self.monthElements[i].textContent =
                    monthToStr(d.getMonth(), self.config.shorthandCurrentMonth, self.l10n) + " ";
            }
            else {
                self.monthsDropdownContainer.value = d.getMonth().toString();
            }
            yearElement.value = d.getFullYear().toString();
        });
        self._hidePrevMonthArrow =
            self.config.minDate !== undefined &&
                (self.currentYear === self.config.minDate.getFullYear()
                    ? self.currentMonth <= self.config.minDate.getMonth()
                    : self.currentYear < self.config.minDate.getFullYear());
        self._hideNextMonthArrow =
            self.config.maxDate !== undefined &&
                (self.currentYear === self.config.maxDate.getFullYear()
                    ? self.currentMonth + 1 > self.config.maxDate.getMonth()
                    : self.currentYear > self.config.maxDate.getFullYear());
    }
    function getDateStr(specificFormat) {
        var format = specificFormat ||
            (self.config.altInput ? self.config.altFormat : self.config.dateFormat);
        return self.selectedDates
            .map(function (dObj) { return self.formatDate(dObj, format); })
            .filter(function (d, i, arr) {
            return self.config.mode !== "range" ||
                self.config.enableTime ||
                arr.indexOf(d) === i;
        })
            .join(self.config.mode !== "range"
            ? self.config.conjunction
            : self.l10n.rangeSeparator);
    }
    function updateValue(triggerChange) {
        if (triggerChange === void 0) { triggerChange = true; }
        if (self.mobileInput !== undefined && self.mobileFormatStr) {
            self.mobileInput.value =
                self.latestSelectedDateObj !== undefined
                    ? self.formatDate(self.latestSelectedDateObj, self.mobileFormatStr)
                    : "";
        }
        self.input.value = getDateStr(self.config.dateFormat);
        if (self.altInput !== undefined) {
            self.altInput.value = getDateStr(self.config.altFormat);
        }
        if (triggerChange !== false)
            triggerEvent("onValueUpdate");
    }
    function onMonthNavClick(e) {
        var eventTarget = getEventTarget(e);
        var isPrevMonth = self.prevMonthNav.contains(eventTarget);
        var isNextMonth = self.nextMonthNav.contains(eventTarget);
        if (isPrevMonth || isNextMonth) {
            changeMonth(isPrevMonth ? -1 : 1);
        }
        else if (self.yearElements.indexOf(eventTarget) >= 0) {
            eventTarget.select();
        }
        else if (eventTarget.classList.contains("arrowUp")) {
            self.changeYear(self.currentYear + 1);
        }
        else if (eventTarget.classList.contains("arrowDown")) {
            self.changeYear(self.currentYear - 1);
        }
    }
    function timeWrapper(e) {
        e.preventDefault();
        var isKeyDown = e.type === "keydown", eventTarget = getEventTarget(e), input = eventTarget;
        if (self.amPM !== undefined && eventTarget === self.amPM) {
            self.amPM.textContent =
                self.l10n.amPM[int(self.amPM.textContent === self.l10n.amPM[0])];
        }
        var min = parseFloat(input.getAttribute("min")), max = parseFloat(input.getAttribute("max")), step = parseFloat(input.getAttribute("step")), curValue = parseInt(input.value, 10), delta = e.delta ||
            (isKeyDown ? (e.which === 38 ? 1 : -1) : 0);
        var newValue = curValue + step * delta;
        if (typeof input.value !== "undefined" && input.value.length === 2) {
            var isHourElem = input === self.hourElement, isMinuteElem = input === self.minuteElement;
            if (newValue < min) {
                newValue =
                    max +
                        newValue +
                        int(!isHourElem) +
                        (int(isHourElem) && int(!self.amPM));
                if (isMinuteElem)
                    incrementNumInput(undefined, -1, self.hourElement);
            }
            else if (newValue > max) {
                newValue =
                    input === self.hourElement ? newValue - max - int(!self.amPM) : min;
                if (isMinuteElem)
                    incrementNumInput(undefined, 1, self.hourElement);
            }
            if (self.amPM &&
                isHourElem &&
                (step === 1
                    ? newValue + curValue === 23
                    : Math.abs(newValue - curValue) > step)) {
                self.amPM.textContent =
                    self.l10n.amPM[int(self.amPM.textContent === self.l10n.amPM[0])];
            }
            input.value = pad(newValue);
        }
    }
    init();
    return self;
}
function _flatpickr(nodeList, config) {
    var nodes = Array.prototype.slice
        .call(nodeList)
        .filter(function (x) { return x instanceof HTMLElement; });
    var instances = [];
    for (var i = 0; i < nodes.length; i++) {
        var node = nodes[i];
        try {
            if (node.getAttribute("data-fp-omit") !== null)
                continue;
            if (node._flatpickr !== undefined) {
                node._flatpickr.destroy();
                node._flatpickr = undefined;
            }
            node._flatpickr = FlatpickrInstance(node, config || {});
            instances.push(node._flatpickr);
        }
        catch (e) {
            console.error(e);
        }
    }
    return instances.length === 1 ? instances[0] : instances;
}
if (typeof HTMLElement !== "undefined" &&
    typeof HTMLCollection !== "undefined" &&
    typeof NodeList !== "undefined") {
    HTMLCollection.prototype.flatpickr = NodeList.prototype.flatpickr = function (config) {
        return _flatpickr(this, config);
    };
    HTMLElement.prototype.flatpickr = function (config) {
        return _flatpickr([this], config);
    };
}
var flatpickr = function (selector, config) {
    if (typeof selector === "string") {
        return _flatpickr(window.document.querySelectorAll(selector), config);
    }
    else if (selector instanceof Node) {
        return _flatpickr([selector], config);
    }
    else {
        return _flatpickr(selector, config);
    }
};
flatpickr.defaultConfig = {};
flatpickr.l10ns = {
    en: __assign({}, english),
    default: __assign({}, english),
};
flatpickr.localize = function (l10n) {
    flatpickr.l10ns.default = __assign(__assign({}, flatpickr.l10ns.default), l10n);
};
flatpickr.setDefaults = function (config) {
    flatpickr.defaultConfig = __assign(__assign({}, flatpickr.defaultConfig), config);
};
flatpickr.parseDate = createDateParser({});
flatpickr.formatDate = createDateFormatter({});
flatpickr.compareDates = compareDates;
if (typeof jQuery !== "undefined" && typeof jQuery.fn !== "undefined") {
    jQuery.fn.flatpickr = function (config) {
        return _flatpickr(this, config);
    };
}
Date.prototype.fp_incr = function (days) {
    return new Date(this.getFullYear(), this.getMonth(), this.getDate() + (typeof days === "string" ? parseInt(days, 10) : days));
};
if (typeof window !== "undefined") {
    window.flatpickr = flatpickr;
}

/*
  Expose functions.
*/

var jalaaliJs;
var hasRequiredJalaaliJs;

function requireJalaaliJs () {
	if (hasRequiredJalaaliJs) return jalaaliJs;
	hasRequiredJalaaliJs = 1;
	jalaaliJs =
	  { toJalaali: toJalaali
	  , toGregorian: toGregorian
	  , isValidJalaaliDate: isValidJalaaliDate
	  , isLeapJalaaliYear: isLeapJalaaliYear
	  , jalaaliMonthLength: jalaaliMonthLength
	  , jalCal: jalCal
	  , j2d: j2d
	  , d2j: d2j
	  , g2d: g2d
	  , d2g: d2g
	  , jalaaliToDateObject: jalaaliToDateObject
	  , jalaaliWeek: jalaaliWeek
	  };

	/*
	  Jalaali years starting the 33-year rule.
	*/
	var breaks =  [ -61, 9, 38, 199, 426, 686, 756, 818, 1111, 1181, 1210
	  , 1635, 2060, 2097, 2192, 2262, 2324, 2394, 2456, 3178
	  ];

	/*
	  Converts a Gregorian date to Jalaali.
	*/
	function toJalaali(gy, gm, gd) {
	  if (Object.prototype.toString.call(gy) === '[object Date]') {
	    gd = gy.getDate();
	    gm = gy.getMonth() + 1;
	    gy = gy.getFullYear();
	  }
	  return d2j(g2d(gy, gm, gd))
	}

	/*
	  Converts a Jalaali date to Gregorian.
	*/
	function toGregorian(jy, jm, jd) {
	  return d2g(j2d(jy, jm, jd))
	}

	/*
	  Checks whether a Jalaali date is valid or not.
	*/
	function isValidJalaaliDate(jy, jm, jd) {
	  return  jy >= -61 && jy <= 3177 &&
	          jm >= 1 && jm <= 12 &&
	          jd >= 1 && jd <= jalaaliMonthLength(jy, jm)
	}

	/*
	  Is this a leap year or not?
	*/
	function isLeapJalaaliYear(jy) {
	  return jalCalLeap(jy) === 0
	}

	/*
	  Number of days in a given month in a Jalaali year.
	*/
	function jalaaliMonthLength(jy, jm) {
	  if (jm <= 6) return 31
	  if (jm <= 11) return 30
	  if (isLeapJalaaliYear(jy)) return 30
	  return 29
	}

	/*
	    This function determines if the Jalaali (Persian) year is
	    leap (366-day long) or is the common year (365 days)

	    @param jy Jalaali calendar year (-61 to 3177)
	    @returns number of years since the last leap year (0 to 4)
	 */
	function jalCalLeap(jy) {
	  var bl = breaks.length
	    , jp = breaks[0]
	    , jm
	    , jump
	    , leap
	    , n
	    , i;

	  if (jy < jp || jy >= breaks[bl - 1])
	    throw new Error('Invalid Jalaali year ' + jy)

	  for (i = 1; i < bl; i += 1) {
	    jm = breaks[i];
	    jump = jm - jp;
	    if (jy < jm)
	      break
	    jp = jm;
	  }
	  n = jy - jp;

	  if (jump - n < 6)
	    n = n - jump + div(jump + 4, 33) * 33;
	  leap = mod(mod(n + 1, 33) - 1, 4);
	  if (leap === -1) {
	    leap = 4;
	  }

	  return leap
	}

	/*
	  This function determines if the Jalaali (Persian) year is
	  leap (366-day long) or is the common year (365 days), and
	  finds the day in March (Gregorian calendar) of the first
	  day of the Jalaali year (jy).

	  @param jy Jalaali calendar year (-61 to 3177)
	  @param withoutLeap when don't need leap (true or false) default is false
	  @return
	    leap: number of years since the last leap year (0 to 4)
	    gy: Gregorian year of the beginning of Jalaali year
	    march: the March day of Farvardin the 1st (1st day of jy)
	  @see: http://www.astro.uni.torun.pl/~kb/Papers/EMP/PersianC-EMP.htm
	  @see: http://www.fourmilab.ch/documents/calendar/
	*/
	function jalCal(jy, withoutLeap) {
	  var bl = breaks.length
	    , gy = jy + 621
	    , leapJ = -14
	    , jp = breaks[0]
	    , jm
	    , jump
	    , leap
	    , leapG
	    , march
	    , n
	    , i;

	  if (jy < jp || jy >= breaks[bl - 1])
	    throw new Error('Invalid Jalaali year ' + jy)

	  // Find the limiting years for the Jalaali year jy.
	  for (i = 1; i < bl; i += 1) {
	    jm = breaks[i];
	    jump = jm - jp;
	    if (jy < jm)
	      break
	    leapJ = leapJ + div(jump, 33) * 8 + div(mod(jump, 33), 4);
	    jp = jm;
	  }
	  n = jy - jp;

	  // Find the number of leap years from AD 621 to the beginning
	  // of the current Jalaali year in the Persian calendar.
	  leapJ = leapJ + div(n, 33) * 8 + div(mod(n, 33) + 3, 4);
	  if (mod(jump, 33) === 4 && jump - n === 4)
	    leapJ += 1;

	  // And the same in the Gregorian calendar (until the year gy).
	  leapG = div(gy, 4) - div((div(gy, 100) + 1) * 3, 4) - 150;

	  // Determine the Gregorian date of Farvardin the 1st.
	  march = 20 + leapJ - leapG;

	  // return with gy and march when we don't need leap
	  if (withoutLeap) return { gy: gy, march: march };


	  // Find how many years have passed since the last leap year.
	  if (jump - n < 6)
	    n = n - jump + div(jump + 4, 33) * 33;
	  leap = mod(mod(n + 1, 33) - 1, 4);
	  if (leap === -1) {
	    leap = 4;
	  }

	  return  { leap: leap
	          , gy: gy
	          , march: march
	          }
	}

	/*
	  Converts a date of the Jalaali calendar to the Julian Day number.

	  @param jy Jalaali year (1 to 3100)
	  @param jm Jalaali month (1 to 12)
	  @param jd Jalaali day (1 to 29/31)
	  @return Julian Day number
	*/
	function j2d(jy, jm, jd) {
	  var r = jalCal(jy, true);
	  return g2d(r.gy, 3, r.march) + (jm - 1) * 31 - div(jm, 7) * (jm - 7) + jd - 1
	}

	/*
	  Converts the Julian Day number to a date in the Jalaali calendar.

	  @param jdn Julian Day number
	  @return
	    jy: Jalaali year (1 to 3100)
	    jm: Jalaali month (1 to 12)
	    jd: Jalaali day (1 to 29/31)
	*/
	function d2j(jdn) {
	  var gy = d2g(jdn).gy // Calculate Gregorian year (gy).
	    , jy = gy - 621
	    , r = jalCal(jy, false)
	    , jdn1f = g2d(gy, 3, r.march)
	    , jd
	    , jm
	    , k;

	  // Find number of days that passed since 1 Farvardin.
	  k = jdn - jdn1f;
	  if (k >= 0) {
	    if (k <= 185) {
	      // The first 6 months.
	      jm = 1 + div(k, 31);
	      jd = mod(k, 31) + 1;
	      return  { jy: jy
	              , jm: jm
	              , jd: jd
	              }
	    } else {
	      // The remaining months.
	      k -= 186;
	    }
	  } else {
	    // Previous Jalaali year.
	    jy -= 1;
	    k += 179;
	    if (r.leap === 1)
	      k += 1;
	  }
	  jm = 7 + div(k, 30);
	  jd = mod(k, 30) + 1;
	  return  { jy: jy
	          , jm: jm
	          , jd: jd
	          }
	}

	/*
	  Calculates the Julian Day number from Gregorian or Julian
	  calendar dates. This integer number corresponds to the noon of
	  the date (i.e. 12 hours of Universal Time).
	  The procedure was tested to be good since 1 March, -100100 (of both
	  calendars) up to a few million years into the future.

	  @param gy Calendar year (years BC numbered 0, -1, -2, ...)
	  @param gm Calendar month (1 to 12)
	  @param gd Calendar day of the month (1 to 28/29/30/31)
	  @return Julian Day number
	*/
	function g2d(gy, gm, gd) {
	  var d = div((gy + div(gm - 8, 6) + 100100) * 1461, 4)
	      + div(153 * mod(gm + 9, 12) + 2, 5)
	      + gd - 34840408;
	  d = d - div(div(gy + 100100 + div(gm - 8, 6), 100) * 3, 4) + 752;
	  return d
	}

	/*
	  Calculates Gregorian and Julian calendar dates from the Julian Day number
	  (jdn) for the period since jdn=-34839655 (i.e. the year -100100 of both
	  calendars) to some millions years ahead of the present.

	  @param jdn Julian Day number
	  @return
	    gy: Calendar year (years BC numbered 0, -1, -2, ...)
	    gm: Calendar month (1 to 12)
	    gd: Calendar day of the month M (1 to 28/29/30/31)
	*/
	function d2g(jdn) {
	  var j
	    , i
	    , gd
	    , gm
	    , gy;
	  j = 4 * jdn + 139361631;
	  j = j + div(div(4 * jdn + 183187720, 146097) * 3, 4) * 4 - 3908;
	  i = div(mod(j, 1461), 4) * 5 + 308;
	  gd = div(mod(i, 153), 5) + 1;
	  gm = mod(div(i, 153), 12) + 1;
	  gy = div(j, 1461) - 100100 + div(8 - gm, 6);
	  return  { gy: gy
	          , gm: gm
	          , gd: gd
	          }
	}

	/**
	 * Return Saturday and Friday day of current week(week start in Saturday)
	 * @param {number} jy jalaali year
	 * @param {number} jm jalaali month
	 * @param {number} jd jalaali day
	 * @returns Saturday and Friday of current week
	 */
	function jalaaliWeek(jy, jm, jd) {
	  var dayOfWeek = jalaaliToDateObject(jy, jm, jd).getDay();

	  var startDayDifference = dayOfWeek == 6 ? 0 : -(dayOfWeek+1);
	  var endDayDifference = 6+startDayDifference;

	  return {
	    saturday: d2j(j2d(jy, jm, jd+startDayDifference)),
	    friday: d2j(j2d(jy, jm, jd+endDayDifference))
	  }
	}

	/**
	 * Convert Jalaali calendar dates to javascript Date object
	 * @param {number} jy jalaali year
	 * @param {number} jm jalaali month
	 * @param {number} jd jalaali day
	 * @param {number} [h] hours
	 * @param {number} [m] minutes
	 * @param {number} [s] seconds
	 * @param {number} [ms] milliseconds
	 * @returns Date object of the jalaali calendar dates
	 */
	function jalaaliToDateObject(
	  jy,
	  jm,
	  jd,
	  h,
	  m,
	  s,
	  ms
	) {
	  var gregorianCalenderDate = toGregorian(jy, jm, jd);

	  return new Date(
	    gregorianCalenderDate.gy,
	    gregorianCalenderDate.gm - 1,
	    gregorianCalenderDate.gd,
	    h || 0,
	    m || 0,
	    s || 0,
	    ms || 0
	  );
	}

	/*
	  Utility helper functions.
	*/

	function div(a, b) {
	  return ~~(a / b)
	}

	function mod(a, b) {
	  return a - ~~(a / b) * b
	}
	return jalaaliJs;
}

var jalaaliJsExports = requireJalaaliJs();

const rangeDatepickerCss = () => `@import url('https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css');.flatpickr-calendar{background:transparent;opacity:0;display:none;text-align:center;visibility:hidden;padding:0;-webkit-animation:none;animation:none;direction:ltr;border:0;font-size:14px;line-height:24px;border-radius:5px;position:absolute;width:307.875px;-webkit-box-sizing:border-box;box-sizing:border-box;-ms-touch-action:manipulation;touch-action:manipulation;background:#fff;-webkit-box-shadow:1px 0 0 #e6e6e6, -1px 0 0 #e6e6e6, 0 1px 0 #e6e6e6, 0 -1px 0 #e6e6e6, 0 3px 13px rgba(0,0,0,0.08);box-shadow:1px 0 0 #e6e6e6, -1px 0 0 #e6e6e6, 0 1px 0 #e6e6e6, 0 -1px 0 #e6e6e6, 0 3px 13px rgba(0,0,0,0.08)}.flatpickr-calendar.open,.flatpickr-calendar.inline{opacity:1;max-height:640px;visibility:visible}.flatpickr-calendar.open{display:inline-block;z-index:99999}.flatpickr-calendar.animate.open{-webkit-animation:fpFadeInDown 300ms cubic-bezier(0.23, 1, 0.32, 1);animation:fpFadeInDown 300ms cubic-bezier(0.23, 1, 0.32, 1)}.flatpickr-calendar.inline{display:block;position:relative;top:2px}.flatpickr-calendar.static{position:absolute;top:calc(100% + 2px)}.flatpickr-calendar.static.open{z-index:999;display:block}.flatpickr-calendar.multiMonth .flatpickr-days .dayContainer:nth-child(n+1) .flatpickr-day.inRange:nth-child(7n+7){-webkit-box-shadow:none !important;box-shadow:none !important}.flatpickr-calendar.multiMonth .flatpickr-days .dayContainer:nth-child(n+2) .flatpickr-day.inRange:nth-child(7n+1){-webkit-box-shadow:-2px 0 0 #e6e6e6, 5px 0 0 #e6e6e6;box-shadow:-2px 0 0 #e6e6e6, 5px 0 0 #e6e6e6}.flatpickr-calendar .hasWeeks .dayContainer,.flatpickr-calendar .hasTime .dayContainer{border-bottom:0;border-bottom-right-radius:0;border-bottom-left-radius:0}.flatpickr-calendar .hasWeeks .dayContainer{border-left:0}.flatpickr-calendar.hasTime .flatpickr-time{height:40px;border-top:1px solid #e6e6e6}.flatpickr-calendar.noCalendar.hasTime .flatpickr-time{height:auto}.flatpickr-calendar:before,.flatpickr-calendar:after{position:absolute;display:block;pointer-events:none;border:solid transparent;content:'';height:0;width:0;left:22px}.flatpickr-calendar.rightMost:before,.flatpickr-calendar.arrowRight:before,.flatpickr-calendar.rightMost:after,.flatpickr-calendar.arrowRight:after{left:auto;right:22px}.flatpickr-calendar.arrowCenter:before,.flatpickr-calendar.arrowCenter:after{left:50%;right:50%}.flatpickr-calendar:before{border-width:5px;margin:0 -5px}.flatpickr-calendar:after{border-width:4px;margin:0 -4px}.flatpickr-calendar.arrowTop:before,.flatpickr-calendar.arrowTop:after{bottom:100%}.flatpickr-calendar.arrowTop:before{border-bottom-color:#e6e6e6}.flatpickr-calendar.arrowTop:after{border-bottom-color:#fff}.flatpickr-calendar.arrowBottom:before,.flatpickr-calendar.arrowBottom:after{top:100%}.flatpickr-calendar.arrowBottom:before{border-top-color:#e6e6e6}.flatpickr-calendar.arrowBottom:after{border-top-color:#fff}.flatpickr-calendar:focus{outline:0}.flatpickr-wrapper{position:relative;display:inline-block}.flatpickr-months{display:-webkit-box;display:-webkit-flex;display:-ms-flexbox;display:flex}.flatpickr-months .flatpickr-month{background:transparent;color:rgba(0,0,0,0.9);fill:rgba(0,0,0,0.9);height:34px;line-height:1;text-align:center;position:relative;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;overflow:hidden;-webkit-box-flex:1;-webkit-flex:1;-ms-flex:1;flex:1}.flatpickr-months .flatpickr-prev-month,.flatpickr-months .flatpickr-next-month{-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;text-decoration:none;cursor:pointer;position:absolute;top:0;height:34px;padding:10px;z-index:3;color:rgba(0,0,0,0.9);fill:rgba(0,0,0,0.9)}.flatpickr-months .flatpickr-prev-month.flatpickr-disabled,.flatpickr-months .flatpickr-next-month.flatpickr-disabled{display:none}.flatpickr-months .flatpickr-prev-month i,.flatpickr-months .flatpickr-next-month i{position:relative}.flatpickr-months .flatpickr-prev-month.flatpickr-prev-month,.flatpickr-months .flatpickr-next-month.flatpickr-prev-month{left:0;}.flatpickr-months .flatpickr-prev-month.flatpickr-next-month,.flatpickr-months .flatpickr-next-month.flatpickr-next-month{right:0;}.flatpickr-months .flatpickr-prev-month:hover,.flatpickr-months .flatpickr-next-month:hover{color:#959ea9}.flatpickr-months .flatpickr-prev-month:hover svg,.flatpickr-months .flatpickr-next-month:hover svg{fill:#f64747}.flatpickr-months .flatpickr-prev-month svg,.flatpickr-months .flatpickr-next-month svg{width:14px;height:14px}.flatpickr-months .flatpickr-prev-month svg path,.flatpickr-months .flatpickr-next-month svg path{-webkit-transition:fill 0.1s;transition:fill 0.1s;fill:inherit}.numInputWrapper{position:relative;height:auto}.numInputWrapper input,.numInputWrapper span{display:inline-block}.numInputWrapper input{width:100%}.numInputWrapper input::-ms-clear{display:none}.numInputWrapper input::-webkit-outer-spin-button,.numInputWrapper input::-webkit-inner-spin-button{margin:0;-webkit-appearance:none}.numInputWrapper span{position:absolute;right:0;width:14px;padding:0 4px 0 2px;height:50%;line-height:50%;opacity:0;cursor:pointer;border:1px solid rgba(57,57,57,0.15);-webkit-box-sizing:border-box;box-sizing:border-box}.numInputWrapper span:hover{background:rgba(0,0,0,0.1)}.numInputWrapper span:active{background:rgba(0,0,0,0.2)}.numInputWrapper span:after{display:block;content:"";position:absolute}.numInputWrapper span.arrowUp{top:0;border-bottom:0}.numInputWrapper span.arrowUp:after{border-left:4px solid transparent;border-right:4px solid transparent;border-bottom:4px solid rgba(57,57,57,0.6);top:26%}.numInputWrapper span.arrowDown{top:50%}.numInputWrapper span.arrowDown:after{border-left:4px solid transparent;border-right:4px solid transparent;border-top:4px solid rgba(57,57,57,0.6);top:40%}.numInputWrapper span svg{width:inherit;height:auto}.numInputWrapper span svg path{fill:rgba(0,0,0,0.5)}.numInputWrapper:hover{background:rgba(0,0,0,0.05)}.numInputWrapper:hover span{opacity:1}.flatpickr-current-month{font-size:135%;line-height:inherit;font-weight:300;color:inherit;position:absolute;width:75%;left:12.5%;padding:7.48px 0 0 0;line-height:1;height:34px;display:inline-block;text-align:center;-webkit-transform:translate3d(0px, 0px, 0px);transform:translate3d(0px, 0px, 0px)}.flatpickr-current-month span.cur-month{font-family:inherit;font-weight:700;color:inherit;display:inline-block;margin-left:0.5ch;padding:0}.flatpickr-current-month span.cur-month:hover{background:rgba(0,0,0,0.05)}.flatpickr-current-month .numInputWrapper{width:6ch;width:7ch\\0;display:inline-block}.flatpickr-current-month .numInputWrapper span.arrowUp:after{border-bottom-color:rgba(0,0,0,0.9)}.flatpickr-current-month .numInputWrapper span.arrowDown:after{border-top-color:rgba(0,0,0,0.9)}.flatpickr-current-month input.cur-year{background:transparent;-webkit-box-sizing:border-box;box-sizing:border-box;color:inherit;cursor:text;padding:0 0 0 0.5ch;margin:0;display:inline-block;font-size:inherit;font-family:inherit;font-weight:300;line-height:inherit;height:auto;border:0;border-radius:0;vertical-align:initial;-webkit-appearance:textfield;-moz-appearance:textfield;appearance:textfield}.flatpickr-current-month input.cur-year:focus{outline:0}.flatpickr-current-month input.cur-year[disabled],.flatpickr-current-month input.cur-year[disabled]:hover{font-size:100%;color:rgba(0,0,0,0.5);background:transparent;pointer-events:none}.flatpickr-current-month .flatpickr-monthDropdown-months{appearance:menulist;background:transparent;border:none;border-radius:0;box-sizing:border-box;color:inherit;cursor:pointer;font-size:inherit;font-family:inherit;font-weight:300;height:auto;line-height:inherit;margin:-1px 0 0 0;outline:none;padding:0 0 0 0.5ch;position:relative;vertical-align:initial;-webkit-box-sizing:border-box;-webkit-appearance:menulist;-moz-appearance:menulist;width:auto}.flatpickr-current-month .flatpickr-monthDropdown-months:focus,.flatpickr-current-month .flatpickr-monthDropdown-months:active{outline:none}.flatpickr-current-month .flatpickr-monthDropdown-months:hover{background:rgba(0,0,0,0.05)}.flatpickr-current-month .flatpickr-monthDropdown-months .flatpickr-monthDropdown-month{background-color:transparent;outline:none;padding:0}.flatpickr-weekdays{background:transparent;text-align:center;overflow:hidden;width:100%;display:-webkit-box;display:-webkit-flex;display:-ms-flexbox;display:flex;-webkit-box-align:center;-webkit-align-items:center;-ms-flex-align:center;align-items:center;height:28px}.flatpickr-weekdays .flatpickr-weekdaycontainer{display:-webkit-box;display:-webkit-flex;display:-ms-flexbox;display:flex;-webkit-box-flex:1;-webkit-flex:1;-ms-flex:1;flex:1}span.flatpickr-weekday{cursor:default;font-size:90%;background:transparent;color:rgba(0,0,0,0.54);line-height:1;margin:0;text-align:center;display:block;-webkit-box-flex:1;-webkit-flex:1;-ms-flex:1;flex:1;font-weight:bolder}.dayContainer,.flatpickr-weeks{padding:1px 0 0 0}.flatpickr-days{position:relative;overflow:hidden;display:-webkit-box;display:-webkit-flex;display:-ms-flexbox;display:flex;-webkit-box-align:start;-webkit-align-items:flex-start;-ms-flex-align:start;align-items:flex-start;width:307.875px}.flatpickr-days:focus{outline:0}.dayContainer{padding:0;outline:0;text-align:left;width:307.875px;min-width:307.875px;max-width:307.875px;-webkit-box-sizing:border-box;box-sizing:border-box;display:inline-block;display:-ms-flexbox;display:-webkit-box;display:-webkit-flex;display:flex;-webkit-flex-wrap:wrap;flex-wrap:wrap;-ms-flex-wrap:wrap;-ms-flex-pack:justify;-webkit-justify-content:space-around;justify-content:space-around;-webkit-transform:translate3d(0px, 0px, 0px);transform:translate3d(0px, 0px, 0px);opacity:1}.dayContainer+.dayContainer{-webkit-box-shadow:-1px 0 0 #e6e6e6;box-shadow:-1px 0 0 #e6e6e6}.flatpickr-day{background:none;border:1px solid transparent;border-radius:150px;-webkit-box-sizing:border-box;box-sizing:border-box;color:#393939;cursor:pointer;font-weight:400;width:14.2857143%;-webkit-flex-basis:14.2857143%;-ms-flex-preferred-size:14.2857143%;flex-basis:14.2857143%;max-width:39px;height:39px;line-height:39px;margin:0;display:inline-block;position:relative;-webkit-box-pack:center;-webkit-justify-content:center;-ms-flex-pack:center;justify-content:center;text-align:center}.flatpickr-day.inRange,.flatpickr-day.prevMonthDay.inRange,.flatpickr-day.nextMonthDay.inRange,.flatpickr-day.today.inRange,.flatpickr-day.prevMonthDay.today.inRange,.flatpickr-day.nextMonthDay.today.inRange,.flatpickr-day:hover,.flatpickr-day.prevMonthDay:hover,.flatpickr-day.nextMonthDay:hover,.flatpickr-day:focus,.flatpickr-day.prevMonthDay:focus,.flatpickr-day.nextMonthDay:focus{cursor:pointer;outline:0;background:#e6e6e6;border-color:#e6e6e6}.flatpickr-day.today{border-color:#959ea9}.flatpickr-day.today:hover,.flatpickr-day.today:focus{border-color:#959ea9;background:#959ea9;color:#fff}.flatpickr-day.selected,.flatpickr-day.startRange,.flatpickr-day.endRange,.flatpickr-day.selected.inRange,.flatpickr-day.startRange.inRange,.flatpickr-day.endRange.inRange,.flatpickr-day.selected:focus,.flatpickr-day.startRange:focus,.flatpickr-day.endRange:focus,.flatpickr-day.selected:hover,.flatpickr-day.startRange:hover,.flatpickr-day.endRange:hover,.flatpickr-day.selected.prevMonthDay,.flatpickr-day.startRange.prevMonthDay,.flatpickr-day.endRange.prevMonthDay,.flatpickr-day.selected.nextMonthDay,.flatpickr-day.startRange.nextMonthDay,.flatpickr-day.endRange.nextMonthDay{background:#569ff7;-webkit-box-shadow:none;box-shadow:none;color:#fff;border-color:#569ff7}.flatpickr-day.selected.startRange,.flatpickr-day.startRange.startRange,.flatpickr-day.endRange.startRange{border-radius:50px 0 0 50px}.flatpickr-day.selected.endRange,.flatpickr-day.startRange.endRange,.flatpickr-day.endRange.endRange{border-radius:0 50px 50px 0}.flatpickr-day.selected.startRange+.endRange:not(:nth-child(7n+1)),.flatpickr-day.startRange.startRange+.endRange:not(:nth-child(7n+1)),.flatpickr-day.endRange.startRange+.endRange:not(:nth-child(7n+1)){-webkit-box-shadow:-10px 0 0 #569ff7;box-shadow:-10px 0 0 #569ff7}.flatpickr-day.selected.startRange.endRange,.flatpickr-day.startRange.startRange.endRange,.flatpickr-day.endRange.startRange.endRange{border-radius:50px}.flatpickr-day.inRange{border-radius:0;-webkit-box-shadow:-5px 0 0 #e6e6e6, 5px 0 0 #e6e6e6;box-shadow:-5px 0 0 #e6e6e6, 5px 0 0 #e6e6e6}.flatpickr-day.flatpickr-disabled,.flatpickr-day.flatpickr-disabled:hover,.flatpickr-day.prevMonthDay,.flatpickr-day.nextMonthDay,.flatpickr-day.notAllowed,.flatpickr-day.notAllowed.prevMonthDay,.flatpickr-day.notAllowed.nextMonthDay{color:rgba(57,57,57,0.3);background:transparent;border-color:transparent;cursor:default}.flatpickr-day.flatpickr-disabled,.flatpickr-day.flatpickr-disabled:hover{cursor:not-allowed;color:rgba(57,57,57,0.1)}.flatpickr-day.week.selected{border-radius:0;-webkit-box-shadow:-5px 0 0 #569ff7, 5px 0 0 #569ff7;box-shadow:-5px 0 0 #569ff7, 5px 0 0 #569ff7}.flatpickr-day.hidden{visibility:hidden}.rangeMode .flatpickr-day{margin-top:1px}.flatpickr-weekwrapper{float:left}.flatpickr-weekwrapper .flatpickr-weeks{padding:0 12px;-webkit-box-shadow:1px 0 0 #e6e6e6;box-shadow:1px 0 0 #e6e6e6}.flatpickr-weekwrapper .flatpickr-weekday{float:none;width:100%;line-height:28px}.flatpickr-weekwrapper span.flatpickr-day,.flatpickr-weekwrapper span.flatpickr-day:hover{display:block;width:100%;max-width:none;color:rgba(57,57,57,0.3);background:transparent;cursor:default;border:none}.flatpickr-innerContainer{display:block;display:-webkit-box;display:-webkit-flex;display:-ms-flexbox;display:flex;-webkit-box-sizing:border-box;box-sizing:border-box;overflow:hidden}.flatpickr-rContainer{display:inline-block;padding:0;-webkit-box-sizing:border-box;box-sizing:border-box}.flatpickr-time{text-align:center;outline:0;display:block;height:0;line-height:40px;max-height:40px;-webkit-box-sizing:border-box;box-sizing:border-box;overflow:hidden;display:-webkit-box;display:-webkit-flex;display:-ms-flexbox;display:flex}.flatpickr-time:after{content:"";display:table;clear:both}.flatpickr-time .numInputWrapper{-webkit-box-flex:1;-webkit-flex:1;-ms-flex:1;flex:1;width:40%;height:40px;float:left}.flatpickr-time .numInputWrapper span.arrowUp:after{border-bottom-color:#393939}.flatpickr-time .numInputWrapper span.arrowDown:after{border-top-color:#393939}.flatpickr-time.hasSeconds .numInputWrapper{width:26%}.flatpickr-time.time24hr .numInputWrapper{width:49%}.flatpickr-time input{background:transparent;-webkit-box-shadow:none;box-shadow:none;border:0;border-radius:0;text-align:center;margin:0;padding:0;height:inherit;line-height:inherit;color:#393939;font-size:14px;position:relative;-webkit-box-sizing:border-box;box-sizing:border-box;-webkit-appearance:textfield;-moz-appearance:textfield;appearance:textfield}.flatpickr-time input.flatpickr-hour{font-weight:bold}.flatpickr-time input.flatpickr-minute,.flatpickr-time input.flatpickr-second{font-weight:400}.flatpickr-time input:focus{outline:0;border:0}.flatpickr-time .flatpickr-time-separator,.flatpickr-time .flatpickr-am-pm{height:inherit;float:left;line-height:inherit;color:#393939;font-weight:bold;width:2%;-webkit-user-select:none;-moz-user-select:none;-ms-user-select:none;user-select:none;-webkit-align-self:center;-ms-flex-item-align:center;align-self:center}.flatpickr-time .flatpickr-am-pm{outline:0;width:18%;cursor:pointer;text-align:center;font-weight:400}.flatpickr-time input:hover,.flatpickr-time .flatpickr-am-pm:hover,.flatpickr-time input:focus,.flatpickr-time .flatpickr-am-pm:focus{background:#eee}.flatpickr-input[readonly]{cursor:pointer}@-webkit-keyframes fpFadeInDown{from{opacity:0;-webkit-transform:translate3d(0, -20px, 0);transform:translate3d(0, -20px, 0)}to{opacity:1;-webkit-transform:translate3d(0, 0, 0);transform:translate3d(0, 0, 0)}}@keyframes fpFadeInDown{from{opacity:0;-webkit-transform:translate3d(0, -20px, 0);transform:translate3d(0, -20px, 0)}to{opacity:1;-webkit-transform:translate3d(0, 0, 0);transform:translate3d(0, 0, 0)}}ez-range-datepicker{display:block;position:relative}.my-range-datepicker-wrapper{overflow:visible;position:relative;--datepicker-primary-color:#f97316;--datepicker-text-color:#1f2937;--datepicker-border-color:#d1d5db;--datepicker-highlight-color:rgba(249, 115, 22, 0.08);--datepicker-selected-bg:#f97316;--datepicker-selected-text:#ffffff;--datepicker-range-bg:#fde68a;--datepicker-range-preview-bg:rgba(253, 230, 138, 0.6);--datepicker-font-family:'Vazirmatn', sans-serif;--datepicker-border-radius:8px;--datepicker-shadow:0 4px 6px rgba(0, 0, 0, 0.1);--datepicker-error-color:#dc2626;--datepicker-holiday-color:#dc2626;--datepicker-today-border:#dc2626;position:relative;direction:rtl;text-align:right;font-family:var(--datepicker-font-family);display:block}.my-range-datepicker-input{width:100%;max-width:320px;padding:0.625rem 0.875rem;font-size:0.9375rem;font-family:var(--datepicker-font-family);color:var(--datepicker-text-color);border:1.5px solid var(--datepicker-border-color);border-radius:var(--datepicker-border-radius);background:#fff;cursor:pointer;direction:rtl;transition:all 0.2s ease}.my-range-datepicker-input:hover:not(:disabled){border-color:var(--datepicker-primary-color)}.my-range-datepicker-input:focus{outline:none;border-color:var(--datepicker-primary-color);box-shadow:0 0 0 3px rgba(59, 130, 246, 0.15)}.my-range-datepicker-input:focus-visible{outline:2px solid var(--datepicker-primary-color);outline-offset:2px}.my-range-datepicker-input::placeholder{color:#9ca3af}.my-range-datepicker-input:disabled{cursor:not-allowed;opacity:0.6;background:#f3f4f6}.error-message{color:var(--datepicker-error-color);font-size:0.8125rem;margin-top:0.375rem;font-weight:500}.flatpickr-calendar{direction:ltr;font-family:var(--datepicker-font-family) !important;font-variant-numeric:normal;z-index:9999;background:#fff !important;border-radius:var(--datepicker-border-radius) !important;border:1px solid var(--datepicker-border-color) !important;box-shadow:var(--datepicker-shadow) !important;padding:0 !important;overflow:hidden;min-width:320px;max-width:360px}.flatpickr-calendar.open{max-height:640px}.flatpickr-calendar.arrowTop:before,.flatpickr-calendar.arrowTop:after{border-bottom-color:var(--datepicker-primary-color)}.flatpickr-months{background:#fff;border-radius:var(--datepicker-border-radius) var(--datepicker-border-radius) 0 0;border-bottom:1px solid var(--datepicker-border-color);padding:0.75rem 1rem;min-height:3rem}.flatpickr-months .flatpickr-month{background:transparent;color:var(--datepicker-text-color);min-height:34px;position:relative}.flatpickr-current-month{font-size:1.0625rem;font-weight:700;color:var(--datepicker-text-color);padding:0.375rem 0 0;position:absolute;left:12.5%;width:75%;text-align:center;visibility:visible;opacity:1}.flatpickr-current-month span.cur-month{font-weight:700;color:var(--datepicker-text-color)}.flatpickr-current-month .flatpickr-monthDropdown-months,.flatpickr-current-month .numInputWrapper,.flatpickr-current-month input.cur-year{color:var(--datepicker-text-color);font-weight:600}.flatpickr-current-month .flatpickr-monthDropdown-months:hover,.flatpickr-current-month .numInputWrapper:hover{background:#f3f4f6}.flatpickr-prev-month,.flatpickr-next-month{fill:#6b7280;color:#6b7280;padding:0.5rem;background:none;border:none;border-radius:var(--datepicker-border-radius);display:flex;align-items:center;justify-content:center;transition:all 0.2s ease}.flatpickr-prev-month:hover,.flatpickr-next-month:hover{fill:var(--datepicker-primary-color);color:var(--datepicker-primary-color);background:#f3f4f6}.flatpickr-prev-month{left:0;right:auto}.flatpickr-next-month{right:0;left:auto}.flatpickr-prev-month svg,.flatpickr-next-month svg{width:16px;height:16px}.flatpickr-weekdays{background:#f9fafb;border-bottom:1px solid var(--datepicker-border-color);padding:0.625rem 0.75rem 0.375rem}.flatpickr-weekday{color:#6b7280;font-weight:600;font-size:0.8125rem}.flatpickr-days{border:none}.flatpickr-days .dayContainer{padding:0.75rem;gap:4px}.flatpickr-day{font-variant-numeric:normal;color:var(--datepicker-text-color);border-radius:var(--datepicker-border-radius);font-weight:500;font-size:0.9375rem;max-width:46px;min-height:40px;height:40px;line-height:40px;border:1.5px solid transparent;transition:all 0.15s ease}.flatpickr-day.selected,.flatpickr-day.startRange,.flatpickr-day.endRange,.flatpickr-day.selected.inRange,.flatpickr-day.startRange.inRange,.flatpickr-day.endRange.inRange,.flatpickr-day.selected:focus,.flatpickr-day.startRange:focus,.flatpickr-day.endRange:focus,.flatpickr-day.selected:hover,.flatpickr-day.startRange:hover,.flatpickr-day.endRange:hover,.flatpickr-day.selected.prevMonthDay,.flatpickr-day.startRange.prevMonthDay,.flatpickr-day.endRange.prevMonthDay,.flatpickr-day.selected.nextMonthDay,.flatpickr-day.startRange.nextMonthDay,.flatpickr-day.endRange.nextMonthDay{background:var(--datepicker-selected-bg) !important;border-color:var(--datepicker-selected-bg) !important;color:var(--datepicker-selected-text) !important}.flatpickr-day.inRange{background:var(--datepicker-range-bg) !important;box-shadow:-2px 0 0 var(--datepicker-range-bg), 2px 0 0 var(--datepicker-range-bg);border-color:transparent !important;border-radius:0}.flatpickr-day.today{border:2px solid var(--datepicker-today-border);font-weight:700}.flatpickr-day.today:not(.selected):not(.startRange):not(.endRange){background:transparent;color:var(--datepicker-today-border)}.flatpickr-day:hover:not(.flatpickr-disabled):not(.prevMonthDay):not(.nextMonthDay){background:var(--datepicker-highlight-color);border-color:var(--datepicker-highlight-color)}.flatpickr-day.prevMonthDay,.flatpickr-day.nextMonthDay{color:#d1d5db}.flatpickr-day.flatpickr-disabled{color:#d1d5db;cursor:not-allowed}.flatpickr-custom-buttons{display:flex;flex-wrap:wrap;gap:0.5rem;padding:0.625rem 0.75rem;border-top:1px solid var(--datepicker-border-color);background:#f9fafb;border-radius:0 0 var(--datepicker-border-radius) var(--datepicker-border-radius);font-family:var(--datepicker-font-family)}.flatpickr-calendar-switch{display:flex;width:100%;gap:0.375rem;margin-bottom:0.375rem}.flatpickr-switch-btn{flex:1;padding:0.5rem;font-size:0.875rem;font-family:inherit;font-weight:500;border:1.5px solid var(--datepicker-border-color);border-radius:6px;background:#fff;color:var(--datepicker-text-color);cursor:pointer;transition:all 0.2s ease}.flatpickr-switch-btn:hover{background:var(--datepicker-highlight-color);border-color:var(--datepicker-primary-color)}.flatpickr-switch-btn.active{background:var(--datepicker-primary-color);color:#fff;border-color:var(--datepicker-primary-color)}.flatpickr-today-btn,.flatpickr-clear-btn{flex:1;padding:0.5rem 0.875rem;font-size:0.875rem;font-family:inherit;font-weight:500;border:1.5px solid var(--datepicker-border-color);border-radius:6px;background:#fff;color:var(--datepicker-text-color);cursor:pointer;transition:all 0.2s ease}.flatpickr-today-btn{background:var(--datepicker-primary-color);color:#fff;border-color:var(--datepicker-primary-color)}.flatpickr-today-btn:hover{opacity:0.9;box-shadow:0 2px 4px rgba(0, 0, 0, 0.1)}.flatpickr-clear-btn:hover{background:#fee2e2;border-color:#fca5a5;color:#dc2626}.flatpickr-confirm-btn{flex:1;padding:0.5rem 0.875rem;font-size:0.875rem;font-family:inherit;font-weight:600;border:1.5px solid var(--datepicker-primary-color);border-radius:6px;background:var(--datepicker-primary-color);color:#fff;cursor:pointer;transition:all 0.2s ease}.flatpickr-confirm-btn:hover{opacity:0.9;box-shadow:0 2px 4px rgba(0, 0, 0, 0.1)}.jalali-calendar{position:absolute;top:calc(100% + 4px);right:0;left:auto;width:100%;min-width:320px;max-width:360px;background:#fff;border-radius:var(--datepicker-border-radius);box-shadow:var(--datepicker-shadow);border:1px solid var(--datepicker-border-color);direction:rtl;font-family:var(--datepicker-font-family);z-index:9999;padding:0;overflow:hidden;transition:opacity 0.2s ease}.jalali-calendar.jalali-calendar-loading{opacity:0.7}.jalali-calendar-loading-overlay{position:absolute;inset:0;background:rgba(255, 255, 255, 0.7);display:flex;align-items:center;justify-content:center;border-radius:var(--datepicker-border-radius);z-index:1}.jalali-calendar-spinner{width:24px;height:24px;border:2px solid var(--datepicker-border-color);border-top-color:var(--datepicker-primary-color);border-radius:50%;animation:jalali-spin 0.7s linear infinite}@keyframes jalali-spin{to{transform:rotate(360deg)}}.jalali-calendar-header{display:flex;align-items:center;justify-content:space-between;padding:0.75rem 1rem;background:#fff;color:var(--datepicker-text-color);border-bottom:1px solid var(--datepicker-border-color)}.jalali-calendar-prev,.jalali-calendar-next{background:none;border:none;color:#6b7280;cursor:pointer;padding:0.5rem;display:flex;align-items:center;justify-content:center;fill:#6b7280;border-radius:var(--datepicker-border-radius);transition:all 0.2s ease}.jalali-calendar-prev:hover,.jalali-calendar-next:hover{background:#f3f4f6;fill:var(--datepicker-primary-color);color:var(--datepicker-primary-color)}.jalali-calendar-prev svg,.jalali-calendar-next svg{width:16px;height:16px}.jalali-calendar-prev svg{transform:scaleX(-1)}.jalali-calendar-title{font-weight:700;font-size:1.0625rem;letter-spacing:-0.01em;color:var(--datepicker-text-color)}.jalali-calendar-select{font-family:var(--datepicker-font-family);font-size:0.9375rem;font-weight:600;color:var(--datepicker-text-color);background:#fff;border:1px solid var(--datepicker-border-color);border-radius:6px;padding:0.35rem 0.5rem;cursor:pointer;min-width:0}.jalali-calendar-select-month{flex:1;max-width:120px}.jalali-calendar-select-year{max-width:72px}.jalali-calendar-mode-switch{display:flex;gap:0;padding:0 0.75rem 0.5rem;border-bottom:1px solid var(--datepicker-border-color);background:#fff}.jalali-mode-btn{flex:1;padding:0.5rem 0.75rem;font-size:0.8125rem;font-weight:600;font-family:var(--datepicker-font-family);border:1px solid var(--datepicker-border-color);background:#f9fafb;color:#6b7280;cursor:pointer;transition:all 0.2s ease;border-radius:0}.jalali-mode-btn:first-child{border-radius:0 var(--datepicker-border-radius) var(--datepicker-border-radius) 0;border-inline-start:none}.jalali-mode-btn:last-child{border-radius:var(--datepicker-border-radius) 0 0 var(--datepicker-border-radius)}.jalali-mode-btn:hover{background:#f3f4f6;color:var(--datepicker-text-color)}.jalali-mode-btn.active{background:var(--datepicker-primary-color);color:#fff;border-color:var(--datepicker-primary-color)}.jalali-calendar-weekdays{display:grid;grid-template-columns:repeat(7, 1fr);gap:0;padding:0.625rem 0.75rem 0.375rem;text-align:center;font-size:0.8125rem;font-weight:600;color:#6b7280;background:#f9fafb;border-bottom:1px solid var(--datepicker-border-color)}.jalali-calendar-days{display:grid;grid-template-columns:repeat(7, 1fr);gap:4px;padding:0.75rem;background:#fff}.jalali-day,.jalali-day-empty{aspect-ratio:1;min-height:40px;max-width:46px;display:flex;align-items:center;justify-content:center;border-radius:var(--datepicker-border-radius);font-size:0.9375rem;font-family:var(--datepicker-font-family);font-weight:500;position:relative}.jalali-day{border:1.5px solid transparent;background:none;color:var(--datepicker-text-color);cursor:pointer;margin:0 auto;transition:all 0.15s ease}.jalali-day:hover:not(.jalali-day-start):not(.jalali-day-end){background:var(--datepicker-highlight-color);border-color:var(--datepicker-primary-color)}.jalali-day-empty{visibility:hidden;pointer-events:none}.jalali-day-disabled{opacity:0.45;cursor:not-allowed;pointer-events:none}.jalali-day-today:not(.jalali-day-start):not(.jalali-day-end){border:2px solid var(--datepicker-today-border);font-weight:700;color:var(--datepicker-today-border)}.jalali-day-start,.jalali-day-end{background:var(--datepicker-selected-bg) !important;color:var(--datepicker-selected-text) !important;border-color:var(--datepicker-selected-bg) !important;font-weight:700}.jalali-day-in-range{background:var(--datepicker-range-bg) !important;border-radius:0;border-color:transparent !important}.jalali-day-preview{background:var(--datepicker-range-preview-bg) !important;border-radius:0;border-color:transparent !important}.jalali-day-friday{border:1px solid rgba(239, 68, 68, 0.5) !important}.jalali-day-holiday-official{border:1px solid rgba(239, 68, 68, 0.25) !important}.jalali-day-event{border:1px solid rgba(59, 130, 246, 0.3) !important}.holiday-indicator{display:none}.jalali-calendar-footer{display:flex;gap:0.5rem;padding:0.625rem 0.75rem;border-top:1px solid var(--datepicker-border-color);background:#f9fafb}.jalali-today-btn,.jalali-clear-btn,.jalali-confirm-btn{flex:1;padding:0.5rem 0.875rem;font-size:0.875rem;font-family:inherit;font-weight:500;border:1.5px solid var(--datepicker-border-color);border-radius:6px;background:#fff;color:var(--datepicker-text-color);cursor:pointer;transition:all 0.2s ease}.jalali-today-btn{background:var(--datepicker-primary-color);color:#fff;border-color:var(--datepicker-primary-color)}.jalali-today-btn:hover{opacity:0.9;box-shadow:0 2px 4px rgba(0, 0, 0, 0.1)}.jalali-clear-btn:hover{background:#fee2e2;border-color:#fca5a5;color:#dc2626}.jalali-confirm-btn{font-weight:600;background:var(--datepicker-primary-color);color:#fff;border-color:var(--datepicker-primary-color)}.jalali-confirm-btn:hover{opacity:0.9;box-shadow:0 2px 4px rgba(0, 0, 0, 0.1)}.holiday-tooltip{position:fixed;background:#fff;color:var(--datepicker-text-color);padding:0;border-radius:10px;font-size:0.8125rem;line-height:1.5;white-space:normal;max-width:260px;min-width:160px;z-index:10000;pointer-events:none;box-shadow:0 10px 40px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0, 0, 0, 0.06);animation:tooltipFadeIn 0.2s ease;overflow:hidden;font-family:var(--datepicker-font-family)}.holiday-tooltip-label{display:block;padding:0.5rem 0.875rem;font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.02em;background:var(--datepicker-primary-color);color:#fff}.holiday-tooltip-text{margin:0;padding:0.75rem 0.875rem;font-size:0.875rem;color:var(--datepicker-text-color);line-height:1.6}.holiday-tooltip::after{content:'';position:absolute;top:100%;left:50%;transform:translateX(-50%);width:0;height:0;border-left:8px solid transparent;border-right:8px solid transparent;border-top:8px solid #fff;filter:drop-shadow(0 2px 2px rgba(0, 0, 0, 0.08))}@keyframes tooltipFadeIn{from{opacity:0;transform:translate(-50%, calc(-100% - 6px))}to{opacity:1;transform:translate(-50%, -100%)}}@media (max-width: 640px){.my-range-datepicker-input{max-width:100%}.jalali-calendar{max-width:100%}.jalali-day,.flatpickr-day{min-height:44px;max-width:44px}}.jalali-day:focus-visible,.jalali-today-btn:focus-visible,.jalali-clear-btn:focus-visible,.jalali-calendar-prev:focus-visible,.jalali-calendar-next:focus-visible,.flatpickr-today-btn:focus-visible,.flatpickr-clear-btn:focus-visible,.flatpickr-switch-btn:focus-visible{outline:2px solid var(--datepicker-primary-color);outline-offset:2px}@media (prefers-contrast: high){.my-range-datepicker-input,.jalali-calendar,.flatpickr-calendar{border-width:2px}.jalali-day-holiday{border-width:3px}}@media (prefers-reduced-motion: reduce){*{animation-duration:0.01ms !important;transition-duration:0.01ms !important}}`;

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
const EzRangeDatepicker = class {
    constructor(hostRef) {
        registerInstance(this, hostRef);
        this.dateChanged = createEvent(this, "dateChanged");
        this.dateCleared = createEvent(this, "dateCleared");
        this.calendarOpened = createEvent(this, "calendarOpened");
        this.calendarClosed = createEvent(this, "calendarClosed");
    }
    get el() { return getElement(this); }
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
        const jToday = jalaaliJsExports.toJalaali(today);
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
            const jDate = jalaaliJsExports.toJalaali(startDate);
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
        const j = jalaaliJsExports.toJalaali(date);
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
        return jalaaliJsExports.jalaaliMonthLength(year, month);
    }
    getJalaliFirstDayOfMonth(year, month) {
        const gDate = jalaaliJsExports.toGregorian(year, month, 1);
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
        const gDate = jalaaliJsExports.toGregorian(this.jalaliCurrentYear, this.jalaliCurrentMonth, day);
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
        const jToday = jalaaliJsExports.toJalaali(today);
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
        const gDate = jalaaliJsExports.toGregorian(year, month, day);
        const dayDate = new Date(gDate.gy, gDate.gm - 1, gDate.gd);
        const start = dateAtNoon(this.pendingStartDate);
        const end = dateAtNoon(this.pendingEndDate);
        return dayDate >= start && dayDate <= end;
    }
    /** روز در بازهٔ پیش‌نمایش (هاور) بین start و روز زیر موس */
    isJalaliDayInPreviewRange(year, month, day) {
        if (!this.pendingStartDate || this.pendingEndDate || !this.jalaliHoverDay)
            return false;
        const gDate = jalaaliJsExports.toGregorian(year, month, day);
        const dayDate = new Date(gDate.gy, gDate.gm - 1, gDate.gd);
        const start = dateAtNoon(this.pendingStartDate);
        const h = jalaaliJsExports.toGregorian(this.jalaliHoverDay.year, this.jalaliHoverDay.month, this.jalaliHoverDay.day);
        const hoverDate = new Date(h.gy, h.gm - 1, h.gd);
        const [from, to] = start <= hoverDate ? [start, hoverDate] : [hoverDate, start];
        return dayDate >= from && dayDate <= to;
    }
    isJalaliDayStart(year, month, day) {
        if (!this.pendingStartDate)
            return false;
        const gDate = jalaaliJsExports.toGregorian(year, month, day);
        const dayDate = new Date(gDate.gy, gDate.gm - 1, gDate.gd);
        const start = dateAtNoon(this.pendingStartDate);
        return dayDate.toDateString() === start.toDateString();
    }
    isJalaliDayEnd(year, month, day) {
        if (!this.pendingEndDate)
            return false;
        const gDate = jalaaliJsExports.toGregorian(year, month, day);
        const dayDate = new Date(gDate.gy, gDate.gm - 1, gDate.gd);
        const end = dateAtNoon(this.pendingEndDate);
        return dayDate.toDateString() === end.toDateString();
    }
    isJalaliDayToday(year, month, day) {
        const gDate = jalaaliJsExports.toGregorian(year, month, day);
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
        const g = jalaaliJsExports.toGregorian(year, month, day);
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
        const gDate = jalaaliJsExports.toGregorian(year, month, day);
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
            const jToday = jalaaliJsExports.toJalaali(new Date());
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
    static get watchers() { return {
        "startDate": [{
                "handlePropChange": 0
            }],
        "endDate": [{
                "handlePropChange": 0
            }],
        "themeColor": [{
                "handleThemeColorChange": 0
            }]
    }; }
};
EzRangeDatepicker.style = rangeDatepickerCss();

export { EzRangeDatepicker as ez_range_datepicker };
