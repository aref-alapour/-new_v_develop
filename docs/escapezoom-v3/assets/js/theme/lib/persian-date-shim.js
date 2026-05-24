import jalaali from 'jalaali-js';

const PERSIAN_MONTHS = [
  'فروردین',
  'اردیبهشت',
  'خرداد',
  'تیر',
  'مرداد',
  'شهریور',
  'مهر',
  'آبان',
  'آذر',
  'دی',
  'بهمن',
  'اسفند',
];

const PERSIAN_WEEKDAYS = [
  'یکشنبه',
  'دوشنبه',
  'سه‌شنبه',
  'چهارشنبه',
  'پنجشنبه',
  'جمعه',
  'شنبه',
];

function toParts(unixSeconds) {
  const date = new Date(unixSeconds * 1000);
  const { jy, jm, jd } = jalaali.toJalaali(
    date.getFullYear(),
    date.getMonth() + 1,
    date.getDate()
  );

  return {
    jy,
    jm,
    jd,
    weekday: PERSIAN_WEEKDAYS[date.getDay()],
  };
}

class PersianDateInstance {
  constructor(unixSeconds) {
    this.unixSeconds = unixSeconds;
  }

  format(pattern) {
    const date = new Date(this.unixSeconds * 1000);
    const hh = String(date.getHours()).padStart(2, '0');
    const minute = String(date.getMinutes()).padStart(2, '0');
    const { jy, jm, jd, weekday } = toParts(this.unixSeconds);
    const day = String(jd).padStart(2, '0');
    const month = String(jm).padStart(2, '0');

    return String(pattern)
      .replace(/dddd/g, weekday)
      .replace(/MMMM/g, PERSIAN_MONTHS[jm - 1] || '')
      .replace(/YYYY/g, String(jy))
      .replace(/MM/g, month)
      .replace(/DD/g, day)
      .replace(/\bD\b/g, String(jd))
      .replace(/HH/g, hh)
      .replace(/mm/g, minute);
  }
}

const persianDate = {
  unix(unixSeconds) {
    return new PersianDateInstance(unixSeconds);
  },
};

window.persianDate = persianDate;

export default persianDate;
