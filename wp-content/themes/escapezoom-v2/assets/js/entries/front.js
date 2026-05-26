import htmx from 'htmx.org';
import Alpine from 'alpinejs';
import { wireHtmx, ezFetch } from '../lib/ez-ajax.js';
import * as ezBookingApi from '../lib/ez-booking-api.js';
import {
  ezBookingSansManagementHtml,
  ezBookingToggleSans,
} from '../lib/ez-booking-client.js';
import { ensureBookingGatewayPagesInit } from '../lib/ez-booking-pages.js';

export function applyEzAjaxBoot() {
  if (typeof window === 'undefined' || window.__EZ_BOOT__) {
    return window.__EZ_BOOT__ || null;
  }
  const localized =
    typeof ezAjaxBoot !== 'undefined' && ezAjaxBoot && typeof ezAjaxBoot === 'object'
      ? ezAjaxBoot
      : null;
  if (localized) {
    window.__EZ_BOOT__ = localized;
  }
  return window.__EZ_BOOT__ || null;
}

if (typeof window !== 'undefined') {
  applyEzAjaxBoot();
  window.applyEzAjaxBoot = applyEzAjaxBoot;
  window.htmx = htmx;
  window.Alpine = Alpine;
  window.ezFetch = ezFetch;
  window.ezBookingApi = {
    ...ezBookingApi,
    ezBookingSansManagementHtml,
    ezBookingToggleSans,
  };
  window.ensureBookingGatewayPagesInit = ensureBookingGatewayPagesInit;
  wireHtmx();
  Alpine.start();
  ensureBookingGatewayPagesInit();
}

import '../main.js';
import '../theme/front/single-product.js';
import '../theme/front/single-post.js';
import '../theme/front/my-reviews.js';
import '../crm.js';
import '../mega-menu-frontend.js';

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', ensureBookingGatewayPagesInit);
} else {
  ensureBookingGatewayPagesInit();
}
