import L from 'leaflet';
import '../../lib/leaflet/L.Control.GeoapifyAddressSearch.js';
import '../../lib/leaflet/highlight.min.js';

window.L = L;

/** نقشهٔ تک‌محصول: اسکریپت اینلاین PHP قبل از ماژول front اجرا می‌شود؛ اینجا بعد از L.init امن است */
function initSingleProductMaps() {
  document.querySelectorAll('[data-ez-product-map][id]').forEach((el) => {
    if (el._leaflet_id) {
      return;
    }
    const lat = parseFloat(el.dataset.lat);
    const lng = parseFloat(el.dataset.lng);
    const zoom = parseInt(el.dataset.zoom || '16', 10);
    const iconUrl = el.dataset.iconUrl || '';
    const popupText = el.dataset.popup || '';
    if (!Number.isFinite(lat) || !Number.isFinite(lng) || !el.id) {
      return;
    }
    const map = L.map(el.id).setView([lat, lng], zoom);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
    const icon =
      iconUrl &&
      L.icon({
        iconUrl,
        iconSize: [28, 34],
      });
    const marker = L.marker([lat, lng], icon ? { icon } : {}).addTo(map);
    if (popupText) {
      marker.bindPopup(popupText);
    }
  });
}

export function initMaps() {
  if (!document.querySelector('.leaflet-container, [data-ez-map], #map')) {
    return;
  }

  initSingleProductMaps();

  document.dispatchEvent(new CustomEvent('ez:maps-ready', { detail: { L } }));
}
