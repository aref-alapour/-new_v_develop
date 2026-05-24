/**
 * افزودن nonce fragment ادمین به درخواست‌های HTMX که به admin-ajax.php می‌روند.
 * باید بعد از htmx لود شود؛ مقدار از window.ezHtmxNonce (wp_localize_script).
 */
(function () {
  'use strict';
  document.body.addEventListener('htmx:configRequest', function (evt) {
    var detail = evt.detail || {};
    var path = detail.path || (detail.requestConfig && detail.requestConfig.path) || '';
    var elt = detail.elt;
    var hxGet = elt && elt.getAttribute ? elt.getAttribute('hx-get') : '';
    var combined = (typeof path === 'string' ? path : '') + ' ' + (typeof hxGet === 'string' ? hxGet : '');
    if (combined.indexOf('admin-ajax.php') === -1) {
      return;
    }
    var cfg = typeof window.ezHtmxNonce !== 'undefined' ? window.ezHtmxNonce : null;
    if (!cfg || !cfg.nonce) {
      return;
    }
    if (!detail.headers) {
      detail.headers = {};
    }
    detail.headers['X-EZ-Htmx-Nonce'] = cfg.nonce;
  });
})();
