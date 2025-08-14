<template>
  <div class="adaptive-learning-dashboard min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 p-4">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">🧠 Aprendizaje Inteligente</h1>
        <p class="text-gray-600">Recomendaciones personalizadas basadas en tu progreso</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-indigo-600 mx-auto mb-4"></div>
        <p class="text-gray-600">Analizando tu progreso...</p>
      </div>

      <!-- Main Content -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recommendations Panel -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Personalized Recommendations -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                ✨ Recomendaciones para Ti
                <span v-if="recommendations.length > 0" class="ml-2 bg-indigo-100 text-indigo-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                  {{ recommendations.length }}
                </span>
              </h2>
              <button @click="refreshRecommendations" 
                      class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                <span>🔄</span>
                <span>Actualizar</span>
              </button>
            </div>

            <div v-if="recommendations.length === 0" class="text-center py-12">
              <div class="text-6xl mb-4">📚</div>
              <h3 class="text-lg font-semibold text-gray-700 mb-2">¡Todo al día!</h3>
              <p class="text-gray-500">No tienes recomendaciones pendientes. ¡Sigue practicando para obtener nuevas sugerencias!</p>
            </div>

            <div v-else class="space-y-4">
              <div v-for="recommendation in recommendations" 
                   :key="recommendation.id"
                   class="recommendation-card border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200"
                   :class="getPriorityCardClass(recommendation.priority)">
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-2">
                      <span class="text-2xl">{{ recommendation.icon }}</span>
                      <span class="text-xs font-semibold px-2 py-1 rounded-full"
                            :class="getPriorityBadgeClass(recommendation.priority)">
                        {{ recommendation.priority_label }}
                      </span>
                      <span class="text-xs text-gray-500">{{ formatRecommendationType(recommendation.recommendation_type) }}</span>
                    </div>
                    
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ recommendation.title }}</h3>
                    <p class="text-gray-600 mb-3">{{ recommendation.description }}</p>
                    
                    <div class="flex items-center space-x-2 text-sm text-gray-500 mb-3">
                      <span>⚡ Confianza: {{ recommendation.confidence_score }}%</span>
                      <span v-if="recommendation.expires_at">• ⏰ Vence: {{ formatExpiryDate(recommendation.expires_at) }}</span>
                    </div>
                  </div>

                  <div class="flex flex-col space-y-2 ml-4">
                    <button @click="markAsViewed(recommendation.id)" 
                            v-if="!recommendation.viewed_at"
                            class="bg-blue-100 hover:bg-blue-200 text-blue-800 px-3 py-1 rounded-lg text-sm transition-colors duration-200">
                      👁️ Ver
                    </button>
                    <button @click="markAsCompleted(recommendation.id)"
                            class="bg-green-100 hover:bg-green-200 text-green-800 px-3 py-1 rounded-lg text-sm transition-colors duration-200">
                      ✅ Completar
                    </button>
                    <button @click="dismissRecommendation(recommendation.id)"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-600 px-3 py-1 rounded-lg text-sm transition-colors duration-200">
                      ❌ Descartar
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Spaced Repetition Schedule -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
              🔄 Repaso Programado
              <span v-if="spacedRepetition.due_count > 0" class="ml-2 bg-orange-100 text-orange-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                {{ spacedRepetition.due_count }} pendientes
              </span>
            </h2>

            <div v-if="spacedRepetition.schedule.length === 0" class="text-center py-8">
              <div class="text-4xl mb-2">✨</div>
              <p class="text-gray-600">No hay repasos pendientes. ¡Excelente trabajo!</p>
            </div>

            <div v-else class="space-y-3">
              <div v-for="item in spacedRepetition.schedule.slice(0, 5)" 
                   :key="item.skill.id"
                   class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                <div class="flex-1">
                  <h4 class="font-semibold text-gray-800">{{ formatSkillTopic(item.skill.skill_topic) }}</h4>
                  <div class="flex items-center space-x-4 text-sm text-gray-500 mt-1">
                    <span>📊 Dominio: {{ Math.round(item.skill.mastery_score) }}%</span>
                    <span>⏰ Vence: {{ formatDate(item.due_date) }}</span>
                    <span :class="getUrgencyClass(item.urgency)">
                      🔥 Urgencia: {{ item.urgency }}/100
                    </span>
                  </div>
                </div>
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm transition-colors duration-200">
                  Practicar Ahora
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Skill Levels Panel -->
        <div class="space-y-6">
          <!-- Skill Overview -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
              📈 Niveles de Habilidad
            </h2>

            <div v-if="skillLevels.skill_levels.length === 0" class="text-center py-6">
              <div class="text-3xl mb-2">🌱</div>
              <p class="text-gray-600 text-sm">Completa tu primer quiz para ver tu progreso</p>
            </div>

            <div v-else class="space-y-4">
              <!-- Statistics Cards -->
              <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="bg-gradient-to-r from-green-400 to-blue-500 rounded-lg p-3 text-white text-center">
                  <div class="text-lg font-bold">{{ skillLevels.statistics.mastered_skills }}</div>
                  <div class="text-xs opacity-90">Dominadas</div>
                </div>
                <div class="bg-gradient-to-r from-yellow-400 to-orange-500 rounded-lg p-3 text-white text-center">
                  <div class="text-lg font-bold">{{ skillLevels.statistics.developing_skills }}</div>
                  <div class="text-xs opacity-90">En Desarrollo</div>
                </div>
              </div>

              <!-- Individual Skills -->
              <div class="space-y-3">
                <div v-for="skill in skillLevels.skill_levels.slice(0, 6)" 
                     :key="skill.id"
                     class="border border-gray-200 rounded-lg p-3">
                  <div class="flex items-center justify-between mb-2">
                    <h4 class="font-semibold text-gray-800 text-sm">{{ formatSkillTopic(skill.skill_topic) }}</h4>
                    <span class="text-xs px-2 py-1 rounded-full text-white"
                          :class="getDifficultyColor(skill.difficulty_level)">
                      {{ skill.difficulty_level }}
                    </span>
                  </div>
                  
                  <div class="mb-2">
                    <div class="flex justify-between text-xs text-gray-600 mb-1">
                      <span>Dominio</span>
                      <span>{{ Math.round(skill.mastery_score) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                      <div class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                           :style="{ width: skill.mastery_score + '%' }"></div>
                    </div>
                  </div>
                  
                  <div class="flex justify-between text-xs text-gray-500">
                    <span>{{ skill.correct_answers }}/{{ skill.total_attempts }} correcto</span>
                    <span>{{ formatMasteryLevel(skill.mastery_score) }}</span>
                  </div>
                </div>
              </div>

              <!-- Overall Progress -->
              <div class="border-t pt-4">
                <div class="text-center">
                  <div class="text-2xl font-bold text-indigo-600 mb-1">
                    {{ Math.round(skillLevels.statistics.average_mastery) }}%
                  </div>
                  <div class="text-sm text-gray-600">Dominio Promedio</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Learning Insights -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
              🎯 Perspectivas Rápidas
            </h2>

            <div class="space-y-4">
              <div v-if="insights.learning_velocity" class="p-3 bg-blue-50 rounded-lg">
                <div class="font-semibold text-blue-800 mb-1">Velocidad de Aprendizaje</div>
                <div class="text-sm text-blue-700">{{ insights.learning_velocity.description }}</div>
              </div>

              <div v-if="insights.performance_trends && insights.performance_trends.length > 0" class="p-3 bg-green-50 rounded-lg">
                <div class="font-semibold text-green-800 mb-1">Tendencia de Mejora</div>
                <div class="text-sm text-green-700">
                  {{ getImprovingSkillsCount() }} habilidades mejorando
                </div>
              </div>

              <div v-if="insights.mastery_predictions && insights.mastery_predictions.length > 0" class="p-3 bg-purple-50 rounded-lg">
                <div class="font-semibold text-purple-800 mb-1">Predicción</div>
                <div class="text-sm text-purple-700">
                  {{ insights.mastery_predictions[0].skill_topic }}: 
                  {{ insights.mastery_predictions[0].estimated_days_to_mastery }} días para dominar
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
  name: 'AdaptiveLearning',
  data() {
    return {
      loading: true,
      recommendations: [],
      skillLevels: {
        skill_levels: [],
        statistics: {}
      },
      spacedRepetition: {
        schedule: [],
        due_count: 0,
        urgent_count: 0
      },
      insights: {}
    }
  },
  mounted() {
    this.loadDashboardData();
  },
  methods: {
    async loadDashboardData() {
      this.loading = true;
      try {
        await Promise.all([
          this.loadRecommendations(),
          this.loadSkillLevels(),
          this.loadSpacedRepetition(),
          this.loadInsights()
        ]);
      } catch (error) {
        console.error('Error loading adaptive learning data:', error);
      } finally {
        this.loading = false;
      }
    },

    async loadRecommendations() {
      try {
        const response = await axios.get('/api/learning/recommendations');
        this.recommendations = response.data.data.recommendations;
      } catch (error) {
        console.error('Error loading recommendations:', error);
      }
    },

    async loadSkillLevels() {
      try {
        const response = await axios.get('/api/learning/skill-levels');
        this.skillLevels = response.data.data;
      } catch (error) {
        console.error('Error loading skill levels:', error);
      }
    },

    async loadSpacedRepetition() {
      try {
        const response = await axios.get('/api/learning/spaced-repetition');
        this.spacedRepetition = response.data.data;
      } catch (error) {
        console.error('Error loading spaced repetition:', error);
      }
    },

    async loadInsights() {
      try {
        const response = await axios.get('/api/learning/insights');
        this.insights = response.data.data;
      } catch (error) {
        console.error('Error loading insights:', error);
      }
    },

    async refreshRecommendations() {
      await this.loadRecommendations();
    },

    async markAsViewed(recommendationId) {
      try {
        await axios.post(`/api/learning/recommendations/${recommendationId}/viewed`);
        await this.loadRecommendations();
      } catch (error) {
        console.error('Error marking recommendation as viewed:', error);
      }
    },

    async markAsCompleted(recommendationId) {
      try {
        await axios.post(`/api/learning/recommendations/${recommendationId}/completed`);
        await this.loadRecommendations();
      } catch (error) {
        console.error('Error marking recommendation as completed:', error);
      }
    },

    async dismissRecommendation(recommendationId) {
      try {
        await axios.delete(`/api/learning/recommendations/${recommendationId}`);
        await this.loadRecommendations();
      } catch (error) {
        console.error('Error dismissing recommendation:', error);
      }
    },

    getPriorityCardClass(priority) {
      const classes = {
        1: 'border-l-4 border-red-500 bg-red-50',
        2: 'border-l-4 border-orange-500 bg-orange-50',
        3: 'border-l-4 border-yellow-500 bg-yellow-50',
        4: 'border-l-4 border-blue-500 bg-blue-50',
        5: 'border-l-4 border-gray-500 bg-gray-50'
      };
      return classes[priority] || 'border-l-4 border-gray-500 bg-gray-50';
    },

    getPriorityBadgeClass(priority) {
      const classes = {
        1: 'bg-red-100 text-red-800',
        2: 'bg-orange-100 text-orange-800',
        3: 'bg-yellow-100 text-yellow-800',
        4: 'bg-blue-100 text-blue-800',
        5: 'bg-gray-100 text-gray-800'
      };
      return classes[priority] || 'bg-gray-100 text-gray-800';
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

    getUrgencyClass(urgency) {
      if (urgency >= 80) return 'text-red-600 font-semibold';
      if (urgency >= 60) return 'text-orange-600 font-semibold';
      return 'text-gray-600';
    },

    formatRecommendationType(type) {
      const types = {
        'skill_improvement': 'Mejora de Habilidad',
        'review_reminder': 'Recordatorio de Repaso',
        'new_content': 'Contenido Nuevo',
        'difficulty_adjustment': 'Ajuste de Dificultad',
        'streak_motivation': 'Motivación de Racha'
      };
      return types[type] || type;
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
    },

    formatExpiryDate(dateString) {
      const date = new Date(dateString);
      const now = new Date();
      const diffHours = Math.ceil((date - now) / (1000 * 60 * 60));
      
      if (diffHours < 24) return `${diffHours}h`;
      return `${Math.ceil(diffHours / 24)}d`;
    },

    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('es-ES', { 
        month: 'short', 
        day: 'numeric' 
      });
    },

    getImprovingSkillsCount() {
      if (!this.insights.performance_trends) return 0;
      return this.insights.performance_trends.filter(skill => 
        skill.performance_trend === 'improving'
      ).length;
    }
  }
}
</script>

<style scoped>
.recommendation-card {
  transition: all 0.2s ease-in-out;
}

.recommendation-card:hover {
  transform: translateY(-2px);
}

.adaptive-learning-dashboard {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}
</style>