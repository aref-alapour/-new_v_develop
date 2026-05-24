/**
 * Brands directory: Alpine + HTMX + signed gateway client (see `assets/js/lib/ez-ajax.js`).
 *
 * HTMX must live in this entry: `main-js` (front.js) does not bundle `htmx.org`, so without it
 * `hx-get` on pagination is never handled and `htmx:configRequest` never fires for ez-ajax.
 */
import htmx from 'htmx.org';
import Alpine from 'alpinejs';
import { wireHtmx as ezAjaxWireHtmx } from '../lib/ez-ajax.js';

if (typeof window !== 'undefined') {
  window.htmx = htmx;
}

document.addEventListener('alpine:init', () => {
  Alpine.data('ezBrandsPageState', () => ({
    busy: false,
    init() {
      const root = this.$el;
      root.addEventListener('htmx:beforeRequest', () => {
        this.busy = true;
      });
      root.addEventListener('htmx:afterSwap', () => {
        this.busy = false;
        const target = document.getElementById('ez-brands-page-root');
        if (target && typeof target.scrollIntoView === 'function') {
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
      root.addEventListener('htmx:responseError', () => {
        this.busy = false;
      });
      root.addEventListener('htmx:sendError', () => {
        this.busy = false;
      });
    },
  }));
});

(function bootstrapBrandsPage() {
  Alpine.start();
  // Central gateway: wireHtmx signs POST /ajax from hx-get targets (see ez-ajax.js).
  ezAjaxWireHtmx();
})();
