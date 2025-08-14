// Service Worker for Spanish English Learning App
// Version 1.0.0 - Phase 4.1 PWA Implementation

const CACHE_NAME = 'spanish-english-learning-v1.0.0';
const OFFLINE_URL = '/offline.html';

// Files to cache for offline functionality
const ESSENTIAL_FILES = [
  '/',
  '/offline.html',
  '/manifest.json',
  '/favicon.ico',
  // CSS and JS bundles (built by Vite)
  '/build/assets/app.js',
  '/build/assets/app.css',
  // Core pages for offline access
  '/foundations',
  '/daily-life',
  '/city',
  '/restaurant',
  '/questions',
  '/quiz-selector',
  '/gamification',
  '/pronunciation-practice',
  '/listening-comprehension',
  '/video-lessons',
  '/conversation-practice'
];

// API endpoints to cache for offline access
const API_CACHE_PATTERNS = [
  '/api/public/courses',
  '/api/public/quizzes',
  '/api/public/achievements',
  '/api/gamification/stats',
  '/api/user'
];

// Cache strategies
const CACHE_STRATEGIES = {
  CACHE_FIRST: 'cache-first',
  NETWORK_FIRST: 'network-first',
  CACHE_FALLING_BACK_TO_NETWORK: 'cache-falling-back-to-network',
  NETWORK_FALLING_BACK_TO_CACHE: 'network-falling-back-to-cache'
};

// Install event - cache essential files
self.addEventListener('install', (event) => {
  console.log('[SW] Installing service worker...');
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        console.log('[SW] Caching essential files');
        return cache.addAll(ESSENTIAL_FILES);
      })
      .then(() => {
        console.log('[SW] Essential files cached successfully');
        return self.skipWaiting(); // Activate immediately
      })
      .catch((error) => {
        console.error('[SW] Error caching essential files:', error);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  console.log('[SW] Activating service worker...');
  
  event.waitUntil(
    caches.keys()
      .then((cacheNames) => {
        return Promise.all(
          cacheNames.map((cacheName) => {
            if (cacheName !== CACHE_NAME) {
              console.log('[SW] Deleting old cache:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => {
        console.log('[SW] Service worker activated');
        return self.clients.claim(); // Take control immediately
      })
  );
});

// Fetch event - handle network requests
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);
  
  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }
  
  // Skip Chrome extension requests
  if (url.protocol === 'chrome-extension:') {
    return;
  }
  
  // Handle different types of requests
  if (url.pathname.startsWith('/api/')) {
    event.respondWith(handleAPIRequest(request));
  } else if (url.pathname.endsWith('.js') || url.pathname.endsWith('.css')) {
    event.respondWith(handleStaticAssets(request));
  } else if (url.pathname.startsWith('/images/')) {
    event.respondWith(handleImages(request));
  } else {
    event.respondWith(handleNavigationRequest(request));
  }
});

// Handle API requests with network-first strategy
async function handleAPIRequest(request) {
  const url = new URL(request.url);
  
  try {
    // Try network first
    const networkResponse = await fetch(request);
    
    // Cache successful API responses
    if (networkResponse.ok && isAPIEndpointCacheable(url.pathname)) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    console.log('[SW] Network failed for API request, trying cache:', url.pathname);
    
    // Fall back to cache
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      return cachedResponse;
    }
    
    // Return offline response for API calls
    return new Response(
      JSON.stringify({ 
        error: 'Offline', 
        message: 'Esta función requiere conexión a internet',
        offline: true
      }),
      {
        status: 503,
        statusText: 'Service Unavailable',
        headers: { 'Content-Type': 'application/json' }
      }
    );
  }
}

// Handle static assets with cache-first strategy
async function handleStaticAssets(request) {
  const cachedResponse = await caches.match(request);
  
  if (cachedResponse) {
    return cachedResponse;
  }
  
  try {
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    console.log('[SW] Failed to fetch static asset:', request.url);
    return new Response('Asset not available offline', { status: 404 });
  }
}

// Handle images with cache-first strategy and fallback
async function handleImages(request) {
  const cachedResponse = await caches.match(request);
  
  if (cachedResponse) {
    return cachedResponse;
  }
  
  try {
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    // Return a default offline image or SVG placeholder
    const offlineImageSVG = `
      <svg width="200" height="200" xmlns="http://www.w3.org/2000/svg">
        <rect width="200" height="200" fill="#f3f4f6"/>
        <text x="100" y="100" text-anchor="middle" dy=".35em" font-family="Arial" font-size="14" fill="#6b7280">
          Imagen no disponible offline
        </text>
      </svg>
    `;
    
    return new Response(offlineImageSVG, {
      headers: { 'Content-Type': 'image/svg+xml' }
    });
  }
}

// Handle navigation requests
async function handleNavigationRequest(request) {
  try {
    // Try network first for navigation
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    console.log('[SW] Network failed for navigation, trying cache:', request.url);
    
    // Try to find cached version
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      return cachedResponse;
    }
    
    // Fall back to cached root page or offline page
    const rootResponse = await caches.match('/');
    if (rootResponse) {
      return rootResponse;
    }
    
    // Ultimate fallback to offline page
    const offlineResponse = await caches.match(OFFLINE_URL);
    return offlineResponse || new Response('Page not available offline', { status: 404 });
  }
}

// Check if API endpoint should be cached
function isAPIEndpointCacheable(pathname) {
  return API_CACHE_PATTERNS.some(pattern => pathname.includes(pattern));
}

// Background sync for offline actions
self.addEventListener('sync', (event) => {
  console.log('[SW] Background sync event:', event.tag);
  
  if (event.tag === 'quiz-progress-sync') {
    event.waitUntil(syncQuizProgress());
  } else if (event.tag === 'chat-messages-sync') {
    event.waitUntil(syncChatMessages());
  } else if (event.tag === 'pronunciation-practice-sync') {
    event.waitUntil(syncPronunciationPractice());
  }
});

// Sync quiz progress when back online
async function syncQuizProgress() {
  try {
    console.log('[SW] Syncing offline quiz progress...');
    
    // Get offline progress data from IndexedDB or localStorage
    const offlineProgress = await getOfflineProgress();
    
    if (offlineProgress && offlineProgress.length > 0) {
      for (const progress of offlineProgress) {
        try {
          const response = await fetch('/api/progress', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(progress)
          });
          
          if (response.ok) {
            console.log('[SW] Synced quiz progress:', progress.section_id);
            await removeOfflineProgress(progress.id);
          }
        } catch (error) {
          console.error('[SW] Failed to sync quiz progress:', error);
        }
      }
    }
  } catch (error) {
    console.error('[SW] Error syncing quiz progress:', error);
  }
}

// Sync chat messages when back online
async function syncChatMessages() {
  try {
    console.log('[SW] Syncing offline chat messages...');
    
    const offlineMessages = await getOfflineChatMessages();
    
    if (offlineMessages && offlineMessages.length > 0) {
      for (const message of offlineMessages) {
        try {
          const response = await fetch('/api/chat/send', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(message)
          });
          
          if (response.ok) {
            console.log('[SW] Synced chat message');
            await removeOfflineChatMessage(message.tempId);
          }
        } catch (error) {
          console.error('[SW] Failed to sync chat message:', error);
        }
      }
    }
  } catch (error) {
    console.error('[SW] Error syncing chat messages:', error);
  }
}

// Sync pronunciation practice data when back online
async function syncPronunciationPractice() {
  try {
    console.log('[SW] Syncing offline pronunciation practice...');
    
    const offlinePractice = await getOfflinePronunciationPractice();
    
    if (offlinePractice && offlinePractice.length > 0) {
      for (const practice of offlinePractice) {
        try {
          // Sync practice data with the server
          const response = await fetch('/api/pronunciation/practice', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(practice)
          });
          
          if (response.ok) {
            console.log('[SW] Synced pronunciation practice');
            await removeOfflinePronunciationPractice(practice.id);
          }
        } catch (error) {
          console.error('[SW] Failed to sync pronunciation practice:', error);
        }
      }
    }
  } catch (error) {
    console.error('[SW] Error syncing pronunciation practice:', error);
  }
}

// Push notification handling
self.addEventListener('push', (event) => {
  console.log('[SW] Push notification received');
  
  const options = {
    icon: '/images/icon-192x192.png',
    badge: '/images/badge-72x72.png',
    vibrate: [200, 100, 200],
    requireInteraction: true,
    actions: [
      {
        action: 'open',
        title: 'Abrir App',
        icon: '/images/action-open.png'
      },
      {
        action: 'dismiss',
        title: 'Descartar',
        icon: '/images/action-dismiss.png'
      }
    ]
  };
  
  let notificationData = {
    title: 'Spanish English Learning App',
    body: 'Tienes nuevas actividades de aprendizaje disponibles',
    ...options
  };
  
  if (event.data) {
    try {
      const pushData = event.data.json();
      notificationData = { ...notificationData, ...pushData };
    } catch (error) {
      console.error('[SW] Error parsing push data:', error);
      notificationData.body = event.data.text() || notificationData.body;
    }
  }
  
  event.waitUntil(
    self.registration.showNotification(notificationData.title, notificationData)
  );
});

// Handle notification clicks
self.addEventListener('notificationclick', (event) => {
  console.log('[SW] Notification clicked:', event.action);
  
  event.notification.close();
  
  if (event.action === 'dismiss') {
    return;
  }
  
  // Determine URL based on notification data
  let targetUrl = '/';
  
  if (event.notification.data) {
    targetUrl = event.notification.data.url || '/';
  }
  
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true })
      .then((clientList) => {
        // Check if app is already open
        for (const client of clientList) {
          if (client.url.includes(self.location.origin) && 'focus' in client) {
            client.focus();
            if (targetUrl !== '/') {
              client.navigate(targetUrl);
            }
            return;
          }
        }
        
        // Open new window if app is not open
        if (clients.openWindow) {
          return clients.openWindow(targetUrl);
        }
      })
  );
});

// Message handling from main thread
self.addEventListener('message', (event) => {
  console.log('[SW] Message received:', event.data);
  
  if (event.data && event.data.type) {
    switch (event.data.type) {
      case 'SKIP_WAITING':
        self.skipWaiting();
        break;
      case 'CACHE_QUIZ_DATA':
        cacheQuizData(event.data.data);
        break;
      case 'STORE_OFFLINE_PROGRESS':
        storeOfflineProgress(event.data.data);
        break;
      case 'GET_OFFLINE_STATUS':
        event.ports[0].postMessage({ offline: !navigator.onLine });
        break;
    }
  }
});

// Helper functions for offline data management
async function getOfflineProgress() {
  // This would typically use IndexedDB for larger storage
  // For now, using a simple approach
  return JSON.parse(localStorage.getItem('offline-quiz-progress') || '[]');
}

async function removeOfflineProgress(progressId) {
  const progress = await getOfflineProgress();
  const updated = progress.filter(p => p.id !== progressId);
  localStorage.setItem('offline-quiz-progress', JSON.stringify(updated));
}

async function getOfflineChatMessages() {
  return JSON.parse(localStorage.getItem('offline-chat-messages') || '[]');
}

async function removeOfflineChatMessage(tempId) {
  const messages = await getOfflineChatMessages();
  const updated = messages.filter(m => m.tempId !== tempId);
  localStorage.setItem('offline-chat-messages', JSON.stringify(updated));
}

async function getOfflinePronunciationPractice() {
  return JSON.parse(localStorage.getItem('offline-pronunciation-practice') || '[]');
}

async function removeOfflinePronunciationPractice(practiceId) {
  const practices = await getOfflinePronunciationPractice();
  const updated = practices.filter(p => p.id !== practiceId);
  localStorage.setItem('offline-pronunciation-practice', JSON.stringify(updated));
}

async function cacheQuizData(data) {
  try {
    const cache = await caches.open(CACHE_NAME);
    const response = new Response(JSON.stringify(data), {
      headers: { 'Content-Type': 'application/json' }
    });
    await cache.put('/api/quiz-data-offline', response);
    console.log('[SW] Quiz data cached for offline use');
  } catch (error) {
    console.error('[SW] Error caching quiz data:', error);
  }
}

async function storeOfflineProgress(progressData) {
  try {
    const existing = await getOfflineProgress();
    existing.push({
      ...progressData,
      id: Date.now(),
      timestamp: new Date().toISOString()
    });
    localStorage.setItem('offline-quiz-progress', JSON.stringify(existing));
    console.log('[SW] Offline progress stored');
  } catch (error) {
    console.error('[SW] Error storing offline progress:', error);
  }
}

console.log('[SW] Service worker loaded successfully');