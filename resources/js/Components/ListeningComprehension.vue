<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 p-4">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">
          🎧 Comprensión Auditiva
        </h1>
        <p class="text-lg text-gray-600">
          Mejora tu comprensión del inglés escuchando diálogos y conversaciones reales
        </p>
      </div>

      <!-- Progress Overview -->
      <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">📊 Tu Progreso</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg p-4 text-white">
            <h3 class="text-sm font-medium opacity-90">Lecciones Completadas</h3>
            <p class="text-2xl font-bold">{{ userProgress.completedLessons }}</p>
          </div>
          <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white">
            <h3 class="text-sm font-medium opacity-90">Precisión Promedio</h3>
            <p class="text-2xl font-bold">{{ userProgress.averageAccuracy }}%</p>
          </div>
          <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-4 text-white">
            <h3 class="text-sm font-medium opacity-90">Tiempo Total</h3>
            <p class="text-2xl font-bold">{{ userProgress.totalTime }}min</p>
          </div>
          <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg p-4 text-white">
            <h3 class="text-sm font-medium opacity-90">Racha Actual</h3>
            <p class="text-2xl font-bold">{{ userProgress.currentStreak }} días</p>
          </div>
        </div>
      </div>

      <!-- Lesson Selection -->
      <div v-if="currentView === 'selection'" class="space-y-6">
        <div v-for="category in lessonCategories" :key="category.id" class="bg-white rounded-xl shadow-lg p-6">
          <div class="flex items-center mb-4">
            <span class="text-3xl mr-3">{{ category.icon }}</span>
            <div>
              <h3 class="text-2xl font-semibold text-gray-800">{{ category.title }}</h3>
              <p class="text-gray-600">{{ category.description }}</p>
            </div>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div 
              v-for="lesson in category.lessons" 
              :key="lesson.id"
              @click="startLesson(lesson)"
              class="border-2 border-gray-200 rounded-lg p-4 hover:border-blue-400 hover:shadow-md transition-all cursor-pointer"
              :class="{ 'bg-green-50 border-green-300': lesson.completed }"
            >
              <div class="flex items-center justify-between mb-2">
                <h4 class="font-semibold text-gray-800">{{ lesson.title }}</h4>
                <span v-if="lesson.completed" class="text-green-500 text-xl">✓</span>
              </div>
              <p class="text-sm text-gray-600 mb-3">{{ lesson.description }}</p>
              <div class="flex items-center justify-between text-sm">
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">{{ lesson.difficulty }}</span>
                <span class="text-gray-500">{{ lesson.duration }}min</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Lesson Interface -->
      <div v-if="currentView === 'lesson'" class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-semibold text-gray-800">{{ currentLesson.title }}</h2>
          <button 
            @click="exitLesson"
            class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition-colors"
          >
            Salir
          </button>
        </div>

        <!-- Audio Player -->
        <div class="bg-gray-50 rounded-lg p-6 mb-6">
          <div class="flex items-center justify-center mb-4">
            <button 
              @click="togglePlayback"
              class="bg-blue-500 text-white rounded-full p-4 hover:bg-blue-600 transition-colors mr-4"
            >
              <span v-if="!isPlaying" class="text-2xl">▶️</span>
              <span v-else class="text-2xl">⏸️</span>
            </button>
            <button 
              @click="playSlowSpeed"
              class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors mr-4"
            >
              🐌 Velocidad Lenta
            </button>
            <button 
              @click="replaySegment"
              class="bg-purple-500 text-white px-4 py-2 rounded-lg hover:bg-purple-600 transition-colors"
            >
              🔄 Repetir
            </button>
          </div>
          
          <!-- Progress Bar -->
          <div class="bg-gray-300 rounded-full h-2 mb-4">
            <div 
              class="bg-blue-500 h-2 rounded-full transition-all duration-300"
              :style="{ width: audioProgress + '%' }"
            ></div>
          </div>
          
          <div class="text-center text-sm text-gray-600">
            {{ formatTime(currentTime) }} / {{ formatTime(totalTime) }}
          </div>
        </div>

        <!-- Questions -->
        <div v-if="showQuestions" class="space-y-6">
          <div v-for="(question, index) in currentLesson.questions" :key="question.id" class="border rounded-lg p-4">
            <h3 class="text-lg font-semibold mb-3">{{ index + 1 }}. {{ question.text }}</h3>
            
            <!-- Multiple Choice -->
            <div v-if="question.type === 'multiple_choice'" class="space-y-2">
              <div 
                v-for="option in question.options" 
                :key="option.id"
                @click="selectAnswer(question.id, option.id)"
                class="border rounded-lg p-3 cursor-pointer transition-colors"
                :class="{
                  'bg-blue-100 border-blue-400': userAnswers[question.id] === option.id,
                  'bg-green-100 border-green-400': showResults && option.isCorrect,
                  'bg-red-100 border-red-400': showResults && userAnswers[question.id] === option.id && !option.isCorrect
                }"
              >
                {{ option.text }}
              </div>
            </div>

            <!-- True/False -->
            <div v-if="question.type === 'true_false'" class="flex space-x-4">
              <button 
                @click="selectAnswer(question.id, true)"
                class="flex-1 border rounded-lg p-3 transition-colors"
                :class="{
                  'bg-blue-100 border-blue-400': userAnswers[question.id] === true,
                  'bg-green-100 border-green-400': showResults && question.correctAnswer === true,
                  'bg-red-100 border-red-400': showResults && userAnswers[question.id] === true && question.correctAnswer !== true
                }"
              >
                Verdadero
              </button>
              <button 
                @click="selectAnswer(question.id, false)"
                class="flex-1 border rounded-lg p-3 transition-colors"
                :class="{
                  'bg-blue-100 border-blue-400': userAnswers[question.id] === false,
                  'bg-green-100 border-green-400': showResults && question.correctAnswer === false,
                  'bg-red-100 border-red-400': showResults && userAnswers[question.id] === false && question.correctAnswer !== false
                }"
              >
                Falso
              </button>
            </div>

            <!-- Fill in the Blank -->
            <div v-if="question.type === 'fill_blank'" class="space-y-2">
              <input 
                v-model="userAnswers[question.id]"
                type="text"
                class="w-full border rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                :class="{
                  'bg-green-100 border-green-400': showResults && checkFillBlankAnswer(question, userAnswers[question.id]),
                  'bg-red-100 border-red-400': showResults && !checkFillBlankAnswer(question, userAnswers[question.id])
                }"
                placeholder="Escribe tu respuesta aquí..."
                :disabled="showResults"
              />
              <div v-if="showResults && !checkFillBlankAnswer(question, userAnswers[question.id])" class="text-sm text-green-600">
                Respuesta correcta: {{ question.correctAnswer }}
              </div>
            </div>

            <!-- Explanation -->
            <div v-if="showResults && question.explanation" class="mt-3 p-3 bg-blue-50 rounded-lg">
              <p class="text-sm text-blue-800">
                <strong>💡 Explicación:</strong> {{ question.explanation }}
              </p>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="text-center">
            <button 
              v-if="!showResults"
              @click="submitAnswers"
              class="bg-blue-500 text-white px-8 py-3 rounded-lg hover:bg-blue-600 transition-colors text-lg font-semibold"
              :disabled="!allQuestionsAnswered"
            >
              Enviar Respuestas
            </button>
            <button 
              v-else
              @click="completeLesson"
              class="bg-green-500 text-white px-8 py-3 rounded-lg hover:bg-green-600 transition-colors text-lg font-semibold"
            >
              Completar Lección
            </button>
          </div>
        </div>

        <!-- Listen First Message -->
        <div v-else class="text-center py-12">
          <h3 class="text-xl font-semibold text-gray-600 mb-4">🎧 Escucha el audio primero</h3>
          <p class="text-gray-500 mb-6">Reproduce el audio al menos una vez antes de responder las preguntas</p>
          <button 
            @click="showQuestions = true"
            class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition-colors"
            :disabled="!hasPlayedOnce"
          >
            Mostrar Preguntas
          </button>
        </div>
      </div>

      <!-- Results -->
      <div v-if="currentView === 'results'" class="bg-white rounded-xl shadow-lg p-6">
        <div class="text-center mb-6">
          <h2 class="text-3xl font-bold text-gray-800 mb-2">🎉 ¡Lección Completada!</h2>
          <p class="text-lg text-gray-600">{{ currentLesson.title }}</p>
        </div>

        <!-- Score -->
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl p-6 mb-6 text-white text-center">
          <h3 class="text-xl font-semibold mb-2">Tu Puntuación</h3>
          <p class="text-4xl font-bold">{{ results.score }}%</p>
          <p class="text-sm opacity-90">{{ results.correct }}/{{ results.total }} respuestas correctas</p>
        </div>

        <!-- Performance Breakdown -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
            <h4 class="font-semibold text-green-800 mb-2">✅ Correctas</h4>
            <p class="text-2xl font-bold text-green-600">{{ results.correct }}</p>
          </div>
          <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
            <h4 class="font-semibold text-red-800 mb-2">❌ Incorrectas</h4>
            <p class="text-2xl font-bold text-red-600">{{ results.incorrect }}</p>
          </div>
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
            <h4 class="font-semibold text-blue-800 mb-2">⏱️ Tiempo</h4>
            <p class="text-2xl font-bold text-blue-600">{{ results.timeSpent }}min</p>
          </div>
        </div>

        <!-- Feedback -->
        <div class="bg-gray-50 rounded-lg p-6 mb-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-3">📝 Retroalimentación</h3>
          <p class="text-gray-700">{{ results.feedback }}</p>
        </div>

        <!-- Next Steps -->
        <div class="text-center space-x-4">
          <button 
            @click="retryLesson"
            class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition-colors"
          >
            🔄 Intentar de Nuevo
          </button>
          <button 
            @click="goToSelection"
            class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 transition-colors"
          >
            📚 Más Lecciones
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ListeningComprehension',
  data() {
    return {
      currentView: 'selection', // 'selection', 'lesson', 'results'
      currentLesson: null,
      isPlaying: false,
      hasPlayedOnce: false,
      showQuestions: false,
      showResults: false,
      audioProgress: 0,
      currentTime: 0,
      totalTime: 180, // 3 minutes default
      userAnswers: {},
      results: {},
      userProgress: {
        completedLessons: 12,
        averageAccuracy: 84,
        totalTime: 156,
        currentStreak: 5
      },
      lessonCategories: [
        {
          id: 'conversations',
          icon: '💬',
          title: 'Conversaciones Cotidianas',
          description: 'Diálogos comunes en situaciones del día a día',
          lessons: [
            {
              id: 'conv_1',
              title: 'En la Tienda',
              description: 'Comprando productos básicos',
              difficulty: 'Principiante',
              duration: 5,
              completed: true,
              audioUrl: '/audio/conversations/shopping.mp3',
              questions: [
                {
                  id: 'q1',
                  type: 'multiple_choice',
                  text: '¿Qué quiere comprar la persona?',
                  options: [
                    { id: 'a', text: 'Pan y leche', isCorrect: true },
                    { id: 'b', text: 'Frutas y verduras', isCorrect: false },
                    { id: 'c', text: 'Carne y pescado', isCorrect: false }
                  ],
                  explanation: 'En la conversación se menciona claramente "bread and milk".'
                },
                {
                  id: 'q2',
                  type: 'true_false',
                  text: 'El cliente paga con tarjeta de crédito.',
                  correctAnswer: false,
                  explanation: 'El cliente paga en efectivo (cash).'
                }
              ]
            },
            {
              id: 'conv_2',
              title: 'En el Restaurante',
              description: 'Ordenando comida y bebidas',
              difficulty: 'Principiante',
              duration: 6,
              completed: false,
              audioUrl: '/audio/conversations/restaurant.mp3',
              questions: [
                {
                  id: 'q1',
                  type: 'multiple_choice',
                  text: '¿Qué ordena el cliente como plato principal?',
                  options: [
                    { id: 'a', text: 'Pasta', isCorrect: false },
                    { id: 'b', text: 'Pizza', isCorrect: true },
                    { id: 'c', text: 'Ensalada', isCorrect: false }
                  ],
                  explanation: 'El cliente pide una pizza margherita.'
                },
                {
                  id: 'q2',
                  type: 'fill_blank',
                  text: 'El cliente pide _____ para beber.',
                  correctAnswer: 'water',
                  explanation: 'El cliente específicamente pide agua (water).'
                }
              ]
            },
            {
              id: 'conv_3',
              title: 'Pidiendo Direcciones',
              description: 'Preguntando cómo llegar a lugares',
              difficulty: 'Intermedio',
              duration: 7,
              completed: false,
              audioUrl: '/audio/conversations/directions.mp3',
              questions: [
                {
                  id: 'q1',
                  type: 'multiple_choice',
                  text: '¿A dónde quiere ir la persona?',
                  options: [
                    { id: 'a', text: 'Al banco', isCorrect: false },
                    { id: 'b', text: 'A la biblioteca', isCorrect: true },
                    { id: 'c', text: 'Al hospital', isCorrect: false }
                  ],
                  explanation: 'La persona pregunta por la biblioteca (library).'
                }
              ]
            }
          ]
        },
        {
          id: 'news',
          icon: '📰',
          title: 'Noticias y Reportajes',
          description: 'Escucha noticias y mejora tu comprensión formal',
          lessons: [
            {
              id: 'news_1',
              title: 'Clima de la Semana',
              description: 'Pronóstico del tiempo',
              difficulty: 'Intermedio',
              duration: 4,
              completed: false,
              audioUrl: '/audio/news/weather.mp3',
              questions: [
                {
                  id: 'q1',
                  type: 'multiple_choice',
                  text: '¿Cómo estará el clima el martes?',
                  options: [
                    { id: 'a', text: 'Soleado', isCorrect: false },
                    { id: 'b', text: 'Lluvioso', isCorrect: true },
                    { id: 'c', text: 'Nublado', isCorrect: false }
                  ],
                  explanation: 'El reporte menciona lluvia para el martes.'
                },
                {
                  id: 'q2',
                  type: 'fill_blank',
                  text: 'La temperatura máxima será de _____ grados.',
                  correctAnswer: '25',
                  explanation: 'El reporte menciona una máxima de 25 grados.'
                }
              ]
            },
            {
              id: 'news_2',
              title: 'Eventos Locales',
              description: 'Actividades en la comunidad',
              difficulty: 'Intermedio',
              duration: 5,
              completed: false,
              audioUrl: '/audio/news/events.mp3',
              questions: [
                {
                  id: 'q1',
                  type: 'true_false',
                  text: 'El festival será el próximo fin de semana.',
                  correctAnswer: true,
                  explanation: 'El reportaje confirma que será el próximo fin de semana.'
                }
              ]
            }
          ]
        },
        {
          id: 'interviews',
          icon: '🎤',
          title: 'Entrevistas',
          description: 'Escucha entrevistas y conversaciones formales',
          lessons: [
            {
              id: 'int_1',
              title: 'Entrevista de Trabajo',
              description: 'Proceso de selección laboral',
              difficulty: 'Avanzado',
              duration: 8,
              completed: false,
              audioUrl: '/audio/interviews/job.mp3',
              questions: [
                {
                  id: 'q1',
                  type: 'multiple_choice',
                  text: '¿Cuál es la experiencia laboral del candidato?',
                  options: [
                    { id: 'a', text: '2 años', isCorrect: false },
                    { id: 'b', text: '3 años', isCorrect: true },
                    { id: 'c', text: '5 años', isCorrect: false }
                  ],
                  explanation: 'El candidato menciona 3 años de experiencia.'
                },
                {
                  id: 'q2',
                  type: 'fill_blank',
                  text: 'El candidato trabajó anteriormente en una empresa de _____.',
                  correctAnswer: 'marketing',
                  explanation: 'Menciona haber trabajado en marketing.'
                }
              ]
            }
          ]
        }
      ]
    }
  },
  computed: {
    allQuestionsAnswered() {
      if (!this.currentLesson || !this.currentLesson.questions) return false;
      return this.currentLesson.questions.every(q => this.userAnswers[q.id] !== undefined);
    }
  },
  methods: {
    startLesson(lesson) {
      this.currentLesson = lesson;
      this.currentView = 'lesson';
      this.resetLessonState();
    },

    resetLessonState() {
      this.isPlaying = false;
      this.hasPlayedOnce = false;
      this.showQuestions = false;
      this.showResults = false;
      this.audioProgress = 0;
      this.currentTime = 0;
      this.userAnswers = {};
    },

    exitLesson() {
      this.currentView = 'selection';
      this.currentLesson = null;
    },

    togglePlayback() {
      this.isPlaying = !this.isPlaying;
      if (this.isPlaying) {
        this.simulateAudioPlayback();
        this.hasPlayedOnce = true;
      }
    },

    playSlowSpeed() {
      this.hasPlayedOnce = true;
      // Simulate slow speed playback
      this.simulateAudioPlayback(0.5);
    },

    replaySegment() {
      this.audioProgress = 0;
      this.currentTime = 0;
      this.togglePlayback();
    },

    simulateAudioPlayback(speed = 1) {
      const interval = setInterval(() => {
        if (!this.isPlaying) {
          clearInterval(interval);
          return;
        }
        
        this.currentTime += speed;
        this.audioProgress = (this.currentTime / this.totalTime) * 100;
        
        if (this.currentTime >= this.totalTime) {
          this.isPlaying = false;
          clearInterval(interval);
        }
      }, 1000 / speed);
    },

    formatTime(seconds) {
      const mins = Math.floor(seconds / 60);
      const secs = Math.floor(seconds % 60);
      return `${mins}:${secs.toString().padStart(2, '0')}`;
    },

    selectAnswer(questionId, answer) {
      if (!this.showResults) {
        this.userAnswers[questionId] = answer;
      }
    },

    checkFillBlankAnswer(question, userAnswer) {
      if (!userAnswer || !question.correctAnswer) return false;
      return userAnswer.toLowerCase().trim() === question.correctAnswer.toLowerCase().trim();
    },

    submitAnswers() {
      this.showResults = true;
      this.calculateResults();
    },

    calculateResults() {
      const questions = this.currentLesson.questions;
      let correct = 0;
      
      questions.forEach(question => {
        const userAnswer = this.userAnswers[question.id];
        let isCorrect = false;
        
        if (question.type === 'multiple_choice') {
          const correctOption = question.options.find(opt => opt.isCorrect);
          isCorrect = userAnswer === correctOption.id;
        } else if (question.type === 'true_false') {
          isCorrect = userAnswer === question.correctAnswer;
        } else if (question.type === 'fill_blank') {
          isCorrect = this.checkFillBlankAnswer(question, userAnswer);
        }
        
        if (isCorrect) correct++;
      });
      
      const score = Math.round((correct / questions.length) * 100);
      const timeSpent = Math.ceil(this.totalTime / 60);
      
      this.results = {
        score,
        correct,
        incorrect: questions.length - correct,
        total: questions.length,
        timeSpent,
        feedback: this.generateFeedback(score)
      };
    },

    generateFeedback(score) {
      if (score >= 90) {
        return '¡Excelente! Tu comprensión auditiva es muy buena. Sigue practicando con lecciones más avanzadas.';
      } else if (score >= 70) {
        return 'Muy bien. Tienes una buena comprensión básica. Practica más para mejorar tu precisión.';
      } else if (score >= 50) {
        return 'Buen intento. Necesitas más práctica con este tipo de contenido. Te recomendamos escuchar más despacio.';
      } else {
        return 'Sigue practicando. La comprensión auditiva mejora con la práctica constante. Intenta escuchar el audio varias veces.';
      }
    },

    completeLesson() {
      // Mark lesson as completed
      this.currentLesson.completed = true;
      this.userProgress.completedLessons++;
      
      // Update progress statistics
      this.updateUserProgress();
      
      this.currentView = 'results';
    },

    updateUserProgress() {
      // Simulate progress update
      const newAccuracy = (this.userProgress.averageAccuracy * (this.userProgress.completedLessons - 1) + this.results.score) / this.userProgress.completedLessons;
      this.userProgress.averageAccuracy = Math.round(newAccuracy);
      this.userProgress.totalTime += this.results.timeSpent;
      
      // Save to localStorage
      localStorage.setItem('listeningProgress', JSON.stringify(this.userProgress));
    },

    retryLesson() {
      this.resetLessonState();
      this.currentView = 'lesson';
    },

    goToSelection() {
      this.currentView = 'selection';
      this.currentLesson = null;
    }
  },

  mounted() {
    // Load saved progress
    const savedProgress = localStorage.getItem('listeningProgress');
    if (savedProgress) {
      this.userProgress = { ...this.userProgress, ...JSON.parse(savedProgress) };
    }
  }
}
</script>

<style scoped>
.transition-all {
  transition: all 0.3s ease;
}

.hover\:border-blue-400:hover {
  border-color: #60a5fa;
}

.hover\:shadow-md:hover {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

input:focus {
  outline: none;
  ring: 2px solid #3b82f6;
}

button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>