/**
 * Gateway booking for reserve.php week grid only (not single-product calendar).
 */
import * as ezBookingApi from './ez-booking-api.js';

/**
 * @param {number} productId
 * @param {number} dayStart
 */
export async function loadReserveWeekTable(productId, dayStart) {
  const root = document.getElementById('table-of-sans');
  if (!root || !productId || !dayStart) {
    return;
  }
  const skeleton =
    "<div class='grid gap-3' style='grid-template-columns: repeat(7, minmax(0, 1fr))'>" +
    '<div class="skeleton aspect-square rounded-xl"></div>'.repeat(7 * 4) +
    '</div>';
  root.innerHTML = skeleton;
  try {
    const html = await ezBookingApi.sansWeekHtml(productId, dayStart);
    if (html && typeof html === 'string' && !html.trim().startsWith('[')) {
      root.innerHTML = html;
      if (window.htmx?.process) {
        window.htmx.process(root);
      }
    } else {
      console.error('[ez-booking] sans_week returned non-HTML payload');
    }
  } catch (e) {
    console.error('[ez-booking] sans_week failed', e);
  }
}

/**
 * Init booking gateway UI on non–single-product pages (reserve calendar).
 * Week loads are driven by reserve.php BuildTable → window.ezBookingLoadWeek.
 */
export function initBookingGatewayPages() {
  if (!window.__EZ_BOOT__?.sub_secret) {
    return;
  }
  if (document.getElementById('table-of-sans')) {
    window.ezBookingLoadWeek = loadReserveWeekTable;
  }
}
