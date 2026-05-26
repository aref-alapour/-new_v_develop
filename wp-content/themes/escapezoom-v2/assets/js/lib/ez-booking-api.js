import { ezFetch } from './ez-ajax.js';
import {
  isJsonLike,
  normalizeWeekDays,
  renderReserveWeekHtml,
} from './ez-booking-week-render.js';

/** @type {AbortController|null} */
let sansDayJsonController = null;

/** @type {AbortController|null} */
let sansManagementWebController = null;

/** @type {AbortController|null} */
let toggleSansController = null;

/** @type {AbortController|null} */
let sansWeekController = null;

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
 * Parse gateway raw JSON (strips BOM; normalizes mistaken nested day bucket).
 *
 * @param {string} text
 * @returns {Array<Record<string, unknown>>}
 */
/**
 * @param {string} text
 * @param {{ days?: number }} [options] days=1 → flat list; days>1 → keep nested week buckets
 */
export function parseGatewaySansJson(text, options = {}) {
  const cleaned = String(text).replace(/^\uFEFF/, '').trim();
  if (!cleaned) {
    return [];
  }
  let parsed;
  try {
    parsed = JSON.parse(cleaned);
  } catch (err) {
    console.error('[EZ Booking] Invalid JSON from gateway:', cleaned.slice(0, 200), err);
    throw err;
  }
  if (!Array.isArray(parsed)) {
    return [];
  }
  const days = options.days ?? 1;
  if (
    days <= 1 &&
    parsed.length > 0 &&
    Array.isArray(parsed[0]) &&
    !Object.prototype.hasOwnProperty.call(parsed[0], 'time')
  ) {
    return /** @type {Array<Record<string, unknown>>} */ (parsed[0]);
  }
  return parsed;
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
    return parseGatewaySansJson(text);
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

/**
 * Legacy admin-ajax HTML when gateway returns JSON by mistake.
 *
 * @param {number} productId
 * @param {number} dayStart
 * @returns {Promise<string>}
 */
async function fetchReserveWeekTableLegacy(productId, dayStart) {
  const root = document.getElementById('table-of-sans');
  const ajaxUrl =
    root?.dataset?.ajaxUrl ||
    (typeof window.ajaxurl === 'string' ? window.ajaxurl : '');
  const nonce = root?.dataset?.ajaxNonce || '';
  if (!ajaxUrl || !nonce) {
    throw new Error('[EZ Booking] reserve ajax fallback missing nonce/url');
  }
  const body = new URLSearchParams({
    action: 'v2_ajax_handler',
    nonce,
    callback: 'reserve_get_table',
    time: String(dayStart),
    product: String(productId),
  });
  const resp = await fetch(ajaxUrl, {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: body.toString(),
  });
  if (!resp.ok) {
    throw new Error(`reserve_get_table HTTP ${resp.status}`);
  }
  return (await resp.text()).replace(/^\uFEFF/, '').trim();
}

/**
 * Fetch week sans as JSON and render reserve grid HTML in the browser.
 *
 * @param {number} productId
 * @param {number} dayStart
 */
async function fetchAndRenderWeekFromJson(productId, dayStart, signal) {
  const resp = await ezFetch(
    'booking.sans_day_json',
    {
      product_id: parseInt(productId, 10),
      day_start_time: parseInt(dayStart, 10),
      days: 7,
    },
    { signal }
  );
  const parsed = parseGatewaySansJson(await resp.text(), { days: 7 });
  const week = normalizeWeekDays(parsed, parseInt(dayStart, 10));
  return renderReserveWeekHtml(week, parseInt(dayStart, 10));
}

export async function sansWeekHtml(productId, dayStart) {
  const pid = parseInt(productId, 10);
  const day = parseInt(dayStart, 10);

  sansWeekController = replaceController(sansWeekController, new AbortController());
  const controller = sansWeekController;

  try {
    try {
      return await fetchAndRenderWeekFromJson(pid, day, controller.signal);
    } catch (jsonErr) {
      if (isAbortError(jsonErr)) {
        return null;
      }
      console.warn('[EZ Booking] JSON week via light gateway failed, trying sans_week HTML', jsonErr);
    }

    const resp = await ezFetch(
      'booking.sans_week',
      {
        product_id: pid,
        day_start_time: day,
        days: 7,
      },
      { signal: controller.signal }
    );
    const text = (await resp.text()).replace(/^\uFEFF/, '').trim();

    if (!isJsonLike(text) && text.includes('class="box')) {
      return text;
    }

    if (isJsonLike(text)) {
      const week = normalizeWeekDays(parseGatewaySansJson(text, { days: 7 }), day);
      return renderReserveWeekHtml(week, day);
    }
  } catch (err) {
    if (isAbortError(err)) {
      return null;
    }
    console.warn('[EZ Booking] sans_week HTML failed, trying admin-ajax', err);
  }

  try {
    const legacy = await fetchReserveWeekTableLegacy(pid, day);
    if (isJsonLike(legacy)) {
      const week = normalizeWeekDays(parseGatewaySansJson(legacy, { days: 7 }), day);
      return renderReserveWeekHtml(week, day);
    }
    return legacy;
  } catch (legacyErr) {
    if (isAbortError(legacyErr)) {
      return null;
    }
    throw legacyErr;
  } finally {
    if (sansWeekController === controller) {
      sansWeekController = null;
    }
  }
}
