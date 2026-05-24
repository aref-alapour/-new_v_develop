import flatpickr from 'flatpickr';

function formatJalali(date) {
  if (!window.persianDate) {
    return '';
  }
  return window.persianDate.unix(Math.floor(date.getTime() / 1000)).format('YYYY/MM/DD');
}

export function initJalaliFlatpickr() {
  document.querySelectorAll('.persian-date-picker, .persian-datepicker, .flatpickr-jalali').forEach((element) => {
    if (element.dataset.ezFlatpickr === '1') {
      return;
    }

    element.dataset.ezFlatpickr = '1';
    flatpickr(element, {
      dateFormat: 'Y/m/d',
      disableMobile: true,
      onChange(selectedDates) {
        if (!selectedDates[0]) {
          return;
        }
        element.value = formatJalali(selectedDates[0]);
      },
    });
  });
}

window.EzFlatpickr = {
  init: initJalaliFlatpickr,
};
