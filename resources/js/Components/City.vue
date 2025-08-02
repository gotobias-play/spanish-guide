<template>
  <div>
    <h2 class="text-3xl font-bold text-center mb-6">Mi Ciudad: Describiendo Lugares</h2>
    <p class="text-center text-lg mb-8">Usa 'There is' / 'There are' y las preposiciones para describir lo que hay en tu ciudad.</p>
    <div class="grid lg:grid-cols-2 gap-8 items-start">
      <div class="card p-6 bg-green-50">
        <h3 class="text-xl font-bold mb-4 text-center">Mapa Interactivo de la Ciudad</h3>
        <div class="relative w-full h-80 bg-green-100 rounded-lg p-4 grid grid-cols-3 grid-rows-3 gap-2">
          <div v-for="(loc, id) in locations" :key="id" @click="selectLocation(id)" :class="[loc.style, { 'ring-4 ring-blue-500': selectedLocation && selectedLocation.name === loc.name }]" class="map-loc rounded flex items-center justify-center text-white font-bold cursor-pointer transition-all duration-300">{{ loc.name }}</div>
        </div>
        <p class="text-center mt-2 text-sm text-gray-600">Haz clic en un lugar para ver su descripción.</p>
      </div>
      <div class="flex flex-col gap-6">
        <div class="card">
          <h3 class="text-xl font-bold mb-2 text-center">Descripción de Preposición</h3>
          <div class="text-center text-lg font-semibold h-24 flex flex-col items-center justify-center" ref="descriptionDisplay">
            <p v-if="selectedLocation">{{ selectedLocation.desc }}</p>
            <p v-if="selectedLocation" class="pronunciation">{{ selectedLocation.p_desc }}</p>
            <p v-if="selectedLocation" class="translation">{{ selectedLocation.es_desc }}</p>
            <p v-else>Selecciona un lugar en el mapa.</p>
          </div>
        </div>
        <div class="card">
          <h3 class="text-xl font-bold mb-4 text-center">Práctica de Preguntas</h3>
          <div class="mb-2">
            <p class="text-center text-lg font-semibold">{{ isAreQuiz.question }}</p>
            <p class="pronunciation text-center">{{ isAreQuiz.pronunciation }}</p>
          </div>
          <div class="flex justify-center gap-4">
            <button @click="checkIsAreAnswer('Is there')" class="interactive-btn">Is there</button>
            <button @click="checkIsAreAnswer('Are there')" class="interactive-btn">Are there</button>
          </div>
          <p class="feedback text-center mt-2" :class="isAreQuiz.feedbackClass">{{ isAreQuiz.feedback }}</p>
        </div>
      </div>
    </div>
    <div class="card mt-8">
      <h3 class="text-xl font-bold mb-4 text-center">Más Preposiciones de Lugar (¡Haz clic!)</h3>
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 text-center">
        <div v-for="prep in prepositions" :key="prep.en" @click="openModal(prep)" class="bg-gray-100 p-2 rounded-lg interactive-card">
          <p class="font-bold">{{ prep.en }}</p>
          <p class="text-sm text-gray-600">{{ prep.es }}</p>
        </div>
      </div>
    </div>
    <Modal v-if="showModal" :title="modalTitle" @close="showModal = false">
      <div v-html="modalContent"></div>
    </Modal>
  </div>
</template>

<script>
import { gsap } from 'gsap';
import Modal from './Modal.vue';

export default {
  name: 'City',
  components: { Modal },
  data() {
    return {
      locations: {
        'loc-park': { name: 'Park', desc: "The park is across from the school.", es_desc: "El parque está enfrente de la escuela.", p_desc: "/da párk is a-krós from da skúul/", type: "singular", p_quiz: "/is dér a párk nir-bái/", style: 'bg-green-400 col-span-2' },
        'loc-bank': { name: 'Bank', desc: "The bank is next to the cafe.", es_desc: "El banco está al lado del café.", p_desc: "/da bánk is nékst tu da ka-féi/", type: "plural", p_quiz: "/ar dér bánks nir-bái/", style: 'bg-gray-400' },
        'loc-cafe': { name: 'Cafe', desc: "The cafe is between the bank and the supermarket.", es_desc: "El café está entre el banco y el supermercado.", p_desc: "/da ka-féi is bi-twíin da bánk and da sú-per-mar-ket/", type: "singular", p_quiz: "/is dér a ka-féi nir-bái/", style: 'bg-yellow-600' },
        'loc-supermarket': { name: 'Supermarket', desc: "The supermarket is next to the cafe.", es_desc: "El supermercado está al lado del café.", p_desc: "/da sú-per-mar-ket is nékst tu da ka-féi/", type: "singular", p_quiz: "/is dér a sú-per-mar-ket nir-bái/", style: 'bg-blue-400 col-span-2' },
        'loc-school': { name: 'School', desc: "The school is across from the park.", es_desc: "La escuela está enfrente del parque.", p_desc: "/da skúul is a-krós from da párk/", type: "singular", p_quiz: "/is dér a skúul nir-bái/", style: 'bg-red-400 row-start-3 col-start-2 col-span-2' },
      },
      prepositions: [
          { en: 'in', es: 'en, dentro de', ex: [ { en: 'The keys are in the box.', es: 'Las llaves están en la caja.', p: '/da kís ar in da boks/' } ] },
          { en: 'on', es: 'sobre, en', ex: [ { en: 'The book is on the table.', es: 'El libro está sobre la mesa.', p: '/da buk is on da téi-bl/' } ] },
          { en: 'at', es: 'en (lugar específico)', ex: [ { en: 'I am at the bus stop.', es: 'Estoy en la parada del bus.', p: '/ai am at da bas stop/' } ] },
          { en: 'under', es: 'debajo de', ex: [ { en: 'The cat is under the chair.', es: 'El gato está debajo de la silla.', p: '/da kat is an-der da cher/' } ] },
          { en: 'next to', es: 'al lado de', ex: [ { en: 'The park is next to the bank.', es: 'El parque está al lado del banco.', p: '/da párk is nékst tu da bánk/' } ] },
          { en: 'between', es: 'entre', ex: [ { en: 'The bank is between the cafe and the supermarket.', es: 'El banco está entre el café y el supermercado.', p: '/da bánk is bi-twíin da ka-féi and da sú-per-mar-ket/' } ] },
          { en: 'near', es: 'cerca de', ex: [ { en: 'The store is near my house.', es: 'La tienda está cerca de mi casa.', p: '/da stor is nir mai haus/' } ] },
          { en: 'behind', es: 'detrás de', ex: [ { en: 'The car is behind the tree.', es: 'El carro está detrás del árbol.', p: '/da kar is bi-háind da tri/' } ] },
          { en: 'in front of', es: 'delante de', ex: [ { en: 'The dog is in front of the door.', es: 'El perro está delante de la puerta.', p: '/da dog is in front ov da dor/' } ] },
          { en: 'across from', es: 'enfrente de', ex: [ { en: 'The school is across from the park.', es: 'La escuela está enfrente del parque.', p: '/da skúul is a-krós from da párk/' } ] }
      ],
      selectedLocation: null,
      isAreQuiz: {
        question: '',
        pronunciation: '',
        correctAnswer: '',
        feedback: '',
        feedbackClass: '',
      },
      showModal: false,
      modalTitle: '',
      modalContent: '',
    };
  },
  methods: {
    selectLocation(id) {
      this.selectedLocation = this.locations[id];
      this.setupIsAreQuiz(this.selectedLocation);
      gsap.from(this.$refs.descriptionDisplay, { duration: 0.5, opacity: 0, y: -10 });
    },
    setupIsAreQuiz(locData) {
      const itemType = locData.type;
      const itemName = itemType === 'singular' ? `a ${locData.name}` : `${locData.name}s`;
      this.isAreQuiz.question = `______ ${itemName} nearby?`;
      this.isAreQuiz.pronunciation = locData.p_quiz;
      this.isAreQuiz.correctAnswer = itemType === 'singular' ? 'Is there' : 'Are there';
      this.isAreQuiz.feedback = '';
      this.isAreQuiz.feedbackClass = '';
    },
    checkIsAreAnswer(answer) {
      if (answer === this.isAreQuiz.correctAnswer) {
        this.isAreQuiz.feedback = '¡Correcto!';
        this.isAreQuiz.feedbackClass = 'correct';
      } else {
        this.isAreQuiz.feedback = 'Inténtalo de nuevo.';
        this.isAreQuiz.feedbackClass = 'incorrect';
      }
    },
    openModal(prep) {
      this.modalTitle = `Ejemplos de: "${prep.en}"`;
      let contentHTML = '';
      prep.ex.forEach(example => {
        contentHTML += `
          <div class="example">
            <p class="font-semibold">${example.en}</p>
            <p class="pronunciation">${example.p}</p>
            <p class="translation">${example.es}</p>
          </div>
        `;
      });
      this.modalContent = contentHTML;
      this.showModal = true;
    },
  },
  mounted() {
    gsap.from(this.$el, { duration: 0.5, opacity: 0, y: 20 });
    this.selectLocation('loc-park');
  },
};
</script>