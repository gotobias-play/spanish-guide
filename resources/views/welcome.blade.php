<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Spanish English Learning App - Aprende inglés de forma interactiva</title>
        <meta name="description" content="Aprende inglés de forma interactiva con multimedia, IA, competencias en vivo y práctica de conversación">

        <!-- PWA Meta Tags -->
        <meta name="theme-color" content="#4f46e5">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="English Learning">
        <meta name="msapplication-TileColor" content="#4f46e5">
        <meta name="msapplication-config" content="/browserconfig.xml">

        <!-- PWA Manifest -->
        <link rel="manifest" href="/manifest.json">

        <!-- Favicon and Icons -->
        <link rel="icon" type="image/png" sizes="32x32" href="/images/icon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/images/icon-16x16.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
        <link rel="mask-icon" href="/images/safari-pinned-tab.svg" color="#4f46e5">

        <!-- Resource Preloading for Performance -->
        <link rel="dns-prefetch" href="https://fonts.bunny.net">
        <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
        <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
        
        <!-- Fonts with display=swap for better performance -->
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        
        <!-- Preload critical assets -->
        <link rel="preload" href="/manifest.json" as="fetch" crossorigin>
        <link rel="modulepreload" href="{{ asset('resources/js/app.js') }}">

        <!-- GSAP -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script>
            window.Laravel = {
                csrfToken: '{{ csrf_token() }}',
                user: @json(Auth::user())
            };
        </script>

        <!-- Service Worker Registration -->
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function() {
                    navigator.serviceWorker.register('/sw.js')
                        .then(function(registration) {
                            console.log('Service Worker registered successfully:', registration.scope);
                            
                            // Check for updates
                            registration.addEventListener('updatefound', function() {
                                const newWorker = registration.installing;
                                newWorker.addEventListener('statechange', function() {
                                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                        // Show update notification
                                        showUpdateNotification();
                                    }
                                });
                            });
                        })
                        .catch(function(error) {
                            console.log('Service Worker registration failed:', error);
                        });
                });

                // Handle service worker messages
                navigator.serviceWorker.addEventListener('message', function(event) {
                    if (event.data && event.data.type === 'OFFLINE_STATUS') {
                        updateOfflineIndicator(event.data.offline);
                    }
                });
            }

            // PWA Install Prompt
            let deferredPrompt;
            window.addEventListener('beforeinstallprompt', function(e) {
                e.preventDefault();
                deferredPrompt = e;
                showInstallPrompt();
            });

            function showInstallPrompt() {
                // Create install prompt UI
                const installBanner = document.createElement('div');
                installBanner.id = 'install-banner';
                installBanner.innerHTML = `
                    <div style="position: fixed; bottom: 20px; left: 20px; right: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); z-index: 1000; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <strong>📱 ¡Instala la App!</strong>
                            <p style="margin: 5px 0 0; font-size: 14px; opacity: 0.9;">Accede más rápido desde tu pantalla de inicio</p>
                        </div>
                        <div>
                            <button onclick="installPWA()" style="background: rgba(255,255,255,0.2); border: none; color: white; padding: 8px 16px; border-radius: 6px; margin-right: 10px; cursor: pointer;">Instalar</button>
                            <button onclick="dismissInstall()" style="background: rgba(255,255,255,0.1); border: none; color: white; padding: 8px 12px; border-radius: 6px; cursor: pointer;">×</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(installBanner);

                // Auto-hide after 10 seconds
                setTimeout(() => {
                    if (document.getElementById('install-banner')) {
                        dismissInstall();
                    }
                }, 10000);
            }

            function installPWA() {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    deferredPrompt.userChoice.then(function(choiceResult) {
                        if (choiceResult.outcome === 'accepted') {
                            console.log('PWA installed');
                        }
                        deferredPrompt = null;
                        dismissInstall();
                    });
                }
            }

            function dismissInstall() {
                const banner = document.getElementById('install-banner');
                if (banner) {
                    banner.remove();
                }
            }

            function showUpdateNotification() {
                const updateBanner = document.createElement('div');
                updateBanner.innerHTML = `
                    <div style="position: fixed; top: 20px; left: 20px; right: 20px; background: #10b981; color: white; padding: 15px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); z-index: 1000; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <strong>🔄 Nueva versión disponible</strong>
                            <p style="margin: 5px 0 0; font-size: 14px; opacity: 0.9;">Se ha descargado una actualización</p>
                        </div>
                        <button onclick="refreshApp()" style="background: rgba(255,255,255,0.2); border: none; color: white; padding: 8px 16px; border-radius: 6px; cursor: pointer;">Actualizar</button>
                    </div>
                `;
                document.body.appendChild(updateBanner);
            }

            function refreshApp() {
                if (navigator.serviceWorker.controller) {
                    navigator.serviceWorker.controller.postMessage({ type: 'SKIP_WAITING' });
                }
                window.location.reload();
            }

            // Push notification setup
            function requestNotificationPermission() {
                if ('Notification' in window && 'serviceWorker' in navigator) {
                    Notification.requestPermission().then(function(permission) {
                        if (permission === 'granted') {
                            console.log('Notification permission granted');
                            subscribeToNotifications();
                        }
                    });
                }
            }

            function subscribeToNotifications() {
                navigator.serviceWorker.ready.then(function(registration) {
                    return registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array('{{ config("app.vapid_public_key", "") }}')
                    });
                }).then(function(subscription) {
                    console.log('User is subscribed to notifications');
                    // Send subscription to server
                    sendSubscriptionToServer(subscription);
                }).catch(function(error) {
                    console.log('Failed to subscribe to notifications:', error);
                });
            }

            function sendSubscriptionToServer(subscription) {
                fetch('/api/notifications/subscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(subscription)
                }).catch(error => console.log('Error sending subscription:', error));
            }

            function urlBase64ToUint8Array(base64String) {
                const padding = '='.repeat((4 - base64String.length % 4) % 4);
                const base64 = (base64String + padding)
                    .replace(/\-/g, '+')
                    .replace(/_/g, '/');
                
                const rawData = window.atob(base64);
                const outputArray = new Uint8Array(rawData.length);
                
                for (let i = 0; i < rawData.length; ++i) {
                    outputArray[i] = rawData.charCodeAt(i);
                }
                return outputArray;
            }

            // Connection status monitoring
            function updateOfflineIndicator(isOffline) {
                let indicator = document.getElementById('offline-indicator');
                
                if (isOffline && !indicator) {
                    indicator = document.createElement('div');
                    indicator.id = 'offline-indicator';
                    indicator.innerHTML = `
                        <div style="position: fixed; top: 0; left: 0; right: 0; background: #f56565; color: white; padding: 8px; text-align: center; font-size: 14px; z-index: 999;">
                            📡 Sin conexión - Trabajando en modo offline
                        </div>
                    `;
                    document.body.appendChild(indicator);
                } else if (!isOffline && indicator) {
                    indicator.remove();
                }
            }

            // Monitor connection status
            window.addEventListener('online', () => updateOfflineIndicator(false));
            window.addEventListener('offline', () => updateOfflineIndicator(true));
            
            // Initialize offline indicator if already offline
            if (!navigator.onLine) {
                updateOfflineIndicator(true);
            }
        </script>
    </head>
    <body class="font-sans antialiased">
        <div id="app"></div>
    </body>
</html>