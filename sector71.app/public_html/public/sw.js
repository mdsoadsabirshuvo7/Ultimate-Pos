const SW_VERSION = 's71-sw-v8';
const STATIC_CACHE = `${SW_VERSION}-static`;
const RUNTIME_CACHE = `${SW_VERSION}-runtime`;
const DB_NAME = 's71-offline-db';
const STORE_NAME = 'request-queue';
const SYNC_TAG = 's71-sync-queue';
const MAX_QUEUE_AGE_MS = 7 * 24 * 60 * 60 * 1000;
const BASE_RETRY_DELAY_MS = 5000;
const MAX_RETRY_DELAY_MS = 5 * 60 * 1000;

const PRECACHE_URLS = [
  '/',
  '/offline.html',
  '/manifest.json',
  '/css/app.css',
  '/css/vendor.css',
  '/css/tailwind/app.css',
  '/js/functions.js',
  '/js/common.js',
  '/js/app.js',
  '/js/offline-sync.js'
];

const CRITICAL_ROUTES = [
  '/home',
  '/pos',
  '/pos/create',
  '/sells',
  '/sells/create',
  '/products',
  '/contacts?type=customer',
  '/contacts?type=supplier',
  '/purchases',
  '/purchases/create',
  '/stock-adjustments',
  '/stock-transfers'
];

const AUTH_PATH_RE = /(?:\/|^)(login|logout|register|password|forgot-password|reset-password|two-factor|email\/verify)(?:\/|$|\?)/i;

self.addEventListener('install', (event) => {
  event.waitUntil((async () => {
    const cache = await caches.open(STATIC_CACHE);
    for (const url of PRECACHE_URLS) {
      try {
        const response = await fetch(new Request(url, { cache: 'reload' }));
        if (response && response.ok) {
          await cache.put(url, response.clone());
        }
      } catch (_) {
        // Skip unavailable assets; runtime caching will handle later.
      }
    }
    await self.skipWaiting();
  })());
});

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    const keys = await caches.keys();
    await Promise.all(keys.filter((k) => !k.startsWith(SW_VERSION)).map((k) => caches.delete(k)));
    await self.clients.claim();
  })());
});

self.addEventListener('message', (event) => {
  if (!event.data || !event.data.type) {
    return;
  }

  if (event.data.type === 'SYNC_NOW') {
    event.waitUntil(flushQueue());
  }

  if (event.data.type === 'CLEAR_OFFLINE_DATA') {
    event.waitUntil(clearOfflineData());
  }

  if (event.data.type === 'CLEAR_QUEUE_ONLY') {
    event.waitUntil(clearQueueOnly());
  }

  if (event.data.type === 'CLEAR_RUNTIME_ONLY') {
    event.waitUntil(clearRuntimeCacheOnly());
  }

  if (event.data.type === 'WARM_CRITICAL_ROUTES') {
    const routes = Array.isArray(event.data.routes) ? event.data.routes : CRITICAL_ROUTES;
    event.waitUntil(warmCriticalRoutes(routes));
  }
});

self.addEventListener('sync', (event) => {
  if (event.tag === SYNC_TAG) {
    event.waitUntil(flushQueue());
  }
});

self.addEventListener('fetch', (event) => {
  const request = event.request;
  const url = new URL(request.url);

  if (url.origin !== self.location.origin) {
    return;
  }

  // Never intercept auth endpoints; let browser/network own login/logout flows.
  if (isAuthPath(url.pathname)) {
    return;
  }

  if (request.method === 'GET') {
    if (request.mode === 'navigate') {
      event.respondWith(handleNavigation(request));
      return;
    }

    if (isStaticAsset(url.pathname)) {
      event.respondWith(staleWhileRevalidate(request));
      return;
    }

    event.respondWith(networkFirst(request));
    return;
  }

  if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(request.method)) {
    event.respondWith(handleMutation(request));
  }
});

function isStaticAsset(pathname) {
  return /\.(?:css|js|png|jpe?g|gif|svg|webp|woff2?|eot|ico)$/i.test(pathname);
}

function isLoginRedirect(response) {
  try {
    if (!response || !response.url) {
      return false;
    }
    const p = new URL(response.url).pathname;
    return p === '/login' || p.endsWith('/login');
  } catch (_) {
    return false;
  }
}

function isAuthPath(pathname) {
  return AUTH_PATH_RE.test(String(pathname || '/').toLowerCase());
}

function looksLikeLoginHtml(html) {
  const body = String(html || '').toLowerCase();
  const hasPasswordField = body.includes('type="password"') || body.includes("type='password'") || body.includes('name="password"') || body.includes("name='password'");
  const hasUserField = body.includes('name="username"') || body.includes("name='username'") || body.includes('name="email"') || body.includes("name='email'");
  const hasLoginToken = body.includes('/login') || body.includes('log in') || body.includes('login');
  return hasPasswordField && hasUserField && hasLoginToken;
}

async function canCacheResponse(request, response) {
  if (!response || response.status !== 200) {
    return false;
  }

  if (isLoginRedirect(response)) {
    return false;
  }

  let reqPath = '/';
  try {
    reqPath = new URL(request.url).pathname || '/';
  } catch (_) {
    reqPath = '/';
  }

  if (request.mode === 'navigate' && isAuthPath(reqPath)) {
    return false;
  }

  const contentType = (response.headers.get('content-type') || '').toLowerCase();
  if (request.mode === 'navigate' && contentType.includes('text/html')) {
    try {
      const text = await response.clone().text();
      if (looksLikeLoginHtml(text)) {
        return false;
      }
    } catch (_) {
      // If body inspection fails, keep default decision.
    }
  }

  return true;
}

async function warmCriticalRoutes(routes) {
  const cache = await caches.open(RUNTIME_CACHE);
  for (const route of routes) {
    try {
      const req = new Request(route, { credentials: 'include', cache: 'no-store' });
      const reqPath = new URL(req.url).pathname || '/';
      if (isAuthPath(reqPath)) {
        continue;
      }
      const response = await fetch(req);
      if (await canCacheResponse(req, response)) {
        await cache.put(req, response.clone());
      }
    } catch (_) {
      // Ignore warming failures; app may still cache route on normal use.
    }
  }
}

async function handleNavigation(request) {
  try {
    const response = await fetch(request);
    if (await canCacheResponse(request, response)) {
      const cache = await caches.open(RUNTIME_CACHE);
      await cache.put(request, response.clone());
    }
    return response;
  } catch (_) {
    const cache = await caches.open(RUNTIME_CACHE);
    const exact = await cache.match(request, { ignoreSearch: false });
    if (exact) {
      return exact;
    }

    const relaxed = await cache.match(request, { ignoreSearch: true });
    if (relaxed) {
      return relaxed;
    }

    const offline = await caches.match('/offline.html');
    if (offline) {
      return offline;
    }

    return new Response('Offline', { status: 503, headers: { 'Content-Type': 'text/plain' } });
  }
}

async function networkFirst(request) {
  const cache = await caches.open(RUNTIME_CACHE);
  try {
    const response = await fetch(request);
    if (await canCacheResponse(request, response)) {
      await cache.put(request, response.clone());
    }
    return response;
  } catch (error) {
    const cached = await cache.match(request, { ignoreSearch: true });
    if (cached) {
      return cached;
    }
    throw error;
  }
}

async function staleWhileRevalidate(request) {
  const cache = await caches.open(RUNTIME_CACHE);
  const cached = await cache.match(request, { ignoreSearch: true });

  const networkPromise = fetch(request)
    .then(async (response) => {
      if (response && response.status === 200) {
        await cache.put(request, response.clone());
      }
      return response;
    })
    .catch(() => null);

  return cached || networkPromise || new Response('', { status: 504 });
}

async function handleMutation(request) {
  const reqPath = new URL(request.url).pathname || '/';
  if (isAuthPath(reqPath)) {
    try {
      return await fetch(request.clone());
    } catch (_) {
      if (request.mode === 'navigate') {
        return Response.redirect('/login?offline_auth=required', 303);
      }
      return new Response(JSON.stringify({ queued: false, offline: true, message: 'Authentication requests require internet connection.' }), {
        status: 503,
        headers: { 'Content-Type': 'application/json' }
      });
    }
  }

  try {
    return await fetch(request.clone());
  } catch (_) {
    const queued = await queueRequest(request);
    if (!queued) {
      return new Response(JSON.stringify({ queued: false, offline: true, message: 'Offline queue not supported for this request type.' }), {
        status: 503,
        headers: { 'Content-Type': 'application/json' }
      });
    }

    await registerSync();

    if (request.mode === 'navigate') {
      return Response.redirect('/home?offline_sync=queued', 303);
    }

    return new Response(JSON.stringify({ queued: true, offline: true, message: 'Saved offline. It will sync automatically when internet returns.' }), {
      status: 202,
      headers: { 'Content-Type': 'application/json' }
    });
  }
}

async function registerSync() {
  if (!self.registration) {
    return;
  }

  if ('sync' in self.registration) {
    try {
      await self.registration.sync.register(SYNC_TAG);
      return;
    } catch (_) {
      // fallback below
    }
  }

  await flushQueue();
}

function openQueueDb() {
  return new Promise((resolve, reject) => {
    const req = indexedDB.open(DB_NAME, 1);

    req.onupgradeneeded = () => {
      const db = req.result;
      if (!db.objectStoreNames.contains(STORE_NAME)) {
        db.createObjectStore(STORE_NAME, { keyPath: 'id', autoIncrement: true });
      }
    };

    req.onsuccess = () => resolve(req.result);
    req.onerror = () => reject(req.error);
  });
}

async function serializeRequest(request) {
  const headers = {};
  request.headers.forEach((value, key) => {
    headers[key] = value;
  });

  const method = request.method.toUpperCase();
  const data = {
    url: request.url,
    method,
    headers,
    body: '',
    createdAt: Date.now(),
    retries: 0
  };

  if (method === 'GET' || method === 'HEAD') {
    return data;
  }

  const clone = request.clone();
  const contentType = (headers['content-type'] || '').toLowerCase();

  if (contentType.includes('application/x-www-form-urlencoded') || contentType.includes('application/json') || contentType.includes('text/plain')) {
    data.body = await clone.text();
    return data;
  }

  try {
    const formData = await clone.formData();
    const params = new URLSearchParams();
    for (const [key, value] of formData.entries()) {
      if (value instanceof File) {
        return null;
      }
      params.append(key, String(value));
    }
    data.body = params.toString();
    data.headers['content-type'] = 'application/x-www-form-urlencoded;charset=UTF-8';
    return data;
  } catch (_) {
    return null;
  }
}

async function queueRequest(request) {
  const serialized = await serializeRequest(request);
  if (!serialized) {
    return false;
  }

  const db = await openQueueDb();
  await new Promise((resolve, reject) => {
    const tx = db.transaction(STORE_NAME, 'readwrite');
    tx.objectStore(STORE_NAME).add(serialized);
    tx.oncomplete = resolve;
    tx.onerror = () => reject(tx.error);
  });
  db.close();
  return true;
}

async function getQueuedRequests() {
  const db = await openQueueDb();
  const rows = await new Promise((resolve, reject) => {
    const tx = db.transaction(STORE_NAME, 'readonly');
    const req = tx.objectStore(STORE_NAME).getAll();
    req.onsuccess = () => resolve(req.result || []);
    req.onerror = () => reject(req.error);
  });
  db.close();
  return rows;
}

async function deleteQueuedRequest(id) {
  const db = await openQueueDb();
  await new Promise((resolve, reject) => {
    const tx = db.transaction(STORE_NAME, 'readwrite');
    tx.objectStore(STORE_NAME).delete(id);
    tx.oncomplete = resolve;
    tx.onerror = () => reject(tx.error);
  });
  db.close();
}

async function updateQueuedRequest(item) {
  const db = await openQueueDb();
  await new Promise((resolve, reject) => {
    const tx = db.transaction(STORE_NAME, 'readwrite');
    tx.objectStore(STORE_NAME).put(item);
    tx.oncomplete = resolve;
    tx.onerror = () => reject(tx.error);
  });
  db.close();
}

function computeNextRetryDelay(retries) {
  const safeRetries = Math.max(1, Number(retries || 1));
  const delay = BASE_RETRY_DELAY_MS * Math.pow(2, Math.min(safeRetries - 1, 6));
  return Math.min(delay, MAX_RETRY_DELAY_MS);
}

async function notifyClients(message) {
  const allClients = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
  for (const client of allClients) {
    client.postMessage(message);
  }
}

async function flushQueue() {
  const rows = await getQueuedRequests();
  for (const item of rows) {
    const now = Date.now();
    const createdAt = Number(item.createdAt || 0);
    if (createdAt > 0 && now - createdAt > MAX_QUEUE_AGE_MS) {
      await notifyClients({ type: 'SYNC_ERROR', url: item.url, method: item.method, reason: 'Expired queued request (>7d)', retries: item.retries || 0, dropped: true, nextRetryAt: 0 });
      await deleteQueuedRequest(item.id);
      continue;
    }

    if (item.nextRetryAt && Number(item.nextRetryAt) > now) {
      continue;
    }

    try {
      const response = await fetch(item.url, {
        method: item.method,
        headers: item.headers,
        body: item.method === 'GET' || item.method === 'HEAD' ? undefined : item.body,
        credentials: 'include'
      });

      if (response && response.ok) {
        await deleteQueuedRequest(item.id);
        await notifyClients({ type: 'SYNC_SUCCESS', url: item.url, method: item.method, retries: item.retries || 0 });
      } else {
        item.retries = (item.retries || 0) + 1;
        const reason = `HTTP ${response ? response.status : 0}`;
        item.lastError = reason;
        const nextRetryAt = Date.now() + computeNextRetryDelay(item.retries);
        item.nextRetryAt = nextRetryAt;
        const dropped = item.retries >= 5;
        await notifyClients({ type: 'SYNC_ERROR', url: item.url, method: item.method, reason, retries: item.retries, dropped, nextRetryAt: dropped ? 0 : nextRetryAt });
        if (dropped) {
          await deleteQueuedRequest(item.id);
        } else {
          await updateQueuedRequest(item);
        }
      }
    } catch (error) {
      item.retries = (item.retries || 0) + 1;
      const reason = error && error.message ? String(error.message) : 'Network failure';
      item.lastError = reason;
      const nextRetryAt = Date.now() + computeNextRetryDelay(item.retries);
      item.nextRetryAt = nextRetryAt;
      const dropped = item.retries >= 5;
      await notifyClients({ type: 'SYNC_ERROR', url: item.url, method: item.method, reason, retries: item.retries, dropped, nextRetryAt: dropped ? 0 : nextRetryAt });
      if (dropped) {
        await deleteQueuedRequest(item.id);
      } else {
        await updateQueuedRequest(item);
      }
      return;
    }
  }
}

async function clearQueueOnly() {
  const db = await openQueueDb();
  await new Promise((resolve, reject) => {
    const tx = db.transaction(STORE_NAME, 'readwrite');
    tx.objectStore(STORE_NAME).clear();
    tx.oncomplete = resolve;
    tx.onerror = () => reject(tx.error);
  });
  db.close();
  await notifyClients({ type: 'QUEUE_CLEARED' });
}

async function clearRuntimeCacheOnly() {
  const keys = await caches.keys();
  await Promise.all(keys.filter((k) => k.includes('-runtime')).map((k) => caches.delete(k)));
}

async function clearOfflineData() {
  await clearRuntimeCacheOnly();

  const db = await openQueueDb();
  await new Promise((resolve, reject) => {
    const tx = db.transaction(STORE_NAME, 'readwrite');
    tx.objectStore(STORE_NAME).clear();
    tx.oncomplete = resolve;
    tx.onerror = () => reject(tx.error);
  });
  db.close();
}
