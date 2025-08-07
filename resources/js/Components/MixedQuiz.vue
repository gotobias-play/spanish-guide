<template>
  <div class="card p-4 mt-8">
    <h3 class="text-xl font-bold text-center mb-4">Mixed Questions Quiz</h3>
    <div v-if="!quizCompleted">
      <p class="text-lg font-semibold mb-4">{{ currentQuestion.q }}</p>
      <div class="flex flex-wrap gap-2 justify-center">
        <button v-for="option in currentQuestion.o" :key="option" @click="checkAnswer(option)" class="interactive-btn">{{ option }}</button>
      </div>
      <p class="feedback text-center mt-4" :class="feedbackClass">{{ feedback }}</p>
      <div class="text-center mt-4" v-if="showNextButton">
        <button @click="nextQuestion" class="interactive-btn">Next Question</button>
      </div>
    </div>
    <div v-else class="text-center">
      <p class="text-2xl font-bold text-green-700 mb-4">Quiz Completed!</p>
      <p class="text-lg">You got {{ correctAnswers }} out of {{ questions.length }} correct.</p>
      <button @click="restartQuiz" class="interactive-btn mt-4">Restart Quiz</button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MixedQuiz',
  data() {
    return {
      questions: [
        { q: "I ___ a student.", o: ["am", "is", "are"], a: "am" },
        { q: "She ___ a car.", o: ["have", "has"], a: "has" },
        { q: "Do you have ___ money?", o: ["some", "any"], a: "any" },
        { q: "He ___ work.", o: ["don't", "doesn't"], a: "doesn't" },
        { q: "How ___ time do you have?", o: ["much", "many"], a: "much" },
      ],
      currentQuestionIndex: 0,
      correctAnswers: 0,
      feedback: '',
      feedbackClass: '',
      showNextButton: false,
      quizCompleted: false,
    };
  },
  computed: {
    currentQuestion() {
      return this.questions[this.currentQuestionIndex];
    },
  },
  methods: {
    checkAnswer(selected) {
      if (selected === this.currentQuestion.a) {
        this.feedback = 'Correct!';
        this.feedbackClass = 'correct';
        this.correctAnswers++;
      } else {
        this.feedback = `Incorrect. The answer was ${this.currentQuestion.a}.`;
        this.feedbackClass = 'incorrect';
      }
      this.showNextButton = true;
    },
    nextQuestion() {
      if (this.currentQuestionIndex < this.questions.length - 1) {
        this.currentQuestionIndex++;
        this.feedback = '';
        this.feedbackClass = '';
        this.showNextButton = false;
      } else {
        this.quizCompleted = true;
        this.$emit('quiz-completed', this.correctAnswers, {
          section: 'mixed-quiz',
          totalQuestions: this.questions.length,
          correctAnswers: this.correctAnswers,
        });
      }
    },
    restartQuiz() {
      this.currentQuestionIndex = 0;
      this.correctAnswers = 0;
      this.feedback = '';
      this.feedbackClass = '';
      this.showNextButton = false;
      this.quizCompleted = false;
    },
  },
};
</script>
