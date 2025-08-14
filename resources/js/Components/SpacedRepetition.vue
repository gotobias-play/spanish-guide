<template>
  <div class="spaced-repetition min-h-screen bg-gradient-to-br from-purple-50 to-pink-100 p-4">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">🧠 Repaso Espaciado</h1>
        <p class="text-gray-600">Sistema inteligente de repaso basado en tu progreso individual</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-purple-600 mx-auto mb-4"></div>
        <p class="text-gray-600">Analizando tu horario de repaso...</p>
      </div>

      <!-- Main Content -->
      <div v-else-if="!inSession" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Schedule Overview -->
        <div class="bg-white rounded-xl shadow-lg p-6">
          <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
            📅 Horario de Repaso
            <span v-if="schedule.due_count > 0" class="ml-2 bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
              {{ schedule.due_count }} pendientes
            </span>
          </h2>

          <div v-if="schedule.schedule.length === 0" class="text-center py-8">
            <div class="text-6xl mb-4">🎉</div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">¡Todo al día!</h3>
            <p class="text-gray-500">No tienes repasos pendientes. ¡Excelente trabajo manteniendo tu conocimiento fresco!</p>
            <button @click="loadSchedule" class="mt-4 bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
              🔄 Actualizar Horario
            </button>
          </div>

          <div v-else class="space-y-4">
            <div v-for="item in schedule.schedule.slice(0, 8)" 
                 :key="item.skill.id"
                 class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200"
                 :class="getUrgencyCardClass(item.urgency)">
              <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-gray-800">{{ formatSkillTopic(item.skill.skill_topic) }}</h3>
                <span class="text-xs px-2 py-1 rounded-full text-white"
                      :class="getDifficultyColor(item.skill.difficulty_level)">
                  {{ item.skill.difficulty_level }}
                </span>
              </div>

              <div class="grid grid-cols-2 gap-4 mb-3 text-sm text-gray-600">
                <div>
                  <span class="font-medium">📊 Dominio:</span>
                  <span class="ml-1">{{ Math.round(item.skill.mastery_score) }}%</span>
                </div>
                <div>
                  <span class="font-medium">📈 Precisión:</span>
                  <span class="ml-1">{{ Math.round(item.skill.accuracy_rate) }}%</span>
                </div>
                <div>
                  <span class="font-medium">⏰ Vence:</span>
                  <span class="ml-1">{{ formatRelativeDate(item.due_date) }}</span>
                </div>
                <div>
                  <span class="font-medium">🔥 Urgencia:</span>
                  <span class="ml-1" :class="getUrgencyTextClass(item.urgency)">{{ item.urgency }}/100</span>
                </div>
              </div>

              <button @click="startRepetitionSession([item])"
                      class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                🎯 Practicar Ahora
              </button>
            </div>

            <!-- Bulk Actions -->
            <div v-if="schedule.schedule.length > 0" class="mt-6 pt-4 border-t">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <button @click="startRepetitionSession(getUrgentItems())"
                        v-if="schedule.urgent_count > 0"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                  🚨 Practicar Urgentes ({{ schedule.urgent_count }})
                </button>
                <button @click="startRepetitionSession(schedule.schedule.slice(0, 5))"
                        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                  📚 Sesión Completa ({{ Math.min(5, schedule.schedule.length) }})
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Statistics & Insights -->
        <div class="space-y-6">
          <!-- Performance Stats -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">📈 Estadísticas de Repaso</h2>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
              <div class="text-center p-4 bg-gradient-to-r from-green-400 to-blue-500 rounded-lg text-white">
                <div class="text-2xl font-bold">{{ schedule.due_count }}</div>
                <div class="text-xs opacity-90">Pendientes</div>
              </div>
              <div class="text-center p-4 bg-gradient-to-r from-purple-400 to-pink-500 rounded-lg text-white">
                <div class="text-2xl font-bold">{{ schedule.urgent_count }}</div>
                <div class="text-xs opacity-90">Urgentes</div>
              </div>
            </div>

            <div v-if="sessionStats.totalSessions > 0" class="space-y-3">
              <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                <span class="text-gray-600">Sesiones Completadas:</span>
                <span class="font-semibold text-gray-800">{{ sessionStats.totalSessions }}</span>
              </div>
              <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                <span class="text-gray-600">Precisión Promedio:</span>
                <span class="font-semibold text-gray-800">{{ Math.round(sessionStats.avgAccuracy) }}%</span>
              </div>
              <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                <span class="text-gray-600">Racha Actual:</span>
                <span class="font-semibold text-gray-800">{{ sessionStats.currentStreak }} días</span>
              </div>
            </div>
          </div>

          <!-- Study Tips -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4">💡 Consejos de Estudio</h2>
            
            <div class="space-y-4">
              <div class="p-4 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                <h3 class="font-semibold text-blue-800 mb-1">Repaso Espaciado</h3>
                <p class="text-sm text-blue-700">El sistema programa repasos basados en tu rendimiento. Las habilidades difíciles aparecen más frecuentemente.</p>
              </div>
              
              <div class="p-4 bg-green-50 rounded-lg border-l-4 border-green-500">
                <h3 class="font-semibold text-green-800 mb-1">Consistencia</h3>
                <p class="text-sm text-green-700">Sesiones cortas y regulares son más efectivas que sesiones largas esporádicas.</p>
              </div>
              
              <div class="p-4 bg-purple-50 rounded-lg border-l-4 border-purple-500">
                <h3 class="font-semibold text-purple-800 mb-1">Urgencia</h3>
                <p class="text-sm text-purple-700">Los elementos urgentes necesitan atención inmediata para evitar el olvido.</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Spaced Repetition Session -->
      <div v-else class="bg-white rounded-xl shadow-lg p-6">
        <div class="max-w-3xl mx-auto">
          <!-- Session Header -->
          <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">🧠 Sesión de Repaso Espaciado</h2>
            <div class="flex justify-center items-center space-x-4 text-sm text-gray-600">
              <span>Pregunta {{ currentQuestionIndex + 1 }} de {{ sessionQuestions.length }}</span>
              <span>•</span>
              <span>Habilidad: {{ formatSkillTopic(currentQuestion.skill_topic) }}</span>
            </div>
            
            <!-- Progress Bar -->
            <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
              <div class="bg-purple-600 h-2 rounded-full transition-all duration-300"
                   :style="{ width: ((currentQuestionIndex) / sessionQuestions.length) * 100 + '%' }"></div>
            </div>
          </div>

          <!-- Question Card -->
          <div class="border border-gray-200 rounded-lg p-6 mb-6">
            <div class="text-center mb-6">
              <h3 class="text-xl font-semibold text-gray-800 mb-2">{{ currentQuestion.question }}</h3>
              <p v-if="currentQuestion.context" class="text-gray-600 text-sm">{{ currentQuestion.context }}</p>
            </div>

            <!-- Answer Input -->
            <div class="max-w-md mx-auto mb-6">
              <input v-model="currentAnswer" 
                     @keyup.enter="submitAnswer"
                     type="text" 
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg text-center text-lg font-medium focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                     :placeholder="currentQuestion.placeholder || 'Escribe tu respuesta...'"
                     :disabled="showingResult">

              <div v-if="showingResult" class="mt-4">
                <div v-if="lastAnswerCorrect" class="p-4 bg-green-50 rounded-lg border border-green-200">
                  <div class="text-center">
                    <div class="text-2xl mb-2">✅</div>
                    <p class="font-semibold text-green-800">¡Correcto!</p>
                    <p v-if="currentQuestion.explanation" class="text-sm text-green-700 mt-2">{{ currentQuestion.explanation }}</p>
                  </div>
                </div>
                <div v-else class="p-4 bg-red-50 rounded-lg border border-red-200">
                  <div class="text-center">
                    <div class="text-2xl mb-2">❌</div>
                    <p class="font-semibold text-red-800">Incorrecto</p>
                    <p class="text-red-700 mt-1">Respuesta correcta: <strong>{{ currentQuestion.correct_answer }}</strong></p>
                    <p v-if="currentQuestion.explanation" class="text-sm text-red-600 mt-2">{{ currentQuestion.explanation }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-center space-x-4">
              <button v-if="!showingResult" 
                      @click="submitAnswer"
                      :disabled="!currentAnswer.trim()"
                      class="bg-purple-600 hover:bg-purple-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200">
                Enviar Respuesta
              </button>
              <button v-else 
                      @click="nextQuestion"
                      class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200">
                {{ currentQuestionIndex < sessionQuestions.length - 1 ? 'Siguiente' : 'Terminar Sesión' }}
              </button>
            </div>
          </div>

          <!-- Session Progress -->
          <div class="text-center text-sm text-gray-500">
            <p>Correctas: {{ sessionStats.correct }} | Incorrectas: {{ sessionStats.incorrect }} | Precisión: {{ Math.round((sessionStats.correct / Math.max(sessionStats.correct + sessionStats.incorrect, 1)) * 100) }}%</p>
          </div>
        </div>
      </div>

      <!-- Session Complete Modal -->
      <div v-if="sessionComplete" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-xl shadow-2xl p-6 max-w-md w-full">
          <div class="text-center">
            <div class="text-6xl mb-4">🎉</div>
            <h3 class="text-2xl font-bold text-gray-800 mb-2">¡Sesión Completada!</h3>
            
            <div class="grid grid-cols-2 gap-4 mb-6">
              <div class="p-3 bg-green-50 rounded-lg">
                <div class="text-lg font-bold text-green-600">{{ sessionStats.correct }}</div>
                <div class="text-sm text-green-800">Correctas</div>
              </div>
              <div class="p-3 bg-red-50 rounded-lg">
                <div class="text-lg font-bold text-red-600">{{ sessionStats.incorrect }}</div>
                <div class="text-sm text-red-800">Incorrectas</div>
              </div>
            </div>
            
            <p class="text-gray-600 mb-2">Precisión Final: <strong>{{ Math.round((sessionStats.correct / sessionQuestions.length) * 100) }}%</strong></p>
            <p class="text-sm text-gray-500 mb-6">Tus intervalos de repaso han sido actualizados automáticamente</p>
            
            <div class="space-y-3">
              <button @click="endSession" class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                Regresar al Horario
              </button>
              <button @click="startAnotherSession" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                Otra Sesión
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SpacedRepetition',
  data() {
    return {
      loading: true,
      schedule: {
        schedule: [],
        due_count: 0,
        urgent_count: 0
      },
      inSession: false,
      sessionQuestions: [],
      currentQuestionIndex: 0,
      currentAnswer: '',
      showingResult: false,
      lastAnswerCorrect: false,
      sessionComplete: false,
      sessionStats: {
        correct: 0,
        incorrect: 0,
        totalSessions: 0,
        avgAccuracy: 0,
        currentStreak: 0
      }
    }
  },
  computed: {
    currentQuestion() {
      return this.sessionQuestions[this.currentQuestionIndex] || {};
    }
  },
  mounted() {
    this.loadSchedule();
    this.loadSessionStats();
  },
  methods: {
    async loadSchedule() {
      this.loading = true;
      try {
        const response = await axios.get('/api/learning/spaced-repetition');
        this.schedule = response.data.data;
      } catch (error) {
        console.error('Error loading spaced repetition schedule:', error);
      } finally {
        this.loading = false;
      }
    },

    async loadSessionStats() {
      try {
        // This would typically load from user stats or analytics
        this.sessionStats = {
          correct: 0,
          incorrect: 0,
          totalSessions: parseInt(localStorage.getItem('spacedRepetitionSessions') || '0'),
          avgAccuracy: parseFloat(localStorage.getItem('spacedRepetitionAvgAccuracy') || '0'),
          currentStreak: parseInt(localStorage.getItem('spacedRepetitionStreak') || '0')
        };
      } catch (error) {
        console.error('Error loading session stats:', error);
      }
    },

    startRepetitionSession(items) {
      if (!items || items.length === 0) return;

      // Generate questions for spaced repetition
      this.sessionQuestions = this.generateSpacedRepetitionQuestions(items);
      this.currentQuestionIndex = 0;
      this.currentAnswer = '';
      this.showingResult = false;
      this.sessionComplete = false;
      this.sessionStats.correct = 0;
      this.sessionStats.incorrect = 0;
      this.inSession = true;
    },

    generateSpacedRepetitionQuestions(items) {
      const questions = [];
      
      for (const item of items) {
        const skill = item.skill;
        
        // Generate questions based on skill type
        switch (skill.skill_topic) {
          case 'to_be_to_have':
            questions.push(...this.generateToBeToHaveQuestions(skill));
            break;
          case 'present_simple':
            questions.push(...this.generatePresentSimpleQuestions(skill));
            break;
          case 'prepositions':
            questions.push(...this.generatePrepositionQuestions(skill));
            break;
          case 'quantifiers':
            questions.push(...this.generateQuantifierQuestions(skill));
            break;
          case 'wh_questions':
            questions.push(...this.generateWhQuestionQuestions(skill));
            break;
          default:
            questions.push(...this.generateGeneralQuestions(skill));
        }
      }

      // Shuffle questions
      return questions.sort(() => Math.random() - 0.5).slice(0, 15); // Limit to 15 questions max
    },

    generateToBeToHaveQuestions(skill) {
      const questions = [
        {
          skill_topic: skill.skill_topic,
          question: "Complete: I ___ a teacher.",
          correct_answer: "am",
          placeholder: "am/is/are",
          explanation: "'I' always uses 'am' with the verb 'to be'"
        },
        {
          skill_topic: skill.skill_topic,
          question: "Complete: She ___ two cats.",
          correct_answer: "has",
          placeholder: "have/has",
          explanation: "'She' uses 'has' with the verb 'to have'"
        },
        {
          skill_topic: skill.skill_topic,
          question: "Complete: They ___ happy.",
          correct_answer: "are",
          placeholder: "am/is/are",
          explanation: "'They' uses 'are' with the verb 'to be'"
        }
      ];

      return questions.slice(0, Math.ceil(3 * Math.random()) + 1);
    },

    generatePresentSimpleQuestions(skill) {
      const questions = [
        {
          skill_topic: skill.skill_topic,
          question: "Complete: He ___ to work every day. (go)",
          correct_answer: "goes",
          explanation: "Third person singular adds -es to 'go'"
        },
        {
          skill_topic: skill.skill_topic,
          question: "Complete: I ___ coffee in the morning. (drink)",
          correct_answer: "drink",
          explanation: "First person uses the base form of the verb"
        },
        {
          skill_topic: skill.skill_topic,
          question: "Complete: She ___ her homework. (do)",
          correct_answer: "does",
          explanation: "Third person singular of 'do' is 'does'"
        }
      ];

      return questions.slice(0, Math.ceil(3 * Math.random()) + 1);
    },

    generatePrepositionQuestions(skill) {
      const questions = [
        {
          skill_topic: skill.skill_topic,
          question: "Complete: The book is ___ the table.",
          correct_answer: "on",
          placeholder: "on/in/at/under",
          explanation: "We use 'on' for surfaces"
        },
        {
          skill_topic: skill.skill_topic,
          question: "Complete: I live ___ Madrid.",
          correct_answer: "in",
          placeholder: "in/at/on",
          explanation: "We use 'in' for cities"
        },
        {
          skill_topic: skill.skill_topic,
          question: "Complete: The meeting is ___ 3 PM.",
          correct_answer: "at",
          placeholder: "at/in/on",
          explanation: "We use 'at' for specific times"
        }
      ];

      return questions.slice(0, Math.ceil(3 * Math.random()) + 1);
    },

    generateQuantifierQuestions(skill) {
      const questions = [
        {
          skill_topic: skill.skill_topic,
          question: "Complete: There are ___ apples in the basket.",
          correct_answer: "some",
          placeholder: "some/any/a few",
          explanation: "'Some' is used in positive statements"
        },
        {
          skill_topic: skill.skill_topic,
          question: "Complete: I don't have ___ money.",
          correct_answer: "any",
          placeholder: "some/any/much",
          explanation: "'Any' is used in negative statements"
        }
      ];

      return questions.slice(0, Math.ceil(2 * Math.random()) + 1);
    },

    generateWhQuestionQuestions(skill) {
      const questions = [
        {
          skill_topic: skill.skill_topic,
          question: "Complete: ___ is your name?",
          correct_answer: "What",
          placeholder: "What/Who/Where/When",
          explanation: "'What' is used to ask about things or names"
        },
        {
          skill_topic: skill.skill_topic,
          question: "Complete: ___ do you live?",
          correct_answer: "Where",
          placeholder: "What/Who/Where/When",
          explanation: "'Where' is used to ask about places"
        }
      ];

      return questions.slice(0, Math.ceil(2 * Math.random()) + 1);
    },

    generateGeneralQuestions(skill) {
      return [
        {
          skill_topic: skill.skill_topic,
          question: "Complete this English sentence correctly.",
          correct_answer: "answer",
          explanation: "Practice general English skills"
        }
      ];
    },

    submitAnswer() {
      if (!this.currentAnswer.trim()) return;

      this.lastAnswerCorrect = this.checkAnswer(this.currentAnswer.trim(), this.currentQuestion.correct_answer);
      
      if (this.lastAnswerCorrect) {
        this.sessionStats.correct++;
      } else {
        this.sessionStats.incorrect++;
      }

      this.showingResult = true;
    },

    checkAnswer(userAnswer, correctAnswer) {
      // Normalize answers for comparison
      const normalize = (str) => str.toLowerCase().trim();
      return normalize(userAnswer) === normalize(correctAnswer);
    },

    nextQuestion() {
      if (this.currentQuestionIndex < this.sessionQuestions.length - 1) {
        this.currentQuestionIndex++;
        this.currentAnswer = '';
        this.showingResult = false;
      } else {
        this.completeSession();
      }
    },

    completeSession() {
      this.sessionComplete = true;
      this.saveSessionStats();
    },

    endSession() {
      this.inSession = false;
      this.sessionComplete = false;
      this.loadSchedule(); // Refresh schedule
    },

    startAnotherSession() {
      this.sessionComplete = false;
      if (this.schedule.schedule.length > 0) {
        this.startRepetitionSession(this.schedule.schedule.slice(0, 5));
      } else {
        this.endSession();
      }
    },

    saveSessionStats() {
      const totalQuestions = this.sessionQuestions.length;
      const accuracy = (this.sessionStats.correct / totalQuestions) * 100;
      
      // Update local storage stats
      const currentSessions = parseInt(localStorage.getItem('spacedRepetitionSessions') || '0');
      const currentAvgAccuracy = parseFloat(localStorage.getItem('spacedRepetitionAvgAccuracy') || '0');
      
      const newAvgAccuracy = ((currentAvgAccuracy * currentSessions) + accuracy) / (currentSessions + 1);
      
      localStorage.setItem('spacedRepetitionSessions', (currentSessions + 1).toString());
      localStorage.setItem('spacedRepetitionAvgAccuracy', newAvgAccuracy.toFixed(2));
      
      // Update streak
      if (accuracy >= 70) { // 70% threshold for maintaining streak
        const currentStreak = parseInt(localStorage.getItem('spacedRepetitionStreak') || '0');
        localStorage.setItem('spacedRepetitionStreak', (currentStreak + 1).toString());
        this.sessionStats.currentStreak = currentStreak + 1;
      }

      this.sessionStats.totalSessions = currentSessions + 1;
      this.sessionStats.avgAccuracy = newAvgAccuracy;
    },

    getUrgentItems() {
      return this.schedule.schedule.filter(item => item.urgency >= 80);
    },

    getUrgencyCardClass(urgency) {
      if (urgency >= 90) return 'border-l-4 border-red-500 bg-red-50';
      if (urgency >= 70) return 'border-l-4 border-orange-500 bg-orange-50';
      if (urgency >= 50) return 'border-l-4 border-yellow-500 bg-yellow-50';
      return 'border-l-4 border-blue-500 bg-blue-50';
    },

    getUrgencyTextClass(urgency) {
      if (urgency >= 80) return 'text-red-600 font-bold';
      if (urgency >= 60) return 'text-orange-600 font-semibold';
      return 'text-gray-600';
    },

    getDifficultyColor(level) {
      const colors = {
        'beginner': 'bg-green-500',
        'elementary': 'bg-blue-500',
        'intermediate': 'bg-purple-500',
        'advanced': 'bg-red-500'
      };
      return colors[level] || 'bg-gray-500';
    },

    formatSkillTopic(topic) {
      const topics = {
        'to_be_to_have': 'To Be / To Have',
        'present_simple': 'Presente Simple',
        'prepositions': 'Preposiciones',
        'quantifiers': 'Cuantificadores',
        'wh_questions': 'Preguntas WH',
        'mixed_skills': 'Habilidades Mixtas'
      };
      return topics[topic] || topic.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
    },

    formatRelativeDate(dateString) {
      const date = new Date(dateString);
      const now = new Date();
      const diffHours = Math.ceil((date - now) / (1000 * 60 * 60));
      
      if (diffHours < 0) return 'Vencido';
      if (diffHours < 24) return `En ${diffHours}h`;
      return `En ${Math.ceil(diffHours / 24)}d`;
    }
  }
}
</script>

<style scoped>
.spaced-repetition {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

.interactive-card {
  transition: all 0.2s ease-in-out;
}

.interactive-card:hover {
  transform: translateY(-2px);
}
</style>