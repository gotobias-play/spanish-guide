<template>
  <div class="card p-4 mt-8">
    <div v-if="loading" class="text-center text-lg">Loading timed quiz...</div>
    <div v-else-if="error" class="text-center text-lg text-red-500">Error: {{ error }}</div>
    
    <template v-else-if="quiz">
      <!-- Quiz Header with Timer -->
      <div class="text-center mb-6">
        <h3 class="text-xl font-bold mb-2">{{ quiz.title }}</h3>
        
        <!-- Timer Display -->
        <div v-if="quiz.is_timed && quiz.show_timer && !quizCompleted" 
             class="timer-display mb-4 p-3 rounded-lg"
             :class="timerWarningClass">
          <div class="flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z"/>
            </svg>
            <span class="text-lg font-mono font-bold">{{ formattedTimeRemaining }}</span>
          </div>
          <div v-if="isQuestionTimer" class="text-sm mt-1">por pregunta</div>
          <div v-else class="text-sm mt-1">tiempo total restante</div>
        </div>
        
        <!-- Speed Bonus Indicator -->
        <div v-if="quiz.is_timed && speedBonusAvailable && !quizCompleted" 
             class="speed-bonus-indicator p-2 bg-yellow-100 border border-yellow-300 rounded-lg mb-4">
          <span class="text-yellow-700">⚡ ¡Responde rápido para puntos bonus!</span>
        </div>
      </div>
      
      <div v-if="!quizCompleted && currentQuestion">
        <div class="mb-4">
          <span class="text-sm text-gray-500">Question {{ currentQuestionIndex + 1 }} of {{ quiz.questions.length }}</span>
        </div>
        
        <p class="text-lg font-semibold mb-4">{{ currentQuestion.question_text }}</p>
        
        <!-- Multiple Choice Questions -->
        <div v-if="currentQuestion.question_type === 'multiple_choice'" class="flex flex-wrap gap-2 justify-center mb-4">
          <button 
            v-for="option in currentQuestion.options" 
            :key="option.id"
            @click="checkAnswer(option)"
            :disabled="answered || timeUp"
            class="interactive-btn"
            :class="{ 
              'bg-green-500': answered && option.is_correct,
              'bg-red-500': answered && selectedOption?.id === option.id && !option.is_correct,
              'opacity-50': (answered && !option.is_correct && selectedOption?.id !== option.id) || timeUp
            }"
          >
            {{ option.option_text }}
          </button>
        </div>
        
        <!-- Fill in the blank Questions -->
        <div v-else-if="currentQuestion.question_type === 'fill_in_the_blank'" class="mb-4">
          <input 
            v-model="fillInAnswer"
            @keyup.enter="checkFillInAnswer"
            :disabled="answered || timeUp"
            type="text" 
            class="w-full p-3 border rounded-lg text-center text-lg"
            placeholder="Type your answer here..."
          >
          <button 
            @click="checkFillInAnswer"
            :disabled="answered || !fillInAnswer.trim() || timeUp"
            class="interactive-btn mt-2 w-full"
          >
            Submit Answer
          </button>
        </div>
        
        <!-- Time Up Message -->
        <p v-if="timeUp && !answered" class="text-center text-lg text-red-600 font-semibold mb-4">
          ⏰ ¡Tiempo agotado! Se seleccionó automáticamente.
        </p>
        
        <p v-if="feedback" class="feedback text-center mt-4" :class="feedbackClass">
          {{ feedback }}
          <span v-if="speedBonus > 0" class="speed-bonus-text">⚡ +{{ speedBonus }} puntos bonus por velocidad!</span>
        </p>
        
        <div class="text-center mt-4" v-if="showNextButton">
          <button @click="nextQuestion" class="interactive-btn">
            {{ currentQuestionIndex < quiz.questions.length - 1 ? 'Next Question' : 'Finish Quiz' }}
          </button>
        </div>
      </div>
      
      <div v-else-if="quizCompleted" class="text-center">
        <p class="text-2xl font-bold text-green-700 mb-4">¡Quiz Completado!</p>
        <p class="text-lg mb-2">You got {{ correctAnswers }} out of {{ quiz.questions.length }} correct.</p>
        <p class="text-lg mb-2">Score: {{ Math.round((correctAnswers / quiz.questions.length) * 100) }}%</p>
        
        <!-- Time Stats -->
        <div v-if="quiz.is_timed" class="mb-4 p-4 bg-blue-50 rounded-lg">
          <h4 class="font-semibold text-blue-800 mb-2">⏱️ Estadísticas de Tiempo</h4>
          <p class="text-sm text-blue-700">Tiempo total usado: {{ formatTime(totalTimeUsed) }}</p>
          <p v-if="totalSpeedBonus > 0" class="text-sm text-yellow-700">⚡ Bonus por velocidad: +{{ totalSpeedBonus }} puntos</p>
          <p v-if="quiz.time_limit_seconds" class="text-sm text-blue-700">
            Tiempo límite: {{ formatTime(quiz.time_limit_seconds) }}
          </p>
        </div>
        
        <div class="mb-4">
          <h4 class="font-semibold mb-2">Review:</h4>
          <div v-for="(result, index) in questionResults" :key="index" class="text-left mb-2 p-2 rounded" 
               :class="result.correct ? 'bg-green-100' : 'bg-red-100'">
            <p class="font-medium">{{ result.question }}</p>
            <p class="text-sm">Your answer: {{ result.userAnswer }}</p>
            <p v-if="!result.correct" class="text-sm text-green-600">Correct answer: {{ result.correctAnswer }}</p>
            <p v-if="result.timeUsed !== undefined" class="text-xs text-gray-500">
              Time: {{ formatTime(result.timeUsed) }}
              <span v-if="result.speedBonus > 0" class="text-yellow-600">⚡ +{{ result.speedBonus }}</span>
            </p>
          </div>
        </div>
        
        <button @click="restartQuiz" class="interactive-btn mt-4">Restart Quiz</button>
      </div>
    </template>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'TimedQuiz',
  props: {
    quizId: {
      type: Number,
      required: true
    }
  },
  data() {
    return {
      quiz: null,
      loading: true,
      error: null,
      currentQuestionIndex: 0,
      correctAnswers: 0,
      feedback: '',
      feedbackClass: '',
      showNextButton: false,
      quizCompleted: false,
      answered: false,
      selectedOption: null,
      fillInAnswer: '',
      questionResults: [],
      
      // Timer-specific data
      quizStartTime: null,
      currentQuestionStartTime: null,
      timeRemaining: 0,
      questionTimeRemaining: 0,
      timerInterval: null,
      timeUp: false,
      totalTimeUsed: 0,
      speedBonus: 0,
      totalSpeedBonus: 0
    };
  },
  computed: {
    currentQuestion() {
      return this.quiz?.questions?.[this.currentQuestionIndex] || null;
    },
    
    isQuestionTimer() {
      return this.quiz?.time_per_question_seconds && !this.quiz?.time_limit_seconds;
    },
    
    formattedTimeRemaining() {
      const time = this.isQuestionTimer ? this.questionTimeRemaining : this.timeRemaining;
      return this.formatTime(time);
    },
    
    timerWarningClass() {
      const time = this.isQuestionTimer ? this.questionTimeRemaining : this.timeRemaining;
      const warningThreshold = this.getWarningThreshold();
      
      if (time <= 5) {
        return 'bg-red-100 border border-red-400 text-red-800 animate-pulse';
      } else if (time <= warningThreshold) {
        return 'bg-yellow-100 border border-yellow-400 text-yellow-800';
      } else {
        return 'bg-green-100 border border-green-400 text-green-800';
      }
    },
    
    speedBonusAvailable() {
      return this.quiz?.timer_settings?.speed_bonus_enabled === true;
    }
  },
  
  async created() {
    await this.loadQuiz();
  },
  
  beforeUnmount() {
    if (this.timerInterval) {
      clearInterval(this.timerInterval);
    }
  },
  
  watch: {
    quizId: {
      immediate: true,
      async handler(newId) {
        if (newId) {
          await this.loadQuiz();
        }
      }
    }
  },
  
  methods: {
    async loadQuiz() {
      try {
        this.loading = true;
        this.error = null;
        const response = await axios.get(`/api/public/quizzes/${this.quizId}`);
        this.quiz = response.data;
        
        // Shuffle questions for randomization
        if (this.quiz.questions) {
          this.quiz.questions = this.shuffleArray([...this.quiz.questions]);
          // Shuffle options for multiple choice questions
          this.quiz.questions.forEach(question => {
            if (question.question_type === 'multiple_choice' && question.options) {
              question.options = this.shuffleArray([...question.options]);
            }
          });
        }
        
        this.resetQuiz();
        this.initializeTimer();
        
      } catch (err) {
        this.error = err.message || 'Failed to load quiz';
        console.error('Error loading quiz:', err);
      } finally {
        this.loading = false;
      }
    },
    
    initializeTimer() {
      if (!this.quiz?.is_timed) return;
      
      this.quizStartTime = Date.now();
      
      // Set up quiz-wide timer
      if (this.quiz.time_limit_seconds) {
        this.timeRemaining = this.quiz.time_limit_seconds;
      }
      
      // Set up question timer
      if (this.quiz.time_per_question_seconds) {
        this.questionTimeRemaining = this.quiz.time_per_question_seconds;
        this.currentQuestionStartTime = Date.now();
      }
      
      // Start timer interval
      this.timerInterval = setInterval(() => {
        this.updateTimer();
      }, 1000);
    },
    
    updateTimer() {
      if (this.quizCompleted) {
        clearInterval(this.timerInterval);
        return;
      }
      
      const now = Date.now();
      
      // Update quiz-wide timer
      if (this.quiz.time_limit_seconds) {
        const elapsed = Math.floor((now - this.quizStartTime) / 1000);
        this.timeRemaining = Math.max(0, this.quiz.time_limit_seconds - elapsed);
        
        if (this.timeRemaining <= 0) {
          this.handleTimeUp();
        }
      }
      
      // Update question timer
      if (this.quiz.time_per_question_seconds && this.currentQuestionStartTime) {
        const questionElapsed = Math.floor((now - this.currentQuestionStartTime) / 1000);
        this.questionTimeRemaining = Math.max(0, this.quiz.time_per_question_seconds - questionElapsed);
        
        if (this.questionTimeRemaining <= 0 && !this.answered) {
          this.handleTimeUp();
        }
      }
    },
    
    handleTimeUp() {
      if (this.answered) return;
      
      this.timeUp = true;
      
      if (this.quiz.timer_settings?.auto_submit) {
        // Auto-submit with no answer or random answer
        if (this.currentQuestion.question_type === 'multiple_choice') {
          // Select random option for auto-submit
          const randomOption = this.currentQuestion.options[Math.floor(Math.random() * this.currentQuestion.options.length)];
          this.checkAnswer(randomOption, true);
        } else {
          this.checkFillInAnswer(true);
        }
      }
    },
    
    getWarningThreshold() {
      if (this.quiz?.timer_settings?.warning_at_seconds) {
        return this.quiz.timer_settings.warning_at_seconds;
      }
      
      // Default warning thresholds
      if (this.isQuestionTimer) {
        return Math.max(5, Math.floor(this.quiz.time_per_question_seconds * 0.3));
      } else {
        return Math.max(30, Math.floor(this.quiz.time_limit_seconds * 0.2));
      }
    },
    
    calculateSpeedBonus(timeUsed) {
      if (!this.speedBonusAvailable) return 0;
      
      let timeLimit;
      if (this.quiz.time_per_question_seconds) {
        timeLimit = this.quiz.time_per_question_seconds;
      } else if (this.quiz.time_limit_seconds) {
        timeLimit = Math.floor(this.quiz.time_limit_seconds / this.quiz.questions.length);
      } else {
        return 0;
      }
      
      // Award bonus for answering in first 50% of time
      const halfTime = Math.floor(timeLimit / 2);
      if (timeUsed <= halfTime) {
        return Math.max(1, Math.floor((halfTime - timeUsed) / 2));
      }
      
      return 0;
    },
    
    shuffleArray(array) {
      const shuffled = [...array];
      for (let i = shuffled.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
      }
      return shuffled;
    },
    
    checkAnswer(option, autoSubmit = false) {
      if (this.answered) return;
      
      this.answered = true;
      this.selectedOption = option;
      this.timeUp = autoSubmit;
      
      // Calculate time used for this question
      const questionTimeUsed = this.currentQuestionStartTime ? 
        Math.floor((Date.now() - this.currentQuestionStartTime) / 1000) : 0;
      
      // Calculate speed bonus
      this.speedBonus = this.calculateSpeedBonus(questionTimeUsed);
      this.totalSpeedBonus += this.speedBonus;
      
      if (option.is_correct && !autoSubmit) {
        this.feedback = '¡Correcto!';
        this.feedbackClass = 'correct';
        this.correctAnswers++;
        this.questionResults.push({
          question: this.currentQuestion.question_text,
          userAnswer: option.option_text,
          correctAnswer: option.option_text,
          correct: true,
          timeUsed: questionTimeUsed,
          speedBonus: this.speedBonus
        });
      } else {
        const correctOption = this.currentQuestion.options.find(opt => opt.is_correct);
        this.feedback = autoSubmit ? 
          `Tiempo agotado. La respuesta correcta es "${correctOption.option_text}".` :
          `Incorrecto. La respuesta correcta es "${correctOption.option_text}".`;
        this.feedbackClass = 'incorrect';
        this.questionResults.push({
          question: this.currentQuestion.question_text,
          userAnswer: autoSubmit ? '(sin respuesta)' : option.option_text,
          correctAnswer: correctOption.option_text,
          correct: false,
          timeUsed: questionTimeUsed,
          speedBonus: 0
        });
      }
      
      this.showNextButton = true;
    },
    
    checkFillInAnswer(autoSubmit = false) {
      if (this.answered || (!this.fillInAnswer.trim() && !autoSubmit)) return;
      
      this.answered = true;
      this.timeUp = autoSubmit;
      
      const questionTimeUsed = this.currentQuestionStartTime ? 
        Math.floor((Date.now() - this.currentQuestionStartTime) / 1000) : 0;
      
      const correctOption = this.currentQuestion.options.find(opt => opt.is_correct);
      const userAnswer = autoSubmit ? '' : this.fillInAnswer.trim();
      const correctAnswer = correctOption.option_text.toLowerCase();
      
      if (!autoSubmit && userAnswer.toLowerCase() === correctAnswer) {
        this.speedBonus = this.calculateSpeedBonus(questionTimeUsed);
        this.totalSpeedBonus += this.speedBonus;
        
        this.feedback = '¡Correcto!';
        this.feedbackClass = 'correct';
        this.correctAnswers++;
        this.questionResults.push({
          question: this.currentQuestion.question_text,
          userAnswer: this.fillInAnswer.trim(),
          correctAnswer: correctOption.option_text,
          correct: true,
          timeUsed: questionTimeUsed,
          speedBonus: this.speedBonus
        });
      } else {
        this.speedBonus = 0;
        this.feedback = autoSubmit ? 
          `Tiempo agotado. La respuesta correcta es "${correctOption.option_text}".` :
          `Incorrecto. La respuesta correcta es "${correctOption.option_text}".`;
        this.feedbackClass = 'incorrect';
        this.questionResults.push({
          question: this.currentQuestion.question_text,
          userAnswer: autoSubmit ? '(sin respuesta)' : this.fillInAnswer.trim(),
          correctAnswer: correctOption.option_text,
          correct: false,
          timeUsed: questionTimeUsed,
          speedBonus: 0
        });
      }
      
      this.showNextButton = true;
    },
    
    nextQuestion() {
      if (this.currentQuestionIndex < this.quiz.questions.length - 1) {
        this.currentQuestionIndex++;
        this.resetQuestionState();
        
        // Reset question timer
        if (this.quiz.time_per_question_seconds) {
          this.questionTimeRemaining = this.quiz.time_per_question_seconds;
          this.currentQuestionStartTime = Date.now();
        }
      } else {
        this.completeQuiz();
      }
    },
    
    completeQuiz() {
      this.quizCompleted = true;
      this.totalTimeUsed = this.quizStartTime ? Math.floor((Date.now() - this.quizStartTime) / 1000) : 0;
      
      if (this.timerInterval) {
        clearInterval(this.timerInterval);
      }
      
      const score = Math.round((this.correctAnswers / this.quiz.questions.length) * 100);
      
      // Emit quiz completion event with results
      this.$emit('quiz-completed', score, {
        quizId: this.quizId,
        quizTitle: this.quiz.title,
        totalQuestions: this.quiz.questions.length,
        correctAnswers: this.correctAnswers,
        score: score,
        results: this.questionResults,
        isTimed: this.quiz.is_timed,
        totalTimeUsed: this.totalTimeUsed,
        speedBonus: this.totalSpeedBonus
      });
    },
    
    resetQuestionState() {
      this.answered = false;
      this.selectedOption = null;
      this.fillInAnswer = '';
      this.feedback = '';
      this.feedbackClass = '';
      this.showNextButton = false;
      this.timeUp = false;
      this.speedBonus = 0;
    },
    
    resetQuiz() {
      this.currentQuestionIndex = 0;
      this.correctAnswers = 0;
      this.quizCompleted = false;
      this.questionResults = [];
      this.totalSpeedBonus = 0;
      this.totalTimeUsed = 0;
      this.resetQuestionState();
      
      if (this.timerInterval) {
        clearInterval(this.timerInterval);
      }
    },
    
    restartQuiz() {
      // Reshuffle questions and options
      if (this.quiz.questions) {
        this.quiz.questions = this.shuffleArray([...this.quiz.questions]);
        this.quiz.questions.forEach(question => {
          if (question.question_type === 'multiple_choice' && question.options) {
            question.options = this.shuffleArray([...question.options]);
          }
        });
      }
      this.resetQuiz();
      this.initializeTimer();
    },
    
    formatTime(seconds) {
      const mins = Math.floor(seconds / 60);
      const secs = seconds % 60;
      return `${mins}:${secs.toString().padStart(2, '0')}`;
    }
  }
};
</script>

<style scoped>
.timer-display {
  transition: all 0.3s ease;
}

.speed-bonus-indicator {
  animation: pulse 2s infinite;
}

.speed-bonus-text {
  display: block;
  color: #f59e0b;
  font-weight: bold;
  margin-top: 0.25rem;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.02); }
}
</style>