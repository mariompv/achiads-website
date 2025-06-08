/**
 * ACHIADS - Service Worker
 * Progressive Web App functionality
 * Versión: 1.0.0
 */

const CACHE_NAME = 'achiads-v1';
const STATIC_CACHE = 'achiads-static-v1';
const DYNAMIC_CACHE = 'achiads-dynamic-v1';

// Archivos principales para cachear
const STATIC_FILES = [
    '/',
    '/index.html',
    '/assets/css/styles.css',
    '/assets/js/main.js',
    '/favicon.svg',
    '/manifest.json',
    '/404.html',
    'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap'
];

// Archivos que no deben cachearse
const EXCLUDED_PATHS = [
    '/contact_form.php',
    '/admin',
    '/logs'
];

// Instalación del Service Worker
self.addEventListener('install', event => {
    console.log('Service Worker: Instalando...');
    
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then(cache => {
                console.log('Service Worker: Cacheando archivos estáticos');
                return cache.addAll(STATIC_FILES);
            })
            .then(() => {
                console.log('Service Worker: Instalado correctamente');
                // Forzar activación inmediata
                return self.skipWaiting();
            })
            .catch(error => {
                console.error('Service Worker: Error durante instalación:', error);
            })
    );
});

// Activación del Service Worker
self.addEventListener('activate', event => {
    console.log('Service Worker: Activando...');
    
    event.waitUntil(
        caches.keys()
            .then(cacheNames => {
                return Promise.all(
                    cacheNames.map(cacheName => {
                        // Eliminar caches antiguos
                        if (cacheName !== STATIC_CACHE && cacheName !== DYNAMIC_CACHE) {
                            console.log('Service Worker: Eliminando cache antiguo:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('Service Worker: Activado correctamente');
                // Tomar control de todas las páginas inmediatamente
                return self.clients.claim();
            })
    );
});

// Interceptar peticiones
self.addEventListener('fetch', event => {
    const request = event.request;
    const url = new URL(request.url);
    
    // Solo manejar peticiones GET
    if (request.method !== 'GET') {
        return;
    }
    
    // Excluir ciertas rutas del cache
    if (EXCLUDED_PATHS.some(path => url.pathname.startsWith(path))) {
        return;
    }
    
    // Estrategia de cache
    if (isStaticAsset(request)) {
        // Cache First para archivos estáticos
        event.respondWith(cacheFirst(request));
    } else if (isHTMLPage(request)) {
        // Network First para páginas HTML
        event.respondWith(networkFirst(request));
    } else {
        // Stale While Revalidate para otros recursos
        event.respondWith(staleWhileRevalidate(request));
    }
});

// Verificar si es un archivo estático
function isStaticAsset(request) {
    const url = new URL(request.url);
    return url.pathname.match(/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2)$/);
}

// Verificar si es una página HTML
function isHTMLPage(request) {
    const url = new URL(request.url);
    return url.pathname === '/' || 
           url.pathname.endsWith('.html') || 
           !url.pathname.includes('.');
}

// Estrategia Cache First
async function cacheFirst(request) {
    try {
        const cachedResponse = await caches.match(request);
        if (cachedResponse) {
            return cachedResponse;
        }
        
        const networkResponse = await fetch(request);
        if (networkResponse.ok) {
            const cache = await caches.open(STATIC_CACHE);
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
        
    } catch (error) {
        console.error('Cache First error:', error);
        return caches.match('/404.html');
    }
}

// Estrategia Network First
async function networkFirst(request) {
    try {
        const networkResponse = await fetch(request);
        if (networkResponse.ok) {
            const cache = await caches.open(DYNAMIC_CACHE);
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
        
    } catch (error) {
        console.log('Network First fallback to cache:', error);
        const cachedResponse = await caches.match(request);
        
        if (cachedResponse) {
            return cachedResponse;
        }
        
        // Fallback para páginas HTML
        if (isHTMLPage(request)) {
            return caches.match('/');
        }
        
        return caches.match('/404.html');
    }
}

// Estrategia Stale While Revalidate
async function staleWhileRevalidate(request) {
    const cache = await caches.open(DYNAMIC_CACHE);
    const cachedResponse = await cache.match(request);
    
    const fetchPromise = fetch(request).then(networkResponse => {
        if (networkResponse.ok) {
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    }).catch(error => {
        console.log('Stale while revalidate fetch error:', error);
        return cachedResponse;
    });
    
    return cachedResponse || fetchPromise;
}

// Limpiar cache dinámico cuando sea muy grande
async function limitCacheSize(cacheName, maxItems) {
    const cache = await caches.open(cacheName);
    const keys = await cache.keys();
    
    if (keys.length > maxItems) {
        await cache.delete(keys[0]);
        limitCacheSize(cacheName, maxItems);
    }
}

// Limpiar periódicamente el cache dinámico
self.addEventListener('message', event => {
    if (event.data && event.data.type === 'CLEAN_CACHE') {
        limitCacheSize(DYNAMIC_CACHE, 50);
    }
});

// Notificaciones push (para futuro)
self.addEventListener('push', event => {
    if (!event.data) return;
    
    const data = event.data.json();
    const options = {
        body: data.body,
        icon: '/favicon.svg',
        badge: '/favicon.svg',
        vibrate: [100, 50, 100],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: data.primaryKey
        },
        actions: [
            {
                action: 'explore',
                title: 'Ver más',
                icon: '/assets/images/checkmark.png'
            },
            {
                action: 'close',
                title: 'Cerrar',
                icon: '/assets/images/xmark.png'
            }
        ]
    };
    
    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

// Manejar clics en notificaciones
self.addEventListener('notificationclick', event => {
    event.notification.close();
    
    if (event.action === 'explore') {
        event.waitUntil(
            clients.openWindow('/')
        );
    }
});

// Sincronización en segundo plano (para futuro)
self.addEventListener('sync', event => {
    if (event.tag === 'background-sync') {
        event.waitUntil(doBackgroundSync());
    }
});

async function doBackgroundSync() {
    // Implementar sincronización de datos offline
    console.log('Background sync ejecutándose...');
}