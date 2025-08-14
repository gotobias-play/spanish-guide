<template>
  <div class="container mx-auto p-4 md:p-8">
    <h2 class="text-3xl font-bold text-center mb-6">🧠 Análisis Inteligente de Errores</h2>
    
    <!-- Statistics Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="card p-6 border rounded-lg bg-blue-50">
        <h3 class="text-lg font-semibold text-blue-700">📊 Total Errores</h3>
        <div class="text-3xl font-bold text-blue-900 mt-2">{{ mistakeStats.totalMistakes }}</div>
        <div class="text-sm text-gray-600">Detectados y analizados</div>
      </div>
      <div class="card p-6 border rounded-lg bg-red-50">
        <h3 class="text-lg font-semibold text-red-700">🔄 Errores Recurrentes</h3>
        <div class="text-3xl font-bold text-red-900 mt-2">{{ mistakeStats.recurringMistakes }}</div>
        <div class="text-sm text-gray-600">Necesitan atención</div>
      </div>
      <div class="card p-6 border rounded-lg bg-green-50">
        <h3 class="text-lg font-semibold text-green-700">✅ Errores Corregidos</h3>
        <div class="text-3xl font-bold text-green-900 mt-2">{{ mistakeStats.improvedMistakes }}</div>
        <div class="text-sm text-gray-600">Ya superados</div>
      </div>
      <div class="card p-6 border rounded-lg bg-purple-50">
        <h3 class="text-lg font-semibold text-purple-700">📈 Tasa de Mejora</h3>
        <div class="text-3xl font-bold text-purple-900 mt-2">{{ mistakeStats.improvementRate }}%</div>
        <div class="text-sm text-gray-600">Progreso general</div>
      </div>
    </div>

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

    <!-- Error Patterns Tab -->
    <div v-if="activeTab === 'patterns'" class="tab-panel">
      <div class="max-w-6xl mx-auto">
        <h3 class="text-2xl font-bold mb-6">🔍 Patrones de Errores Detectados</h3>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          
          <!-- Grammar Error Patterns -->
          <div class="card p-6 border rounded-lg">
            <h4 class="text-xl font-semibold text-red-700 mb-4">📝 Errores Gramaticales</h4>
            <div class="space-y-4">
              <div v-for="pattern in grammarPatterns" :key="pattern.type" 
                   class="p-4 bg-red-50 rounded border-l-4 border-red-400">
                <div class="flex justify-between items-center mb-2">
                  <h5 class="font-semibold text-red-800">{{ pattern.title }}</h5>
                  <span class="text-sm bg-red-200 text-red-800 px-2 py-1 rounded">{{ pattern.frequency }}x</span>
                </div>
                <p class="text-gray-700 text-sm mb-2">{{ pattern.description }}</p>
                <div class="text-xs text-gray-600">
                  <span class="font-medium">Ejemplo:</span> {{ pattern.example }}
                </div>
                <div class="mt-2">
                  <span class="text-xs px-2 py-1 bg-orange-200 text-orange-800 rounded">
                    Severidad: {{ pattern.severity }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Vocabulary Error Patterns -->
          <div class="card p-6 border rounded-lg">
            <h4 class="text-xl font-semibold text-purple-700 mb-4">📚 Errores de Vocabulario</h4>
            <div class="space-y-4">
              <div v-for="pattern in vocabularyPatterns" :key="pattern.type" 
                   class="p-4 bg-purple-50 rounded border-l-4 border-purple-400">
                <div class="flex justify-between items-center mb-2">
                  <h5 class="font-semibold text-purple-800">{{ pattern.title }}</h5>
                  <span class="text-sm bg-purple-200 text-purple-800 px-2 py-1 rounded">{{ pattern.frequency }}x</span>
                </div>
                <p class="text-gray-700 text-sm mb-2">{{ pattern.description }}</p>
                <div class="text-xs text-gray-600">
                  <span class="font-medium">Ejemplo:</span> {{ pattern.example }}
                </div>
                <div class="mt-2">
                  <span class="text-xs px-2 py-1 bg-orange-200 text-orange-800 rounded">
                    Severidad: {{ pattern.severity }}
                  </span>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Personalized Remediation Tab -->
    <div v-if="activeTab === 'remediation'" class="tab-panel">
      <div class="max-w-6xl mx-auto">
        <h3 class="text-2xl font-bold mb-6">🎯 Plan de Remediación Personalizado</h3>
        
        <!-- Priority Areas -->
        <div class="card p-6 border rounded-lg mb-6 bg-yellow-50">
          <h4 class="text-xl font-semibold text-yellow-800 mb-4">⚠️ Áreas Prioritarias</h4>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div v-for="priority in priorityAreas" :key="priority.area" 
                 class="p-4 bg-white rounded border border-yellow-300">
              <h5 class="font-semibold text-yellow-800">{{ priority.area }}</h5>
              <p class="text-sm text-gray-600 mb-2">{{ priority.description }}</p>
              <div class="text-xs text-yellow-700">
                Errores: {{ priority.errorCount }} | Impacto: {{ priority.impact }}
              </div>
            </div>
          </div>
        </div>

        <!-- Recommended Exercises -->
        <div class="space-y-6">
          <div v-for="recommendation in remediationPlan" :key="recommendation.id" 
               class="card p-6 border rounded-lg">
            <div class="flex justify-between items-start mb-4">
              <div>
                <h4 class="text-xl font-semibold text-blue-700">{{ recommendation.title }}</h4>
                <p class="text-gray-600">{{ recommendation.description }}</p>
              </div>
              <div class="text-right">
                <span class="text-sm bg-blue-200 text-blue-800 px-2 py-1 rounded">
                  {{ recommendation.priority }}
                </span>
              </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <div>
                <h5 class="font-medium text-gray-800 mb-2">🎯 Objetivos:</h5>
                <ul class="text-sm text-gray-600 space-y-1">
                  <li v-for="objective in recommendation.objectives" :key="objective" 
                      class="flex items-start">
                    <span class="text-green-600 mr-2">•</span>
                    {{ objective }}
                  </li>
                </ul>
              </div>
              <div>
                <h5 class="font-medium text-gray-800 mb-2">📝 Ejercicios Sugeridos:</h5>
                <ul class="text-sm text-gray-600 space-y-1">
                  <li v-for="exercise in recommendation.exercises" :key="exercise" 
                      class="flex items-start">
                    <span class="text-blue-600 mr-2">→</span>
                    {{ exercise }}
                  </li>
                </ul>
              </div>
            </div>
            
            <div class="flex justify-between items-center">
              <div class="text-sm text-gray-500">
                Tiempo estimado: {{ recommendation.estimatedTime }}
              </div>
              <button @click="startRemediation(recommendation)" 
                      class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                🚀 Comenzar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Progress Tracking Tab -->
    <div v-if="activeTab === 'progress'" class="tab-panel">
      <div class="max-w-6xl mx-auto">
        <h3 class="text-2xl font-bold mb-6">📊 Seguimiento de Progreso</h3>
        
        <!-- Progress Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          <div class="card p-6 border rounded-lg">
            <h4 class="text-lg font-semibold text-green-700 mb-4">📈 Mejoras Identificadas</h4>
            <div class="space-y-3">
              <div v-for="improvement in improvements" :key="improvement.area" 
                   class="flex justify-between items-center p-3 bg-green-50 rounded">
                <div>
                  <div class="font-medium text-green-800">{{ improvement.area }}</div>
                  <div class="text-sm text-gray-600">{{ improvement.description }}</div>
                </div>
                <div class="text-right">
                  <div class="text-green-700 font-bold">+{{ improvement.improvement }}%</div>
                  <div class="text-xs text-gray-500">{{ improvement.timeFrame }}</div>
                </div>
              </div>
            </div>
          </div>

          <div class="card p-6 border rounded-lg">
            <h4 class="text-lg font-semibold text-orange-700 mb-4">⚠️ Áreas que Necesitan Atención</h4>
            <div class="space-y-3">
              <div v-for="concern in concernAreas" :key="concern.area" 
                   class="flex justify-between items-center p-3 bg-orange-50 rounded">
                <div>
                  <div class="font-medium text-orange-800">{{ concern.area }}</div>
                  <div class="text-sm text-gray-600">{{ concern.issue }}</div>
                </div>
                <div class="text-right">
                  <div class="text-orange-700 font-bold">{{ concern.errorRate }}%</div>
                  <div class="text-xs text-gray-500">Tasa de error</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Learning Curve -->
        <div class="card p-6 border rounded-lg">
          <h4 class="text-lg font-semibold text-gray-800 mb-4">📉 Curva de Aprendizaje</h4>
          <div class="space-y-4">
            <div v-for="week in learningCurve" :key="week.week" 
                 class="flex items-center space-x-4">
              <div class="w-16 text-sm font-medium text-gray-600">Semana {{ week.week }}</div>
              <div class="flex-1 bg-gray-200 rounded-full h-4">
                <div class="h-4 rounded-full bg-gradient-to-r from-red-400 to-green-400" 
                     :style="`width: ${week.accuracy}%`"></div>
              </div>
              <div class="w-16 text-sm font-bold text-gray-800">{{ week.accuracy }}%</div>
              <div class="w-20 text-xs text-gray-500">{{ week.mistakes }} errores</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- AI Insights Tab -->
    <div v-if="activeTab === 'insights'" class="tab-panel">
      <div class="max-w-6xl mx-auto">
        <h3 class="text-2xl font-bold mb-6">🤖 Insights de Inteligencia Artificial</h3>
        
        <!-- AI Analysis -->
        <div class="card p-6 border rounded-lg mb-6 bg-gradient-to-r from-blue-50 to-purple-50">
          <h4 class="text-xl font-semibold text-purple-800 mb-4">🧠 Análisis IA de tu Progreso</h4>
          <div class="space-y-4">
            <div v-for="insight in aiInsights" :key="insight.id" 
                 class="p-4 bg-white rounded border border-purple-200">
              <div class="flex items-start space-x-3">
                <div class="text-2xl">{{ insight.icon }}</div>
                <div class="flex-1">
                  <h5 class="font-semibold text-purple-800">{{ insight.title }}</h5>
                  <p class="text-gray-700 mb-2">{{ insight.description }}</p>
                  <div class="text-sm text-purple-600">
                    <span class="font-medium">Recomendación:</span> {{ insight.recommendation }}
                  </div>
                  <div class="mt-2 text-xs text-gray-500">
                    Confianza: {{ insight.confidence }}% | Basado en {{ insight.dataPoints }} puntos de datos
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Predictive Analysis -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="card p-6 border rounded-lg">
            <h4 class="text-lg font-semibold text-blue-700 mb-4">🔮 Predicciones de Aprendizaje</h4>
            <div class="space-y-3">
              <div v-for="prediction in predictions" :key="prediction.skill" 
                   class="p-3 bg-blue-50 rounded">
                <div class="flex justify-between items-center mb-1">
                  <span class="font-medium text-blue-800">{{ prediction.skill }}</span>
                  <span class="text-sm text-blue-600">{{ prediction.timeToMaster }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div class="bg-blue-600 h-2 rounded-full" 
                       :style="`width: ${prediction.currentLevel}%`"></div>
                </div>
                <div class="text-xs text-gray-600 mt-1">
                  Progreso actual: {{ prediction.currentLevel }}%
                </div>
              </div>
            </div>
          </div>

          <div class="card p-6 border rounded-lg">
            <h4 class="text-lg font-semibold text-green-700 mb-4">✨ Sugerencias Personalizadas</h4>
            <div class="space-y-3">
              <div v-for="suggestion in personalizedSuggestions" :key="suggestion.id" 
                   class="p-3 bg-green-50 rounded border-l-4 border-green-400">
                <h5 class="font-medium text-green-800">{{ suggestion.title }}</h5>
                <p class="text-sm text-gray-700 mb-2">{{ suggestion.description }}</p>
                <div class="flex justify-between items-center">
                  <span class="text-xs text-green-600">Impacto: {{ suggestion.impact }}</span>
                  <button @click="applySuggestion(suggestion)" 
                          class="px-3 py-1 bg-green-500 text-white rounded text-xs hover:bg-green-600">
                    Aplicar
                  </button>
                </div>
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
  name: 'MistakeAnalysis',
  data() {
    return {
      activeTab: 'patterns',
      tabs: [
        { id: 'patterns', name: 'Patrones', icon: '🔍' },
        { id: 'remediation', name: 'Remediación', icon: '🎯' },
        { id: 'progress', name: 'Progreso', icon: '📊' },
        { id: 'insights', name: 'IA Insights', icon: '🤖' }
      ],
      
      mistakeStats: {
        totalMistakes: 47,
        recurringMistakes: 8,
        improvedMistakes: 23,
        improvementRate: 68
      },
      
      grammarPatterns: [
        {
          type: 'subject_verb_agreement',
          title: 'Concordancia Sujeto-Verbo',
          description: 'Dificultades para hacer concordar el verbo con el sujeto en número y persona.',
          example: '"The students is" → "The students are"',
          frequency: 12,
          severity: 'Alta'
        },
        {
          type: 'verb_tenses',
          title: 'Tiempos Verbales',
          description: 'Uso incorrecto de tiempos verbales, especialmente presente perfecto vs pasado simple.',
          example: '"I go yesterday" → "I went yesterday"',
          frequency: 8,
          severity: 'Media'
        },
        {
          type: 'articles',
          title: 'Uso de Artículos',
          description: 'Omisión o uso incorrecto de artículos definidos e indefinidos.',
          example: '"I am student" → "I am a student"',
          frequency: 15,
          severity: 'Alta'
        }
      ],
      
      vocabularyPatterns: [
        {
          type: 'false_friends',
          title: 'Falsos Amigos',
          description: 'Uso incorrecto de palabras que se parecen al español pero tienen diferente significado.',
          example: '"I am embarazada" → "I am pregnant"',
          frequency: 6,
          severity: 'Media'
        },
        {
          type: 'word_order',
          title: 'Orden de Palabras',
          description: 'Aplicación del orden de palabras del español en estructuras inglesas.',
          example: '"The car red" → "The red car"',
          frequency: 9,
          severity: 'Alta'
        },
        {
          type: 'collocations',
          title: 'Colocaciones',
          description: 'Combinaciones incorrectas de palabras que suenan naturales en español.',
          example: '"Make a photo" → "Take a photo"',
          frequency: 11,
          severity: 'Media'
        }
      ],
      
      priorityAreas: [
        {
          area: 'Artículos',
          description: 'Uso correcto de a, an, the',
          errorCount: 15,
          impact: 'Alto'
        },
        {
          area: 'Concordancia',
          description: 'Sujeto-verbo agreement',
          errorCount: 12,
          impact: 'Alto'
        },
        {
          area: 'Colocaciones',
          description: 'Combinaciones naturales',
          errorCount: 11,
          impact: 'Medio'
        }
      ],
      
      remediationPlan: [
        {
          id: 1,
          title: 'Dominio de Artículos en Inglés',
          description: 'Plan intensivo para dominar el uso de a, an, y the en diferentes contextos.',
          priority: 'Alta',
          objectives: [
            'Identificar cuándo usar artículos definidos e indefinidos',
            'Reconocer sustantivos contables y no contables',
            'Aplicar reglas de artículos con nombres propios'
          ],
          exercises: [
            'Ejercicios de fill-in-the-blank con artículos',
            'Análisis de textos identificando uso de artículos',
            'Práctica de conversación enfocada en artículos'
          ],
          estimatedTime: '2-3 semanas'
        },
        {
          id: 2,
          title: 'Concordancia Sujeto-Verbo Perfecta',
          description: 'Ejercicios específicos para automatizar la concordancia correcta.',
          priority: 'Alta',
          objectives: [
            'Identificar el sujeto real de la oración',
            'Aplicar concordancia con sujetos compuestos',
            'Manejar casos especiales de concordancia'
          ],
          exercises: [
            'Ejercicios de transformación de oraciones',
            'Identificación de errores en textos',
            'Práctica oral con retroalimentación inmediata'
          ],
          estimatedTime: '1-2 semanas'
        }
      ],
      
      improvements: [
        {
          area: 'Pronunciación',
          description: 'Mejora significativa en sonidos vocálicos',
          improvement: 25,
          timeFrame: 'Últimas 4 semanas'
        },
        {
          area: 'Tiempo Presente',
          description: 'Uso correcto del presente simple y continuo',
          improvement: 40,
          timeFrame: 'Último mes'
        },
        {
          area: 'Vocabulario Básico',
          description: 'Ampliación del vocabulario activo',
          improvement: 35,
          timeFrame: 'Últimas 6 semanas'
        }
      ],
      
      concernAreas: [
        {
          area: 'Artículos',
          issue: 'Omisión frecuente de artículos indefinidos',
          errorRate: 32
        },
        {
          area: 'Preposiciones',
          issue: 'Confusión entre in, on, at',
          errorRate: 28
        },
        {
          area: 'Pasado Simple',
          issue: 'Irregularidades verbales no dominadas',
          errorRate: 24
        }
      ],
      
      learningCurve: [
        { week: 1, accuracy: 45, mistakes: 23 },
        { week: 2, accuracy: 52, mistakes: 19 },
        { week: 3, accuracy: 58, mistakes: 17 },
        { week: 4, accuracy: 65, mistakes: 14 },
        { week: 5, accuracy: 71, mistakes: 12 },
        { week: 6, accuracy: 76, mistakes: 9 },
        { week: 7, accuracy: 82, mistakes: 7 },
        { week: 8, accuracy: 85, mistakes: 6 }
      ],
      
      aiInsights: [
        {
          id: 1,
          icon: '🎯',
          title: 'Patrón de Mejora Detectado',
          description: 'Tu precisión en el uso de artículos ha mejorado un 40% en las últimas 3 semanas. El patrón sugiere que estás internalizando las reglas.',
          recommendation: 'Continúa con ejercicios de artículos pero aumenta la complejidad incluyendo contextos académicos.',
          confidence: 92,
          dataPoints: 156
        },
        {
          id: 2,
          icon: '⚠️',
          title: 'Punto de Atención Identificado',
          description: 'Hay una tendencia a cometer más errores de concordancia en oraciones largas con múltiples cláusulas.',
          recommendation: 'Practica parsing de oraciones complejas antes de abordar ejercicios de concordancia.',
          confidence: 87,
          dataPoints: 89
        },
        {
          id: 3,
          icon: '🚀',
          title: 'Momento Óptimo de Aprendizaje',
          description: 'Los datos muestran que tu retención es 35% mayor durante sesiones de 25-30 minutos por la mañana.',
          recommendation: 'Programa tus sesiones de estudio más desafiantes entre 9:00-11:00 AM.',
          confidence: 78,
          dataPoints: 342
        }
      ],
      
      predictions: [
        {
          skill: 'Uso de Artículos',
          currentLevel: 75,
          timeToMaster: '3-4 semanas'
        },
        {
          skill: 'Concordancia S-V',
          currentLevel: 65,
          timeToMaster: '2-3 semanas'
        },
        {
          skill: 'Tiempos Verbales',
          currentLevel: 45,
          timeToMaster: '6-8 semanas'
        },
        {
          skill: 'Preposiciones',
          currentLevel: 35,
          timeToMaster: '8-10 semanas'
        }
      ],
      
      personalizedSuggestions: [
        {
          id: 1,
          title: 'Sesiones de Micro-Aprendizaje',
          description: 'Dividir las sesiones de 60 minutos en bloques de 25 minutos con descansos de 5 minutos.',
          impact: 'Alto'
        },
        {
          id: 2,
          title: 'Enfoque en Contextos Reales',
          description: 'Practicar gramática usando situaciones de tu trabajo o intereses personales.',
          impact: 'Medio'
        },
        {
          id: 3,
          title: 'Revisión Espaciada',
          description: 'Revisar errores anteriores siguiendo un patrón: 1 día, 3 días, 1 semana, 2 semanas.',
          impact: 'Alto'
        }
      ]
    };
  },
  
  methods: {
    startRemediation(recommendation) {
      alert(`Iniciando plan de remediación: ${recommendation.title}`);
      // In a real app, this would navigate to specific exercises
      console.log('Starting remediation for:', recommendation);
    },
    
    applySuggestion(suggestion) {
      alert(`Aplicando sugerencia: ${suggestion.title}`);
      // In a real app, this would update user preferences
      console.log('Applying suggestion:', suggestion);
    }
  }
};
</script>