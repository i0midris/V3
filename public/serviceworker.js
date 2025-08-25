self.addEventListener('install', function(event) {
  console.log('[ServiceWorker] Install');
  self.skipWaiting(); // Activate worker immediately
});

self.addEventListener('activate', function(event) {
  console.log('[ServiceWorker] Activate');
  return self.clients.claim(); // Become available to all pages
});

self.addEventListener('fetch', function(event) {
  // This is a basic placeholder. It doesn't intercept or cache anything.
  return;
});
