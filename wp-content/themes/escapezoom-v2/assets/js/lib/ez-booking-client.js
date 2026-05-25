/**
 * Signed gateway helpers for owner sans management (panel sans-manager).
 */
import { ezFetch } from './ez-ajax.js';

/**
 * @param {string} action booking.sans_management_web | booking.open_sans | booking.close_sans
 * @param {Record<string, unknown>} body
 * @returns {Promise<Response>}
 */
export function ezBookingGateway(action, body = {}) {
  return ezFetch(action, body);
}

/**
 * @param {number} productId
 * @param {number} dayStart
 * @returns {Promise<string>}
 */
export async function ezBookingSansManagementHtml(productId, dayStart) {
  const resp = await ezBookingGateway('booking.sans_management_web', {
    product_id: productId,
    day_start_time: dayStart,
  });
  return resp.text();
}

/**
 * @param {'open'|'close'} verb
 * @param {number} productId
 * @param {number} sansTime
 */
export async function ezBookingToggleSans(verb, productId, sansTime) {
  const action = verb === 'open' ? 'booking.open_sans' : 'booking.close_sans';
  const resp = await ezBookingGateway(action, {
    product_id: productId,
    sans_time: sansTime,
  });
  return resp.json();
}
