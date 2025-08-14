<template>
  <div class="container mx-auto p-4 md:p-8">
    <h2 class="text-3xl font-bold text-center mb-8">📊 Tu Progreso</h2>
    
    <div v-if="loading" class="text-center text-lg">Cargando estadísticas...</div>
    <div v-else-if="error" class="text-center text-lg text-red-500">Error: {{ error }}</div>
    
    <div v-else class="space-y-6">
      <!-- Stats Overview -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="card p-6 text-center bg-gradient-to-br from-yellow-50 to-yellow-100">
          <div class="text-3xl mb-2">💎</div>
          <div class="text-2xl font-bold text-yellow-600">{{ stats.total_points }}</div>
          <div class="text-sm text-gray-600">Puntos Totales</div>
        </div>
        
        <div class="card p-6 text-center bg-gradient-to-br from-green-50 to-green-100">
          <div class="text-3xl mb-2">📚</div>
          <div class="text-2xl font-bold text-green-600">{{ stats.quizzes_completed }}</div>
          <div class="text-sm text-gray-600">Quizzes Completados</div>
        </div>
        
        <div class="card p-6 text-center bg-gradient-to-br from-orange-50 to-orange-100">
          <div class="text-3xl mb-2">🔥</div>
          <div class="text-2xl font-bold text-orange-600">{{ stats.current_streak }}</div>
          <div class="text-sm text-gray-600">Racha Actual (días)</div>
        </div>
        
        <div class="card p-6 text-center bg-gradient-to-br from-purple-50 to-purple-100">
          <div class="text-3xl mb-2">🏆</div>
          <div class="text-2xl font-bold text-purple-600">{{ stats.achievement_count }}</div>
          <div class="text-sm text-gray-600">Logros Desbloqueados</div>
        </div>
      </div>
      
      <!-- Achievements Section -->
      <div class="card p-6">
        <h3 class="text-xl font-semibold mb-4">🏆 Tus Logros</h3>
        
        <div v-if="userAchievements.length === 0" class="text-center text-gray-500 py-8">
          <div class="text-6xl mb-4">🎯</div>
          <p>¡Completa tu primer quiz para ganar logros!</p>
        </div>
        
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div 
            v-for="achievement in userAchievements" 
            :key="achievement.id"
            class="p-4 rounded-lg border-2 border-yellow-200 bg-gradient-to-br from-yellow-50 to-yellow-100"
          >
            <div class="text-center">
              <div class="text-3xl mb-2">{{ achievement.badge_icon }}</div>
              <h4 class="font-semibold text-lg" :style="{ color: achievement.badge_color }">
                {{ achievement.name }}
              </h4>
              <p class="text-sm text-gray-600 mt-1">{{ achievement.description }}</p>
              <p class="text-xs text-gray-500 mt-2">
                {{ formatDate(achievement.earned_at) }}
              </p>
            </div>
          </div>
        </div>
        
        <!-- Available Achievements -->
        <div v-if="availableAchievements.length > 0" class="mt-8">
          <h4 class="text-lg font-semibold mb-4">🎯 Logros Disponibles</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div 
              v-for="achievement in availableAchievements.slice(0, 6)" 
              :key="achievement.id"
              class="p-4 rounded-lg border-2 border-gray-200 bg-gray-50 opacity-75"
            >
              <div class="text-center">
                <div class="text-3xl mb-2 grayscale">{{ achievement.badge_icon }}</div>
                <h4 class="font-semibold text-gray-700">{{ achievement.name }}</h4>
                <p class="text-sm text-gray-600 mt-1">{{ achievement.description }}</p>
                <p class="text-xs text-gray-500 mt-2">{{ getAchievementProgress(achievement) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Points History -->
      <div class="card p-6">
        <h3 class="text-xl font-semibold mb-4">💰 Historial de Puntos</h3>
        
        <div v-if="pointsHistory.length === 0" class="text-center text-gray-500 py-8">
          <div class="text-6xl mb-4">📈</div>
          <p>¡Completa quizzes para comenzar a ganar puntos!</p>
        </div>
        
        <div v-else class="space-y-2 max-h-64 overflow-y-auto">
          <div 
            v-for="(point, index) in pointsHistory" 
            :key="index"
            class="flex justify-between items-center p-3 bg-gray-50 rounded-lg"
          >
            <div class="flex-1">
              <p class="font-medium">{{ point.reason }}</p>
              <p class="text-xs text-gray-500">{{ formatDate(point.earned_at) }}</p>
            </div>
            <div class="text-right">
              <span class="font-bold text-green-600">+{{ point.points }}</span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Leaderboard -->
      <div class="card p-6">
        <h3 class="text-xl font-semibold mb-4">🏅 Clasificación</h3>
        
        <div v-if="leaderboard.length === 0" class="text-center text-gray-500 py-8">
          <div class="text-6xl mb-4">👥</div>
          <p>Cargando clasificación...</p>
        </div>
        
        <div v-else class="space-y-2">
          <div 
            v-for="(player, index) in leaderboard" 
            :key="player.id"
            class="flex items-center p-3 bg-gray-50 rounded-lg"
            :class="{ 'bg-yellow-100 border-2 border-yellow-300': player.id === currentUserId }"
          >
            <div class="w-8 text-center font-bold">
              <span v-if="index === 0">🥇</span>
              <span v-else-if="index === 1">🥈</span>
              <span v-else-if="index === 2">🥉</span>
              <span v-else>{{ index + 1 }}</span>
            </div>
            <div class="flex-1 ml-4">
              <p class="font-medium">{{ player.name }}</p>
              <p class="text-sm text-gray-600">{{ player.achievements_count }} logros</p>
            </div>
            <div class="text-right">
              <span class="font-bold text-blue-600">{{ player.total_points }} pts</span>
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
  name: 'GamificationDashboard',
  data() {
    return {
      loading: true,
      error: null,
      stats: {
        total_points: 0,
        current_streak: 0,
        longest_streak: 0,
        quizzes_completed: 0,
        achievement_count: 0
      },
      userAchievements: [],
      availableAchievements: [],
      pointsHistory: [],
      leaderboard: [],
      currentUserId: null
    };
  },
  
  async created() {
    await this.loadDashboardData();
  },
  
  methods: {
    async loadDashboardData() {
      try {
        this.loading = true;
        this.error = null;
        
        // Get current user
        const userResponse = await axios.get('/api/user');
        this.currentUserId = userResponse.data.id;
        
        // Load all gamification data in parallel
        const [statsResponse, achievementsResponse, allAchievementsResponse, pointsResponse, leaderboardResponse] = await Promise.all([
          axios.get('/api/gamification/stats'),
          axios.get('/api/gamification/achievements'),
          axios.get('/api/public/achievements'),
          axios.get('/api/gamification/points-history'),
          axios.get('/api/public/leaderboard')
        ]);
        
        this.stats = statsResponse.data.data;
        this.userAchievements = achievementsResponse.data.data;
        this.pointsHistory = pointsResponse.data.data;
        this.leaderboard = leaderboardResponse.data.data;
        
        // Filter available achievements (not yet earned)
        const earnedIds = new Set(this.userAchievements.map(a => a.id));
        this.availableAchievements = allAchievementsResponse.data.data.filter(a => !earnedIds.has(a.id));
        
      } catch (err) {
        this.error = err.message || 'Failed to load dashboard data';
        console.error('Error loading dashboard data:', err);
      } finally {
        this.loading = false;
      }
    },
    
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },
    
    getAchievementProgress(achievement) {
      const condition = achievement.condition_value;
      
      switch (achievement.condition_type) {
        case 'quizzes_completed':
          return `Completa ${condition.count} ${condition.count === 1 ? 'quiz' : 'quizzes'}`;
        case 'perfect_score':
          return `Obtén ${condition.score}% en un quiz`;
        case 'points':
          return `Acumula ${condition.total} puntos`;
        case 'streak':
          return `Mantén una racha de ${condition.days} ${condition.days === 1 ? 'día' : 'días'}`;
        case 'course_completion':
          return 'Completa todos los quizzes del curso';
        default:
          return 'Condición especial';
      }
    }
  }
};
</script>

<style scoped>
.grayscale {
  filter: grayscale(100%);
}
</style>