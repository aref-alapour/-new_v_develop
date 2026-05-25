import { ezFetch } from './ez-ajax.js';

/** @type {AbortController|null} */
let sansDayJsonController = null;

/** @type {AbortController|null} */
let sansManagementWebController = null;

/** @type {AbortController|null} */
let toggleSansController = null;

/**
 * @param {AbortController|null} current
 * @param {AbortController} next
 */
function replaceController(current, next) {
  if (current) {
    current.abort();
  }
  return next;
}

/**
 * @param {unknown} error
 * @returns {boolean}
 */
function isAbortError(error) {
  return (
    error instanceof DOMException && error.name === 'AbortError'
  ) || (error && typeof error === 'object' && error.name === 'AbortError');
}

/**
 * @param {number} productId
 * @param {number} dayStart unix day start
 * @returns {Promise<string|null>} HTML or null when superseded
 */
export async function sansManagementWeb(productId, dayStart) {
  sansManagementWebController = replaceController(
    sansManagementWebController,
    new AbortController()
  );
  const controller = sansManagementWebController;

  try {
    const resp = await ezFetch(
      'booking.sans_management_web',
      {
        product_id: parseInt(productId, 10),
        day_start_time: parseInt(dayStart, 10),
      },
      { signal: controller.signal }
    );
    return await resp.text();
  } catch (error) {
    if (isAbortError(error)) {
      return null;
    }
    throw error;
  } finally {
    if (sansManagementWebController === controller) {
      sansManagementWebController = null;
    }
  }
}

/**
 * @param {'open'|'close'} kind
 * @param {number} productId
 * @param {number} sansTime
 */
export async function toggleSans(kind, productId, sansTime) {
  toggleSansController = replaceController(
    toggleSansController,
    new AbortController()
  );
  const controller = toggleSansController;

  try {
    const action = kind === 'open' ? 'booking.open_sans' : 'booking.close_sans';
    const resp = await ezFetch(
      action,
      {
        product_id: parseInt(productId, 10),
        sans_time: parseInt(sansTime, 10),
      },
      { signal: controller.signal }
    );
    return await resp.json();
  } catch (error) {
    if (isAbortError(error)) {
      return null;
    }
    throw error;
  } finally {
    if (toggleSansController === controller) {
      toggleSansController = null;
    }
  }
}

/**
 * Legacy flat JSON for single-product BuildSans (same as get_sanses days=1).
 *
 * @param {number} productId
 * @param {number} dayStart unix day start
 * @returns {Promise<Array<Record<string, unknown>>|null>}
 */
export async function sansDayJson(productId, dayStart) {
  sansDayJsonController = replaceController(
    sansDayJsonController,
    new AbortController()
  );
  const controller = sansDayJsonController;

  try {
    const resp = await ezFetch(
      'booking.sans_day_json',
      {
        product_id: parseInt(productId, 10),
        day_start_time: parseInt(dayStart, 10),
        days: 1,
      },
      { signal: controller.signal }
    );
    const text = await resp.text();
    const parsed = JSON.parse(text);
    return Array.isArray(parsed) ? parsed : [];
  } catch (error) {
    if (isAbortError(error)) {
      return null;
    }
    throw error;
  } finally {
    if (sansDayJsonController === controller) {
      sansDayJsonController = null;
    }
  }
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
