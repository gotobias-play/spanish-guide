<template>
  <div>
    <h2 class="text-3xl font-bold text-center mb-6">Fundamentos: Los Verbos Clave</h2>
    <p class="text-center text-lg mb-8">Los verbos 'To Be' (Ser/Estar) y 'To Have' (Tener) son la base de casi todo en inglés. ¡Vamos a dominarlos!</p>
    <div class="grid md:grid-cols-2 gap-8">
      <div class="card">
        <h3 class="text-2xl font-bold text-[#4A55A2] mb-4 text-center">Verbo 'To Be' (Ser / Estar)</h3>
        <div class="flex flex-wrap gap-2 mb-4 justify-center">
          <button v-for="item in toBe" :key="item.pronoun" @click="selectToBe(item)" :class="{ 'selected': selectedToBe && selectedToBe.pronoun === item.pronoun }" class="interactive-btn">{{ item.pronoun }}</button>
        </div>
        <div class="bg-blue-50 p-4 rounded-lg text-center h-28 flex flex-col items-center justify-center">
          <p v-if="selectedToBe" class="text-lg font-semibold">{{ selectedToBe.example }}</p>
          <p v-if="selectedToBe" class="translation">{{ selectedToBe.es }}</p>
          <p v-else class="text-lg font-semibold">Selecciona un pronombre.</p>
        </div>
      </div>
      <div class="card">
        <h3 class="text-2xl font-bold text-[#4A55A2] mb-4 text-center">Verbo 'To Have' (Tener)</h3>
        <div class="flex flex-wrap gap-2 mb-4 justify-center">
          <button v-for="item in toHave" :key="item.pronoun" @click="selectToHave(item)" :class="{ 'selected': selectedToHave && selectedToHave.pronoun === item.pronoun }" class="interactive-btn">{{ item.pronoun }}</button>
        </div>
        <div class="bg-blue-50 p-4 rounded-lg text-center h-28 flex flex-col items-center justify-center">
          <p v-if="selectedToHave" class="text-lg font-semibold">{{ selectedToHave.example }}</p>
          <p v-if="selectedToHave" class="translation">{{ selectedToHave.es }}</p>
          <p v-else class="text-lg font-semibold">Selecciona un pronombre.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { gsap } from 'gsap';

export default {
  name: 'Foundations',
  data() {
    return {
      toBe: [
        { pronoun: 'I', verb: 'am', example: 'I am a student.', es: 'Yo soy un estudiante.' },
        { pronoun: 'You', verb: 'are', example: 'You are happy.', es: 'Tú estás feliz.' },
        { pronoun: 'He', verb: 'is', example: 'He is a doctor.', es: 'Él es un doctor.' },
        { pronoun: 'She', verb: 'is', example: 'She is tired.', es: 'Ella está cansada.' },
        { pronoun: 'It', verb: 'is', example: 'It is late.', es: 'Es tarde.' },
        { pronoun: 'We', verb: 'are', example: 'We are in the house.', es: 'Nosotros estamos en la casa.' },
        { pronoun: 'They', verb: 'are', example: 'They are friends.', es: 'Ellos son amigos.' }
      ],
      toHave: [
        { pronoun: 'I', verb: 'have', example: 'I have a car.', es: 'Yo tengo un carro.' },
        { pronoun: 'You', verb: 'have', example: 'You have a question.', es: 'Tú tienes una pregunta.' },
        { pronoun: 'He', verb: 'has', example: 'He has a dog.', es: 'Él tiene un perro.' },
        { pronoun: 'She', verb: 'has', example: 'She has a bike.', es: 'Ella tiene una bicicleta.' },
        { pronoun: 'It', verb: 'has', example: 'It has a problem.', es: 'Eso tiene un problema.' },
        { pronoun: 'We', verb: 'have', example: 'We have a big family.', es: 'Nosotros tenemos una familia grande.' },
        { pronoun: 'They', verb: 'have', example: 'They have brown eyes.', es: 'Ellos tienen ojos cafés.' }
      ],
      selectedToBe: null,
      selectedToHave: null,
    };
  },
  methods: {
    selectToBe(item) {
      this.selectedToBe = item;
    },
    selectToHave(item) {
      this.selectedToHave = item;
    },
  },
  mounted() {
    gsap.from(this.$el, { duration: 0.5, opacity: 0, y: 20 });
  },
};
</script>