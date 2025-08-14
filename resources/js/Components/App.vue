<template>
  <div id="app" class="min-h-screen bg-gray-100" :class="{ 'mobile-layout': isMobile }">
    <!-- Mobile Navigation -->
    <div v-if="isMobile" class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50">
      <div class="flex justify-around py-2">
        <router-link 
          to="/" 
          class="mobile-nav-item"
          :class="{ 'active': $route.path === '/' }"
        >
          <span class="text-xl">🏠</span>
          <span class="text-xs">Inicio</span>
        </router-link>
        <router-link 
          to="/quiz-selector" 
          class="mobile-nav-item"
          :class="{ 'active': $route.path === '/quiz-selector' }"
        >
          <span class="text-xl">📚</span>
          <span class="text-xs">Práctica</span>
        </router-link>
        <router-link 
          to="/gamification" 
          class="mobile-nav-item"
          :class="{ 'active': $route.path === '/gamification' }"
        >
          <span class="text-xl">📊</span>
          <span class="text-xs">Progreso</span>
        </router-link>
        <router-link 
          to="/chat" 
          class="mobile-nav-item"
          :class="{ 'active': $route.path === '/chat' }"
        >
          <span class="text-xl">💬</span>
          <span class="text-xs">Chat</span>
        </router-link>
        <button 
          @click="toggleMobileMenu" 
          class="mobile-nav-item"
          :class="{ 'active': showMobileMenu }"
        >
          <span class="text-xl">☰</span>
          <span class="text-xs">Más</span>
        </button>
      </div>
    </div>

    <!-- Mobile Menu Overlay -->
    <div 
      v-if="isMobile && showMobileMenu" 
      class="fixed inset-0 bg-black bg-opacity-50 z-40"
      @click="showMobileMenu = false"
    >
      <div 
        class="fixed right-0 top-0 bottom-0 w-80 bg-white transform transition-transform duration-300"
        :class="showMobileMenu ? 'translate-x-0' : 'translate-x-full'"
        @click.stop
      >
        <div class="p-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">Menú</h3>
            <button @click="showMobileMenu = false" class="p-2">✕</button>
          </div>
        </div>
        
        <div class="overflow-y-auto h-full pb-20">
          <div v-if="user" class="p-4 border-b border-gray-200">
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                {{ user.name.charAt(0).toUpperCase() }}
              </div>
              <div>
                <p class="font-semibold">{{ user.name }}</p>
                <p class="text-sm text-gray-600">{{ user.email }}</p>
              </div>
            </div>
          </div>

          <!-- Language Switcher in Mobile Menu -->
          <div class="p-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-gray-700">Idioma</span>
              <LanguageSwitcher />
            </div>
          </div>

          <div class="py-2">
            <div class="mobile-menu-section">
              <h4 class="mobile-menu-header">Lecciones</h4>
              <router-link to="/foundations" class="mobile-menu-link" @click="showMobileMenu = false">
                <span class="text-lg mr-3">📖</span> Fundamentos
              </router-link>
              <router-link to="/daily-life" class="mobile-menu-link" @click="showMobileMenu = false">
                <span class="text-lg mr-3">🏠</span> Mi Día a Día
              </router-link>
              <router-link to="/city" class="mobile-menu-link" @click="showMobileMenu = false">
                <span class="text-lg mr-3">🏙️</span> Mi Ciudad
              </router-link>
              <router-link to="/restaurant" class="mobile-menu-link" @click="showMobileMenu = false">
                <span class="text-lg mr-3">🍽️</span> En el Restaurante
              </router-link>
              <router-link to="/questions" class="mobile-menu-link" @click="showMobileMenu = false">
                <span class="text-lg mr-3">❓</span> Haciendo Preguntas
              </router-link>
            </div>

            <div class="mobile-menu-section">
              <h4 class="mobile-menu-header">Práctica</h4>
              <router-link to="/pronunciation-practice" class="mobile-menu-link" @click="showMobileMenu = false">
                <span class="text-lg mr-3">🎤</span> Pronunciación
              </router-link>
              <router-link to="/listening-comprehension" class="mobile-menu-link" @click="showMobileMenu = false">
                <span class="text-lg mr-3">🎧</span> Comprensión Auditiva
              </router-link>
              <router-link to="/video-lessons" class="mobile-menu-link" @click="showMobileMenu = false">
                <span class="text-lg mr-3">🎬</span> Lecciones en Video
              </router-link>
              <router-link to="/conversation-practice" class="mobile-menu-link" @click="showMobileMenu = false">
                <span class="text-lg mr-3">💬</span> Conversación
              </router-link>
              <router-link to="/writing-practice" class="mobile-menu-link" @click="showMobileMenu = false">
                <span class="text-lg mr-3">✍️</span> Escritura
              </router-link>
            </div>

            <div class="mobile-menu-section">
              <h4 class="mobile-menu-header">Inteligencia Artificial</h4>
              <router-link to="/mistake-analysis" class="mobile-menu-link" @click="showMobileMenu = false">
                <span class="text-lg mr-3">🧠</span> Análisis de Errores
              </router-link>
              <router-link to="/adaptive-learning" class="mobile-menu-link" @click="showMobileMenu = false">
                <span class="text-lg mr-3">🤖</span> Aprendizaje IA
              </router-link>
              <router-link to="/spaced-repetition" class="mobile-menu-link" @click="showMobileMenu = false">
                <span class="text-lg mr-3">🔄</span> Repaso Espaciado
              </router-link>
            </div>

            <div class="mobile-menu-section">
              <h4 class="mobile-menu-header">Social</h4>
              <router-link to="/multiplayer" class="mobile-menu-link" @click="showMobileMenu = false">
                <span class="text-lg mr-3">🏆</span> Competencias
              </router-link>
              <router-link to="/social" class="mobile-menu-link" @click="showMobileMenu = false">
                <span class="text-lg mr-3">👥</span> Amigos
              </router-link>
              <router-link to="/certificates" class="mobile-menu-link" @click="showMobileMenu = false">
                <span class="text-lg mr-3">🏆</span> Certificados
              </router-link>
            </div>

            <div class="mobile-menu-section">
              <h4 class="mobile-menu-header">Analytics</h4>
              <router-link to="/analytics" class="mobile-menu-link" @click="showMobileMenu = false">
                <span class="text-lg mr-3">📈</span> Analytics
              </router-link>
              <router-link to="/quiz-history" class="mobile-menu-link" @click="showMobileMenu = false">
                <span class="text-lg mr-3">📝</span> Quiz History
              </router-link>
              <router-link to="/dashboard" class="mobile-menu-link" @click="showMobileMenu = false">
                <span class="text-lg mr-3">📊</span> Dashboard
              </router-link>
              <router-link to="/instructor" class="mobile-menu-link" @click="showMobileMenu = false">
                <span class="text-lg mr-3">👨‍🏫</span> Panel de Instructor
              </router-link>
            </div>

            <div v-if="user?.is_admin" class="mobile-menu-section">
              <h4 class="mobile-menu-header">Administración</h4>
              <router-link to="/admin" class="mobile-menu-link" @click="showMobileMenu = false">
                <span class="text-lg mr-3">⚙️</span> Admin Panel
              </router-link>
            </div>

            <div v-if="user" class="mobile-menu-section">
              <button @click="logout" class="mobile-menu-link text-red-600 w-full text-left">
                <span class="text-lg mr-3">🚪</span> Cerrar Sesión
              </button>
            </div>
            <div v-else class="mobile-menu-section">
              <a href="/login" class="mobile-menu-link">
                <span class="text-lg mr-3">🔑</span> Iniciar Sesión
              </a>
              <a href="/register" class="mobile-menu-link">
                <span class="text-lg mr-3">📝</span> Registrarse
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Desktop Header -->
    <header v-if="!isMobile" class="bg-white/80 backdrop-blur-lg shadow-sm sticky top-0 z-10">
      <nav class="container mx-auto px-4 py-3 flex justify-between items-center">
        <div class="flex flex-wrap justify-center items-center gap-2 md:gap-4">
          <router-link to="/" class="nav-btn text-sm md:text-base px-3 py-2 rounded-lg">Inicio</router-link>
          <router-link to="/foundations" class="nav-btn text-sm md:text-base px-3 py-2 rounded-lg">Fundamentos</router-link>
          <router-link to="/daily-life" class="nav-btn text-sm md:text-base px-3 py-2 rounded-lg">Mi Día a Día</router-link>
          <router-link to="/city" class="nav-btn text-sm md:text-base px-3 py-2 rounded-lg">Mi Ciudad</router-link>
          <router-link to="/restaurant" class="nav-btn text-sm md:text-base px-3 py-2 rounded-lg">En el Restaurante</router-link>
          <router-link to="/questions" class="nav-btn text-sm md:text-base px-3 py-2 rounded-lg">Haciendo Preguntas</router-link>
          <router-link to="/quizzes" class="nav-btn text-sm md:text-base px-3 py-2 rounded-lg">Quizzes</router-link>
          <router-link to="/quiz-selector" class="nav-btn text-sm md:text-base px-3 py-2 rounded-lg">Práctica</router-link>
        </div>

        <div class="flex items-center gap-3">
          <!-- Language Switcher -->
          <LanguageSwitcher />
          
          <div class="relative pb-1" @mouseenter="showDropdown = true" @mouseleave="showDropdown = false">
          <template v-if="user">
            <button class="auth-btn text-sm md:text-base px-3 py-2 rounded-lg flex items-center">
              {{ user.name }}
              <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
            <div v-if="showDropdown" class="absolute right-0 w-48 bg-white rounded-md shadow-lg py-1 z-20">
              <a href="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Account Settings</a>
              <router-link to="/gamification" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">📊 Mi Progreso</router-link>
              <router-link to="/analytics" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">📈 Analytics</router-link>
              <router-link to="/writing-practice" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">✍️ Práctica de Escritura</router-link>
              <router-link to="/pronunciation-practice" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🎤 Práctica de Pronunciación</router-link>
              <router-link to="/listening-comprehension" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🎧 Comprensión Auditiva</router-link>
              <router-link to="/video-lessons" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🎬 Lecciones en Video</router-link>
              <router-link to="/conversation-practice" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">💬 Práctica de Conversación</router-link>
              <router-link to="/mistake-analysis" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🧠 Análisis de Errores</router-link>
              <router-link to="/adaptive-learning" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🧠 Aprendizaje IA</router-link>
              <router-link to="/spaced-repetition" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🔄 Repaso Espaciado</router-link>
              <router-link to="/chat" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">💬 Chat en Vivo</router-link>
              <router-link to="/multiplayer" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🏆 Competencias</router-link>
              <router-link to="/certificates" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">🏆 Certificados</router-link>
              <router-link to="/social" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">👥 Amigos</router-link>
              <router-link to="/quiz-history" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Quiz History</router-link>
              <router-link to="/dashboard" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Dashboard</router-link>
              <router-link to="/instructor" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">👨‍🏫 Panel de Instructor</router-link>
              <router-link v-if="user.is_admin" to="/admin" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Admin Panel</router-link>
              <button @click="logout" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</button>
            </div>
          </template>
          <template v-else>
            <a href="/login" class="auth-btn text-sm md:text-base px-3 py-2 rounded-lg">Login</a>
            <a href="/register" class="auth-btn text-sm md:text-base px-3 py-2 rounded-lg ml-2">Register</a>
          </template>
          </div>
        </div>
      </nav>
    </header>

    <!-- PWA Install Prompt -->
    <div v-if="showInstallPrompt" class="fixed bottom-20 md:bottom-4 left-4 right-4 bg-gradient-to-r from-purple-600 to-indigo-600 text-white p-4 rounded-lg shadow-lg z-30">
      <div class="flex items-center justify-between">
        <div>
          <p class="font-semibold">📱 ¡Instala la App!</p>
          <p class="text-sm opacity-90">Accede más rápido desde tu pantalla de inicio</p>
        </div>
        <div class="flex space-x-2">
          <button @click="installPWA" class="bg-white/20 px-3 py-1 rounded text-sm">
            Instalar
          </button>
          <button @click="dismissInstallPrompt" class="bg-white/10 px-2 py-1 rounded text-sm">
            ✕
          </button>
        </div>
      </div>
    </div>

    <!-- Offline Indicator -->
    <div v-if="!isOnline" class="fixed top-0 left-0 right-0 bg-red-500 text-white text-center py-2 text-sm z-50">
      📡 Sin conexión - Trabajando en modo offline
    </div>

    <!-- Main Content -->
    <main class="container mx-auto p-4 md:p-8" :class="{ 'pb-20': isMobile }">
      <router-view v-slot="{ Component }">
        <transition name="fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </main>
  </div>
</template>

<script>
import { gsap } from 'gsap';
import LanguageSwitcher from './LanguageSwitcher.vue';

export default {
  name: 'App',
  components: {
    LanguageSwitcher
  },
  data() {
    return {
      user: null,
      showDropdown: false,
      showMobileMenu: false,
      isMobile: false,
      isOnline: navigator.onLine,
      showInstallPrompt: false,
      deferredPrompt: null
    };
  },
  async mounted() {
    try {
      const response = await axios.get('/api/user');
      this.user = response.data;
      console.log('User API Response:', response.data);
    } catch (error) {
      console.error('Failed to fetch user:', error);
      console.error('User API Error:', error.response);
    }

    // Initialize mobile detection and PWA features
    this.initializeMobileDetection();
    this.initializePWAFeatures();
    this.initializeOfflineDetection();
    this.initializeTouchGestures();
  },
  methods: {
    async logout() {
      try {
        await axios.post('/logout');
        window.location.href = '/'; // Redirect to home after logout
      } catch (error) {
        console.error('Logout failed:', error);
      }
    },

    // Mobile Detection
    initializeMobileDetection() {
      const checkMobile = () => {
        this.isMobile = window.innerWidth <= 768 || /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
      };
      
      checkMobile();
      window.addEventListener('resize', checkMobile);
    },

    toggleMobileMenu() {
      this.showMobileMenu = !this.showMobileMenu;
      
      // Prevent body scroll when menu is open
      if (this.showMobileMenu) {
        document.body.style.overflow = 'hidden';
      } else {
        document.body.style.overflow = '';
      }
    },

    // PWA Features
    initializePWAFeatures() {
      // Listen for PWA install prompt
      window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        this.deferredPrompt = e;
        this.showInstallPrompt = true;
        
        // Auto-hide after 10 seconds
        setTimeout(() => {
          this.showInstallPrompt = false;
        }, 10000);
      });

      // Check if already installed
      window.addEventListener('appinstalled', () => {
        this.showInstallPrompt = false;
        this.deferredPrompt = null;
        console.log('PWA was installed');
      });

      // Request notification permission for logged-in users
      if (this.user && 'Notification' in window && Notification.permission === 'default') {
        setTimeout(() => {
          this.requestNotificationPermission();
        }, 3000);
      }
    },

    installPWA() {
      if (this.deferredPrompt) {
        this.deferredPrompt.prompt();
        this.deferredPrompt.userChoice.then((choiceResult) => {
          if (choiceResult.outcome === 'accepted') {
            console.log('User accepted the install prompt');
          }
          this.deferredPrompt = null;
          this.showInstallPrompt = false;
        });
      }
    },

    dismissInstallPrompt() {
      this.showInstallPrompt = false;
    },

    // Offline Detection
    initializeOfflineDetection() {
      window.addEventListener('online', () => {
        this.isOnline = true;
        this.showOnlineNotification();
      });
      
      window.addEventListener('offline', () => {
        this.isOnline = false;
      });
    },

    showOnlineNotification() {
      if ('serviceWorker' in navigator && 'Notification' in window && Notification.permission === 'granted') {
        navigator.serviceWorker.ready.then((registration) => {
          registration.showNotification('¡Conexión Restaurada!', {
            body: 'Ya puedes acceder a todas las funciones de la app',
            icon: '/images/icon-192x192.png',
            badge: '/images/badge-72x72.png',
            tag: 'connection-restored',
            requireInteraction: false
          });
        });
      }
    },

    // Touch Gestures
    initializeTouchGestures() {
      let startX = 0;
      let startY = 0;
      let distX = 0;
      let distY = 0;
      
      document.addEventListener('touchstart', (e) => {
        const touch = e.touches[0];
        startX = touch.clientX;
        startY = touch.clientY;
      });
      
      document.addEventListener('touchmove', (e) => {
        if (!startX || !startY) return;
        
        const touch = e.touches[0];
        distX = touch.clientX - startX;
        distY = touch.clientY - startY;
      });
      
      document.addEventListener('touchend', (e) => {
        if (!startX || !startY) return;
        
        // Swipe threshold
        const threshold = 100;
        
        // Right swipe to open mobile menu (when on left edge)
        if (startX < 50 && distX > threshold && Math.abs(distY) < threshold) {
          if (this.isMobile && !this.showMobileMenu) {
            this.showMobileMenu = true;
          }
        }
        
        // Left swipe to close mobile menu
        if (this.showMobileMenu && distX < -threshold && Math.abs(distY) < threshold) {
          this.showMobileMenu = false;
        }
        
        // Reset values
        startX = 0;
        startY = 0;
        distX = 0;
        distY = 0;
      });
    },

    // Notification Permission
    async requestNotificationPermission() {
      if ('Notification' in window && 'serviceWorker' in navigator) {
        try {
          const permission = await Notification.requestPermission();
          if (permission === 'granted') {
            console.log('Notification permission granted');
            this.subscribeToNotifications();
          }
        } catch (error) {
          console.error('Error requesting notification permission:', error);
        }
      }
    },

    async subscribeToNotifications() {
      try {
        const registration = await navigator.serviceWorker.ready;
        const subscription = await registration.pushManager.subscribe({
          userVisibleOnly: true,
          applicationServerKey: this.urlBase64ToUint8Array('BCqKW9VBnF4QgWWxrK8P7-Fv2LjTgqwGLJKp5bQfJ8hKrZPqwL7E8dF3Z4hBxQ2')
        });
        
        // Send subscription to server
        await axios.post('/api/notifications/subscribe', subscription);
        console.log('Successfully subscribed to push notifications');
      } catch (error) {
        console.error('Failed to subscribe to push notifications:', error);
      }
    },

    urlBase64ToUint8Array(base64String) {
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
  },

  beforeUnmount() {
    // Clean up body styles
    document.body.style.overflow = '';
  }
};
</script>

<style>
.nav-btn {
  transition: all 0.3s ease;
}
.nav-btn.router-link-exact-active {
  background-color: #4A55A2;
  color: #FFFFFF;
  font-weight: 700;
}
.auth-btn {
  @apply bg-purple-600 text-white px-4 py-2 rounded-lg text-sm md:text-base font-semibold hover:bg-purple-700 transition-colors duration-200;
}
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.1s;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}
</style>