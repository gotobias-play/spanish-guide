<template>
  <div class="container mx-auto p-4 md:p-8">
    <h2 class="text-3xl font-bold text-center mb-6">🎤 Práctica de Pronunciación</h2>
    
    <!-- Navigation Tabs -->
    <div class="flex flex-wrap justify-center mb-6 border-b">
      <button
        v-for="tab in tabs"
        :key="tab.id"
        @click="activeTab = tab.id"
        :class="['px-4 py-2 mx-1 mb-2 rounded-t-lg font-medium transition-colors', 
                 activeTab === tab.id 
                   ? 'bg-blue-500 text-white border-b-2 border-blue-500' 
                   : 'bg-gray-200 text-gray-700 hover:bg-gray-300']"
      >
        {{ tab.icon }} {{ tab.name }}
      </button>
    </div>

    <!-- Pronunciation Exercises Tab -->
    <div v-if="activeTab === 'exercises'" class="tab-panel">
      <div class="max-w-4xl mx-auto">
        
        <!-- Exercise Categories -->
        <div v-if="!selectedCategory" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="category in exerciseCategories" :key="category.id" 
               @click="selectCategory(category)"
               class="card p-6 border rounded-lg cursor-pointer hover:shadow-lg transition-shadow bg-gradient-to-br from-blue-50 to-green-50">
            <h3 class="text-xl font-semibold text-blue-700 mb-3">{{ category.title }}</h3>
            <p class="text-gray-600 mb-4">{{ category.description }}</p>
            <div class="flex items-center justify-between text-sm text-gray-500">
              <span>📊 {{ category.difficulty }}</span>
              <span>🎯 {{ category.exercises.length }} ejercicios</span>
              <span>⏱️ ~{{ category.estimatedTime }}min</span>
            </div>
          </div>
        </div>

        <!-- Exercise Interface -->
        <div v-else-if="!currentExercise" class="space-y-4">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-blue-800">{{ selectedCategory.title }}</h3>
            <button @click="selectedCategory = null" 
                    class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
              ⬅️ Volver
            </button>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-for="exercise in selectedCategory.exercises" :key="exercise.id" 
                 @click="startExercise(exercise)"
                 class="card p-4 border rounded-lg cursor-pointer hover:shadow-md transition-shadow">
              <h4 class="font-semibold text-blue-700 mb-2">{{ exercise.title }}</h4>
              <p class="text-gray-600 text-sm mb-3">{{ exercise.description }}</p>
              <div class="flex items-center justify-between text-xs text-gray-500">
                <span>🔊 {{ exercise.words.length }} palabras</span>
                <span>📈 {{ exercise.difficulty }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Active Exercise Interface -->
        <div v-else class="space-y-6">
          
          <!-- Exercise Header -->
          <div class="card p-6 border rounded-lg bg-blue-50">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-2xl font-bold text-blue-800">{{ currentExercise.title }}</h3>
              <button @click="exitExercise" 
                      class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                ❌ Salir
              </button>
            </div>
            <p class="text-gray-700 mb-4">{{ currentExercise.description }}</p>
            <div class="flex items-center space-x-4 text-sm text-gray-600">
              <span>📍 Palabra: {{ currentWordIndex + 1 }} / {{ currentExercise.words.length }}</span>
              <span>🎯 Puntuación: {{ exerciseScore }}</span>
              <span>🔥 Racha: {{ currentStreak }}</span>
            </div>
          </div>

          <!-- Current Word Practice -->
          <div class="card p-8 border rounded-lg text-center">
            <div class="space-y-6">
              
              <!-- Word Display -->
              <div>
                <h2 class="text-4xl font-bold text-blue-900 mb-2">{{ currentWord.word }}</h2>
                <p class="text-lg text-gray-600 mb-1">{{ currentWord.phonetic }}</p>
                <p class="text-gray-500">{{ currentWord.meaning }}</p>
              </div>

              <!-- Audio Controls -->
              <div class="flex justify-center space-x-4">
                <button @click="playModelAudio" 
                        :disabled="isPlayingModel"
                        class="px-6 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 disabled:bg-gray-400">
                  {{ isPlayingModel ? '🔊 Reproduciendo...' : '🔊 Escuchar Modelo' }}
                </button>
                <button @click="playSlowAudio" 
                        :disabled="isPlayingSlow"
                        class="px-6 py-3 bg-orange-500 text-white rounded-lg hover:bg-orange-600 disabled:bg-gray-400">
                  {{ isPlayingSlow ? '🐌 Reproduciendo...' : '🐌 Escuchar Lento' }}
                </button>
              </div>

              <!-- Recording Interface -->
              <div class="space-y-4">
                <div v-if="!isRecording && !hasRecording" class="space-y-4">
                  <p class="text-gray-600">Presiona el botón para grabar tu pronunciación:</p>
                  <button @click="startRecording" 
                          class="px-8 py-4 bg-red-500 text-white rounded-full hover:bg-red-600 text-lg">
                    🎤 Grabar Mi Pronunciación
                  </button>
                </div>

                <div v-else-if="isRecording" class="space-y-4">
                  <div class="flex items-center justify-center space-x-4">
                    <div class="w-4 h-4 bg-red-500 rounded-full animate-pulse"></div>
                    <p class="text-red-600 font-semibold">Grabando... {{ recordingTime }}s</p>
                  </div>
                  <button @click="stopRecording" 
                          class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    ⏹️ Detener Grabación
                  </button>
                </div>

                <div v-else-if="hasRecording" class="space-y-4">
                  <p class="text-green-600 font-semibold">✅ Grabación completa</p>
                  <div class="flex justify-center space-x-4">
                    <button @click="playUserRecording" 
                            :disabled="isPlayingUser"
                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:bg-gray-400">
                      {{ isPlayingUser ? '🔊 Reproduciendo...' : '🔊 Escuchar Mi Grabación' }}
                    </button>
                    <button @click="analyzeRecording" 
                            :disabled="isAnalyzing"
                            class="px-6 py-2 bg-purple-500 text-white rounded hover:bg-purple-600 disabled:bg-gray-400">
                      {{ isAnalyzing ? '🤖 Analizando...' : '🤖 Analizar Pronunciación' }}
                    </button>
                    <button @click="resetRecording" 
                            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                      🔄 Grabar de Nuevo
                    </button>
                  </div>
                </div>
              </div>

              <!-- Analysis Results -->
              <div v-if="pronunciationAnalysis" class="space-y-4">
                <div class="card p-6 border rounded-lg bg-gradient-to-r from-green-50 to-blue-50">
                  <h4 class="text-lg font-semibold text-green-800 mb-4">📊 Análisis de Pronunciación</h4>
                  
                  <!-- Overall Score -->
                  <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div class="text-center">
                      <div class="text-2xl font-bold text-blue-600">{{ pronunciationAnalysis.overallScore }}%</div>
                      <div class="text-sm text-gray-600">General</div>
                    </div>
                    <div class="text-center">
                      <div class="text-lg font-bold text-green-600">{{ pronunciationAnalysis.accuracyScore }}%</div>
                      <div class="text-sm text-gray-600">Precisión</div>
                    </div>
                    <div class="text-center">
                      <div class="text-lg font-bold text-orange-600">{{ pronunciationAnalysis.fluencyScore }}%</div>
                      <div class="text-sm text-gray-600">Fluidez</div>
                    </div>
                    <div class="text-center">
                      <div class="text-lg font-bold text-purple-600">{{ pronunciationAnalysis.clarityScore }}%</div>
                      <div class="text-sm text-gray-600">Claridad</div>
                    </div>
                  </div>

                  <!-- Feedback -->
                  <div class="space-y-3">
                    <div v-for="feedback in pronunciationAnalysis.feedback" :key="feedback.type" 
                         class="p-3 rounded border-l-4" 
                         :class="feedback.type === 'positive' ? 'bg-green-50 border-green-400' : 
                                 feedback.type === 'warning' ? 'bg-yellow-50 border-yellow-400' : 
                                 'bg-red-50 border-red-400'">
                      <div class="flex items-start space-x-2">
                        <span class="text-lg">{{ feedback.icon }}</span>
                        <div>
                          <div class="font-medium" 
                               :class="feedback.type === 'positive' ? 'text-green-800' : 
                                       feedback.type === 'warning' ? 'text-yellow-800' : 
                                       'text-red-800'">
                            {{ feedback.title }}
                          </div>
                          <div class="text-sm text-gray-600">{{ feedback.message }}</div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Action Buttons -->
                  <div class="flex justify-center space-x-4 mt-6">
                    <button @click="nextWord" 
                            v-if="currentWordIndex < currentExercise.words.length - 1"
                            class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                      ➡️ Siguiente Palabra
                    </button>
                    <button @click="completeExercise" 
                            v-else
                            class="px-6 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                      ✅ Completar Ejercicio
                    </button>
                    <button @click="repeatWord" 
                            class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">
                      🔄 Repetir Palabra
                    </button>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Progress Tab -->
    <div v-if="activeTab === 'progress'" class="tab-panel">
      <div class="max-w-4xl mx-auto">
        <h3 class="text-2xl font-bold mb-6">📈 Progreso en Pronunciación</h3>
        
        <!-- Overall Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="card p-6 border rounded-lg bg-blue-50">
            <h4 class="text-lg font-semibold text-blue-700">📊 Palabras Practicadas</h4>
            <div class="text-3xl font-bold text-blue-900 mt-2">{{ pronunciationStats.totalWords }}</div>
            <div class="text-sm text-gray-600">Palabras únicas</div>
          </div>
          <div class="card p-6 border rounded-lg bg-green-50">
            <h4 class="text-lg font-semibold text-green-700">🎯 Precisión Promedio</h4>
            <div class="text-3xl font-bold text-green-900 mt-2">{{ pronunciationStats.averageAccuracy }}%</div>
            <div class="text-sm text-gray-600">Última semana</div>
          </div>
          <div class="card p-6 border rounded-lg bg-purple-50">
            <h4 class="text-lg font-semibold text-purple-700">🔥 Racha Actual</h4>
            <div class="text-3xl font-bold text-purple-900 mt-2">{{ pronunciationStats.currentStreak }}</div>
            <div class="text-sm text-gray-600">Días consecutivos</div>
          </div>
          <div class="card p-6 border rounded-lg bg-orange-50">
            <h4 class="text-lg font-semibold text-orange-700">⏱️ Tiempo Total</h4>
            <div class="text-3xl font-bold text-orange-900 mt-2">{{ pronunciationStats.totalTime }}h</div>
            <div class="text-sm text-gray-600">Practicando</div>
          </div>
        </div>

        <!-- Skill Progress -->
        <div class="card p-6 border rounded-lg mb-6">
          <h4 class="text-lg font-semibold text-gray-800 mb-6">🎯 Progreso por Habilidad</h4>
          <div class="space-y-4">
            <div v-for="skill in skillProgress" :key="skill.name">
              <div class="flex justify-between text-sm font-medium text-gray-700 mb-1">
                <span>{{ skill.name }}</span>
                <span>{{ skill.score }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3">
                <div class="h-3 rounded-full" 
                     :class="skill.color"
                     :style="`width: ${skill.score}%`"></div>
              </div>
              <div class="text-xs text-gray-500 mt-1">{{ skill.description }}</div>
            </div>
          </div>
        </div>

        <!-- Recent Practice -->
        <div class="card p-6 border rounded-lg">
          <h4 class="text-lg font-semibold text-gray-800 mb-4">📝 Práctica Reciente</h4>
          <div class="space-y-3">
            <div v-for="session in recentSessions" :key="session.id" 
                 class="flex items-center justify-between p-3 bg-gray-50 rounded">
              <div>
                <div class="font-medium text-gray-800">{{ session.category }}</div>
                <div class="text-sm text-gray-600">{{ session.wordsCount }} palabras • {{ session.duration }}min</div>
              </div>
              <div class="text-right">
                <div class="font-bold text-blue-600">{{ session.score }}%</div>
                <div class="text-xs text-gray-500">{{ session.date }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Settings Tab -->
    <div v-if="activeTab === 'settings'" class="tab-panel">
      <div class="max-w-2xl mx-auto">
        <h3 class="text-2xl font-bold mb-6">⚙️ Configuración de Pronunciación</h3>
        
        <div class="space-y-6">
          
          <!-- Audio Settings -->
          <div class="card p-6 border rounded-lg">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">🔊 Configuración de Audio</h4>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Velocidad de Reproducción</label>
                <select v-model="audioSettings.playbackSpeed" 
                        class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500">
                  <option value="0.75">Lento (0.75x)</option>
                  <option value="1.0">Normal (1.0x)</option>
                  <option value="1.25">Rápido (1.25x)</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Volumen</label>
                <input type="range" v-model="audioSettings.volume" 
                       min="0" max="100" 
                       class="w-full">
                <div class="text-sm text-gray-600">{{ audioSettings.volume }}%</div>
              </div>
              <div>
                <label class="flex items-center">
                  <input type="checkbox" v-model="audioSettings.autoRepeat" 
                         class="mr-2">
                  <span class="text-sm text-gray-700">Repetir automáticamente el audio modelo</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Recording Settings -->
          <div class="card p-6 border rounded-lg">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">🎤 Configuración de Grabación</h4>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Duración Máxima de Grabación</label>
                <select v-model="recordingSettings.maxDuration" 
                        class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500">
                  <option value="3">3 segundos</option>
                  <option value="5">5 segundos</option>
                  <option value="10">10 segundos</option>
                </select>
              </div>
              <div>
                <label class="flex items-center">
                  <input type="checkbox" v-model="recordingSettings.autoAnalyze" 
                         class="mr-2">
                  <span class="text-sm text-gray-700">Analizar automáticamente después de grabar</span>
                </label>
              </div>
              <div>
                <label class="flex items-center">
                  <input type="checkbox" v-model="recordingSettings.showWaveform" 
                         class="mr-2">
                  <span class="text-sm text-gray-700">Mostrar forma de onda durante la grabación</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Analysis Settings -->
          <div class="card p-6 border rounded-lg">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">🤖 Configuración de Análisis</h4>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sensibilidad del Análisis</label>
                <select v-model="analysisSettings.sensitivity" 
                        class="w-full p-2 border rounded focus:ring-2 focus:ring-blue-500">
                  <option value="lenient">Permisivo - Más tolerante</option>
                  <option value="normal">Normal - Equilibrado</option>
                  <option value="strict">Estricto - Más preciso</option>
                </select>
              </div>
              <div>
                <label class="flex items-center">
                  <input type="checkbox" v-model="analysisSettings.detailedFeedback" 
                         class="mr-2">
                  <span class="text-sm text-gray-700">Proporcionar feedback detallado</span>
                </label>
              </div>
              <div>
                <label class="flex items-center">
                  <input type="checkbox" v-model="analysisSettings.showPhonetics" 
                         class="mr-2">
                  <span class="text-sm text-gray-700">Mostrar transcripción fonética</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Save Settings -->
          <div class="text-center">
            <button @click="saveSettings" 
                    class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
              💾 Guardar Configuración
            </button>
          </div>

        </div>
      </div>
    </div>

  </div>
</template>

<script>
export default {
  name: 'PronunciationPractice',
  data() {
    return {
      activeTab: 'exercises',
      tabs: [
        { id: 'exercises', name: 'Ejercicios', icon: '🎤' },
        { id: 'progress', name: 'Progreso', icon: '📈' },
        { id: 'settings', name: 'Configuración', icon: '⚙️' }
      ],
      
      selectedCategory: null,
      currentExercise: null,
      currentWordIndex: 0,
      exerciseScore: 0,
      currentStreak: 0,
      
      // Recording states
      isRecording: false,
      hasRecording: false,
      recordingTime: 0,
      recordingTimer: null,
      
      // Audio states
      isPlayingModel: false,
      isPlayingSlow: false,
      isPlayingUser: false,
      isAnalyzing: false,
      
      pronunciationAnalysis: null,
      
      exerciseCategories: [
        {
          id: 1,
          title: 'Sonidos Vocálicos',
          description: 'Practica los sonidos de las vocales en inglés',
          difficulty: 'Principiante',
          estimatedTime: 15,
          exercises: [
            {
              id: 11,
              title: 'Vocales Cortas',
              description: 'Sonidos vocálicos cortos en inglés',
              difficulty: 'Fácil',
              words: [
                { word: 'cat', phonetic: '/kæt/', meaning: 'gato' },
                { word: 'bed', phonetic: '/bed/', meaning: 'cama' },
                { word: 'sit', phonetic: '/sɪt/', meaning: 'sentarse' },
                { word: 'hot', phonetic: '/hɒt/', meaning: 'caliente' },
                { word: 'cup', phonetic: '/kʌp/', meaning: 'taza' }
              ]
            },
            {
              id: 12,
              title: 'Vocales Largas',
              description: 'Sonidos vocálicos largos en inglés',
              difficulty: 'Intermedio',
              words: [
                { word: 'beat', phonetic: '/biːt/', meaning: 'golpear' },
                { word: 'boot', phonetic: '/buːt/', meaning: 'bota' },
                { word: 'bird', phonetic: '/bɜːd/', meaning: 'pájaro' },
                { word: 'cart', phonetic: '/kɑːrt/', meaning: 'carrito' },
                { word: 'port', phonetic: '/pɔːrt/', meaning: 'puerto' }
              ]
            }
          ]
        },
        {
          id: 2,
          title: 'Consonantes Difíciles',
          description: 'Consonantes que causan problemas a hispanohablantes',
          difficulty: 'Intermedio',
          estimatedTime: 20,
          exercises: [
            {
              id: 21,
              title: 'TH Sounds',
              description: 'Sonidos θ y ð que no existen en español',
              difficulty: 'Intermedio',
              words: [
                { word: 'think', phonetic: '/θɪŋk/', meaning: 'pensar' },
                { word: 'that', phonetic: '/ðæt/', meaning: 'eso' },
                { word: 'three', phonetic: '/θriː/', meaning: 'tres' },
                { word: 'mother', phonetic: '/ˈmʌðər/', meaning: 'madre' },
                { word: 'tooth', phonetic: '/tuːθ/', meaning: 'diente' }
              ]
            },
            {
              id: 22,
              title: 'R vs L Sounds',
              description: 'Diferenciación entre R y L',
              difficulty: 'Intermedio',
              words: [
                { word: 'right', phonetic: '/raɪt/', meaning: 'correcto' },
                { word: 'light', phonetic: '/laɪt/', meaning: 'luz' },
                { word: 'rice', phonetic: '/raɪs/', meaning: 'arroz' },
                { word: 'lice', phonetic: '/laɪs/', meaning: 'piojos' },
                { word: 'red', phonetic: '/red/', meaning: 'rojo' }
              ]
            }
          ]
        },
        {
          id: 3,
          title: 'Palabras Comunes',
          description: 'Vocabulario de uso frecuente',
          difficulty: 'Principiante',
          estimatedTime: 12,
          exercises: [
            {
              id: 31,
              title: 'Saludos y Presentaciones',
              description: 'Expresiones básicas de cortesía',
              difficulty: 'Fácil',
              words: [
                { word: 'hello', phonetic: '/həˈloʊ/', meaning: 'hola' },
                { word: 'goodbye', phonetic: '/ɡʊdˈbaɪ/', meaning: 'adiós' },
                { word: 'please', phonetic: '/pliːz/', meaning: 'por favor' },
                { word: 'thank you', phonetic: '/θæŋk juː/', meaning: 'gracias' },
                { word: 'excuse me', phonetic: '/ɪkˈskjuːz miː/', meaning: 'disculpe' }
              ]
            }
          ]
        }
      ],
      
      pronunciationStats: {
        totalWords: 127,
        averageAccuracy: 82,
        currentStreak: 5,
        totalTime: 8.5
      },
      
      skillProgress: [
        { name: 'Vocales', score: 85, color: 'bg-blue-600', description: 'Sonidos vocálicos básicos' },
        { name: 'Consonantes', score: 78, color: 'bg-green-600', description: 'Consonantes difíciles' },
        { name: 'TH Sounds', score: 65, color: 'bg-orange-600', description: 'Sonidos θ y ð' },
        { name: 'R vs L', score: 72, color: 'bg-purple-600', description: 'Diferenciación R/L' },
        { name: 'Fluidez', score: 88, color: 'bg-pink-600', description: 'Ritmo y entonación' }
      ],
      
      recentSessions: [
        { id: 1, category: 'Sonidos Vocálicos', wordsCount: 8, duration: 12, score: 89, date: 'Hoy' },
        { id: 2, category: 'TH Sounds', wordsCount: 5, duration: 8, score: 76, date: 'Ayer' },
        { id: 3, category: 'Palabras Comunes', wordsCount: 10, duration: 15, score: 92, date: '2 días' },
        { id: 4, category: 'R vs L Sounds', wordsCount: 6, duration: 9, score: 71, date: '3 días' }
      ],
      
      // Settings
      audioSettings: {
        playbackSpeed: 1.0,
        volume: 80,
        autoRepeat: false
      },
      
      recordingSettings: {
        maxDuration: 5,
        autoAnalyze: true,
        showWaveform: false
      },
      
      analysisSettings: {
        sensitivity: 'normal',
        detailedFeedback: true,
        showPhonetics: true
      }
    };
  },
  
  computed: {
    currentWord() {
      if (!this.currentExercise || !this.currentExercise.words[this.currentWordIndex]) {
        return null;
      }
      return this.currentExercise.words[this.currentWordIndex];
    }
  },
  
  methods: {
    selectCategory(category) {
      this.selectedCategory = category;
    },
    
    startExercise(exercise) {
      this.currentExercise = exercise;
      this.currentWordIndex = 0;
      this.exerciseScore = 0;
      this.currentStreak = 0;
      this.resetRecording();
    },
    
    exitExercise() {
      this.currentExercise = null;
      this.resetRecording();
    },
    
    playModelAudio() {
      if (!this.currentWord) return;
      
      this.isPlayingModel = true;
      // Simulate audio playback
      setTimeout(() => {
        this.isPlayingModel = false;
      }, 1500);
      
      // In a real app, this would play the actual audio file
      console.log('Playing model audio for:', this.currentWord.word);
    },
    
    playSlowAudio() {
      if (!this.currentWord) return;
      
      this.isPlayingSlow = true;
      // Simulate slower audio playback
      setTimeout(() => {
        this.isPlayingSlow = false;
      }, 2500);
      
      console.log('Playing slow audio for:', this.currentWord.word);
    },
    
    startRecording() {
      this.isRecording = true;
      this.recordingTime = 0;
      
      // Simulate recording timer
      this.recordingTimer = setInterval(() => {
        this.recordingTime++;
        if (this.recordingTime >= this.recordingSettings.maxDuration) {
          this.stopRecording();
        }
      }, 1000);
      
      console.log('Started recording');
    },
    
    stopRecording() {
      this.isRecording = false;
      this.hasRecording = true;
      clearInterval(this.recordingTimer);
      
      // Auto-analyze if enabled
      if (this.recordingSettings.autoAnalyze) {
        setTimeout(() => {
          this.analyzeRecording();
        }, 500);
      }
      
      console.log('Stopped recording');
    },
    
    resetRecording() {
      this.isRecording = false;
      this.hasRecording = false;
      this.recordingTime = 0;
      this.pronunciationAnalysis = null;
      clearInterval(this.recordingTimer);
    },
    
    playUserRecording() {
      this.isPlayingUser = true;
      
      // Simulate playback
      setTimeout(() => {
        this.isPlayingUser = false;
      }, this.recordingTime * 1000);
      
      console.log('Playing user recording');
    },
    
    async analyzeRecording() {
      this.isAnalyzing = true;
      
      try {
        // Simulate AI analysis
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        // Generate analysis results
        const overallScore = Math.floor(Math.random() * 30) + 70;
        const accuracyScore = Math.floor(Math.random() * 25) + 75;
        const fluencyScore = Math.floor(Math.random() * 35) + 65;
        const clarityScore = Math.floor(Math.random() * 30) + 70;
        
        this.pronunciationAnalysis = {
          overallScore,
          accuracyScore,
          fluencyScore,
          clarityScore,
          feedback: this.generateFeedback(overallScore, accuracyScore, fluencyScore, clarityScore)
        };
        
        // Update exercise score
        this.exerciseScore += overallScore;
        
        // Update streak
        if (overallScore >= 80) {
          this.currentStreak++;
        } else {
          this.currentStreak = 0;
        }
        
      } catch (error) {
        console.error('Error analyzing recording:', error);
      } finally {
        this.isAnalyzing = false;
      }
    },
    
    generateFeedback(overall, accuracy, fluency, clarity) {
      const feedback = [];
      
      if (overall >= 90) {
        feedback.push({
          type: 'positive',
          icon: '🎉',
          title: 'Excelente pronunciación',
          message: 'Tu pronunciación es casi perfecta. ¡Sigue así!'
        });
      } else if (overall >= 80) {
        feedback.push({
          type: 'positive',
          icon: '👍',
          title: 'Muy buena pronunciación',
          message: 'Estás progresando muy bien. Solo necesitas pequeños ajustes.'
        });
      } else if (overall >= 70) {
        feedback.push({
          type: 'warning',
          icon: '⚠️',
          title: 'Pronunciación aceptable',
          message: 'Hay margen de mejora. Sigue practicando.'
        });
      } else {
        feedback.push({
          type: 'error',
          icon: '🔄',
          title: 'Necesita práctica',
          message: 'Te recomiendo escuchar el modelo varias veces antes de intentar de nuevo.'
        });
      }
      
      if (accuracy < 75) {
        feedback.push({
          type: 'error',
          icon: '🎯',
          title: 'Mejora la precisión',
          message: 'Concéntrate en los sonidos individuales de cada letra.'
        });
      }
      
      if (fluency < 70) {
        feedback.push({
          type: 'warning',
          icon: '🌊',
          title: 'Trabaja la fluidez',
          message: 'Intenta hablar de manera más natural y relajada.'
        });
      }
      
      if (clarity < 75) {
        feedback.push({
          type: 'error',
          icon: '🔊',
          title: 'Aumenta la claridad',
          message: 'Articula más claramente cada sonido.'
        });
      }
      
      return feedback;
    },
    
    nextWord() {
      this.currentWordIndex++;
      this.resetRecording();
    },
    
    completeExercise() {
      const finalScore = Math.floor(this.exerciseScore / this.currentExercise.words.length);
      alert(`¡Ejercicio completado!\nPuntuación final: ${finalScore}%\nRacha: ${this.currentStreak} palabras seguidas`);
      
      // Save progress
      this.saveExerciseProgress(finalScore);
      
      // Return to exercise list
      this.currentExercise = null;
      this.resetRecording();
    },
    
    repeatWord() {
      this.resetRecording();
    },
    
    saveExerciseProgress(score) {
      // In a real app, this would save to the backend
      console.log('Saving exercise progress:', {
        exercise: this.currentExercise.title,
        score: score,
        streak: this.currentStreak
      });
    },
    
    saveSettings() {
      // Save settings to localStorage
      localStorage.setItem('pronunciationSettings', JSON.stringify({
        audio: this.audioSettings,
        recording: this.recordingSettings,
        analysis: this.analysisSettings
      }));
      
      alert('Configuración guardada exitosamente');
    },
    
    loadSettings() {
      const saved = localStorage.getItem('pronunciationSettings');
      if (saved) {
        const settings = JSON.parse(saved);
        this.audioSettings = { ...this.audioSettings, ...settings.audio };
        this.recordingSettings = { ...this.recordingSettings, ...settings.recording };
        this.analysisSettings = { ...this.analysisSettings, ...settings.analysis };
      }
    }
  },
  
  mounted() {
    this.loadSettings();
  },
  
  beforeUnmount() {
    clearInterval(this.recordingTimer);
  }
};
</script>