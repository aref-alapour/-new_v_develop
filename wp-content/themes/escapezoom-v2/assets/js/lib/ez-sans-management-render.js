/**
 * Client-side HTML for team/panel sans-management (JSON-first contract).
 */

function escHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function escAttr(value) {
  return escHtml(value).replace(/'/g, '&#39;');
}

function mojavezedarBadgeHtml() {
  return '<span class="inline-flex items-center leading-6 px-3 rounded-full gap-2 text-xs font-bold" style="color:#6D28D9;background:rgba(109,40,217,0.14);">مجموعه دار</span>';
}

function reservedEyeSvg() {
  return '<svg xmlns="http://www.w3.org/2000/svg" class="mx-0" width="19" height="19" viewBox="0 0 19 19" fill="none">'
    + '<rect x="0.5" y="0.5" width="18" height="18" rx="4" fill="#FF6900" />'
    + '<path d="M14.9397 8.95573C15.113 9.19853 15.1996 9.3205 15.1996 9.50003C15.1996 9.68013 15.113 9.80153 14.9397 10.0443C14.1612 11.1363 12.1727 13.4896 9.5002 13.4896C6.82717 13.4896 4.83921 11.1358 4.06067 10.0443C3.88741 9.80153 3.80078 9.67956 3.80078 9.50003C3.80078 9.31993 3.88741 9.19853 4.06067 8.95573C4.83921 7.86373 6.82774 5.51044 9.5002 5.51044C12.1732 5.51044 14.1612 7.8643 14.9397 8.95573Z" stroke="white" stroke-linecap="round" stroke-linejoin="round" />'
    + '<path d="M11.2107 9.49999C11.2107 9.04651 11.0305 8.61161 10.7099 8.29096C10.3892 7.9703 9.95431 7.79016 9.50084 7.79016C9.04737 7.79016 8.61247 7.9703 8.29181 8.29096C7.97116 8.61161 7.79102 9.04651 7.79102 9.49999C7.79102 9.95346 7.97116 10.3884 8.29181 10.709C8.61247 11.0297 9.04737 11.2098 9.50084 11.2098C9.95431 11.2098 10.3892 11.0297 10.7099 10.709C11.0305 10.3884 11.2107 9.95346 11.2107 9.49999Z" stroke="white" stroke-linecap="round" stroke-linejoin="round" />'
    + '</svg>';
}

function renderBulkRadios(isAllClosed) {
  const openChecked = !isAllClosed ? 'checked' : '';
  const closeChecked = isAllClosed ? 'checked' : '';
  const openLabelClass = !isAllClosed ? 'text-gray-900' : 'text-[#90A1B9]';
  const closeLabelClass = isAllClosed ? 'text-gray-900' : 'text-[#90A1B9]';

  return '<div id="radio-toggle-template" style="display: none;">'
    + '<div class="flex items-center justify-center gap-6 w-full mb-4">'
    + `<label class="flex items-center gap-2 cursor-pointer text-sm font-bold ${openLabelClass}">`
    + `<input type="radio" name="bulk_action" value="open_all" class="form-radio text-[#f97316] focus:ring-[#f97316] w-5 h-5" ${openChecked}>`
    + '<span>باز کردن همه سانس ها</span></label>'
    + `<label class="flex items-center gap-2 cursor-pointer text-sm font-bold ${closeLabelClass}">`
    + `<input type="radio" name="bulk_action" value="close_all" class="form-radio text-[#9ca3af] focus:ring-[#9ca3af] w-5 h-5" ${closeChecked}>`
    + '<span>بستن همه سانس ها</span></label>'
    + '</div></div>';
}

/**
 * @param {Array<Record<string, unknown>>} reservationData
 * @param {number} productId
 * @param {number} dayStart
 */
function renderSlots(reservationData, productId, dayStart) {
  let html = '';

  for (const data of reservationData) {
    const time = Number(data.time || 0);
    const status = String(data.status || '');
    const timeLbl = escHtml(data.time_lbl || '');

    if (status === 'reserved' && data.reserved_data && typeof data.reserved_data === 'object') {
      const rd = /** @type {Record<string, unknown>} */ (data.reserved_data);
      const ezSansCid = Number(rd.customer_id || 0);
      const ezSansMoj = !!rd.is_mojavezedar;
      const userInfo = {
        customer_id: rd.customer_id || 0,
        name: rd.name || '',
        level_title: rd.level_title || '',
        level_color: rd.level_color || '',
        phone: rd.phone || '',
        order_id: rd.order_id || 0,
        date: rd.name || '',
        quantity: rd.quantity || 0,
      };
      const userInfoAttr = escAttr(JSON.stringify(userInfo));
      const slotPre = ezSansMoj ? mojavezedarBadgeHtml() : '';
      const slotAttr = ezSansMoj ? ' data-ez-mojavezedar="1"' : '';
      const name = escHtml(rd.name || '');

      html += `<div class="rounded-xl border border-orangee bg-[#F1F5F9] px-4 py-2.5 shadow-13 openModalInfo cursor-pointer" style="box-shadow: 0px 1px 0px 0px #FF6900;" data-user-info='${userInfoAttr}'>`;
      html += `<bdo dir="ltr" class="text-2xl block text-center font-yekan-bold"> ${timeLbl} </bdo>`;
      html += '<div class="space-y-2.5 mt-3"><div class="flex items-center justify-between gap-7 bg-white h-[39px] rounded-lg px-3 py-2">';
      html += '<div class="flex items-center gap-2 min-w-0 flex-wrap">';
      html += `<span class="text-xs font-bold text-navyBlue">${name}</span>`;
      html += `<span class="ez-sans-badge-slot inline-flex flex-wrap shrink-0" data-ez-customer="${escAttr(String(ezSansCid))}"${slotAttr}>${slotPre}</span>`;
      html += '</div>';
      html += reservedEyeSvg();
      html += '</div></div></div>';
      continue;
    }

    if (status === 'closeable') {
      html += '<div class="rounded-xl border border-[#DBE2EA] bg-white p-2.5 shadow-13">';
      html += `<bdo dir="ltr" class="text-2xl block text-center font-yekan-bold">${timeLbl}</bdo>`;
      html += `<button type="button" data-room-action="close" data-product="${escAttr(String(productId))}" data-timestamp="${escAttr(`${time}.${dayStart}`)}" class="toggle-btn h-10 w-full rounded-lg font-yekan-bold mt-3 bg-[#04B968] text-white">باز</button>`;
      html += '</div>';
      continue;
    }

    if (status === 'openable') {
      html += '<div class="rounded-xl border border-[#DBE2EA] bg-white p-2.5 shadow-13">';
      html += `<bdo dir="ltr" class="text-2xl block text-center font-yekan-bold">${timeLbl}</bdo>`;
      html += `<button type="button" data-room-action="open" data-product="${escAttr(String(productId))}" data-timestamp="${escAttr(`${time}.${dayStart}`)}" class="toggle-btn h-10 w-full rounded-lg font-yekan-bold mt-3 bg-[#E2E8F0] text-black">بسته</button>`;
      html += '</div>';
      continue;
    }

    if (status === 'reserving') {
      html += '<div class="rounded-xl border border-[#DBE2EA] bg-white p-2.5 shadow-13 opacity-90">';
      html += `<bdo dir="ltr" class="text-2xl block text-center font-yekan-bold">${timeLbl}</bdo>`;
      html += '<button type="button" disabled class="h-10 w-full rounded-lg font-yekan-bold mt-3 bg-[#EDA10D] text-white cursor-wait">در حال رزرو</button>';
      html += '</div>';
    }
  }

  return html;
}

/**
 * @param {Record<string, unknown>|null|undefined} data
 * @param {number} productId
 * @param {number} dayStart
 * @returns {string}
 */
export function renderSansManagementGrid(data, productId, dayStart) {
  if (!data || !Array.isArray(data.reservation_data)) {
    return '';
  }

  const isAllClosed = !!data.is_all_closed;
  return renderBulkRadios(isAllClosed)
    + renderSlots(/** @type {Array<Record<string, unknown>>} */ (data.reservation_data), productId, dayStart);
}
