/**
 * Client-side week grid for reserve.php when gateway returns JSON.
 */

const EZ_TZ = 'Asia/Tehran';

/**
 * @param {number} unixSec
 * @param {'HH:mm'|'d'|'l'} part
 */
function tehranFmt(unixSec, part) {
  const d = new Date(unixSec * 1000);
  const base = { timeZone: EZ_TZ };
  if (part === 'HH:mm') {
    return new Intl.DateTimeFormat('en-GB', {
      timeZone: EZ_TZ,
      hour: '2-digit',
      minute: '2-digit',
      hour12: false,
    }).format(d);
  }
  if (part === 'd') {
    return new Intl.DateTimeFormat('fa-IR', { day: 'numeric', ...base }).format(d);
  }
  if (part === 'l') {
    return new Intl.DateTimeFormat('fa-IR', { weekday: 'long', ...base }).format(d);
  }
  return '';
}

/**
 * @param {unknown} parsed
 * @param {number} dayStart
 * @returns {Array<Array<Record<string, unknown>>>}
 */
export function normalizeWeekDays(parsed, dayStart) {
  if (!Array.isArray(parsed) || parsed.length === 0) {
    return Array.from({ length: 7 }, () => []);
  }

  const first = parsed[0];
  const isFlat =
    first &&
    typeof first === 'object' &&
    !Array.isArray(first) &&
    Object.prototype.hasOwnProperty.call(first, 'time');

  if (isFlat) {
    const week = Array.from({ length: 7 }, () => /** @type {Array<Record<string, unknown>>} */ ([]));
    for (const row of parsed) {
      if (!row || typeof row !== 'object') {
        continue;
      }
      const ts = parseInt(String(/** @type {{ time?: unknown }} */ (row).time), 10);
      if (!Number.isFinite(ts)) {
        continue;
      }
      const idx = Math.floor((ts - dayStart) / 86400);
      if (idx >= 0 && idx < 7) {
        week[idx].push(/** @type {Record<string, unknown>} */ (row));
      }
    }
    return week;
  }

  const week = Array.from({ length: 7 }, () => /** @type {Array<Record<string, unknown>>} */ ([]));
  for (let i = 0; i < 7; i++) {
    const bucket = parsed[i];
    if (!Array.isArray(bucket)) {
      continue;
    }
    week[i] = bucket.filter((r) => r && typeof r === 'object');
  }
  return week;
}

/**
 * @param {Record<string, unknown>} item
 * @param {number} minPlayers
 */
function renderReservableBox(item, minPlayers) {
  const time = parseInt(String(item.time), 10);
  const price = parseInt(String(item.price ?? 0), 10);
  const offPrice = parseInt(String(item.off_price ?? 0), 10);
  const sell = offPrice > 0 ? offPrice : price;
  const hasOff = offPrice > 0;
  const timeLabel = tehranFmt(time, 'HH:mm');
  const priceFmt = (n) => new Intl.NumberFormat('fa-IR').format(n);

  const priceBlock = hasOff
    ? `<span class="text-md shrink-0 max-lg:flex items-center max-lg:px-2 drop-shadow-104 py-0.5 bg-black/5 line-through max-lg:text-16 max-lg:gap-1">${priceFmt(price)} تومان</span>
       <span class="text-md shrink-0 max-lg:flex items-center justify-center max-lg:px-2 drop-shadow-104 py-0.5 bg-black/15 w-30 lg:w-full text-center max-lg:text-22 max-lg:gap-1">${priceFmt(offPrice)} <span class="max-lg:text-12">تومان</span></span>`
    : `<span class="text-md shrink-0 max-lg:flex items-center justify-center max-lg:px-2 drop-shadow-104 py-0.5 bg-black/15 w-30 lg:w-full text-center max-lg:text-22 max-lg:gap-1">${priceFmt(price)} <span class="max-lg:text-12">تومان</span></span>`;

  const offClass = hasOff ? ' off' : '';

  return `<div data-item-timestamp="${time}" data-item-sell-price="${sell}" class="box open cursor-pointer${offClass} max-lg:h-12 h-[112px] rounded-lg w-full shadow-102 overflow-hidden relative">
    <div class="back text-white bg-blue absolute w-full h-full flex lg:flex-col text-center justify-between">
      <span class="text-2xl drop-shadow-104 flex grow w-full lg:justify-center items-center max-lg:px-2 max-lg:text-30">${timeLabel}</span>
      ${priceBlock}
    </div>
    <div class="front text-textColor bg-white px-1.5 absolute top-full w-full h-full flex text-center justify-between transition-all duration-150 items-center">
      <button type="button" data-action="plus" class="bg-accent-450 rounded p-2 aspect-square">+</button>
      <span class="flex lg:flex-col max-lg:items-center max-lg:gap-x-4 text-center leading-4 text-xl">
        <strong class="text-4xl">${minPlayers}</strong> نفر
      </span>
      <button type="button" data-action="minus" class="bg-gray-400 rounded p-2 aspect-square">−</button>
    </div>
  </div>`;
}

/**
 * @param {Record<string, unknown>} item
 */
function renderStatusBox(item) {
  const time = parseInt(String(item.time), 10);
  const status = String(item.status ?? '');
  const timeLabel = tehranFmt(time, 'HH:mm');

  if (status === 'non_reservable') {
    return `<div class="box closed cursor-not-allowed max-lg:h-12.5 h-[112px] rounded-lg w-full shadow-102 overflow-hidden relative">
    <div class="back bg-[#F2F6FA] text-[#334155] absolute w-full h-full flex lg:flex-col text-center justify-between">
      <span class="text-2xl font-extrabold flex grow w-full lg:justify-center items-center max-lg:px-2 max-lg:text-30">${timeLabel}</span>
      <span class="text-md font-bold shrink-0 max-lg:flex items-center justify-center max-lg:px-2 py-2 bg-[#CBD5E1] w-30 lg:w-full max-lg:text-22 text-[#334155]">بسته شده</span>
    </div>
  </div>`;
  }

  const labels = {
    reserving: ['bg-[#EDA10D]', 'در حال رزرو'],
    reserved: ['bg-[#EF4E5D]', 'رزرو شده'],
  };
  const cfg = labels[status] || labels.reserved;
  return `<div class="box ${status === 'reserving' ? 'reserving cursor-wait' : status} max-lg:h-12.5 h-[112px] rounded-lg w-full shadow-102 overflow-hidden relative">
    <div class="back text-white ${cfg[0]} absolute w-full h-full flex lg:flex-col text-center justify-between">
      <span class="text-2xl drop-shadow-104 flex grow w-full lg:justify-center items-center max-lg:px-2">${timeLabel}</span>
      <span class="text-md shrink-0 max-lg:flex items-center justify-center max-lg:px-2 drop-shadow-104 py-2 bg-black/15 w-30 lg:w-full max-lg:text-22">${cfg[1]}</span>
    </div>
  </div>`;
}

/**
 * @param {Array<Array<Record<string, unknown>>>} weekDays
 * @param {number} dayStart
 * @param {{ minPlayers?: number }} [opts]
 */
export function renderReserveWeekHtml(weekDays, dayStart, opts = {}) {
  const minPlayers = opts.minPlayers ?? 2;
  const todayStart = new Date();
  todayStart.setHours(0, 0, 0, 0);
  const todayTs = Math.floor(todayStart.getTime() / 1000);

  let tabs = '<div class="flex justify-around mb-12 text-nowrap max-lg:gap-5.5 overflow-x-auto no-scrollbar">';
  for (let i = 0; i < 7; i++) {
    const ts = dayStart + i * 86400;
    const isToday = dayStart === todayTs && i === 0;
    const activeStyle =
      i === 0 ? ' style="background: rgb(80, 145, 251); border-color: transparent; color: rgb(255, 255, 255);"' : '';
    tabs += `<button type="button" data-tab="${i}" class="bg-[#F9FAFB] font-extrabold border border-[#E8EDF1] flex flex-col items-center justify-center rounded-xl p-4 leading-4 shadow-13 w-[80px] h-[120px] gap-1"${activeStyle}>`;
    if (isToday) {
      tabs += `<span class="text-16">امروز</span><span class="text-26">${tehranFmt(ts, 'd')}</span>`;
    } else {
      tabs += `<span class="text-14">${tehranFmt(ts, 'l')}</span><span class="text-26">${tehranFmt(ts, 'd')}</span>`;
    }
    tabs += '</button>';
  }
  tabs += '</div>';

  let grid = '<div class="flex justify-between mb-12">';
  for (let di = 0; di < 7; di++) {
    const hidden = di !== 0 ? ' max-lg:hidden' : '';
    grid += `<div id="tab-${di}" class="tabs flex flex-col max-lg:px-0 px-4 w-full lg:border-l last-of-type:border-l-0 gap-4${hidden}">`;
    const day = weekDays[di] || [];
    for (const item of day) {
      const status = String(item.status ?? '');
      if (status === 'reservable') {
        grid += renderReservableBox(item, minPlayers);
      } else if (status) {
        grid += renderStatusBox(item);
      }
    }
    grid += '</div>';
  }
  grid += '</div>';

  const footer = `<div class="reserve-result border max-lg:p-4 p-10 rounded-2xl text-lg hidden items-center max-lg:gap-0 gap-20 max-lg:flex-col">
    <div class="flex grow justify-between max-lg:flex-wrap max-lg:border max-lg:shadow-13 border-0 shadow-none max-lg:mb-4 max-lg:w-full max-lg:p-4 rounded-2xl">
      <span class="text-slate-130 max-lg:w-full">انتخاب شما</span>
      <div class="selected-date flex gap-2"></div>
      <div class="ticket-count"></div>
    </div>
    <a href="#" class="bg-accent-420 flex rounded-xl overflow-hidden items-center justify-center text-white gap-3 max-lg:w-full max-lg:justify-between">
      <span class="flex items-center gap-3 py-3 px-16 max-lg:px-4">پرداخت و ثبت رزرو</span>
      <strong class="bg-accent-450 py-3 px-8 max-lg:px-4"></strong>
    </a>
  </div>`;

  return tabs + grid + footer;
}

/**
 * @param {string} text
 */
export function isJsonLike(text) {
  const t = String(text).replace(/^\uFEFF/, '').trim();
  return t.startsWith('[') || t.startsWith('{');
}
