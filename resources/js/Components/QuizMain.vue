<template>
  <div class="container mx-auto p-4 md:p-8">
    <h2 class="text-3xl font-bold text-center mb-6">Cuestionarios de Inglés</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div class="card interactive-card p-4" @click="selectQuiz('restaurant')">
        <h3 class="text-xl font-semibold text-blue-700">Cuantificadores del Restaurante</h3>
        <p class="text-gray-600">Pon a prueba tus conocimientos sobre cuantificadores y vocabulario de comida.</p>
      </div>
      <div class="card interactive-card p-4" @click="selectQuiz('mixed')">
        <h3 class="text-xl font-semibold text-blue-700">Preguntas Variadas</h3>
        <p class="text-gray-600">Un cuestionario desafiante con preguntas de varias lecciones.</p>
      </div>
    </div>

    <div v-if="selectedQuizComponent" class="mt-8">
      <component :is="selectedQuizComponent" @quiz-completed="handleQuizCompletion" />
    </div>
  </div>
</template>

<script>
import RestaurantQuiz from './Restaurant.vue'; // We will refactor Restaurant.vue to be a quiz component
import axios from 'axios';
import MixedQuiz from './MixedQuiz.vue'; // This will be a new component

export default {
  name: 'QuizMain',
  components: {
    RestaurantQuiz,
    MixedQuiz,
  },
  data() {
    return {
      selectedQuiz: null,
    };
  },
  computed: {
    selectedQuizComponent() {
      if (this.selectedQuiz === 'restaurant') {
        return RestaurantQuiz;
      } else if (this.selectedQuiz === 'mixed') {
        return MixedQuiz;
      }
      return null;
    },
  },
  methods: {
    selectQuiz(quizType) {
      this.selectedQuiz = quizType;
    },
    handleQuizCompletion(score, data) {
      // This method will be called by the quiz components when they are completed
      // You can then save the progress here if needed
      console.log(`Quiz completed! Score: ${score}, Data:`, data);
      try {
        const sectionId = data.section;
        const payload = {
          section_id: sectionId,
          score: score,
          data: JSON.stringify(data),
        };
        axios.post('/api/progress', payload);
        alert('Progress saved successfully!');
      } catch (error) {
        console.error('Error saving progress:', error);
        alert('Failed to save progress.');
      }
    },
  },
};
</script>
