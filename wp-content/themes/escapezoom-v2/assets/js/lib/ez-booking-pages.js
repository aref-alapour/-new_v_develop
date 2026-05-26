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

function bindReserveWeekTable() {
  const root = document.getElementById('table-of-sans');
  if (!root?.dataset.productId) {
    return;
  }
  const productId = parseInt(root.dataset.productId, 10);
  const dayStart = parseInt(root.dataset.dayStart || '0', 10);
  loadReserveWeekTable(productId, dayStart);

  document.body.addEventListener('click', (ev) => {
    const btn = ev.target.closest('[data-timestamp]');
    if (!btn || !document.getElementById('table-of-sans')) {
      return;
    }
    const ts = parseInt(btn.getAttribute('data-timestamp') || '0', 10);
    if (ts > 0) {
      loadReserveWeekTable(productId, ts);
    }
  });
}

/**
 * Init booking gateway UI on non–single-product pages (reserve calendar).
 */
export function initBookingGatewayPages() {
  if (!window.__EZ_BOOT__?.sub_secret) {
    return;
  }
  if (document.getElementById('table-of-sans')) {
    window.ezBookingLoadWeek = loadReserveWeekTable;
    bindReserveWeekTable();
  }
}
