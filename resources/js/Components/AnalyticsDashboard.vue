<template>
  <div class="container mx-auto p-4 md:p-8">
    <h2 class="text-3xl font-bold text-center mb-8">📊 Analytics Dashboard</h2>
    
    <div v-if="loading" class="text-center text-lg">Cargando análisis detallado...</div>
    <div v-else-if="error" class="text-center text-lg text-red-500">Error: {{ error }}</div>
    
    <div v-else class="space-y-8">
      <!-- Basic Stats Overview -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="card p-6 text-center bg-gradient-to-br from-blue-50 to-blue-100">
          <div class="text-3xl mb-2">📚</div>
          <div class="text-2xl font-bold text-blue-600">{{ analytics.basic_stats.total_quizzes }}</div>
          <div class="text-sm text-gray-600">Quizzes Completados</div>
        </div>
        
        <div class="card p-6 text-center bg-gradient-to-br from-green-50 to-green-100">
          <div class="text-3xl mb-2">📈</div>
          <div class="text-2xl font-bold text-green-600">{{ analytics.basic_stats.average_score }}%</div>
          <div class="text-sm text-gray-600">Puntuación Promedio</div>
        </div>
        
        <div class="card p-6 text-center bg-gradient-to-br from-purple-50 to-purple-100">
          <div class="text-3xl mb-2">💯</div>
          <div class="text-2xl font-bold text-purple-600">{{ analytics.basic_stats.perfect_scores }}</div>
          <div class="text-sm text-gray-600">Puntuaciones Perfectas</div>
        </div>
        
        <div class="card p-6 text-center bg-gradient-to-br from-orange-50 to-orange-100">
          <div class="text-3xl mb-2">⚡</div>
          <div class="text-2xl font-bold text-orange-600">{{ analytics.basic_stats.timed_quizzes }}</div>
          <div class="text-sm text-gray-600">Quizzes Cronometrados</div>
        </div>
      </div>

      <!-- Performance Trends -->
      <div class="card p-6">
        <h3 class="text-xl font-semibold mb-4">📈 Tendencias de Rendimiento</h3>
        
        <!-- Weekly Performance Chart -->
        <div class="mb-6">
          <h4 class="text-lg font-medium mb-2">Rendimiento Semanal</h4>
          <div class="bg-gray-50 p-4 rounded-lg">
            <div class="flex justify-between items-end h-48">
              <div 
                v-for="week in analytics.performance_trends.weekly_performance" 
                :key="week.week"
                class="flex-1 mx-1"
              >
                <div class="text-center mb-2">
                  <div 
                    class="bg-blue-500 rounded-t mx-auto"
                    :style="{ height: getBarHeight(week.average_score, 100) + 'px', width: '100%', maxWidth: '40px' }"
                  ></div>
                  <div class="text-xs text-gray-600 mt-1">{{ week.week }}</div>
                  <div class="text-xs font-semibold">{{ Math.round(week.average_score) }}%</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Daily Activity -->
        <div>
          <h4 class="text-lg font-medium mb-2">Actividad Diaria (Últimos 14 días)</h4>
          <div class="flex gap-1 p-4 bg-gray-50 rounded-lg">
            <div 
              v-for="day in analytics.performance_trends.daily_activity" 
              :key="day.date"
              class="flex-1 text-center"
            >
              <div 
                class="w-full h-8 rounded mb-1"
                :class="day.active ? 'bg-green-500' : 'bg-gray-300'"
                :title="day.date + ': ' + day.quizzes + ' quizzes'"
              ></div>
              <div class="text-xs text-gray-600">{{ day.date.split(' ')[1] }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Subject Analysis -->
      <div class="card p-6">
        <h3 class="text-xl font-semibold mb-4">🎯 Análisis por Materia</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div 
            v-for="subject in analytics.subject_analysis" 
            :key="subject.subject"
            class="p-4 border rounded-lg"
            :class="getSubjectCardClass(subject.mastery_level)"
          >
            <h4 class="font-semibold mb-2">{{ subject.subject }}</h4>
            <div class="space-y-1 text-sm">
              <div class="flex justify-between">
                <span>Promedio:</span>
                <span class="font-semibold">{{ subject.average_score }}%</span>
              </div>
              <div class="flex justify-between">
                <span>Mejor:</span>
                <span class="font-semibold">{{ subject.best_score }}%</span>
              </div>
              <div class="flex justify-between">
                <span>Intentos:</span>
                <span class="font-semibold">{{ subject.attempts }}</span>
              </div>
              <div class="flex justify-between">
                <span>Perfectos:</span>
                <span class="font-semibold">{{ subject.perfect_scores }}</span>
              </div>
              <div class="mt-2 text-center">
                <span 
                  class="px-2 py-1 rounded-full text-xs font-semibold"
                  :class="getMasteryBadgeClass(subject.mastery_level)"
                >
                  {{ subject.mastery_level }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Learning Insights -->
      <div class="card p-6">
        <h3 class="text-xl font-semibold mb-4">💡 Insights de Aprendizaje</h3>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Insights -->
          <div>
            <h4 class="font-medium mb-3">Observaciones</h4>
            <div class="space-y-3">
              <div 
                v-for="insight in analytics.learning_insights" 
                :key="insight.title"
                class="p-3 rounded-lg border-l-4"
                :class="getInsightClass(insight.type)"
              >
                <div class="font-medium">{{ insight.title }}</div>
                <div class="text-sm mt-1">{{ insight.message }}</div>
              </div>
            </div>
          </div>

          <!-- Goals -->
          <div>
            <h4 class="font-medium mb-3">Objetivos Sugeridos</h4>
            <div class="space-y-3">
              <div 
                v-for="goal in analytics.goals" 
                :key="goal.title"
                class="p-3 bg-gray-50 rounded-lg"
              >
                <div class="flex justify-between items-center mb-2">
                  <div class="font-medium">{{ goal.title }}</div>
                  <div class="text-sm text-gray-600">{{ Math.round(goal.progress) }}%</div>
                </div>
                <div class="text-sm text-gray-600 mb-2">{{ goal.description }}</div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    class="bg-blue-600 h-2 rounded-full"
                    :style="{ width: goal.progress + '%' }"
                  ></div>
                </div>
                <div class="text-xs text-gray-600 mt-1">
                  {{ goal.current }} / {{ goal.target }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Streak & Time Analysis -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Streak Analysis -->
        <div class="card p-6">
          <h3 class="text-xl font-semibold mb-4">🔥 Análisis de Racha</h3>
          
          <div class="space-y-4">
            <div class="flex justify-between items-center">
              <span>Racha Actual:</span>
              <span class="text-xl font-bold text-orange-600">{{ analytics.streak_analysis.current_streak }} días</span>
            </div>
            <div class="flex justify-between items-center">
              <span>Racha Más Larga:</span>
              <span class="text-xl font-bold text-red-600">{{ analytics.streak_analysis.longest_streak }} días</span>
            </div>
            <div class="flex justify-between items-center">
              <span>Días Activos (mes):</span>
              <span class="text-xl font-bold text-green-600">{{ analytics.streak_analysis.active_days_month }}</span>
            </div>
            
            <!-- Consistency Score -->
            <div>
              <div class="flex justify-between mb-1">
                <span>Puntuación de Consistencia:</span>
                <span class="font-semibold">{{ Math.round(analytics.streak_analysis.consistency_score) }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3">
                <div 
                  class="bg-gradient-to-r from-green-400 to-green-600 h-3 rounded-full"
                  :style="{ width: analytics.streak_analysis.consistency_score + '%' }"
                ></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Achievement Progress -->
        <div class="card p-6">
          <h3 class="text-xl font-semibold mb-4">🏆 Progreso de Logros</h3>
          
          <div class="text-center mb-4">
            <div class="text-4xl font-bold text-purple-600 mb-2">
              {{ analytics.achievement_progress.earned }} / {{ analytics.achievement_progress.total_available }}
            </div>
            <div class="text-lg text-gray-600">Logros Desbloqueados</div>
          </div>

          <!-- Achievement Progress Circle -->
          <div class="flex justify-center mb-4">
            <div class="relative w-32 h-32">
              <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 100 100">
                <circle
                  cx="50" cy="50" r="40"
                  stroke="currentColor"
                  stroke-width="8"
                  fill="none"
                  class="text-gray-200"
                />
                <circle
                  cx="50" cy="50" r="40"
                  stroke="currentColor"
                  stroke-width="8"
                  fill="none"
                  stroke-linecap="round"
                  class="text-purple-600"
                  :stroke-dasharray="251.2"
                  :stroke-dashoffset="251.2 - (251.2 * analytics.achievement_progress.completion_percentage / 100)"
                />
              </svg>
              <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-xl font-bold text-purple-600">
                  {{ Math.round(analytics.achievement_progress.completion_percentage) }}%
                </span>
              </div>
            </div>
          </div>

          <div class="text-center text-sm text-gray-600">
            ¡Sigue completando quizzes para desbloquear más logros!
          </div>
        </div>
      </div>

      <!-- Adaptive Learning Insights -->
      <div v-if="adaptiveLearningData" class="card p-6">
        <h3 class="text-xl font-semibold mb-4 flex items-center">
          🧠 Aprendizaje Adaptativo
          <router-link to="/adaptive-learning" class="ml-3 text-sm bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded-lg transition-colors duration-200">
            Ver Detalles
          </router-link>
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <!-- Skill Levels Overview -->
          <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
            <h4 class="font-semibold text-blue-800 mb-2">📊 Habilidades Desarrolladas</h4>
            <div class="space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-green-700">Dominadas:</span>
                <span class="font-semibold">{{ adaptiveLearningData.statistics.mastered_skills }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-yellow-700">En Desarrollo:</span>
                <span class="font-semibold">{{ adaptiveLearningData.statistics.developing_skills }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-red-700">Necesitan Práctica:</span>
                <span class="font-semibold">{{ adaptiveLearningData.statistics.struggling_skills }}</span>
              </div>
            </div>
          </div>

          <!-- Recommendations -->
          <div class="p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg border border-purple-200">
            <h4 class="font-semibold text-purple-800 mb-2">✨ Recomendaciones Activas</h4>
            <div class="text-center">
              <div class="text-2xl font-bold text-purple-600 mb-1">{{ recommendations.length }}</div>
              <div class="text-sm text-purple-700">
                {{ recommendations.length === 0 ? 'Todo al día' : 'Pendientes de revisar' }}
              </div>
              <div v-if="recommendations.length > 0" class="mt-2">
                <div class="text-xs text-purple-600">
                  {{ recommendations.filter(r => r.priority <= 2).length }} de alta prioridad
                </div>
              </div>
            </div>
          </div>

          <!-- Spaced Repetition -->
          <div class="p-4 bg-gradient-to-r from-green-50 to-teal-50 rounded-lg border border-green-200">
            <h4 class="font-semibold text-green-800 mb-2">🔄 Repaso Espaciado</h4>
            <div class="text-center">
              <div class="text-2xl font-bold text-green-600 mb-1">{{ spacedRepetitionData.due_count || 0 }}</div>
              <div class="text-sm text-green-700">
                {{ spacedRepetitionData.due_count === 0 ? 'Todo repasado' : 'Repasos pendientes' }}
              </div>
              <div v-if="spacedRepetitionData.urgent_count > 0" class="mt-2">
                <div class="text-xs text-red-600 font-semibold">
                  {{ spacedRepetitionData.urgent_count }} urgentes
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Top Skill Performance -->
        <div v-if="adaptiveLearningData.skill_levels.length > 0" class="mt-6">
          <h4 class="font-semibold text-gray-800 mb-3">🎯 Rendimiento por Habilidad</h4>
          <div class="space-y-2">
            <div v-for="skill in adaptiveLearningData.skill_levels.slice(0, 5)" 
                 :key="skill.id"
                 class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
              <div class="flex-1">
                <div class="font-medium text-sm">{{ formatSkillTopic(skill.skill_topic) }}</div>
                <div class="flex items-center space-x-2 text-xs text-gray-500">
                  <span>{{ skill.correct_answers }}/{{ skill.total_attempts }}</span>
                  <span>•</span>
                  <span>{{ formatMasteryLevel(skill.mastery_score) }}</span>
                </div>
              </div>
              <div class="flex items-center space-x-3">
                <div class="w-16 bg-gray-200 rounded-full h-2">
                  <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                       :style="{ width: skill.mastery_score + '%' }"></div>
                </div>
                <div class="text-sm font-semibold text-gray-700 w-12">
                  {{ Math.round(skill.mastery_score) }}%
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
import axios from 'axios';

export default {
  name: 'AnalyticsDashboard',
  data() {
    return {
      analytics: null,
      adaptiveLearningData: null,
      recommendations: [],
      spacedRepetitionData: {},
      loading: true,
      error: null
    };
  },
  
  async created() {
    await this.loadAnalytics();
  },
  
  methods: {
    async loadAnalytics() {
      try {
        this.loading = true;
        this.error = null;
        
        // Load main analytics
        const response = await axios.get('/api/analytics');
        this.analytics = response.data.data;
        
        // Load adaptive learning data
        await this.loadAdaptiveLearningData();
        
      } catch (err) {
        this.error = err.message || 'Failed to load analytics data';
        console.error('Error loading analytics:', err);
      } finally {
        this.loading = false;
      }
    },

    async loadAdaptiveLearningData() {
      try {
        // Load skill levels
        const skillResponse = await axios.get('/api/learning/skill-levels');
        this.adaptiveLearningData = skillResponse.data.data;
        
        // Load recommendations
        const recResponse = await axios.get('/api/learning/recommendations');
        this.recommendations = recResponse.data.data.recommendations || [];
        
        // Load spaced repetition data
        const srResponse = await axios.get('/api/learning/spaced-repetition');
        this.spacedRepetitionData = srResponse.data.data;
        
      } catch (error) {
        console.warn('Could not load adaptive learning data:', error);
        // Don't fail the whole component if adaptive learning data fails
        this.adaptiveLearningData = { skill_levels: [], statistics: {} };
        this.recommendations = [];
        this.spacedRepetitionData = { due_count: 0, urgent_count: 0 };
      }
    },
    
    getBarHeight(value, max) {
      const percentage = Math.max(0, Math.min(100, (value / max) * 100));
      return Math.max(4, (percentage / 100) * 120); // Min height 4px, max 120px
    },
    
    getSubjectCardClass(masteryLevel) {
      const classes = {
        'Expert': 'border-green-500 bg-green-50',
        'Advanced': 'border-blue-500 bg-blue-50', 
        'Intermediate': 'border-yellow-500 bg-yellow-50',
        'Beginner': 'border-orange-500 bg-orange-50',
        'Learning': 'border-red-500 bg-red-50'
      };
      return classes[masteryLevel] || 'border-gray-300 bg-gray-50';
    },
    
    getMasteryBadgeClass(masteryLevel) {
      const classes = {
        'Expert': 'bg-green-500 text-white',
        'Advanced': 'bg-blue-500 text-white',
        'Intermediate': 'bg-yellow-500 text-white',
        'Beginner': 'bg-orange-500 text-white',
        'Learning': 'bg-red-500 text-white'
      };
      return classes[masteryLevel] || 'bg-gray-500 text-white';
    },
    
    getInsightClass(type) {
      const classes = {
        'success': 'border-green-500 bg-green-50',
        'info': 'border-blue-500 bg-blue-50',
        'warning': 'border-yellow-500 bg-yellow-50',
        'tip': 'border-purple-500 bg-purple-50'
      };
      return classes[type] || 'border-gray-500 bg-gray-50';
    },

    formatSkillTopic(topic) {
      const topics = {
        'to_be_to_have': 'To Be / To Have',
        'present_simple': 'Presente Simple',
        'prepositions': 'Preposiciones',
        'quantifiers': 'Cuantificadores',
        'wh_questions': 'Preguntas WH',
        'mixed_skills': 'Habilidades Mixtas',
        'grammar_speed': 'Gramática Rápida',
        'daily_life_timed': 'Vida Diaria (Cronometrado)'
      };
      return topics[topic] || topic.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
    },

    formatMasteryLevel(score) {
      if (score >= 90) return 'Experto';
      if (score >= 75) return 'Avanzado';
      if (score >= 60) return 'Intermedio';
      if (score >= 40) return 'Básico';
      return 'Principiante';
    }
  }
};
</script>

<style scoped>
.card {
  transition: all 0.3s ease;
}

.card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}
</style>