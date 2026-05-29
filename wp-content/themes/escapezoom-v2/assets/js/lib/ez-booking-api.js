import { ezFetch, readGatewayBodyText } from './ez-ajax.js';
import {
  isJsonLike,
  normalizeWeekDays,
  renderReserveWeekHtml,
} from './ez-booking-week-render.js';

/** @type {AbortController|null} */
let sansDayJsonController = null;

/** @type {string} */
let sansDayJsonInFlightKey = '';

/** @type {AbortController|null} */
let sansManagementWebController = null;

/** @type {AbortController|null} */
let toggleSansController = null;

/** @type {AbortController|null} */
let sansWeekController = null;

/** @type {string} */
let sansWeekInFlightKey = '';

/** @type {AbortController|null} */
let checkPlayingController = null;

/** @type {AbortController|null} */
let gameSearchController = null;

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
 * @param {Response} resp
 * @param {string} bodyText
 * @returns {boolean}
 */
function isGatewayAuthFailure(resp, bodyText) {
  if (resp.status !== 401 && resp.status !== 403) {
    return false;
  }
  try {
    const data = JSON.parse(bodyText);
    const code = data?.error?.code;
    return code === 'BAD_SIGNATURE' || code === 'MISSING_HEADERS' || code === 'BAD_TIMESTAMP' || code === 'REPLAY';
  } catch {
    return true;
  }
}

/**
 * Reject gateway error envelopes before parsing sans JSON/HTML.
 *
 * @param {Response} resp
 * @param {string} text
 */
function assertGatewayJsonResponse(resp, text) {
  const body = String(text).replace(/^\uFEFF/, '').trim();

  if (!resp.ok) {
    let code = '';
    let message = '';
    try {
      const data = JSON.parse(body);
      if (data && data.ok === false && data.error) {
        code = String(data.error.code || '');
        message = String(data.error.message || '');
      }
    } catch (_) {
      /* non-JSON error body */
    }
    const err = new Error(
      code
        ? `[EZ Booking] Gateway error: ${code}${message ? ` — ${message}` : ''} (HTTP ${resp.status})`
        : `[EZ Booking] Gateway HTTP ${resp.status}`
    );
    if (isGatewayAuthFailure(resp, body)) {
      err.gatewayAuth = true;
    }
    throw err;
  }

  if (body.startsWith('{')) {
    try {
      const data = JSON.parse(body);
      if (data && data.ok === false) {
        const code = String(data.error?.code || 'GATEWAY_ERROR');
        const message = String(data.error?.message || '');
        const err = new Error(
          `[EZ Booking] Gateway error: ${code}${message ? ` — ${message}` : ''}`
        );
        if (code === 'BAD_SIGNATURE' || code === 'BAD_TIMESTAMP' || code === 'REPLAY') {
          err.gatewayAuth = true;
        }
        throw err;
      }
    } catch (parseErr) {
      if (!(parseErr instanceof SyntaxError)) {
        throw parseErr;
      }
    }
  }
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
    return await readGatewayBodyText(resp);
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
 * Versioned JSON contract for sans-management (v2).
 *
 * @param {number} productId
 * @param {number} dayStart unix day start
 * @param {string} [version]
 * @returns {Promise<Record<string, unknown>|null>}
 */
export async function sansManagementData(productId, dayStart, version = 'v2') {
  sansManagementWebController = replaceController(
    sansManagementWebController,
    new AbortController()
  );
  const controller = sansManagementWebController;

  try {
    const resp = await ezFetch(
      'booking.sans_management_data',
      {
        product_id: parseInt(productId, 10),
        day_start_time: parseInt(dayStart, 10),
        version: String(version || 'v2'),
      },
      { signal: controller.signal }
    );
    const text = await readGatewayBodyText(resp);
    if (!text || !text.trim()) {
      return {};
    }
    return JSON.parse(text);
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
/**
 * Open or close all manageable sanses for one day (sans-manager bulk buttons).
 *
 * @param {'open_all_sanses'|'close_all_sanses'} actionType
 * @param {number} productId
 * @param {number} dayStart unix day start
 * @returns {Promise<{success: boolean, data?: unknown}>}
 */
export async function bulkToggleDay(actionType, productId, dayStart) {
  const action =
    actionType === 'open_all_sanses'
      ? 'booking.open_all_sanses'
      : 'booking.close_all_sanses';

  const resp = await ezFetch(action, {
    product_id: parseInt(productId, 10),
    day_start_time: parseInt(dayStart, 10),
  });

  const text = await readGatewayBodyText(resp);
  try {
    return JSON.parse(text);
  } catch {
    throw new Error(`bulk day action HTTP ${resp.status}`);
  }
}

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
    const text = await readGatewayBodyText(resp);
    return JSON.parse(text);
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
 * @param {number} productId
 * @param {number} dayStart unix day start
 * @returns {Promise<string|null>}
 */
export async function checkPlayingHtml(productId, dayStart) {
  checkPlayingController = replaceController(
    checkPlayingController,
    new AbortController()
  );
  const controller = checkPlayingController;

  try {
    const resp = await ezFetch(
      'booking.check_playing',
      {
        product_id: parseInt(productId, 10),
        day_start_time: parseInt(dayStart, 10),
      },
      { signal: controller.signal }
    );
    return await readGatewayBodyText(resp);
  } catch (error) {
    if (isAbortError(error)) {
      return null;
    }
    throw error;
  } finally {
    if (checkPlayingController === controller) {
      checkPlayingController = null;
    }
  }
}

/**
 * @param {string} term
 * @returns {Promise<string|null>}
 */
export async function gameSearchHtml(term) {
  gameSearchController = replaceController(
    gameSearchController,
    new AbortController()
  );
  const controller = gameSearchController;

  try {
    const resp = await ezFetch(
      'booking.game_search',
      { term: String(term || '') },
      { signal: controller.signal }
    );
    return await readGatewayBodyText(resp);
  } catch (error) {
    if (isAbortError(error)) {
      return null;
    }
    throw error;
  } finally {
    if (gameSearchController === controller) {
      gameSearchController = null;
    }
  }
}

/**
 * @param {{ productId: number, startDate: string, endDate: string, action: 'open'|'close' }} opts
 * @returns {Promise<{success: boolean, data?: unknown}>}
 */
/**
 * Non-blocking product view counter (gateway replacement for admin-ajax product_set_view).
 *
 * @param {number|string} productId
 * @param {string} ip
 */
export function productSetView(productId, ip) {
  if (!window.__EZ_BOOT__?.sub_secret) {
    return;
  }
  const pid = parseInt(productId, 10);
  if (!Number.isFinite(pid) || pid <= 0) {
    return;
  }
  void ezFetch('booking.product_set_view', {
    product_id: pid,
    ip: String(ip || ''),
  }).catch(() => {
    /* analytics side-effect; ignore failures */
  });
}

export async function bulkDateRange(opts) {
  const resp = await ezFetch('booking.bulk_date_range', {
    product_id: parseInt(opts.productId, 10),
    start_date: opts.startDate,
    end_date: opts.endDate,
    action: opts.action,
  });
  const text = await readGatewayBodyText(resp);
  try {
    return JSON.parse(text);
  } catch {
    throw new Error(`bulk date range HTTP ${resp.status}`);
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
  const pid = parseInt(productId, 10);
  const day = parseInt(dayStart, 10);
  if (!Number.isFinite(pid) || pid <= 0 || !Number.isFinite(day) || day <= 0) {
    throw new Error(`[EZ Booking] Invalid sansDayJson args: product=${productId} day=${dayStart}`);
  }

  const requestKey = `${pid}:${day}`;
  if (sansDayJsonInFlightKey !== requestKey) {
    sansDayJsonController = replaceController(
      sansDayJsonController,
      new AbortController()
    );
    sansDayJsonInFlightKey = requestKey;
  }
  const controller = sansDayJsonController;

  try {
    const resp = await ezFetch(
      'booking.sans_day_json',
      {
        product_id: pid,
        day_start_time: day,
        days: 1,
      },
      { signal: controller.signal }
    );
    const text = await readGatewayBodyText(resp);
    assertGatewayJsonResponse(resp, text);
    return parseGatewaySansJson(text);
  } catch (error) {
    if (isAbortError(error)) {
      return null;
    }
    throw error;
  } finally {
    if (sansDayJsonController === controller) {
      sansDayJsonController = null;
      sansDayJsonInFlightKey = '';
    }
  }
}

export async function sansDayHtml(productId, dayStart) {
  const resp = await ezFetch('booking.sans_day', {
    product_id: productId,
    day_start_time: dayStart,
  });
  return readGatewayBodyText(resp);
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
  const text = await readGatewayBodyText(resp);
  assertGatewayJsonResponse(resp, text);
  const parsed = parseGatewaySansJson(text, { days: 7 });
  const week = normalizeWeekDays(parsed, parseInt(dayStart, 10));
  return renderReserveWeekHtml(week, parseInt(dayStart, 10));
}

export async function sansWeekHtml(productId, dayStart) {
  const pid = parseInt(productId, 10);
  const day = parseInt(dayStart, 10);
  const requestKey = `${pid}:${day}`;

  if (sansWeekInFlightKey !== requestKey) {
    sansWeekController = replaceController(
      sansWeekController,
      new AbortController()
    );
    sansWeekInFlightKey = requestKey;
  }
  const controller = sansWeekController;

  try {
    try {
      const html = await fetchAndRenderWeekFromJson(pid, day, controller.signal);
      return html;
    } catch (jsonErr) {
      if (isAbortError(jsonErr)) {
        return null;
      }
      if (jsonErr && typeof jsonErr === 'object' && jsonErr.gatewayAuth) {
        throw jsonErr;
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
    const text = (await readGatewayBodyText(resp)).replace(/^\uFEFF/, '').trim();

    if (!resp.ok) {
      assertGatewayJsonResponse(resp, text);
    } else if (isJsonLike(text)) {
      assertGatewayJsonResponse(resp, text);
    }

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
    if (err && typeof err === 'object' && err.gatewayAuth) {
      throw err;
    }
    const respText = err && typeof err === 'object' && err.responseText;
    if (err && typeof err === 'object' && err.status && (err.status === 401 || err.status === 403)) {
      throw err;
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
      sansWeekInFlightKey = '';
    }
  }
}
