import { initEnhancedSelects } from '../theme/lib/enhanced-select.js';
import { initJalaliFlatpickr } from '../theme/lib/flatpickr-init.js';
import { initMaps } from '../theme/lib/map-init.js';
import { initScaryParticles } from '../theme/lib/scary-particles.js';
import '../lib/zebline/zebline-sdk.js';
import '../lib/qrcode/qrcode.js';
import '../main.js';
import '../theme/front/single-product.js';
import '../theme/front/single-post.js';
import '../theme/front/my-reviews.js';
import '../crm.js';
import '../mega-menu-frontend.js';

document.addEventListener('DOMContentLoaded', () => {
  initEnhancedSelects();
  initJalaliFlatpickr();
  initMaps();
  initScaryParticles();
});
