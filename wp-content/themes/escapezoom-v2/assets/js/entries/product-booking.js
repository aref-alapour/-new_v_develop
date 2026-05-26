import htmx from 'htmx.org';
import Alpine from 'alpinejs';
import { wireHtmx } from '../lib/ez-ajax.js';
import * as ezBookingApi from '../lib/ez-booking-api.js';
import {
  ezBookingSansManagementHtml,
  ezBookingToggleSans,
} from '../lib/ez-booking-client.js';

export { ezBookingSansManagementHtml, ezBookingToggleSans };

if (typeof window !== 'undefined') {
  window.htmx = htmx;
  window.Alpine = Alpine;
  window.ezBookingApi = ezBookingApi;
  wireHtmx();
  Alpine.start();
}

/**
 * Load sans list for a day into desktop + mobile containers.
 */
async function loadSansDay(productId, dayStart) {
  const desktop = document.getElementById('sessions-list-desktop');
  const mobile = document.getElementById('sessions-list-mobile');
  const targets = [desktop, mobile].filter(Boolean);
  if (!targets.length || !productId || !dayStart) {
    return;
  }

  const skeleton =
    '<div class="skeleton h-12 w-full rounded-[10px] mb-2.5"></div>'.repeat(4);
  targets.forEach((el) => {
    el.innerHTML = skeleton;
  });

  try {
    const html = await ezBookingApi.sansDayHtml(productId, dayStart);
    targets.forEach((el) => {
      el.innerHTML = html;
      if (window.htmx?.process) {
        window.htmx.process(el);
      }
    });
    document.dispatchEvent(
      new CustomEvent('ez-booking-sans-loaded', {
        detail: { productId, dayStart },
      }),
    );
  } catch (e) {
    console.error('[ez-booking] sans_day failed', e);
  }
}

function bindDatePickers() {
  const productId =
    typeof ProductJsObject !== 'undefined'
      ? parseInt(ProductJsObject.product_id, 10)
      : parseInt(document.body.dataset.ezProductId || '0', 10);

  const onPick = (ts) => {
    const day = parseInt(ts, 10);
    if (day > 0) {
      loadSansDay(productId, day);
    }
  };

  document.body.addEventListener('click', (ev) => {
    const btn = ev.target.closest('[data-reserve-timestamp], .date-btn[data-date]');
    if (!btn) {
      return;
    }
    const ts = btn.getAttribute('data-reserve-timestamp') || btn.getAttribute('data-date');
    if (ts) {
      onPick(ts);
    }
  });

  const todayDesktop = document.getElementById('today-btn-desktop');
  const todayMobile = document.getElementById('today-btn-mobile');
  [todayDesktop, todayMobile].forEach((el) => {
    if (!el) {
      return;
    }
    el.addEventListener('click', () => {
      const d = el.getAttribute('data-date');
      if (d) {
        onPick(d);
      }
    });
  });
}

document.addEventListener('DOMContentLoaded', () => {
  if (!window.__EZ_BOOT__?.sub_secret) {
    return;
  }
  bindDatePickers();

  const initialDay =
    parseInt(document.body.dataset.ezInitialDay || '0', 10) ||
    parseInt(
      document.querySelector('[data-reserve-timestamp].active, .date-btn.active')?.getAttribute('data-reserve-timestamp') ||
        document.querySelector('[data-reserve-timestamp], .date-btn[data-date]')?.getAttribute('data-reserve-timestamp') ||
        '0',
      10,
    );
  const productId =
    typeof ProductJsObject !== 'undefined'
      ? parseInt(ProductJsObject.product_id, 10)
      : parseInt(document.body.dataset.ezProductId || '0', 10);
  if (productId > 0 && initialDay > 0 && document.getElementById('sessions-list-desktop')) {
    loadSansDay(productId, initialDay);
  }
});
