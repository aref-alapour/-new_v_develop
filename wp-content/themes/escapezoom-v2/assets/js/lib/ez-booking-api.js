import { ezFetch } from './ez-ajax.js';

/**
 * @param {number} productId
 * @param {number} dayStart unix day start
 * @returns {Promise<string>} HTML
 */
export async function sansManagementWeb(productId, dayStart) {
  const resp = await ezFetch('booking.sans_management_web', {
    product_id: productId,
    day_start_time: dayStart,
  });
  return resp.text();
}

/**
 * @param {'open'|'close'} kind
 * @param {number} productId
 * @param {number} sansTime
 */
export async function toggleSans(kind, productId, sansTime) {
  const action = kind === 'open' ? 'booking.open_sans' : 'booking.close_sans';
  const resp = await ezFetch(action, {
    product_id: productId,
    sans_time: sansTime,
  });
  return resp.json();
}

/**
 * Legacy flat JSON for single-product BuildSans (same as get_sanses days=1).
 *
 * @param {number} productId
 * @param {number} dayStart unix day start
 * @returns {Promise<Array<Record<string, unknown>>>}
 */
export async function sansDayJson(productId, dayStart) {
  const resp = await ezFetch('booking.sans_day_json', {
    product_id: productId,
    day_start_time: dayStart,
    days: 1,
  });
  const text = await resp.text();
  const parsed = JSON.parse(text);
  return Array.isArray(parsed) ? parsed : [];
}

export async function sansDayHtml(productId, dayStart) {
  const resp = await ezFetch('booking.sans_day', {
    product_id: productId,
    day_start_time: dayStart,
  });
  return resp.text();
}

export async function sansWeekHtml(productId, dayStart) {
  const resp = await ezFetch('booking.sans_week', {
    product_id: productId,
    day_start_time: dayStart,
  });
  return resp.text();
}
