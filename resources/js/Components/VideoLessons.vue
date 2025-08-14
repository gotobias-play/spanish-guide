<template>
  <div class="min-h-screen bg-gradient-to-br from-purple-50 to-pink-100 p-4">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">
          🎬 Lecciones en Video
        </h1>
        <p class="text-lg text-gray-600">
          Aprende inglés con videos interactivos y ejercicios dinámicos
        </p>
      </div>

      <!-- Progress Overview -->
      <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">📊 Tu Progreso en Videos</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg p-4 text-white">
            <h3 class="text-sm font-medium opacity-90">Videos Completados</h3>
            <p class="text-2xl font-bold">{{ userProgress.completedVideos }}</p>
          </div>
          <div class="bg-gradient-to-r from-pink-500 to-pink-600 rounded-lg p-4 text-white">
            <h3 class="text-sm font-medium opacity-90">Tiempo de Estudio</h3>
            <p class="text-2xl font-bold">{{ userProgress.studyTime }}h</p>
          </div>
          <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg p-4 text-white">
            <h3 class="text-sm font-medium opacity-90">Precisión Promedio</h3>
            <p class="text-2xl font-bold">{{ userProgress.averageAccuracy }}%</p>
          </div>
          <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-lg p-4 text-white">
            <h3 class="text-sm font-medium opacity-90">Certificados</h3>
            <p class="text-2xl font-bold">{{ userProgress.certificates }}</p>
          </div>
        </div>
      </div>

      <!-- Video Library -->
      <div v-if="currentView === 'library'" class="space-y-6">
        <div v-for="series in videoSeries" :key="series.id" class="bg-white rounded-xl shadow-lg p-6">
          <div class="flex items-center mb-4">
            <span class="text-3xl mr-3">{{ series.icon }}</span>
            <div class="flex-1">
              <h3 class="text-2xl font-semibold text-gray-800">{{ series.title }}</h3>
              <p class="text-gray-600">{{ series.description }}</p>
              <div class="flex items-center mt-2 space-x-4">
                <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-sm">{{ series.level }}</span>
                <span class="text-gray-500 text-sm">{{ series.totalVideos }} videos</span>
                <span class="text-gray-500 text-sm">{{ series.totalDuration }} min total</span>
              </div>
            </div>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div 
              v-for="video in series.videos" 
              :key="video.id"
              @click="startVideo(video)"
              class="relative border-2 border-gray-200 rounded-lg overflow-hidden hover:border-purple-400 hover:shadow-md transition-all cursor-pointer"
              :class="{ 'bg-green-50 border-green-300': video.completed }"
            >
              <!-- Video Thumbnail -->
              <div class="relative bg-gray-200 h-40 flex items-center justify-center">
                <div v-if="video.thumbnail" class="w-full h-full bg-cover bg-center" :style="{ backgroundImage: `url(${video.thumbnail})` }"></div>
                <div v-else class="text-6xl text-gray-400">🎬</div>
                
                <!-- Play Button Overlay -->
                <div class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity">
                  <div class="bg-white rounded-full p-3">
                    <span class="text-2xl text-purple-600">▶️</span>
                  </div>
                </div>
                
                <!-- Completion Badge -->
                <div v-if="video.completed" class="absolute top-2 right-2 bg-green-500 text-white rounded-full p-1">
                  <span class="text-sm">✓</span>
                </div>
                
                <!-- Duration Badge -->
                <div class="absolute bottom-2 right-2 bg-black bg-opacity-75 text-white px-2 py-1 rounded text-xs">
                  {{ video.duration }}min
                </div>
              </div>
              
              <!-- Video Info -->
              <div class="p-4">
                <h4 class="font-semibold text-gray-800 mb-1">{{ video.title }}</h4>
                <p class="text-sm text-gray-600 mb-2">{{ video.description }}</p>
                <div class="flex items-center justify-between text-xs">
                  <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded">{{ video.topic }}</span>
                  <span class="text-gray-500">{{ video.questions }} preguntas</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Video Player Interface -->
      <div v-if="currentView === 'player'" class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Video Header -->
        <div class="bg-purple-600 text-white p-4 flex items-center justify-between">
          <div>
            <h2 class="text-xl font-semibold">{{ currentVideo.title }}</h2>
            <p class="text-purple-200">{{ currentVideo.topic }}</p>
          </div>
          <button 
            @click="exitVideo"
            class="bg-purple-700 px-4 py-2 rounded-lg hover:bg-purple-800 transition-colors"
          >
            Salir
          </button>
        </div>

        <!-- Video Player -->
        <div class="relative bg-black">
          <div class="aspect-video bg-gray-900 flex items-center justify-center">
            <!-- Simulated Video Content -->
            <div v-if="!videoStarted" class="text-center text-white">
              <div class="text-8xl mb-4">🎬</div>
              <h3 class="text-2xl font-semibold mb-2">{{ currentVideo.title }}</h3>
              <p class="text-gray-300 mb-6">{{ currentVideo.description }}</p>
              <button 
                @click="startVideoPlayback"
                class="bg-purple-600 text-white px-8 py-3 rounded-lg hover:bg-purple-700 transition-colors text-lg font-semibold"
              >
                ▶️ Iniciar Video
              </button>
            </div>
            
            <!-- Video Playing State -->
            <div v-else class="w-full h-full relative">
              <div class="absolute inset-0 bg-gradient-to-b from-purple-900 to-indigo-900 flex items-center justify-center">
                <div class="text-center text-white">
                  <div class="text-6xl mb-4">🎬</div>
                  <h3 class="text-xl font-semibold mb-2">Video en reproducción...</h3>
                  <p class="text-gray-300">{{ currentVideo.title }}</p>
                </div>
              </div>
              
              <!-- Subtitles -->
              <div v-if="showSubtitles && currentSubtitle" class="absolute bottom-20 left-1/2 transform -translate-x-1/2 bg-black bg-opacity-75 text-white px-6 py-3 rounded-lg max-w-4xl">
                <p class="text-center text-lg">{{ currentSubtitle.text }}</p>
                <p v-if="showTranslation" class="text-center text-sm text-gray-300 mt-1">{{ currentSubtitle.translation }}</p>
              </div>
            </div>
          </div>
          
          <!-- Video Controls -->
          <div class="bg-gray-800 text-white p-4">
            <div class="flex items-center space-x-4 mb-4">
              <button 
                @click="togglePlayPause"
                class="bg-purple-600 rounded-full p-2 hover:bg-purple-700 transition-colors"
              >
                <span v-if="!isPlaying" class="text-xl">▶️</span>
                <span v-else class="text-xl">⏸️</span>
              </button>
              
              <button 
                @click="rewind"
                class="bg-gray-600 rounded-full p-2 hover:bg-gray-700 transition-colors"
              >
                ⏪
              </button>
              
              <button 
                @click="fastForward"
                class="bg-gray-600 rounded-full p-2 hover:bg-gray-700 transition-colors"
              >
                ⏩
              </button>
              
              <div class="flex-1">
                <div class="bg-gray-600 rounded-full h-2 cursor-pointer" @click="seekTo">
                  <div 
                    class="bg-purple-500 h-2 rounded-full transition-all duration-300"
                    :style="{ width: videoProgress + '%' }"
                  ></div>
                </div>
              </div>
              
              <span class="text-sm">{{ formatTime(currentTime) }} / {{ formatTime(videoDuration) }}</span>
            </div>
            
            <!-- Additional Controls -->
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <label class="flex items-center">
                  <input 
                    type="checkbox" 
                    v-model="showSubtitles" 
                    class="mr-2"
                  />
                  <span class="text-sm">Subtítulos</span>
                </label>
                
                <label class="flex items-center">
                  <input 
                    type="checkbox" 
                    v-model="showTranslation" 
                    class="mr-2"
                    :disabled="!showSubtitles"
                  />
                  <span class="text-sm">Traducción</span>
                </label>
                
                <select v-model="playbackSpeed" class="bg-gray-700 text-white rounded px-2 py-1 text-sm">
                  <option value="0.5">0.5x</option>
                  <option value="0.75">0.75x</option>
                  <option value="1">1x</option>
                  <option value="1.25">1.25x</option>
                  <option value="1.5">1.5x</option>
                </select>
              </div>
              
              <div class="flex items-center space-x-2">
                <button 
                  @click="toggleQuestions"
                  class="bg-blue-600 px-4 py-2 rounded hover:bg-blue-700 transition-colors text-sm"
                  :disabled="!canShowQuestions"
                >
                  {{ showQuestions ? 'Ocultar' : 'Mostrar' }} Preguntas
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Interactive Questions Panel -->
        <div v-if="showQuestions && currentVideo.interactiveQuestions" class="p-6 bg-gray-50">
          <h3 class="text-xl font-semibold text-gray-800 mb-4">📝 Preguntas Interactivas</h3>
          
          <div v-for="(question, index) in currentVideo.interactiveQuestions" :key="question.id" class="mb-6 border rounded-lg p-4 bg-white">
            <div class="flex items-center justify-between mb-3">
              <h4 class="font-semibold text-gray-800">{{ index + 1 }}. {{ question.text }}</h4>
              <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded">{{ formatTime(question.timestamp) }}</span>
            </div>
            
            <!-- Multiple Choice -->
            <div v-if="question.type === 'multiple_choice'" class="space-y-2">
              <div 
                v-for="option in question.options" 
                :key="option.id"
                @click="selectVideoAnswer(question.id, option.id)"
                class="border rounded-lg p-3 cursor-pointer transition-colors"
                :class="{
                  'bg-purple-100 border-purple-400': videoAnswers[question.id] === option.id,
                  'bg-green-100 border-green-400': showVideoResults && option.isCorrect,
                  'bg-red-100 border-red-400': showVideoResults && videoAnswers[question.id] === option.id && !option.isCorrect
                }"
              >
                {{ option.text }}
              </div>
            </div>

            <!-- True/False -->
            <div v-if="question.type === 'true_false'" class="flex space-x-4">
              <button 
                @click="selectVideoAnswer(question.id, true)"
                class="flex-1 border rounded-lg p-3 transition-colors"
                :class="{
                  'bg-purple-100 border-purple-400': videoAnswers[question.id] === true,
                  'bg-green-100 border-green-400': showVideoResults && question.correctAnswer === true,
                  'bg-red-100 border-red-400': showVideoResults && videoAnswers[question.id] === true && question.correctAnswer !== true
                }"
              >
                Verdadero
              </button>
              <button 
                @click="selectVideoAnswer(question.id, false)"
                class="flex-1 border rounded-lg p-3 transition-colors"
                :class="{
                  'bg-purple-100 border-purple-400': videoAnswers[question.id] === false,
                  'bg-green-100 border-green-400': showVideoResults && question.correctAnswer === false,
                  'bg-red-100 border-red-400': showVideoResults && videoAnswers[question.id] === false && question.correctAnswer !== false
                }"
              >
                Falso
              </button>
            </div>

            <!-- Explanation -->
            <div v-if="showVideoResults && question.explanation" class="mt-3 p-3 bg-blue-50 rounded-lg">
              <p class="text-sm text-blue-800">
                <strong>💡 Explicación:</strong> {{ question.explanation }}
              </p>
            </div>
          </div>

          <!-- Questions Submit -->
          <div class="text-center mt-6">
            <button 
              v-if="!showVideoResults"
              @click="submitVideoAnswers"
              class="bg-purple-600 text-white px-8 py-3 rounded-lg hover:bg-purple-700 transition-colors text-lg font-semibold"
              :disabled="!allVideoQuestionsAnswered"
            >
              Enviar Respuestas
            </button>
            <button 
              v-else
              @click="completeVideo"
              class="bg-green-600 text-white px-8 py-3 rounded-lg hover:bg-green-700 transition-colors text-lg font-semibold"
            >
              Completar Video
            </button>
          </div>
        </div>
      </div>

      <!-- Video Results -->
      <div v-if="currentView === 'results'" class="bg-white rounded-xl shadow-lg p-6">
        <div class="text-center mb-6">
          <h2 class="text-3xl font-bold text-gray-800 mb-2">🎉 ¡Video Completado!</h2>
          <p class="text-lg text-gray-600">{{ currentVideo.title }}</p>
        </div>

        <!-- Score -->
        <div class="bg-gradient-to-r from-purple-500 to-pink-600 rounded-xl p-6 mb-6 text-white text-center">
          <h3 class="text-xl font-semibold mb-2">Tu Puntuación</h3>
          <p class="text-4xl font-bold">{{ videoResults.score }}%</p>
          <p class="text-sm opacity-90">{{ videoResults.correct }}/{{ videoResults.total }} respuestas correctas</p>
        </div>

        <!-- Performance Breakdown -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
          <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
            <h4 class="font-semibold text-green-800 mb-2">✅ Correctas</h4>
            <p class="text-2xl font-bold text-green-600">{{ videoResults.correct }}</p>
          </div>
          <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
            <h4 class="font-semibold text-red-800 mb-2">❌ Incorrectas</h4>
            <p class="text-2xl font-bold text-red-600">{{ videoResults.incorrect }}</p>
          </div>
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
            <h4 class="font-semibold text-blue-800 mb-2">⏱️ Tiempo</h4>
            <p class="text-2xl font-bold text-blue-600">{{ videoResults.timeSpent }}min</p>
          </div>
        </div>

        <!-- Feedback -->
        <div class="bg-gray-50 rounded-lg p-6 mb-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-3">📝 Retroalimentación</h3>
          <p class="text-gray-700">{{ videoResults.feedback }}</p>
        </div>

        <!-- Next Steps -->
        <div class="text-center space-x-4">
          <button 
            @click="watchAgain"
            class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition-colors"
          >
            🔄 Ver de Nuevo
          </button>
          <button 
            @click="goToLibrary"
            class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors"
          >
            📚 Más Videos
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'VideoLessons',
  data() {
    return {
      currentView: 'library', // 'library', 'player', 'results'
      currentVideo: null,
      videoStarted: false,
      isPlaying: false,
      videoProgress: 0,
      currentTime: 0,
      videoDuration: 300, // 5 minutes default
      showSubtitles: true,
      showTranslation: false,
      playbackSpeed: 1,
      showQuestions: false,
      showVideoResults: false,
      videoAnswers: {},
      videoResults: {},
      currentSubtitle: null,
      userProgress: {
        completedVideos: 8,
        studyTime: 4.2,
        averageAccuracy: 87,
        certificates: 2
      },
      videoSeries: [
        {
          id: 'everyday',
          icon: '🏠',
          title: 'Inglés para el Día a Día',
          description: 'Aprende inglés práctico para situaciones cotidianas',
          level: 'Principiante',
          totalVideos: 12,
          totalDuration: 90,
          videos: [
            {
              id: 'everyday_1',
              title: 'Saludos y Presentaciones',
              description: 'Aprende a saludar y presentarte en inglés',
              topic: 'Conversación',
              duration: 8,
              questions: 5,
              completed: true,
              thumbnail: 'https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=300&h=200&fit=crop',
              interactiveQuestions: [
                {
                  id: 'q1',
                  type: 'multiple_choice',
                  text: '¿Cuál es la forma más formal de saludar?',
                  timestamp: 120,
                  options: [
                    { id: 'a', text: 'Hi there!', isCorrect: false },
                    { id: 'b', text: 'Good morning', isCorrect: true },
                    { id: 'c', text: 'Hey!', isCorrect: false }
                  ],
                  explanation: '"Good morning" es el saludo más formal para la mañana.'
                },
                {
                  id: 'q2',
                  type: 'true_false',
                  text: '"Nice to meet you" se usa cuando conoces a alguien por primera vez.',
                  timestamp: 200,
                  correctAnswer: true,
                  explanation: 'Es correcto, se usa al conocer a alguien por primera vez.'
                }
              ],
              subtitles: [
                { timestamp: 10, text: 'Hello, my name is Sarah.', translation: 'Hola, mi nombre es Sarah.' },
                { timestamp: 15, text: 'Nice to meet you!', translation: '¡Mucho gusto!' },
                { timestamp: 20, text: 'How are you today?', translation: '¿Cómo estás hoy?' }
              ]
            },
            {
              id: 'everyday_2',
              title: 'En el Supermercado',
              description: 'Vocabulario y frases para hacer compras',
              topic: 'Compras',
              duration: 10,
              questions: 6,
              completed: false,
              thumbnail: 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=300&h=200&fit=crop',
              interactiveQuestions: [
                {
                  id: 'q1',
                  type: 'multiple_choice',
                  text: '¿Qué significa "checkout"?',
                  timestamp: 180,
                  options: [
                    { id: 'a', text: 'Entrada', isCorrect: false },
                    { id: 'b', text: 'Caja registradora', isCorrect: true },
                    { id: 'c', text: 'Carrito', isCorrect: false }
                  ],
                  explanation: '"Checkout" es la caja registradora donde pagas.'
                }
              ]
            },
            {
              id: 'everyday_3',
              title: 'Preguntando Direcciones',
              description: 'Cómo pedir y dar direcciones en inglés',
              topic: 'Navegación',
              duration: 7,
              questions: 4,
              completed: false,
              thumbnail: 'https://images.unsplash.com/photo-1477650112965-999ddd8e5223?w=300&h=200&fit=crop',
              interactiveQuestions: [
                {
                  id: 'q1',
                  type: 'true_false',
                  text: '"Turn left" significa girar a la derecha.',
                  timestamp: 150,
                  correctAnswer: false,
                  explanation: '"Turn left" significa girar a la izquierda.'
                }
              ]
            }
          ]
        },
        {
          id: 'business',
          icon: '💼',
          title: 'Inglés de Negocios',
          description: 'Inglés profesional para el mundo laboral',
          level: 'Intermedio',
          totalVideos: 10,
          totalDuration: 75,
          videos: [
            {
              id: 'business_1',
              title: 'Reuniones de Trabajo',
              description: 'Vocabulario y frases para reuniones efectivas',
              topic: 'Reuniones',
              duration: 12,
              questions: 8,
              completed: false,
              thumbnail: 'https://images.unsplash.com/photo-1556761175-b413da4baf72?w=300&h=200&fit=crop',
              interactiveQuestions: [
                {
                  id: 'q1',
                  type: 'multiple_choice',
                  text: '¿Cómo inicias una reunión formalmente?',
                  timestamp: 240,
                  options: [
                    { id: 'a', text: "Let's get started", isCorrect: true },
                    { id: 'b', text: 'Hey everyone', isCorrect: false },
                    { id: 'c', text: 'What\s up?', isCorrect: false }
                  ],
                  explanation: '"Let\'s get started" es la forma más profesional.'
                }
              ]
            },
            {
              id: 'business_2',
              title: 'Presentaciones Efectivas',
              description: 'Cómo hacer presentaciones profesionales',
              topic: 'Presentaciones',
              duration: 15,
              questions: 10,
              completed: false,
              thumbnail: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=200&fit=crop'
            }
          ]
        },
        {
          id: 'travel',
          icon: '✈️',
          title: 'Inglés para Viajeros',
          description: 'Todo lo que necesitas para viajar con confianza',
          level: 'Intermedio',
          totalVideos: 8,
          totalDuration: 60,
          videos: [
            {
              id: 'travel_1',
              title: 'En el Aeropuerto',
              description: 'Navegando por el aeropuerto en inglés',
              topic: 'Viajes',
              duration: 9,
              questions: 6,
              completed: false,
              thumbnail: 'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=300&h=200&fit=crop',
              interactiveQuestions: [
                {
                  id: 'q1',
                  type: 'multiple_choice',
                  text: '¿Qué documento necesitas para "check-in"?',
                  timestamp: 120,
                  options: [
                    { id: 'a', text: 'Passport', isCorrect: false },
                    { id: 'b', text: 'Boarding pass', isCorrect: false },
                    { id: 'c', text: 'Flight ticket', isCorrect: true }
                  ],
                  explanation: 'Necesitas tu ticket o confirmación de vuelo para el check-in.'
                }
              ]
            },
            {
              id: 'travel_2',
              title: 'En el Hotel',
              description: 'Check-in, quejas y servicios del hotel',
              topic: 'Hospedaje',
              duration: 11,
              questions: 7,
              completed: false,
              thumbnail: 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=300&h=200&fit=crop'
            }
          ]
        }
      ]
    }
  },
  computed: {
    allVideoQuestionsAnswered() {
      if (!this.currentVideo || !this.currentVideo.interactiveQuestions) return false;
      return this.currentVideo.interactiveQuestions.every(q => this.videoAnswers[q.id] !== undefined);
    },
    canShowQuestions() {
      return this.videoStarted && this.currentTime > 30; // Can show questions after 30 seconds
    }
  },
  methods: {
    startVideo(video) {
      this.currentVideo = video;
      this.currentView = 'player';
      this.resetVideoState();
    },

    resetVideoState() {
      this.videoStarted = false;
      this.isPlaying = false;
      this.videoProgress = 0;
      this.currentTime = 0;
      this.showQuestions = false;
      this.showVideoResults = false;
      this.videoAnswers = {};
      this.currentSubtitle = null;
      this.videoDuration = this.currentVideo.duration * 60; // Convert to seconds
    },

    exitVideo() {
      this.currentView = 'library';
      this.currentVideo = null;
    },

    startVideoPlayback() {
      this.videoStarted = true;
      this.isPlaying = true;
      this.simulateVideoPlayback();
    },

    togglePlayPause() {
      this.isPlaying = !this.isPlaying;
      if (this.isPlaying) {
        this.simulateVideoPlayback();
      }
    },

    rewind() {
      this.currentTime = Math.max(0, this.currentTime - 10);
      this.videoProgress = (this.currentTime / this.videoDuration) * 100;
      this.updateSubtitles();
    },

    fastForward() {
      this.currentTime = Math.min(this.videoDuration, this.currentTime + 10);
      this.videoProgress = (this.currentTime / this.videoDuration) * 100;
      this.updateSubtitles();
    },

    seekTo(event) {
      const rect = event.target.getBoundingClientRect();
      const clickX = event.clientX - rect.left;
      const percentage = clickX / rect.width;
      this.currentTime = percentage * this.videoDuration;
      this.videoProgress = percentage * 100;
      this.updateSubtitles();
    },

    simulateVideoPlayback() {
      const interval = setInterval(() => {
        if (!this.isPlaying) {
          clearInterval(interval);
          return;
        }
        
        this.currentTime += this.playbackSpeed;
        this.videoProgress = (this.currentTime / this.videoDuration) * 100;
        this.updateSubtitles();
        
        if (this.currentTime >= this.videoDuration) {
          this.isPlaying = false;
          clearInterval(interval);
        }
      }, 1000);
    },

    updateSubtitles() {
      if (!this.currentVideo.subtitles || !this.showSubtitles) {
        this.currentSubtitle = null;
        return;
      }

      const currentSub = this.currentVideo.subtitles.find(sub => 
        this.currentTime >= sub.timestamp && 
        this.currentTime < sub.timestamp + 5 // Show for 5 seconds
      );
      
      this.currentSubtitle = currentSub || null;
    },

    formatTime(seconds) {
      const mins = Math.floor(seconds / 60);
      const secs = Math.floor(seconds % 60);
      return `${mins}:${secs.toString().padStart(2, '0')}`;
    },

    toggleQuestions() {
      this.showQuestions = !this.showQuestions;
    },

    selectVideoAnswer(questionId, answer) {
      if (!this.showVideoResults) {
        this.videoAnswers[questionId] = answer;
      }
    },

    submitVideoAnswers() {
      this.showVideoResults = true;
      this.calculateVideoResults();
    },

    calculateVideoResults() {
      const questions = this.currentVideo.interactiveQuestions;
      let correct = 0;
      
      questions.forEach(question => {
        const userAnswer = this.videoAnswers[question.id];
        let isCorrect = false;
        
        if (question.type === 'multiple_choice') {
          const correctOption = question.options.find(opt => opt.isCorrect);
          isCorrect = userAnswer === correctOption.id;
        } else if (question.type === 'true_false') {
          isCorrect = userAnswer === question.correctAnswer;
        }
        
        if (isCorrect) correct++;
      });
      
      const score = Math.round((correct / questions.length) * 100);
      const timeSpent = Math.ceil(this.videoDuration / 60);
      
      this.videoResults = {
        score,
        correct,
        incorrect: questions.length - correct,
        total: questions.length,
        timeSpent,
        feedback: this.generateVideoFeedback(score)
      };
    },

    generateVideoFeedback(score) {
      if (score >= 90) {
        return '¡Excelente comprensión! Tienes muy buena retención de información visual y auditiva.';
      } else if (score >= 70) {
        return 'Muy bien. Has captado la mayoría de los conceptos. Considera ver el video de nuevo para reforzar.';
      } else if (score >= 50) {
        return 'Buen intento. Te recomendamos activar los subtítulos y pausar para tomar notas.';
      } else {
        return 'Necesitas más práctica. Intenta ver el video más lentamente y con subtítulos activados.';
      }
    },

    completeVideo() {
      // Mark video as completed
      this.currentVideo.completed = true;
      this.userProgress.completedVideos++;
      
      // Update progress statistics
      this.updateVideoProgress();
      
      this.currentView = 'results';
    },

    updateVideoProgress() {
      // Simulate progress update
      const newAccuracy = (this.userProgress.averageAccuracy * (this.userProgress.completedVideos - 1) + this.videoResults.score) / this.userProgress.completedVideos;
      this.userProgress.averageAccuracy = Math.round(newAccuracy);
      this.userProgress.studyTime += (this.videoResults.timeSpent / 60);
      
      // Check for new certificates
      if (this.userProgress.completedVideos % 5 === 0) {
        this.userProgress.certificates++;
      }
      
      // Save to localStorage
      localStorage.setItem('videoProgress', JSON.stringify(this.userProgress));
    },

    watchAgain() {
      this.resetVideoState();
      this.currentView = 'player';
    },

    goToLibrary() {
      this.currentView = 'library';
      this.currentVideo = null;
    }
  },

  mounted() {
    // Load saved progress
    const savedProgress = localStorage.getItem('videoProgress');
    if (savedProgress) {
      this.userProgress = { ...this.userProgress, ...JSON.parse(savedProgress) };
    }
  }
}
</script>

<style scoped>
.aspect-video {
  aspect-ratio: 16 / 9;
}

.transition-all {
  transition: all 0.3s ease;
}

.hover\:border-purple-400:hover {
  border-color: #a855f7;
}

.hover\:shadow-md:hover {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.bg-cover {
  background-size: cover;
}

.bg-center {
  background-position: center;
}

button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

input[type="checkbox"] {
  accent-color: #8b5cf6;
}

select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.5rem center;
  background-repeat: no-repeat;
  background-size: 1.5em 1.5em;
  padding-right: 2.5rem;
}
</style>