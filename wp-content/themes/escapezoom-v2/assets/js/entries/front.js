import htmx from 'htmx.org';
import Alpine from 'alpinejs';
import { wireHtmx, ezFetch } from '../lib/ez-ajax.js';
import * as ezBookingApi from '../lib/ez-booking-api.js';
import {
  ezBookingSansManagementHtml,
  ezBookingToggleSans,
} from '../lib/ez-booking-client.js';
import { initBookingGatewayPages } from '../lib/ez-booking-pages.js';

if (typeof window !== 'undefined') {
  window.htmx = htmx;
  window.Alpine = Alpine;
  window.ezFetch = ezFetch;
  window.ezBookingApi = {
    ...ezBookingApi,
    ezBookingSansManagementHtml,
    ezBookingToggleSans,
  };
  wireHtmx();
  Alpine.start();
}

import '../main.js';
import '../theme/front/single-product.js';
import '../theme/front/single-post.js';
import '../theme/front/my-reviews.js';
import '../crm.js';
import '../mega-menu-frontend.js';

document.addEventListener('DOMContentLoaded', () => {
  initBookingGatewayPages();
});
