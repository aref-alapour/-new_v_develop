/**
 * EZ AJAX Gateway client.
 *
 * Purpose (same-origin enforcement, not TLS):
 * - Each request carries a short-lived derived secret + HMAC over a canonical line.
 * - Server checks signature, timestamp skew, and one-time nonce replay store.
 * Copying the URL into another tab or omitting headers fails verification.
 *
 * Implementation: HMAC-SHA256 via `@noble/hashes` (pure JS) so signing works on plain HTTP
 * dev hosts; we do not rely on `crypto.subtle` (restricted to “secure contexts” in browsers).
 *
 * Canonical format (MUST match server SignatureVerifier::canonical):
 *   v1|<METHOD>|<path>|<action>|<client_id>|<client_kind>|<timestamp>|<nonce>|<sha256_hex(body)>
 *
 * TODO(phase 2): refresh boot data via `auth.bootstrap` when sub_secret < 60s from expiry.
 *
 * Until then: any gateway JSON error with code `BAD_TIMESTAMP` triggers a full page reload so
 * fresh `window.__EZ_BOOT__` is issued (covers expired sub-secret and severe clock skew).
 */

import { gcm } from '@noble/ciphers/aes.js';
import { hmac } from '@noble/hashes/hmac.js';
import { sha256 } from '@noble/hashes/sha2.js';

const TEXT_ENCODER = new TextEncoder();

/** @type {Set<string>} */
const WRITE_ENCRYPT_ACTIONS = new Set([
  'booking.open_sans',
  'booking.close_sans',
  'booking.open_all_sanses',
  'booking.close_all_sanses',
  'booking.bulk_date_range',
]);

/** @type {Set<string>} */
const READ_ENCRYPT_ACTIONS = new Set([
  'booking.sans_day_json',
  'booking.sans_day',
  'booking.sans_week',
  'booking.sans_management_web',
  'booking.sans_management_data',
  'booking.check_playing',
  'booking.game_search',
]);

/**
 * @param {string} action
 * @param {Record<string, unknown>} boot
 */
function shouldEncryptPayload(action, boot) {
  if (action === 'booking.sans_day_json') {
    return true;
  }
  if (
    action === 'booking.sans_management_web' ||
    action === 'booking.sans_management_data' ||
    action === 'booking.check_playing' ||
    action === 'booking.game_search'
  ) {
    return true;
  }
  if (WRITE_ENCRYPT_ACTIONS.has(action) && boot.encrypt_writes) {
    return true;
  }
  return READ_ENCRYPT_ACTIONS.has(action) && !!boot.encrypt_reads;
}

/**
 * @param {string} plainJson
 * @param {string} subSecretB64Url
 */
function encryptWireBody(plainJson, subSecretB64Url) {
  const key = base64UrlDecode(subSecretB64Url);
  const iv = crypto.getRandomValues(new Uint8Array(12));
  const aes = gcm(key, iv);
  const ct = aes.encrypt(TEXT_ENCODER.encode(plainJson));
  return JSON.stringify({
    ez_enc: 'v1',
    iv: bytesToBase64Url(iv),
    ct: bytesToBase64Url(ct),
  });
}

/**
 * @param {string} wireJson
 */
function isWireEnvelope(wireJson) {
  const trimmed = String(wireJson).trim();
  if (!trimmed.startsWith('{')) {
    return false;
  }
  try {
    const env = JSON.parse(trimmed);
    return (
      env &&
      typeof env === 'object' &&
      env.ez_enc === 'v1' &&
      typeof env.iv === 'string' &&
      typeof env.ct === 'string'
    );
  } catch {
    return false;
  }
}

/**
 * @param {string} wireJson
 * @param {string} subSecretB64Url
 */
function decryptWireBody(wireJson, subSecretB64Url) {
  const trimmed = String(wireJson).trim();
  if (!isWireEnvelope(trimmed)) {
    return trimmed;
  }
  const env = JSON.parse(trimmed);
  const key = base64UrlDecode(subSecretB64Url);
  const iv = base64UrlDecode(env.iv);
  const ct = base64UrlDecode(env.ct);
  const aes = gcm(key, iv);
  const plain = aes.decrypt(ct);
  return new TextDecoder().decode(plain);
}

/**
 * Read gateway response body; decrypt wire envelopes (header is optional signal).
 *
 * @param {Response} resp
 * @returns {Promise<string>}
 */
export async function readGatewayBodyText(resp) {
  const wireText = await resp.text();
  const headerEncrypted =
    resp.headers.get('X-EZ-Response-Encrypted') === 'v1' ||
    resp.headers.get('x-ez-response-encrypted') === 'v1';

  if (isWireEnvelope(wireText)) {
    const boot = getBoot();
    if (!boot?.sub_secret) {
      throw new Error('[EZ AJAX] Encrypted response but boot sub_secret missing.');
    }
    if (!headerEncrypted && typeof console !== 'undefined') {
      console.warn(
        '[EZ AJAX] Decrypting response envelope without X-EZ-Response-Encrypted header.',
      );
    }
    return decryptWireBody(wireText, String(boot.sub_secret));
  }

  return wireText;
}

export function getBoot() {
  const boot = typeof window !== 'undefined' ? window.__EZ_BOOT__ : null;
  if (!boot || typeof boot !== 'object' || !boot.sub_secret) {
    return null;
  }
  return boot;
}

/** @returns {string} */
function normalizeGatewayPathname(pathname) {
  if (!pathname || pathname === '/') {
    return '/';
  }
  return pathname.replace(/\/+$/, '') || '/';
}

/**
 * Gateway pathname from boot (subdir installs → `/blog/ajax`), default `/ajax`.
 * Does not require `sub_secret`; used only for intercept matching.
 *
 * @returns {string}
 */
function getGatewayPathnameFromPage() {
  const raw = typeof window !== 'undefined' ? window.__EZ_BOOT__?.ajax_url : null;
  if (typeof raw === 'string' && raw.trim() !== '') {
    try {
      return normalizeGatewayPathname(new URL(raw, window.location.origin).pathname);
    } catch (_) {
      /* fall through */
    }
  }
  return '/ajax';
}

/**
 * Progressive enhancement when boot data is missing: follow the `<a href>`.
 *
 * @param {Record<string, unknown>} detail
 */
function assignLocationFromAnchoredHref(detail) {
  const elt = detail?.elt;
  const a =
    elt && typeof elt.closest === 'function' ? elt.closest('a[href]') : null;
  if (a instanceof HTMLAnchorElement && a.href) {
    window.location.assign(a.href);
  }
}

/** Same class HTMX applies to `#hx-indicator` targets during requests. */
function ezAjaxHtmxRequestClass() {
  return typeof window.htmx?.config?.requestClass === 'string'
    ? window.htmx.config.requestClass
    : 'htmx-request';
}

/**
 * Mirrors HTMX indicator resolution for `hx-indicator` (nearest ancestor wins).
 *
 * @param {Element|null|undefined} start
 * @returns {Element[]}
 */
function ezAjaxResolveIndicatorElements(start) {
  if (!(start instanceof Element)) {
    return [];
  }
  let cur = start;
  let attr = '';
  for (let i = 0; i < 16 && cur; i++) {
    attr =
      typeof cur.getAttribute === 'function' ? cur.getAttribute('hx-indicator') || '' : '';
    if (attr.trim() !== '') {
      break;
    }
    cur = cur.parentElement;
  }
  if (!attr.trim()) {
    return [];
  }
  const out = [];
  for (const part of attr.split(',')) {
    const sel = part.trim();
    if (!sel) continue;
    try {
      const el = document.querySelector(sel);
      if (el instanceof Element) out.push(el);
    } catch (_) {
      /* invalid selector → skip */
    }
  }
  return out;
}

/**
 * Prefer the node that initiated the UX (htmx elt); fall back for edge cases.
 *
 * @param {Record<string, unknown>} detail
 * @returns {Element}
 */
function ezAjaxResolveTriggerElt(detail) {
  const d = detail && typeof detail === 'object' ? detail : {};
  /** `elt` is the initiating control (pagination link); `target` is the swap recipient. */
  if (d.elt instanceof Element) return d.elt;
  if (d.target instanceof Element) return d.target;
  const root = document.getElementById('ez-brands-page-root');
  return root instanceof Element ? root : document.body;
}

/**
 * @param {string} type
 * @param {Element} elt
 * @param {Record<string, unknown>} merge
 */
function ezAjaxBubbleEvent(type, elt, merge) {
  const node = elt instanceof Element && document.body.contains(elt) ? elt : document.body;
  node.dispatchEvent(
    new CustomEvent(type, { bubbles: true, cancelable: false, detail: merge }),
  );
}

function base64UrlDecode(str) {
  const pad = str.length % 4 === 0 ? '' : '='.repeat(4 - (str.length % 4));
  const b64 = str.replace(/-/g, '+').replace(/_/g, '/') + pad;
  const bin = atob(b64);
  const out = new Uint8Array(bin.length);
  for (let i = 0; i < bin.length; i++) out[i] = bin.charCodeAt(i);
  return out;
}

function bytesToBase64Url(bytes) {
  let s = '';
  for (let i = 0; i < bytes.length; i++) s += String.fromCharCode(bytes[i]);
  return btoa(s).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/g, '');
}

function bytesToHex(bytes) {
  const hex = new Array(bytes.length);
  for (let i = 0; i < bytes.length; i++) {
    hex[i] = bytes[i].toString(16).padStart(2, '0');
  }
  return hex.join('');
}

export function randomNonce(byteLength = 16) {
  const buf = new Uint8Array(byteLength);
  crypto.getRandomValues(buf);
  return bytesToHex(buf);
}

const EZ_AJAX_STALE_OVERLAY_ID = 'ez-ajax-session-refresh-overlay';

/**
 * Full-screen veil so users see immediate feedback before `location.reload()` (slow network/HTML).
 */
function showStaleCredentialReloadOverlay() {
  if (typeof document === 'undefined') return;
  if (document.getElementById(EZ_AJAX_STALE_OVERLAY_ID)) return;

  const reduced =
    typeof window.matchMedia === 'function' &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  const el = document.createElement('div');
  el.id = EZ_AJAX_STALE_OVERLAY_ID;
  el.setAttribute('role', 'status');
  el.setAttribute('aria-live', 'polite');
  el.textContent = 'به‌روزرسانی اتصال…';

  Object.assign(el.style, {
    position: 'fixed',
    inset: '0',
    zIndex: '2147483646',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    background: 'rgba(15, 23, 42, 0.55)',
    color: '#f8fafc',
    fontFamily: 'system-ui, -apple-system, Segoe UI, sans-serif',
    fontSize: reduced ? '1rem' : '1.0625rem',
    fontWeight: '600',
    pointerEvents: 'auto',
    textAlign: 'center',
    padding: '1rem',
  });

  document.documentElement.setAttribute('aria-busy', 'true');
  document.body.appendChild(el);
}

/**
 * Thrown when gateway returned `BAD_TIMESTAMP` and a full reload was scheduled — callers must not treat the raw `Response` as a normal error.
 */
export class EzAjaxStaleReloadError extends Error {
  constructor(message = 'EZ AJAX gateway credential expired — reloading') {
    super(message);
    this.name = 'EzAjaxStaleReloadError';
  }
}

/**
 * Gateway JSON errors use `{ ok:false, error:{ code } }` (see EZ AJAX Response::error server-side).
 * Reload the document when credentials baked into the page are no longer valid.
 *
 * @param {number} status HTTP status
 * @param {string} responseText Response body (already read, or from `resp.clone().text()`)
 * @returns {boolean} True if reload was triggered — caller should skip normal error handling
 */
export function reloadPageIfGatewayStaleCredential(status, responseText) {
  if (status !== 401 || typeof responseText !== 'string' || responseText.length === 0) {
    return false;
  }
  /** @type {unknown} */
  let code;
  try {
    const data = JSON.parse(responseText);
    if (
      !data ||
      typeof data !== 'object' ||
      /** @type {{ ok?: unknown }} */ (data).ok !== false ||
      /** @type {{ error?: unknown }} */ (data).error == null
    ) {
      return false;
    }
    const err = /** @type {{ error?: unknown }} */ (data).error;
    code =
      typeof err === 'object' && err !== null && 'code' in err
        ? /** @type {{ code?: unknown }} */ (err).code
        : err;
  } catch (_) {
    return false;
  }
  if (code !== 'BAD_TIMESTAMP') {
    return false;
  }
  showStaleCredentialReloadOverlay();
  requestAnimationFrame(() => {
    window.location.reload();
  });
  return true;
}

/**
 * @param {{ method: string, path: string, action: string, body: string, boot: Record<string, unknown> }} p
 */
export function signGatewayRequest(p) {
  const { method, path, action, body, boot } = p;
  const ts = Math.floor(Date.now() / 1000);
  const nonce = randomNonce(16);
  const bodyStr = typeof body === 'string' ? body : '';
  const bodyHash = bytesToHex(sha256(TEXT_ENCODER.encode(bodyStr)));
  const canonical = [
    'v1',
    method.toUpperCase(),
    path,
    action,
    boot.client_id,
    boot.client_kind,
    String(ts),
    nonce,
    bodyHash,
  ].join('|');

  const keyBytes = base64UrlDecode(boot.sub_secret);
  const sigBytes = hmac(sha256, keyBytes, TEXT_ENCODER.encode(canonical));
  const signature = bytesToBase64Url(sigBytes);

  return {
    canonical,
    headers: {
      'X-EZ-Action': action,
      'X-EZ-Kid': boot.kid,
      'X-EZ-Client-Id': boot.client_id,
      'X-EZ-Client-Kind': boot.client_kind,
      'X-EZ-Sub-Expires': String(boot.expires_at),
      'X-EZ-Timestamp': String(ts),
      'X-EZ-Nonce': nonce,
      'X-EZ-Signature': signature,
    },
  };
}

/**
 * Builds the body, signs, calls fetch.
 *
 * On `401` + gateway `BAD_TIMESTAMP`, triggers overlay/reload and throws {@link EzAjaxStaleReloadError}
 * so callers do not parse the body twice or treat stale credentials like a generic HTTP error.
 *
 * @param {string} action
 * @param {Record<string,unknown>} body
 * @param {{ method?: string, signal?: AbortSignal, headers?: Record<string,string> }} [opts]
 * @throws {EzAjaxStaleReloadError}
 */
export async function ezFetch(action, body = {}, opts = {}) {
  const boot = getBoot();
  if (!boot) {
    throw new Error('EZ AJAX boot data missing — gateway client cannot run.');
  }
  const method = (opts.method || 'POST').toUpperCase();
  const url = new URL(boot.ajax_url, window.location.origin);
  const bodyJson = method === 'GET' ? '' : JSON.stringify(body);
  let wireBody = bodyJson;
  const encryptHeaders = {};
  if (method !== 'GET' && shouldEncryptPayload(action, boot)) {
    wireBody = encryptWireBody(bodyJson, String(boot.sub_secret));
    encryptHeaders['X-EZ-Encrypted'] = 'v1';
  }

  const path = url.pathname; // server signs against the path only (no query/host).

  const { headers } = signGatewayRequest({
    method,
    path,
    action,
    body: wireBody,
    boot,
  });

  const init = {
    method,
    credentials: 'same-origin',
    signal: opts.signal,
    headers: {
      'Accept': 'application/json, text/html;q=0.9, */*;q=0.5',
      'Content-Type': 'application/json',
      ...(opts.headers || {}),
      ...headers,
      ...encryptHeaders,
    },
  };

  if (method !== 'GET') {
    init.body = wireBody;
  } else {
    // Echo action in query so server can read it even before the body parser runs.
    url.searchParams.set('action', action);
  }

  const resp = await fetch(url.toString(), init);

  if (!resp.ok && resp.status === 401) {
    try {
      const peek = await resp.clone().text();
      if (reloadPageIfGatewayStaleCredential(resp.status, peek)) {
        throw new EzAjaxStaleReloadError();
      }
    } catch (e) {
      if (e instanceof EzAjaxStaleReloadError) {
        throw e;
      }
      /* clone/body read failure — surface raw resp to caller */
    }
  }

  return resp;
}

/**
 * Wire HTMX so any request targeting the gateway path gets signed automatically.
 *
 *  - `htmx:configRequest`: we `preventDefault()` and `fetch` manually (headers must be signed).
 *  - POST + JSON body keeps the canonical body-hash deterministic.
 */
export function wireHtmx() {
  if (typeof document === 'undefined') return;
  if (document.__ezAjaxHtmxWired) return;
  document.__ezAjaxHtmxWired = true;

  document.body.addEventListener('htmx:configRequest', (evt) => {
    const detail = evt.detail || {};
    const rawPath = typeof detail.path === 'string' ? detail.path : '';
    if (!rawPath) return;

    let url;
    try {
      url = new URL(rawPath, window.location.origin);
    } catch (_) {
      return;
    }
    const ajaxPathname = normalizeGatewayPathname(getGatewayPathnameFromPage());
    if (normalizeGatewayPathname(url.pathname) !== ajaxPathname) return;

    const boot = getBoot();
    if (!boot) {
      evt.preventDefault();
      assignLocationFromAnchoredHref(detail);
      return;
    }

    const action = url.searchParams.get('action') || detail.parameters?.action || '';
    if (!action) return;

    // HTMX puts form fields in `detail.parameters` only; query args on `hx-get` live on
    // `detail.path` until after this event — merge URL search params into the JSON body.
    /** @type {Record<string, unknown>} */
    let params = {};
    const p = detail.parameters;
    if (p != null && typeof p === 'object' && typeof p.toJSON === 'function') {
      params = { ...p.toJSON() };
    } else if (p != null && typeof p === 'object') {
      params = Object.assign({}, p);
    }
    for (const [key, value] of url.searchParams.entries()) {
      if (key === 'action') {
        continue;
      }
      if (!Object.prototype.hasOwnProperty.call(params, key)) {
        params[key] = value;
      }
    }
    delete params.action;

    evt.preventDefault();
    const rawPathForEvt = typeof detail.path === 'string' ? detail.path : '';

    /** @type {Element} */
    const triggerElt = ezAjaxResolveTriggerElt(detail);
    const rqClass = ezAjaxHtmxRequestClass();
    /** @type {Element[]} */
    const indicatorElts = ezAjaxResolveIndicatorElements(triggerElt);

    const xhrPlacehold = {};

    /** @type {Record<string, unknown>} */
    const beforeDetail = Object.assign({}, detail, {
      elt: triggerElt,
      xhr: xhrPlacehold,
      boosted: !!detail.boosted,
      target: detail.target,
      pathInfo: detail.pathInfo || { requestPath: rawPathForEvt },
      path: rawPathForEvt,
    });

    if (
      triggerElt.dispatchEvent(
        new CustomEvent('htmx:beforeRequest', {
          bubbles: true,
          cancelable: true,
          detail: beforeDetail,
        }),
      ) === false
    ) {
      return;
    }

    for (const ic of indicatorElts) {
      ic.classList.add(rqClass);
    }

    const target = detail.target;
    const swap = detail.swap || 'innerHTML';
    const verb = (detail.verb || 'get').toUpperCase();
    const method = verb === 'GET' ? 'POST' : verb; // GET hx-get is fine; we still POST signed.
    const targetId = target instanceof Element && target.id ? target.id : '';

    const stripIndicators = () => {
      for (const ic of indicatorElts) {
        ic.classList.remove(rqClass);
      }
    };

    ezFetch(action, params, { method })
      .then(async (resp) => {
        const text = await readGatewayBodyText(resp);
        Object.assign(xhrPlacehold, {
          status: resp.status,
          statusText: resp.statusText,
          response: text,
          responseText: text,
          /**
           * @param {string} h
           * @returns {string|null}
           */
          getResponseHeader: (h) => resp.headers.get(h),
        });

        /** @type {Record<string, unknown>} */
        const responseInfoBase = Object.assign({}, beforeDetail, {
          xhr: xhrPlacehold,
          successful: resp.ok,
          failed: !resp.ok,
          pathInfo: Object.assign({}, beforeDetail.pathInfo || {}, {
            requestPath: rawPathForEvt || (beforeDetail.pathInfo && beforeDetail.pathInfo.requestPath),
          }),
        });

        ezAjaxBubbleEvent(
          'htmx:afterRequest',
          triggerElt,
          Object.assign({ path: rawPathForEvt }, responseInfoBase),
        );

        stripIndicators();

        if (!resp.ok) {
          ezAjaxBubbleEvent(
            'htmx:responseError',
            triggerElt,
            Object.assign(
              {
                error: `Response Status Error Code ${resp.status} (${action})`,
              },
              responseInfoBase,
            ),
          );
          return;
        }

        const pushUrl = resp.headers.get('HX-Push-Url');

        if (target instanceof Element) {
          const swapEvt = new CustomEvent('htmx:beforeSwap', {
            detail: { xhr: xhrPlacehold, target, swap },
            bubbles: true,
            cancelable: true,
          });
          target.dispatchEvent(swapEvt);
          if (swap === 'outerHTML') {
            target.outerHTML = text;
          } else {
            target.innerHTML = text;
          }
          if (pushUrl && typeof window.history?.pushState === 'function') {
            window.history.pushState({ ezAjax: true }, '', pushUrl);
          }
          const fresh =
            targetId && typeof document !== 'undefined'
              ? document.getElementById(targetId)
              : null;
          const evtTarget = fresh || target;
          evtTarget.dispatchEvent(
            new CustomEvent('htmx:afterSwap', { bubbles: true, detail: { target: evtTarget } }),
          );
          if (window.htmx && typeof window.htmx.process === 'function' && fresh) {
            window.htmx.process(fresh);
          }
        }
      })
      .catch((err) => {
        stripIndicators();

        if (err instanceof EzAjaxStaleReloadError) {
          return;
        }

        ezAjaxBubbleEvent(
          'htmx:afterRequest',
          triggerElt,
          Object.assign({}, detail, {
            elt: triggerElt,
            xhr: xhrPlacehold,
            error: err instanceof Error ? err.message : String(err),
            boosted: !!detail.boosted,
            successful: false,
            failed: true,
            pathInfo: detail.pathInfo || { requestPath: rawPathForEvt },
            path: rawPathForEvt,
          }),
        );
        ezAjaxBubbleEvent(
          'htmx:sendError',
          triggerElt,
          Object.assign({}, detail, {
            elt: triggerElt,
            xhr: xhrPlacehold,
            error: err instanceof Error ? err.message : String(err),
            boosted: !!detail.boosted,
          }),
        );
        if (window.console && console.warn) console.warn('[ez-ajax] gateway request failed:', err);
      });
  });
}
