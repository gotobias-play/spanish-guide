<template>
  <div class="text-center">
    <h1 class="text-4xl md:text-5xl font-bold text-[#4A55A2] mb-4">¡Bienvenido a tu Guía Interactiva de Inglés!</h1>
    <p class="text-lg md:text-xl max-w-3xl mx-auto mb-8">Esta es una herramienta para ayudarte a repasar y practicar todo lo que has aprendido. Navega por las secciones usando los botones de arriba para explorar los temas de forma divertida e interactiva.</p>
    <div class="card max-w-md mx-auto">
      <h2 class="text-2xl font-bold mb-2">¿Cómo funciona?</h2>
      <p>Haz clic en los botones, responde las preguntas y mira cómo la gramática cobra vida. ¡El objetivo es practicar! No tengas miedo de equivocarte. ¡Empecemos!</p>
    </div>
  </div>
</template>

<script>
import { gsap } from 'gsap';

export default {
  name: 'Home',
  mounted() {
    gsap.from(this.$el, { duration: 0.5, opacity: 0, y: 20 });
  },
};
</script>
