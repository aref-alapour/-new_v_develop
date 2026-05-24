/**
 * Escape Zoom PWA — Service Worker
 *
 * Deploy: روی سرور برای این فایل و manifest.json ترجیحاً
 * Cache-Control: no-cache یا max-age کوتاه بگذارید تا به‌روزرسانی سریع برسد.
 *
 * نسخه کش را با هر تغییر جدی در این فایل افزایش دهید.
 */
const CACHE_VERSION = 'escapezoom-v2.0.2-emergency';
const RUNTIME_CACHE = `runtime-${CACHE_VERSION}`; // فقط تصویر/فونت (شبکه اول)

// base از مسیر واقعی sw.js (با http/https، لوکال، زیرپوشه وردپرس)
const _swPath = self.location.pathname || '/sw.js';
const _swDir = _swPath.includes('/')
  ? _swPath.substring(0, _swPath.lastIndexOf('/') + 1)
  : '/';
const baseUrl = self.location.origin + (_swDir === '/' ? '/' : _swDir);

const OFFLINE_URL = new URL('offline.html', baseUrl).href;

// الگوهای URL که هرگز توسط SW کش/پردازش نمی‌شوند (فقط شبکه / رفتار پیش‌فرض مرورگر)
const URL_BYPASS_PATTERNS = [
  'wp-admin',
  'wp-login.php',
  'preview=true',
  'cart/',
  'checkout/',
  'my-account/orders/',
  'add-to-cart',
  '/api/',
  'api.php',
  'wp-json',
  'admin-ajax.php',
  'wc-ajax=',
  '/ajax/',
  '?ajax',
  '&ajax',
  'get_server_time',
  'web-service',
  'wc-api',
];

function urlMatchesBypass(url) {
  const u = url.toLowerCase();
  return URL_BYPASS_PATTERNS.some((p) => u.includes(p.toLowerCase()));
}

/**
 * AJAX / API از روی هدر — فقط غیر navigation
 */
function looksLikeAjaxOrApi(request) {
  if (request.mode === 'navigate') {
    return false;
  }
  if (request.headers.get('X-Requested-With') === 'XMLHttpRequest') {
    return true;
  }
  const accept = request.headers.get('Accept') || '';
  if (accept.includes('application/json') && !accept.includes('text/html')) {
    return true;
  }
  const dest = request.destination;
  if (dest === 'empty' && request.method === 'GET') {
    const url = request.url;
    if (url.includes('admin-ajax') || url.includes('wp-json')) {
      return true;
    }
  }
  return false;
}

function shouldBypassServiceWorkerCaching(request) {
  if (request.method !== 'GET' && request.method !== 'HEAD') {
    return true;
  }
  if (looksLikeAjaxOrApi(request)) {
    return true;
  }
  if (urlMatchesBypass(request.url)) {
    return true;
  }
  return false;
}

/** عبور مستقیم: بدون intercept کش‌کننده در SW */
function passthroughFetch(request) {
  return fetch(
    new Request(request, {
      cache: 'no-store',
      credentials: request.credentials,
      mode: request.mode,
      redirect: request.redirect,
    })
  );
}

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches
      .open(RUNTIME_CACHE)
      .then((cache) => cache.addAll([OFFLINE_URL]).catch(() => {}))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches
      .keys()
      .then((names) => Promise.all(names.map((name) => caches.delete(name))))
      .then(() => self.clients.claim())
  );
});

/** تصویر و فونت: شبکه اول، در موفقیت ذخیره در RUNTIME (آفلاین سبک) */
function networkFirstRuntime(request) {
  return fetch(request)
    .then((res) => {
      if (res.ok) {
        const copy = res.clone();
        caches.open(RUNTIME_CACHE).then((cache) => cache.put(request, copy));
      }
      return res;
    })
    .catch(() =>
      caches.match(request).then((cached) => {
        if (cached) {
          return cached;
        }
        return new Response('Offline', { status: 503, statusText: 'Service Unavailable' });
      })
    );
}

/** سند HTML: فقط شبکه؛ بدون ذخیره HTML در Cache Storage */
function handleNavigation(request) {
  return fetch(request)
    .catch(() =>
      caches.match(OFFLINE_URL).then(
        (fallback) =>
          fallback ||
          new Response(
            '<!doctype html><html dir="rtl"><head><meta charset="utf-8"><title>آفلاین</title></head><body><p>اتصال برقرار نیست.</p></body></html>',
            { status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8' } }
          )
      )
    );
}

/** JS/CSS: همیشه شبکه تازه — SW این نوع را در Cache Storage نگه نمی‌دارد */
function handleScriptStyle(request) {
  return fetch(
    new Request(request, {
      cache: 'reload',
      credentials: request.credentials,
      mode: request.mode,
      redirect: request.redirect,
    })
  );
}

self.addEventListener('fetch', (event) => {
  const { request } = event;
  if (!request.url.startsWith('http://') && !request.url.startsWith('https://')) {
    return;
  }

  const url = new URL(request.url);
  if (url.pathname.endsWith('/api.php') && url.searchParams.get('action') === 'get_server_time') {
    event.respondWith(passthroughFetch(request));
    return;
  }

  if (shouldBypassServiceWorkerCaching(request)) {
    event.respondWith(passthroughFetch(request));
    return;
  }

  if (request.method === 'GET' || request.method === 'HEAD') {
    // ناوبری / سند
    if (request.mode === 'navigate' || request.destination === 'document') {
      event.respondWith(handleNavigation(request));
      return;
    }

    if (
      request.destination === 'image' ||
      /\.(jpg|jpeg|png|gif|webp|svg|avif|ico)$/i.test(url.pathname)
    ) {
      event.respondWith(networkFirstRuntime(request));
      return;
    }

    if (
      request.destination === 'font' ||
      /\.(woff2?|ttf|otf|eot)$/i.test(url.pathname)
    ) {
      event.respondWith(networkFirstRuntime(request));
      return;
    }

    if (
      request.destination === 'style' ||
      request.destination === 'script' ||
      /\.(css|js)$/i.test(url.pathname)
    ) {
      event.respondWith(handleScriptStyle(request));
      return;
    }

    // بقیه GET (مثلاً manifest، json بدون هدر API)
    event.respondWith(
      fetch(request).catch(() =>
        caches.match(request).then((c) => c || new Response('', { status: 504 }))
      )
    );
  }
});

self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'CLEAR_CACHE') {
    event.waitUntil(
      caches.keys().then((cacheNames) => {
        return Promise.all(
          cacheNames.map((cacheName) => {
            if (event.data.cacheName) {
              if (cacheName === event.data.cacheName) {
                return caches.delete(cacheName);
              }
              return Promise.resolve();
            }
            return caches.delete(cacheName);
          })
        );
      })
    );
  }
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const urlToOpen = event.notification.data && event.notification.data.url ? event.notification.data.url : baseUrl;
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
      for (const client of clientList) {
        if (client.url === urlToOpen && 'focus' in client) {
          return client.focus();
        }
      }
      if (clients.openWindow) {
        return clients.openWindow(urlToOpen);
      }
    })
  );
});

self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-reservations') {
    event.waitUntil(syncReservations());
  }
});

async function syncReservations() {}

self.addEventListener('push', (event) => {
  let notificationData = {
    title: 'اسکیپ زوم',
    body: 'شما یک پیام جدید دارید',
    icon: baseUrl + 'wp-content/themes/escapezoom-v2/assets/images/fav-icon.png',
    badge: baseUrl + 'wp-content/themes/escapezoom-v2/assets/images/fav-icon.png',
    data: { url: baseUrl },
  };
  if (event.data) {
    try {
      const data = event.data.json();
      notificationData = Object.assign({}, notificationData, data);
    } catch (e) {
      notificationData.body = event.data.text();
    }
  }
  event.waitUntil(self.registration.showNotification(notificationData.title, notificationData));
});
