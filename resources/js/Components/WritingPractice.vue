<template>
  <div class="container mx-auto p-4 md:p-8">
    <h2 class="text-3xl font-bold text-center mb-6">✍️ Práctica de Escritura con IA</h2>
    
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

    <!-- Writing Exercises Tab -->
    <div v-if="activeTab === 'exercises'" class="tab-panel">
      <div class="max-w-4xl mx-auto">
        
        <!-- Exercise Selection -->
        <div v-if="!selectedExercise" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="exercise in writingExercises" :key="exercise.id" 
               @click="startExercise(exercise)"
               class="card p-6 border rounded-lg cursor-pointer hover:shadow-lg transition-shadow bg-gradient-to-br from-blue-50 to-purple-50">
            <h3 class="text-xl font-semibold text-blue-700 mb-3">{{ exercise.title }}</h3>
            <p class="text-gray-600 mb-4">{{ exercise.description }}</p>
            <div class="flex items-center justify-between text-sm text-gray-500">
              <span>📝 {{ exercise.type }}</span>
              <span>⏱️ {{ exercise.timeLimit }}min</span>
              <span>📊 {{ exercise.difficulty }}</span>
            </div>
          </div>
        </div>

        <!-- Writing Interface -->
        <div v-else class="space-y-6">
          <div class="card p-6 border rounded-lg bg-blue-50">
            <h3 class="text-2xl font-bold text-blue-800 mb-4">{{ selectedExercise.title }}</h3>
            <p class="text-gray-700 mb-4">{{ selectedExercise.prompt }}</p>
            <div class="flex items-center space-x-4 text-sm text-gray-600">
              <span>📝 Mínimo: {{ selectedExercise.minWords }} palabras</span>
              <span>⏱️ Tiempo: {{ selectedExercise.timeLimit }} minutos</span>
              <span v-if="timeRemaining > 0" class="font-bold text-blue-600">
                🕐 {{ Math.floor(timeRemaining / 60) }}:{{ String(timeRemaining % 60).padStart(2, '0') }}
              </span>
            </div>
          </div>

          <!-- Writing Area -->
          <div class="card p-6 border rounded-lg">
            <textarea
              v-model="userWriting"
              @input="updateWordCount"
              placeholder="Comienza a escribir tu respuesta aquí..."
              class="w-full h-96 p-4 border rounded-lg resize-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :disabled="isSubmitting || hasSubmitted"
            ></textarea>
            
            <div class="flex justify-between items-center mt-4">
              <div class="text-sm text-gray-600">
                <span class="font-medium">Palabras: {{ wordCount }}</span>
                <span class="ml-4">Caracteres: {{ userWriting.length }}</span>
              </div>
              
              <div class="space-x-3">
                <button @click="clearWriting" 
                        class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600"
                        :disabled="isSubmitting || hasSubmitted">
                  🗑️ Limpiar
                </button>
                <button @click="submitWriting" 
                        :disabled="isSubmitting || hasSubmitted || wordCount < selectedExercise.minWords"
                        class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:bg-gray-400">
                  {{ isSubmitting ? '🤖 Analizando...' : '📤 Enviar para Análisis' }}
                </button>
              </div>
            </div>
          </div>

          <!-- AI Feedback Display -->
          <div v-if="aiFeedback" class="space-y-4">
            
            <!-- Overall Score -->
            <div class="card p-6 border rounded-lg bg-green-50">
              <h4 class="text-xl font-bold text-green-800 mb-4">📊 Evaluación General</h4>
              <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="text-center">
                  <div class="text-3xl font-bold text-blue-600">{{ aiFeedback.overallScore }}/100</div>
                  <div class="text-sm text-gray-600">Puntuación Total</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-green-600">{{ aiFeedback.grammarScore }}/100</div>
                  <div class="text-sm text-gray-600">Gramática</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-purple-600">{{ aiFeedback.vocabularyScore }}/100</div>
                  <div class="text-sm text-gray-600">Vocabulario</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-orange-600">{{ aiFeedback.coherenceScore }}/100</div>
                  <div class="text-sm text-gray-600">Coherencia</div>
                </div>
              </div>
            </div>

            <!-- Detailed Feedback -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              
              <!-- Strengths -->
              <div class="card p-6 border rounded-lg bg-green-50">
                <h4 class="text-lg font-bold text-green-800 mb-4">✅ Fortalezas</h4>
                <ul class="space-y-2">
                  <li v-for="strength in aiFeedback.strengths" :key="strength" 
                      class="flex items-start">
                    <span class="text-green-600 mr-2">•</span>
                    <span class="text-gray-700">{{ strength }}</span>
                  </li>
                </ul>
              </div>

              <!-- Areas for Improvement -->
              <div class="card p-6 border rounded-lg bg-yellow-50">
                <h4 class="text-lg font-bold text-yellow-800 mb-4">📝 Áreas de Mejora</h4>
                <ul class="space-y-2">
                  <li v-for="improvement in aiFeedback.improvements" :key="improvement" 
                      class="flex items-start">
                    <span class="text-yellow-600 mr-2">•</span>
                    <span class="text-gray-700">{{ improvement }}</span>
                  </li>
                </ul>
              </div>

            </div>

            <!-- Grammar Corrections -->
            <div v-if="aiFeedback.corrections.length > 0" class="card p-6 border rounded-lg bg-red-50">
              <h4 class="text-lg font-bold text-red-800 mb-4">🔧 Correcciones Sugeridas</h4>
              <div class="space-y-3">
                <div v-for="(correction, index) in aiFeedback.corrections" :key="index" 
                     class="p-3 bg-white rounded border-l-4 border-red-400">
                  <div class="text-red-700 font-medium">❌ Error: {{ correction.error }}</div>
                  <div class="text-green-700 font-medium">✅ Corrección: {{ correction.correction }}</div>
                  <div class="text-gray-600 text-sm">💡 Explicación: {{ correction.explanation }}</div>
                </div>
              </div>
            </div>

            <!-- Vocabulary Suggestions -->
            <div v-if="aiFeedback.vocabularySuggestions.length > 0" class="card p-6 border rounded-lg bg-purple-50">
              <h4 class="text-lg font-bold text-purple-800 mb-4">📚 Sugerencias de Vocabulario</h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-for="suggestion in aiFeedback.vocabularySuggestions" :key="suggestion.word" 
                     class="p-3 bg-white rounded border">
                  <div class="font-bold text-purple-700">{{ suggestion.word }}</div>
                  <div class="text-gray-600 text-sm">{{ suggestion.definition }}</div>
                  <div class="text-purple-600 text-sm">Ejemplo: {{ suggestion.example }}</div>
                </div>
              </div>
            </div>

            <!-- Next Steps -->
            <div class="card p-6 border rounded-lg bg-blue-50">
              <h4 class="text-lg font-bold text-blue-800 mb-4">🎯 Próximos Pasos</h4>
              <ul class="space-y-2">
                <li v-for="step in aiFeedback.nextSteps" :key="step" 
                    class="flex items-start">
                  <span class="text-blue-600 mr-2">→</span>
                  <span class="text-gray-700">{{ step }}</span>
                </li>
              </ul>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-center space-x-4">
              <button @click="retryExercise" 
                      class="px-6 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">
                🔄 Intentar de Nuevo
              </button>
              <button @click="selectNewExercise" 
                      class="px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                📝 Nuevo Ejercicio
              </button>
            </div>

          </div>
        </div>
      </div>
    </div>

    <!-- Writing History Tab -->
    <div v-if="activeTab === 'history'" class="tab-panel">
      <div class="max-w-4xl mx-auto">
        <h3 class="text-2xl font-bold mb-6">📚 Historial de Escritos</h3>
        
        <div v-if="writingHistory.length === 0" class="text-center text-gray-500 py-12">
          <div class="text-6xl mb-4">📝</div>
          <p class="text-lg">Aún no has completado ningún ejercicio de escritura.</p>
          <button @click="activeTab = 'exercises'" 
                  class="mt-4 px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            Comenzar Ahora
          </button>
        </div>

        <div v-else class="space-y-4">
          <div v-for="entry in writingHistory" :key="entry.id" 
               class="card p-6 border rounded-lg hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-3">
              <h4 class="text-lg font-semibold text-blue-700">{{ entry.exercise.title }}</h4>
              <span class="text-sm text-gray-500">{{ formatDate(entry.submittedAt) }}</span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
              <div class="text-center">
                <div class="text-xl font-bold text-blue-600">{{ entry.feedback.overallScore }}/100</div>
                <div class="text-xs text-gray-600">General</div>
              </div>
              <div class="text-center">
                <div class="text-lg font-bold text-green-600">{{ entry.feedback.grammarScore }}/100</div>
                <div class="text-xs text-gray-600">Gramática</div>
              </div>
              <div class="text-center">
                <div class="text-lg font-bold text-purple-600">{{ entry.feedback.vocabularyScore }}/100</div>
                <div class="text-xs text-gray-600">Vocabulario</div>
              </div>
              <div class="text-center">
                <div class="text-lg font-bold text-orange-600">{{ entry.feedback.coherenceScore }}/100</div>
                <div class="text-xs text-gray-600">Coherencia</div>
              </div>
            </div>
            <p class="text-gray-600 text-sm">{{ entry.writing.substring(0, 150) }}...</p>
            <button @click="viewDetails(entry)" 
                    class="mt-3 px-4 py-2 bg-blue-500 text-white rounded text-sm hover:bg-blue-600">
              Ver Detalles
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Progress Tab -->
    <div v-if="activeTab === 'progress'" class="tab-panel">
      <div class="max-w-4xl mx-auto">
        <h3 class="text-2xl font-bold mb-6">📈 Progreso en Escritura</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="card p-6 border rounded-lg bg-blue-50">
            <h4 class="text-lg font-semibold text-blue-700">📝 Ejercicios Completados</h4>
            <div class="text-3xl font-bold text-blue-900 mt-2">{{ writingStats.totalExercises }}</div>
            <div class="text-sm text-gray-600">Total realizados</div>
          </div>
          <div class="card p-6 border rounded-lg bg-green-50">
            <h4 class="text-lg font-semibold text-green-700">📊 Promedio General</h4>
            <div class="text-3xl font-bold text-green-900 mt-2">{{ writingStats.averageScore }}/100</div>
            <div class="text-sm text-gray-600">Puntuación promedio</div>
          </div>
          <div class="card p-6 border rounded-lg bg-purple-50">
            <h4 class="text-lg font-semibold text-purple-700">🎯 Mejor Puntuación</h4>
            <div class="text-3xl font-bold text-purple-900 mt-2">{{ writingStats.bestScore }}/100</div>
            <div class="text-sm text-gray-600">Máximo alcanzado</div>
          </div>
        </div>

        <!-- Skills Progress -->
        <div class="mt-8 card p-6 border rounded-lg">
          <h4 class="text-lg font-semibold text-gray-800 mb-6">📊 Progreso por Habilidad</h4>
          <div class="space-y-4">
            <div>
              <div class="flex justify-between text-sm font-medium text-gray-700 mb-1">
                <span>Gramática</span>
                <span>{{ writingStats.grammarAverage }}/100</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-green-600 h-2 rounded-full" :style="`width: ${writingStats.grammarAverage}%`"></div>
              </div>
            </div>
            <div>
              <div class="flex justify-between text-sm font-medium text-gray-700 mb-1">
                <span>Vocabulario</span>
                <span>{{ writingStats.vocabularyAverage }}/100</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-purple-600 h-2 rounded-full" :style="`width: ${writingStats.vocabularyAverage}%`"></div>
              </div>
            </div>
            <div>
              <div class="flex justify-between text-sm font-medium text-gray-700 mb-1">
                <span>Coherencia</span>
                <span>{{ writingStats.coherenceAverage }}/100</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-orange-600 h-2 rounded-full" :style="`width: ${writingStats.coherenceAverage}%`"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
export default {
  name: 'WritingPractice',
  data() {
    return {
      activeTab: 'exercises',
      tabs: [
        { id: 'exercises', name: 'Ejercicios', icon: '✍️' },
        { id: 'history', name: 'Historial', icon: '📚' },
        { id: 'progress', name: 'Progreso', icon: '📈' }
      ],
      
      selectedExercise: null,
      userWriting: '',
      wordCount: 0,
      timeRemaining: 0,
      timer: null,
      isSubmitting: false,
      hasSubmitted: false,
      aiFeedback: null,
      
      writingExercises: [
        {
          id: 1,
          title: "Mi Día Ideal",
          description: "Describe cómo sería tu día perfecto desde que te levantas hasta que te acuestas.",
          prompt: "Escribe sobre tu día ideal. Incluye qué harías, dónde irías, con quién estarías y por qué ese día sería perfecto para ti. Usa diferentes tiempos verbales y conectores.",
          type: "Narrativa Personal",
          difficulty: "Principiante",
          minWords: 150,
          timeLimit: 20
        },
        {
          id: 2,
          title: "Carta a un Amigo",
          description: "Escribe una carta informal contándole a un amigo sobre tus últimas vacaciones.",
          prompt: "Imagina que acabas de regresar de unas vacaciones increíbles. Escribe una carta a tu mejor amigo contándole todo lo que hiciste, los lugares que visitaste y las experiencias más memorables.",
          type: "Carta Informal",
          difficulty: "Intermedio",
          minWords: 200,
          timeLimit: 25
        },
        {
          id: 3,
          title: "Mi Ciudad",
          description: "Describe tu ciudad natal para alguien que nunca la ha visitado.",
          prompt: "Escribe una descripción de tu ciudad para un turista. Incluye los lugares más interesantes, la comida típica, las tradiciones y qué la hace especial.",
          type: "Descripción",
          difficulty: "Intermedio",
          minWords: 180,
          timeLimit: 22
        },
        {
          id: 4,
          title: "Ensayo de Opinión",
          description: "Argumenta sobre la importancia de aprender idiomas extranjeros.",
          prompt: "Escribe un ensayo argumentativo sobre por qué es importante aprender idiomas extranjeros en el mundo actual. Presenta al menos tres argumentos sólidos con ejemplos.",
          type: "Ensayo Argumentativo",
          difficulty: "Avanzado",
          minWords: 300,
          timeLimit: 35
        },
        {
          id: 5,
          title: "Historia Creativa",
          description: "Inventa una historia corta que comience con 'Era una noche muy extraña...'",
          prompt: "Escribe una historia creativa que comience con la frase 'Era una noche muy extraña...'. Desarrolla personajes interesantes y una trama envolvente.",
          type: "Ficción Creativa",
          difficulty: "Avanzado",
          minWords: 250,
          timeLimit: 30
        },
        {
          id: 6,
          title: "Rutina Diaria",
          description: "Describe tu rutina diaria usando diferentes expresiones de tiempo.",
          prompt: "Describe detalladamente tu rutina desde que te levantas hasta que te acuestas. Usa expresiones de tiempo, presente simple y conectores secuenciales.",
          type: "Descripción Rutina",
          difficulty: "Principiante",
          minWords: 120,
          timeLimit: 15
        }
      ],
      
      writingHistory: [],
      writingStats: {
        totalExercises: 0,
        averageScore: 0,
        bestScore: 0,
        grammarAverage: 0,
        vocabularyAverage: 0,
        coherenceAverage: 0
      }
    };
  },
  
  methods: {
    startExercise(exercise) {
      this.selectedExercise = exercise;
      this.userWriting = '';
      this.wordCount = 0;
      this.timeRemaining = exercise.timeLimit * 60;
      this.hasSubmitted = false;
      this.aiFeedback = null;
      this.startTimer();
    },
    
    startTimer() {
      this.timer = setInterval(() => {
        if (this.timeRemaining > 0) {
          this.timeRemaining--;
        } else {
          this.timeUp();
        }
      }, 1000);
    },
    
    timeUp() {
      clearInterval(this.timer);
      if (!this.hasSubmitted && this.wordCount >= this.selectedExercise.minWords) {
        this.submitWriting();
      }
    },
    
    updateWordCount() {
      this.wordCount = this.userWriting.trim().split(/\s+/).filter(word => word.length > 0).length;
    },
    
    clearWriting() {
      if (confirm('¿Estás seguro de que quieres borrar todo el texto?')) {
        this.userWriting = '';
        this.wordCount = 0;
      }
    },
    
    async submitWriting() {
      if (this.wordCount < this.selectedExercise.minWords) {
        alert(`Necesitas escribir al menos ${this.selectedExercise.minWords} palabras.`);
        return;
      }
      
      this.isSubmitting = true;
      this.hasSubmitted = true;
      clearInterval(this.timer);
      
      try {
        // Simulate AI analysis - in a real app this would call an AI API
        await this.simulateAIAnalysis();
        
        // Save to history
        this.saveToHistory();
        
        // Update statistics
        this.updateStats();
        
      } catch (error) {
        console.error('Error during AI analysis:', error);
        alert('Hubo un error al analizar tu escrito. Por favor, intenta de nuevo.');
        this.isSubmitting = false;
        this.hasSubmitted = false;
      }
    },
    
    async simulateAIAnalysis() {
      // Simulate API delay
      await new Promise(resolve => setTimeout(resolve, 3000));
      
      // Generate realistic AI feedback based on writing
      const grammarScore = Math.floor(Math.random() * 30) + 70;
      const vocabularyScore = Math.floor(Math.random() * 25) + 75;
      const coherenceScore = Math.floor(Math.random() * 35) + 65;
      const overallScore = Math.floor((grammarScore + vocabularyScore + coherenceScore) / 3);
      
      this.aiFeedback = {
        overallScore,
        grammarScore,
        vocabularyScore,
        coherenceScore,
        strengths: [
          "Buen uso de conectores para unir ideas",
          "Vocabulario variado y apropiado",
          "Estructura clara y organizada",
          "Uso correcto de tiempos verbales básicos"
        ],
        improvements: [
          "Trabajar en la concordancia entre sujeto y verbo",
          "Ampliar el uso de adjetivos descriptivos",
          "Mejorar la puntuación en oraciones complejas",
          "Variar más la estructura de las oraciones"
        ],
        corrections: [
          {
            error: "Concordancia incorrecta: 'Las personas es muy amable'",
            correction: "Las personas son muy amables",
            explanation: "El verbo debe concordar en número con el sujeto plural"
          },
          {
            error: "Uso incorrecto del artículo: 'la problema'",
            correction: "el problema",
            explanation: "La palabra 'problema' es masculina aunque termine en 'a'"
          }
        ],
        vocabularySuggestions: [
          {
            word: "fascinante",
            definition: "Que atrae irresistiblemente la atención",
            example: "La ciudad tiene una arquitectura fascinante"
          },
          {
            word: "pintoresco",
            definition: "Que resulta atractivo por su belleza o singularidad",
            example: "Visitamos un pueblo pintoresco en las montañas"
          }
        ],
        nextSteps: [
          "Practicar más ejercicios de concordancia gramatical",
          "Leer textos similares para ampliar vocabulario",
          "Intentar escribir oraciones más complejas",
          "Revisar las reglas de puntuación"
        ]
      };
      
      this.isSubmitting = false;
    },
    
    saveToHistory() {
      const historyEntry = {
        id: Date.now(),
        exercise: this.selectedExercise,
        writing: this.userWriting,
        feedback: this.aiFeedback,
        submittedAt: new Date().toISOString(),
        wordCount: this.wordCount,
        timeUsed: this.selectedExercise.timeLimit * 60 - this.timeRemaining
      };
      
      this.writingHistory.unshift(historyEntry);
      
      // Keep only last 20 entries
      if (this.writingHistory.length > 20) {
        this.writingHistory = this.writingHistory.slice(0, 20);
      }
      
      // Save to localStorage
      localStorage.setItem('writingHistory', JSON.stringify(this.writingHistory));
    },
    
    updateStats() {
      if (this.writingHistory.length === 0) return;
      
      this.writingStats.totalExercises = this.writingHistory.length;
      
      const scores = this.writingHistory.map(entry => entry.feedback.overallScore);
      const grammarScores = this.writingHistory.map(entry => entry.feedback.grammarScore);
      const vocabularyScores = this.writingHistory.map(entry => entry.feedback.vocabularyScore);
      const coherenceScores = this.writingHistory.map(entry => entry.feedback.coherenceScore);
      
      this.writingStats.averageScore = Math.round(scores.reduce((sum, score) => sum + score, 0) / scores.length);
      this.writingStats.bestScore = Math.max(...scores);
      this.writingStats.grammarAverage = Math.round(grammarScores.reduce((sum, score) => sum + score, 0) / grammarScores.length);
      this.writingStats.vocabularyAverage = Math.round(vocabularyScores.reduce((sum, score) => sum + score, 0) / vocabularyScores.length);
      this.writingStats.coherenceAverage = Math.round(coherenceScores.reduce((sum, score) => sum + score, 0) / coherenceScores.length);
      
      // Save to localStorage
      localStorage.setItem('writingStats', JSON.stringify(this.writingStats));
    },
    
    retryExercise() {
      this.userWriting = '';
      this.wordCount = 0;
      this.hasSubmitted = false;
      this.aiFeedback = null;
      this.timeRemaining = this.selectedExercise.timeLimit * 60;
      this.startTimer();
    },
    
    selectNewExercise() {
      this.selectedExercise = null;
      this.userWriting = '';
      this.wordCount = 0;
      this.hasSubmitted = false;
      this.aiFeedback = null;
      clearInterval(this.timer);
    },
    
    viewDetails(entry) {
      // Would open a modal or navigate to detail view
      console.log('Viewing details for:', entry);
    },
    
    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },
    
    loadStoredData() {
      // Load writing history
      const storedHistory = localStorage.getItem('writingHistory');
      if (storedHistory) {
        this.writingHistory = JSON.parse(storedHistory);
      }
      
      // Load writing stats
      const storedStats = localStorage.getItem('writingStats');
      if (storedStats) {
        this.writingStats = JSON.parse(storedStats);
      }
    }
  },
  
  mounted() {
    this.loadStoredData();
  },
  
  beforeUnmount() {
    clearInterval(this.timer);
  }
};
</script>