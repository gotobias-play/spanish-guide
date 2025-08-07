<template>
  <div>
    <div v-for="(detail, index) in details" :key="index" class="mb-4 p-4 rounded-lg" :class="{'bg-green-100': detail.isCorrect, 'bg-red-100': !detail.isCorrect}">
      <p class="font-semibold">{{ detail.question }}</p>
      <p>Your answer: {{ detail.userAnswer }}</p>
      <p v-if="!detail.isCorrect">Correct answer: {{ detail.correctAnswer }}</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'QuizDetail',
  props: {
    quizData: {
      type: Object,
      required: true,
    },
  },
  computed: {
    details() {
      if (!this.quizData) {
        return [];
      }
      return Object.keys(this.quizData).map(key => {
        const item = this.quizData[key];
        if (item && typeof item === 'object' && 'question' in item) {
          return {
            question: item.question,
            userAnswer: item.userAnswer,
            correctAnswer: item.correctAnswer,
            isCorrect: item.isCorrect,
          };
        }
        return null;
      }).filter(Boolean);
    }
  }
};
</script>
