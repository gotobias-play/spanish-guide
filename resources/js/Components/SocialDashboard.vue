<template>
  <div class="max-w-6xl mx-auto p-6 space-y-8">
    <!-- Header Section -->
    <div class="text-center">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">👥 Centro Social</h1>
      <p class="text-gray-600">Conecta con amigos y compite en tu aprendizaje de inglés</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-500"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-12">
      <div class="bg-red-50 border border-red-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-red-800 mb-2">Error</h3>
        <p class="text-red-600">{{ error }}</p>
        <button @click="fetchData" class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
          Reintentar
        </button>
      </div>
    </div>

    <!-- Main Content -->
    <div v-else class="space-y-8">
      <!-- Social Stats Overview -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white p-6 rounded-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-blue-100">Amigos</p>
              <p class="text-3xl font-bold">{{ socialData.friends_count }}</p>
            </div>
            <div class="text-4xl opacity-80">👥</div>
          </div>
        </div>

        <div class="bg-gradient-to-r from-orange-500 to-red-600 text-white p-6 rounded-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-orange-100">Solicitudes Pendientes</p>
              <p class="text-3xl font-bold">{{ socialData.pending_requests_count }}</p>
            </div>
            <div class="text-4xl opacity-80">📨</div>
          </div>
        </div>

        <div class="bg-gradient-to-r from-green-500 to-emerald-600 text-white p-6 rounded-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-green-100">Actividades Recientes</p>
              <p class="text-3xl font-bold">{{ socialData.recent_activities.length }}</p>
            </div>
            <div class="text-4xl opacity-80">📈</div>
          </div>
        </div>
      </div>

      <!-- Tab Navigation -->
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
          <button 
            @click="activeTab = 'friends'"
            :class="[
              'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
              activeTab === 'friends' 
                ? 'border-blue-500 text-blue-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            👥 Mis Amigos
          </button>
          <button 
            @click="activeTab = 'requests'"
            :class="[
              'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
              activeTab === 'requests' 
                ? 'border-blue-500 text-blue-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            📨 Solicitudes ({{ socialData.pending_requests_count }})
          </button>
          <button 
            @click="activeTab = 'search'"
            :class="[
              'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
              activeTab === 'search' 
                ? 'border-blue-500 text-blue-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            🔍 Buscar Amigos
          </button>
          <button 
            @click="activeTab = 'feed'"
            :class="[
              'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
              activeTab === 'feed' 
                ? 'border-blue-500 text-blue-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            📰 Actividad Social
          </button>
          <button 
            @click="activeTab = 'leaderboard'"
            :class="[
              'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
              activeTab === 'leaderboard' 
                ? 'border-blue-500 text-blue-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            🏆 Ranking Amigos
          </button>
        </nav>
      </div>

      <!-- Tab Content -->
      <div class="mt-8">
        <!-- Friends Tab -->
        <div v-if="activeTab === 'friends'">
          <h2 class="text-2xl font-bold text-gray-900 mb-6">Mis Amigos ({{ socialData.friends_count }})</h2>
          
          <div v-if="socialData.friends.length === 0" class="text-center py-12">
            <div class="text-6xl mb-4">👥</div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">¡Aún no tienes amigos!</h3>
            <p class="text-gray-600 mb-6">Busca a otros estudiantes y compite con ellos para aprender inglés juntos.</p>
            <button @click="activeTab = 'search'" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
              Buscar Amigos
            </button>
          </div>

          <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div 
              v-for="friend in socialData.friends" 
              :key="friend.id"
              class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-lg transition-all duration-200"
            >
              <div class="flex items-start justify-between mb-4">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">{{ friend.name }}</h3>
                  <div class="text-sm text-gray-600 mt-2 space-y-1">
                    <div class="flex items-center space-x-2">
                      <span>🏆</span>
                      <span>{{ friend.total_points }} puntos</span>
                    </div>
                    <div class="flex items-center space-x-2">
                      <span>🔥</span>
                      <span>{{ friend.current_streak }} día(s) de racha</span>
                    </div>
                    <div class="flex items-center space-x-2">
                      <span>🎓</span>
                      <span>{{ friend.certificates_count }} certificado(s)</span>
                    </div>
                  </div>
                </div>
                <div class="text-2xl">👤</div>
              </div>
              
              <div class="mt-4 pt-4 border-t border-gray-100">
                <button 
                  @click="removeFriend(friend.id, friend.name)"
                  class="w-full px-4 py-2 bg-red-100 text-red-700 text-sm rounded-lg hover:bg-red-200 transition-colors"
                >
                  Eliminar Amistad
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Requests Tab -->
        <div v-if="activeTab === 'requests'">
          <h2 class="text-2xl font-bold text-gray-900 mb-6">Solicitudes de Amistad</h2>
          
          <div v-if="socialData.pending_requests.length === 0" class="text-center py-12">
            <div class="text-6xl mb-4">📨</div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No hay solicitudes pendientes</h3>
            <p class="text-gray-600">Las nuevas solicitudes de amistad aparecerán aquí.</p>
          </div>

          <div v-else class="space-y-4">
            <div 
              v-for="request in socialData.pending_requests" 
              :key="request.id"
              class="bg-white border border-gray-200 rounded-lg p-6"
            >
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">{{ request.requester.name }}</h3>
                  <p class="text-sm text-gray-600">{{ request.requester.total_points }} puntos totales</p>
                  <p class="text-xs text-gray-500 mt-1">Solicitud enviada: {{ formatDate(request.requested_at) }}</p>
                </div>
                
                <div class="flex space-x-3">
                  <button 
                    @click="respondToRequest(request.id, 'accept')"
                    class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors"
                  >
                    Aceptar
                  </button>
                  <button 
                    @click="respondToRequest(request.id, 'decline')"
                    class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition-colors"
                  >
                    Rechazar
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Search Tab -->
        <div v-if="activeTab === 'search'">
          <h2 class="text-2xl font-bold text-gray-900 mb-6">Buscar Nuevos Amigos</h2>
          
          <div class="mb-6">
            <div class="flex space-x-4">
              <input 
                v-model="searchQuery"
                @keyup.enter="searchUsers"
                type="text" 
                placeholder="Buscar usuarios por nombre..."
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <button 
                @click="searchUsers"
                :disabled="searchQuery.length < 2"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 transition-colors"
              >
                Buscar
              </button>
            </div>
          </div>

          <div v-if="searchLoading" class="flex justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
          </div>

          <div v-else-if="searchResults.length === 0 && searchQuery" class="text-center py-8">
            <p class="text-gray-600">No se encontraron usuarios con ese nombre.</p>
          </div>

          <div v-else class="space-y-4">
            <div 
              v-for="user in searchResults" 
              :key="user.id"
              class="bg-white border border-gray-200 rounded-lg p-6"
            >
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">{{ user.name }}</h3>
                  <div class="text-sm text-gray-600 mt-1">
                    <p>{{ user.total_points }} puntos • {{ user.certificates_count }} certificados</p>
                    <p v-if="user.friendship_status" class="text-orange-600 font-medium mt-1">
                      Estado: {{ getFriendshipStatusText(user.friendship_status) }}
                    </p>
                  </div>
                </div>
                
                <div>
                  <button 
                    v-if="user.can_send_request"
                    @click="sendFriendRequest(user.id, user.name)"
                    class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors"
                  >
                    Enviar Solicitud
                  </button>
                  <span v-else class="text-sm text-gray-500">
                    {{ getFriendshipStatusText(user.friendship_status) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Social Feed Tab -->
        <div v-if="activeTab === 'feed'">
          <h2 class="text-2xl font-bold text-gray-900 mb-6">Actividad de Amigos</h2>
          
          <div v-if="socialData.social_feed.length === 0" class="text-center py-12">
            <div class="text-6xl mb-4">📰</div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No hay actividad reciente</h3>
            <p class="text-gray-600">La actividad de tus amigos aparecerá aquí cuando completen quizzes o ganen logros.</p>
          </div>

          <div v-else class="space-y-4">
            <div 
              v-for="activity in socialData.social_feed" 
              :key="activity.id"
              class="bg-white border border-gray-200 rounded-lg p-6"
            >
              <div class="flex items-start space-x-4">
                <div class="text-2xl">{{ activity.icon }}</div>
                <div class="flex-1">
                  <div class="flex items-center space-x-2">
                    <h3 class="font-semibold text-gray-900">{{ activity.user.name }}</h3>
                    <span class="text-gray-600">{{ activity.title }}</span>
                  </div>
                  <p v-if="activity.description" class="text-sm text-gray-600 mt-1">{{ activity.description }}</p>
                  <p class="text-xs text-gray-500 mt-2">{{ activity.time_ago }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Leaderboard Tab -->
        <div v-if="activeTab === 'leaderboard'">
          <h2 class="text-2xl font-bold text-gray-900 mb-6">Ranking de Amigos</h2>
          
          <div v-if="leaderboardLoading" class="flex justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
          </div>
          
          <div v-else-if="leaderboard.length === 0" class="text-center py-12">
            <div class="text-6xl mb-4">🏆</div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">¡Agrega amigos para ver el ranking!</h3>
            <p class="text-gray-600">El ranking de amigos te permitirá comparar tu progreso con el de tus amigos.</p>
          </div>

          <div v-else class="space-y-3">
            <div 
              v-for="(friend, index) in leaderboard" 
              :key="friend.id"
              class="bg-white border border-gray-200 rounded-lg p-6 flex items-center space-x-4"
            >
              <div class="text-2xl font-bold">
                <span v-if="index === 0" class="text-yellow-500">🥇</span>
                <span v-else-if="index === 1" class="text-gray-400">🥈</span>
                <span v-else-if="index === 2" class="text-amber-600">🥉</span>
                <span v-else class="text-gray-600">#{{ index + 1 }}</span>
              </div>
              
              <div class="flex-1">
                <h3 class="font-semibold text-gray-900">{{ friend.name }}</h3>
                <div class="text-sm text-gray-600 grid grid-cols-2 md:grid-cols-4 gap-4 mt-2">
                  <span>🏆 {{ friend.total_points }} pts</span>
                  <span>🔥 {{ friend.current_streak }} días</span>
                  <span>🎓 {{ friend.certificates_count }} certs</span>
                  <span>🏅 {{ friend.achievements_count }} logros</span>
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
import axios from 'axios'

export default {
  name: 'SocialDashboard',
  data() {
    return {
      loading: true,
      error: null,
      activeTab: 'friends',
      searchQuery: '',
      searchLoading: false,
      searchResults: [],
      leaderboardLoading: false,
      leaderboard: [],
      socialData: {
        friends_count: 0,
        friends: [],
        pending_requests_count: 0,
        pending_requests: [],
        recent_activities: [],
        social_feed: [],
      }
    }
  },
  mounted() {
    this.fetchData()
  },
  watch: {
    activeTab(newTab) {
      if (newTab === 'leaderboard' && this.leaderboard.length === 0) {
        this.fetchLeaderboard()
      }
    }
  },
  methods: {
    async fetchData() {
      try {
        this.loading = true
        this.error = null

        const response = await axios.get('/api/social/dashboard')
        
        if (response.data.success) {
          this.socialData = response.data.data
        } else {
          this.error = response.data.message || 'Error al cargar datos sociales'
        }
      } catch (error) {
        console.error('Error fetching social data:', error)
        this.error = 'Error de conexión. Por favor, intenta de nuevo.'
      } finally {
        this.loading = false
      }
    },

    async searchUsers() {
      if (this.searchQuery.length < 2) return

      try {
        this.searchLoading = true
        const response = await axios.get(`/api/social/search?query=${encodeURIComponent(this.searchQuery)}`)
        
        if (response.data.success) {
          this.searchResults = response.data.data
        }
      } catch (error) {
        console.error('Error searching users:', error)
      } finally {
        this.searchLoading = false
      }
    },

    async sendFriendRequest(userId, userName) {
      try {
        const response = await axios.post('/api/social/friend-request', {
          user_id: userId
        })
        
        if (response.data.success) {
          alert(`¡Solicitud enviada a ${userName}!`)
          // Update search results to reflect new status
          this.searchUsers()
        } else {
          alert(response.data.message)
        }
      } catch (error) {
        console.error('Error sending friend request:', error)
        alert('Error al enviar solicitud de amistad')
      }
    },

    async respondToRequest(requestId, action) {
      try {
        const response = await axios.post(`/api/social/friend-request/${requestId}/respond`, {
          action: action
        })
        
        if (response.data.success) {
          alert(response.data.message)
          this.fetchData() // Refresh data
        } else {
          alert(response.data.message)
        }
      } catch (error) {
        console.error('Error responding to request:', error)
        alert('Error al responder solicitud')
      }
    },

    async removeFriend(userId, userName) {
      if (!confirm(`¿Estás seguro de que quieres eliminar a ${userName} de tus amigos?`)) {
        return
      }

      try {
        const response = await axios.delete('/api/social/friend', {
          data: { user_id: userId }
        })
        
        if (response.data.success) {
          alert(response.data.message)
          this.fetchData() // Refresh data
        } else {
          alert(response.data.message)
        }
      } catch (error) {
        console.error('Error removing friend:', error)
        alert('Error al eliminar amistad')
      }
    },

    async fetchLeaderboard() {
      try {
        this.leaderboardLoading = true
        const response = await axios.get('/api/social/leaderboard')
        
        if (response.data.success) {
          this.leaderboard = response.data.data
        }
      } catch (error) {
        console.error('Error fetching leaderboard:', error)
      } finally {
        this.leaderboardLoading = false
      }
    },

    getFriendshipStatusText(status) {
      const statuses = {
        'pending': 'Solicitud Pendiente',
        'accepted': 'Ya son Amigos',
        'blocked': 'Bloqueado',
      }
      return statuses[status] || 'Desconocido'
    },

    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }
  }
}
</script>