<template>
  <div id="app" class="min-h-screen bg-gray-100">
    <header class="bg-white/80 backdrop-blur-lg shadow-sm sticky top-0 z-10">
      <nav class="container mx-auto px-4 py-3">
        <div class="flex flex-wrap justify-center items-center gap-2 md:gap-4">
          <router-link to="/" class="nav-btn text-sm md:text-base px-3 py-2 rounded-lg">Inicio</router-link>
          <router-link to="/foundations" class="nav-btn text-sm md:text-base px-3 py-2 rounded-lg">Fundamentos</router-link>
          <router-link to="/daily-life" class="nav-btn text-sm md:text-base px-3 py-2 rounded-lg">Mi Día a Día</router-link>
          <router-link to="/city" class="nav-btn text-sm md:text-base px-3 py-2 rounded-lg">Mi Ciudad</router-link>
          <router-link to="/restaurant" class="nav-btn text-sm md:text-base px-3 py-2 rounded-lg">En el Restaurante</router-link>
          <router-link to="/questions" class="nav-btn text-sm md:text-base px-3 py-2 rounded-lg">Haciendo Preguntas</router-link>
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
export default {
  name: 'App',
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
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}
</style>
