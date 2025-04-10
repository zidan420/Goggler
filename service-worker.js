// Service Worker for Goggler - Offline Functionality
const CACHE_NAME = "goggler-offline-v1";
const ASSETS_TO_CACHE = ["/goggler_zidan/styles/index.css", "/goggler_zidan/offline.html"];

// Install event - Pre-cache essential assets
self.addEventListener("install", (event) => {
    event.waitUntil(
        caches
            .open(CACHE_NAME)
            .then((cache) => {
                console.log("Opened cache");
                return cache.addAll(ASSETS_TO_CACHE);
            })
            .catch((error) => {
                console.error("Pre-caching failed:", error);
            })
    );
    self.skipWaiting();
});

// Activate event - Clean up old caches
self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log("Deleting old cache:", cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Fetch event
self.addEventListener("fetch", (event) => {
    if (event.request.method !== "GET") {
        return;
    }

    event.respondWith(
        fetch(event.request)
            .then((response) => {})
            .catch(() => {
                // when offline, display offline.html
                return caches.match("/goggler_zidan/offline.html");
            })
    );
});
