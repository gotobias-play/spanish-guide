<template>
  <div id="app" class="min-h-screen bg-gray-100">
    <header class="bg-white/80 backdrop-blur-lg shadow-sm sticky top-0 z-10">
      <nav class="container mx-auto px-4 py-3 flex justify-between items-center">
        <div class="flex flex-wrap justify-center items-center gap-2 md:gap-4">
          <router-link to="/" class="nav-btn text-sm md:text-base px-3 py-2 rounded-lg">Inicio</router-link>
          <router-link to="/foundations" class="nav-btn text-sm md:text-base px-3 py-2 rounded-lg">Fundamentos</router-link>
          <router-link to="/daily-life" class="nav-btn text-sm md:text-base px-3 py-2 rounded-lg">Mi Día a Día</router-link>
          <router-link to="/city" class="nav-btn text-sm md:text-base px-3 py-2 rounded-lg">Mi Ciudad</router-link>
          <router-link to="/restaurant" class="nav-btn text-sm md:text-base px-3 py-2 rounded-lg">En el Restaurante</router-link>
          <router-link to="/questions" class="nav-btn text-sm md:text-base px-3 py-2 rounded-lg">Haciendo Preguntas</router-link>
          <router-link to="/quizzes" class="nav-btn text-sm md:text-base px-3 py-2 rounded-lg">Quizzes</router-link>
        </div>

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
              <router-link to="/quiz-history" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Quiz History</router-link>
              <router-link to="/dashboard" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Dashboard</router-link>
              <router-link v-if="user.is_admin" to="/admin" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Admin Panel</router-link>
              <button @click="logout" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</button>
            </div>
          </template>
          <template v-else>
            <a href="/login" class="auth-btn text-sm md:text-base px-3 py-2 rounded-lg">Login</a>
            <a href="/register" class="auth-btn text-sm md:text-base px-3 py-2 rounded-lg ml-2">Register</a>
          </template>
        </div>
      </nav>
    </header>
    <main class="container mx-auto p-4 md:p-8">
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

export default {
  name: 'App',
  data() {
    return {
      user: null,
      showDropdown: false,
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
  },
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