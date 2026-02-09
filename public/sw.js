// Minimal service worker - no caching. Keeps registration harmless.
self.addEventListener('install', event => {
  self.skipWaiting();
});

self.addEventListener('activate', event => {
  event.waitUntil(clients.claim());
});

// Note: intentionally no fetch event listener to avoid no-op handler warnings.
