<template>
  <div class="multiplayer-quiz min-h-screen bg-gradient-to-br from-purple-50 to-blue-100 p-4">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">🏆 Competencias Multijugador</h1>
        <p class="text-gray-600">Compite con otros estudiantes en tiempo real</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-purple-600 mx-auto mb-4"></div>
        <p class="text-gray-600">Cargando...</p>
      </div>

      <!-- Main Interface -->
      <div v-else-if="!currentRoom" class="space-y-6">
        <!-- Quick Join -->
        <div class="bg-white rounded-xl shadow-lg p-6">
          <h2 class="text-xl font-semibold mb-4 text-center">🚀 Unirse Rápidamente</h2>
          <form @submit.prevent="quickJoin" class="flex gap-3 max-w-md mx-auto">
            <input v-model="roomCode" 
                   type="text" 
                   class="flex-1 px-4 py-2 border border-gray-300 rounded-lg uppercase text-center tracking-widest font-mono"
                   placeholder="CÓDIGO"
                   maxlength="6"
                   @input="roomCode = roomCode.toUpperCase()">
            <button type="submit" 
                    :disabled="!roomCode || roomCode.length !== 6 || joining"
                    class="bg-purple-600 hover:bg-purple-700 disabled:bg-gray-400 text-white px-6 py-2 rounded-lg font-medium">
              {{ joining ? 'Uniéndose...' : 'Unirse' }}
            </button>
          </form>
        </div>

        <!-- Create Room -->
        <div class="bg-white rounded-xl shadow-lg p-6">
          <h2 class="text-xl font-semibold mb-4">🎯 Crear Nueva Competencia</h2>
          <form @submit.prevent="createRoom" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de la Sala</label>
              <input v-model="newRoom.name" 
                     type="text" 
                     class="w-full px-3 py-2 border border-gray-300 rounded-md"
                     placeholder="Mi Competencia Increíble"
                     required>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Quiz</label>
              <select v-model="newRoom.quiz_id" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-md"
                      required>
                <option value="">Seleccionar Quiz</option>
                <option v-for="quiz in availableQuizzes" :key="quiz.id" :value="quiz.id">
                  {{ quiz.title }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Máx. Participantes</label>
              <select v-model="newRoom.max_participants" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <option :value="2">2 jugadores</option>
                <option :value="4">4 jugadores</option>
                <option :value="6">6 jugadores</option>
                <option :value="8">8 jugadores</option>
                <option :value="12">12 jugadores</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tiempo por Pregunta</label>
              <select v-model="newRoom.question_time_limit" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <option :value="15">15 segundos</option>
                <option :value="20">20 segundos</option>
                <option :value="30">30 segundos</option>
                <option :value="45">45 segundos</option>
                <option :value="60">60 segundos</option>
              </select>
            </div>
            <div class="md:col-span-2">
              <button type="submit" 
                      :disabled="creating"
                      class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white py-2 px-4 rounded-lg font-medium">
                {{ creating ? 'Creando...' : '🎯 Crear Competencia' }}
              </button>
            </div>
          </form>
        </div>

        <!-- Available Rooms -->
        <div class="bg-white rounded-xl shadow-lg p-6">
          <h2 class="text-xl font-semibold mb-4">🎮 Salas Disponibles</h2>
          <div v-if="availableRooms.length === 0" class="text-center py-8 text-gray-500">
            <div class="text-6xl mb-4">🏁</div>
            <p>No hay competencias disponibles</p>
            <p class="text-sm mt-2">¡Crea una nueva para empezar!</p>
          </div>
          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-for="room in availableRooms" 
                 :key="room.id"
                 class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 cursor-pointer"
                 @click="joinRoomById(room.id)">
              <div class="flex justify-between items-start mb-2">
                <h3 class="font-semibold text-lg">{{ room.name }}</h3>
                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">{{ room.room_code }}</span>
              </div>
              <p class="text-gray-600 text-sm mb-3">{{ room.quiz.title }}</p>
              <div class="flex justify-between items-center text-sm">
                <div class="flex items-center space-x-3">
                  <span class="text-gray-500">👤 {{ room.participants_count }}/{{ room.max_participants }}</span>
                  <span class="text-gray-500">⏱️ {{ room.question_time_limit }}s</span>
                </div>
                <span class="text-green-600 font-medium">🎯 Unirse</span>
              </div>
              <div class="text-xs text-gray-400 mt-2">Host: {{ room.host.name }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Room Interface -->
      <div v-else-if="currentRoom" class="space-y-6">
        <!-- Room Header -->
        <div class="bg-white rounded-xl shadow-lg p-6">
          <div class="flex justify-between items-center">
            <div>
              <h2 class="text-2xl font-bold">{{ currentRoom.name }}</h2>
              <p class="text-gray-600">{{ currentRoom.quiz.title }}</p>
              <p class="text-sm text-gray-500">Código de sala: <span class="font-mono font-bold">{{ currentRoom.room_code }}</span></p>
            </div>
            <div class="text-right">
              <div class="text-lg font-semibold text-purple-600">{{ getRoomStatusText() }}</div>
              <button @click="leaveRoom" 
                      class="mt-2 bg-red-100 hover:bg-red-200 text-red-700 px-4 py-1 rounded-lg text-sm">
                🚪 Salir
              </button>
            </div>
          </div>
        </div>

        <!-- Game Interface -->
        <!-- Waiting Room -->
        <div v-if="currentRoom.status === 'waiting'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="lg:col-span-2 bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-xl font-semibold mb-4">🎮 Sala de Espera</h3>
            <div class="space-y-4">
              <div v-for="participant in currentRoom.participants" 
                   :key="participant.id"
                   class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-blue-500 rounded-full flex items-center justify-center text-white font-semibold">
                    {{ participant.user.name.charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <div class="font-medium">{{ participant.user.name }}</div>
                    <div class="text-sm text-gray-500">
                      {{ participant.user.id === currentRoom.host.id ? '👑 Host' : '🎯 Participante' }}
                    </div>
                  </div>
                </div>
                <div class="flex items-center space-x-2">
                  <div v-if="participant.status === 'ready'" class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                    ✅ Listo
                  </div>
                  <div v-else class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">
                    ⏳ Esperando
                  </div>
                </div>
              </div>
            </div>

            <!-- Ready/Start Button -->
            <div class="mt-6 text-center">
              <button v-if="isHost && canStartQuiz" 
                      @click="startQuiz"
                      :disabled="starting"
                      class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white px-8 py-3 rounded-lg font-semibold text-lg">
                {{ starting ? 'Iniciando...' : '🚀 Iniciar Competencia' }}
              </button>
              <button v-else-if="!isHost && !isReady" 
                      @click="setReady"
                      :disabled="settingReady"
                      class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-8 py-3 rounded-lg font-semibold">
                {{ settingReady ? 'Preparando...' : '✅ Estoy Listo' }}
              </button>
              <div v-else-if="!isHost && isReady" class="text-green-600 font-semibold">
                ✅ ¡Estás listo! Esperando al host...
              </div>
              <div v-else-if="isHost && !canStartQuiz" class="text-yellow-600">
                ⏳ Esperando que todos estén listos (mín. 2 jugadores)
              </div>
            </div>
          </div>

          <!-- Quiz Info -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold mb-4">📋 Información del Quiz</h3>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600">Preguntas:</span>
                <span class="font-semibold">{{ currentRoom.total_questions }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Tiempo/pregunta:</span>
                <span class="font-semibold">{{ currentRoom.question_time_limit }}s</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Participantes:</span>
                <span class="font-semibold">{{ currentRoom.participants.length }}/{{ currentRoom.max_participants }}</span>
              </div>
            </div>

            <div class="mt-6 p-4 bg-blue-50 rounded-lg">
              <h4 class="font-semibold text-blue-800 mb-2">🏆 Reglas de Puntuación</h4>
              <ul class="text-sm text-blue-700 space-y-1">
                <li>• Respuesta correcta: 100 puntos</li>
                <li>• Bonus de velocidad: hasta 50 puntos</li>
                <li>• Respuesta incorrecta: 0 puntos</li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Quiz In Progress -->
        <div v-else-if="currentRoom.status === 'in_progress'" class="space-y-6">
          <!-- Question Interface -->
          <div v-if="currentQuestion" class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex justify-between items-center mb-6">
              <div class="text-lg font-semibold text-gray-800">
                Pregunta {{ currentRoom.current_question }} de {{ currentRoom.total_questions }}
              </div>
              <div class="flex items-center space-x-4">
                <div class="text-right">
                  <div class="text-sm text-gray-500">Tiempo restante</div>
                  <div class="text-2xl font-bold font-mono" :class="getTimerColor()">
                    {{ formatTime(timeRemaining) }}
                  </div>
                </div>
              </div>
            </div>

            <div class="mb-6">
              <h3 class="text-xl font-semibold mb-4">{{ currentQuestion.question_text }}</h3>
              
              <!-- Multiple Choice -->
              <div v-if="currentQuestion.question_type === 'multiple_choice'" class="space-y-3">
                <button v-for="option in currentQuestion.options" 
                        :key="option.id"
                        @click="selectAnswer(option.option_text)"
                        :disabled="hasAnswered || timeRemaining <= 0"
                        class="w-full text-left p-4 border border-gray-200 rounded-lg hover:bg-blue-50 disabled:bg-gray-50 disabled:cursor-not-allowed"
                        :class="{ 'bg-blue-100 border-blue-300': selectedAnswer === option.option_text }">
                  {{ option.option_text }}
                </button>
              </div>

              <!-- Fill in the Blank -->
              <div v-else-if="currentQuestion.question_type === 'fill_in_the_blank'" class="space-y-4">
                <input v-model="textAnswer" 
                       @keyup.enter="submitTextAnswer"
                       :disabled="hasAnswered || timeRemaining <= 0"
                       type="text" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg text-lg"
                       placeholder="Escribe tu respuesta...">
                <button @click="submitTextAnswer" 
                        :disabled="!textAnswer || hasAnswered || timeRemaining <= 0"
                        class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white px-6 py-2 rounded-lg">
                  ✅ Enviar Respuesta
                </button>
              </div>
            </div>

            <!-- Answer Status -->
            <div v-if="hasAnswered" class="text-center">
              <div class="inline-flex items-center space-x-2 bg-green-100 text-green-800 px-4 py-2 rounded-lg">
                <span>✅</span>
                <span>Respuesta enviada - Esperando otros jugadores...</span>
              </div>
            </div>
          </div>

          <!-- Live Leaderboard -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold mb-4">🏆 Clasificación en Vivo</h3>
            <div class="space-y-2">
              <div v-for="(participant, index) in sortedParticipants" 
                   :key="participant.id"
                   class="flex items-center justify-between p-3 rounded-lg"
                   :class="participant.user.id === currentUser.id ? 'bg-purple-50 border border-purple-200' : 'bg-gray-50'">
                <div class="flex items-center space-x-3">
                  <div class="text-lg font-bold" :class="getRankColor(index + 1)">
                    {{ getRankEmoji(index + 1) }} #{{ index + 1 }}
                  </div>
                  <div>
                    <div class="font-medium">
                      {{ participant.user.name }}
                      {{ participant.user.id === currentUser.id ? ' (Tú)' : '' }}
                    </div>
                    <div class="text-sm text-gray-500">
                      {{ participant.correct_answers }}/{{ participant.total_questions }} correctas
                    </div>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-lg font-bold text-purple-600">{{ participant.total_score }}</div>
                  <div class="text-xs text-gray-500">pts</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quiz Completed -->
        <div v-else-if="currentRoom.status === 'completed'" class="bg-white rounded-xl shadow-lg p-6">
          <div class="text-center mb-8">
            <div class="text-6xl mb-4">🏆</div>
            <h2 class="text-3xl font-bold mb-2">¡Competencia Finalizada!</h2>
            <p class="text-gray-600">{{ currentRoom.quiz.title }}</p>
          </div>

          <!-- Final Rankings -->
          <div class="mb-8">
            <h3 class="text-xl font-semibold mb-4 text-center">🏅 Resultados Finales</h3>
            <div class="space-y-3">
              <div v-for="(participant, index) in finalLeaderboard" 
                   :key="participant.id"
                   class="flex items-center justify-between p-4 rounded-lg border"
                   :class="[
                     participant.user.id === currentUser.id ? 'bg-purple-50 border-purple-200' : 'bg-gray-50',
                     index === 0 ? 'ring-2 ring-yellow-300' : '',
                     index === 1 ? 'ring-1 ring-gray-300' : '',
                     index === 2 ? 'ring-1 ring-orange-300' : ''
                   ]">
                <div class="flex items-center space-x-4">
                  <div class="text-2xl font-bold" :class="getRankColor(index + 1)">
                    {{ getRankEmoji(index + 1) }} #{{ index + 1 }}
                  </div>
                  <div>
                    <div class="text-lg font-semibold">
                      {{ participant.user.name }}
                      {{ participant.user.id === currentUser.id ? ' (Tú)' : '' }}
                    </div>
                    <div class="text-sm text-gray-600">
                      {{ participant.accuracy }}% precisión • {{ participant.correct_answers }}/{{ participant.total_questions }} correctas
                    </div>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-2xl font-bold text-purple-600">{{ participant.total_score }}</div>
                  <div class="text-sm text-gray-500">
                    {{ participant.speed_bonus > 0 ? `+${participant.speed_bonus} velocidad` : '' }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="text-center">
            <button @click="returnToLobby" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold">
              🏠 Volver al Lobby
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Notifications -->
    <div v-if="notification" 
         class="fixed bottom-4 right-4 bg-blue-600 text-white p-4 rounded-lg shadow-lg max-w-sm z-50">
      <div class="flex items-center justify-between">
        <div>
          <p class="font-semibold">{{ notification.title }}</p>
          <p class="text-blue-100 text-sm">{{ notification.message }}</p>
        </div>
        <button @click="dismissNotification" class="text-white hover:bg-white hover:bg-opacity-20 p-1 rounded">
          ✕
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MultiplayerQuiz',
  data() {
    return {
      loading: true,
      currentUser: null,
      currentRoom: null,
      availableRooms: [],
      availableQuizzes: [],
      
      // Room joining
      roomCode: '',
      joining: false,
      
      // Room creation
      creating: false,
      newRoom: {
        name: '',
        quiz_id: '',
        max_participants: 4,
        question_time_limit: 30,
        is_public: true
      },
      
      // Game state
      currentQuestion: null,
      selectedAnswer: '',
      textAnswer: '',
      hasAnswered: false,
      timeRemaining: 0,
      timerInterval: null,
      
      // Status
      starting: false,
      settingReady: false,
      
      // Leaderboard
      finalLeaderboard: [],
      
      // UI
      notification: null
    }
  },
  computed: {
    isHost() {
      return this.currentRoom && this.currentUser && this.currentRoom.host.id === this.currentUser.id;
    },
    
    isReady() {
      if (!this.currentRoom || !this.currentUser) return false;
      const participant = this.currentRoom.participants.find(p => p.user.id === this.currentUser.id);
      return participant && participant.status === 'ready';
    },
    
    canStartQuiz() {
      if (!this.currentRoom) return false;
      return this.currentRoom.participants.length >= 2 && 
             this.currentRoom.participants.every(p => p.status === 'ready' || p.user.id === this.currentRoom.host.id);
    },
    
    sortedParticipants() {
      if (!this.currentRoom) return [];
      return [...this.currentRoom.participants].sort((a, b) => b.total_score - a.total_score);
    }
  },
  async mounted() {
    await this.initialize();
    this.loadRooms();
    this.loadQuizzes();
    this.loading = false;
  },
  beforeUnmount() {
    this.clearTimer();
    this.disconnectFromRoom();
  },
  methods: {
    async initialize() {
      try {
        const response = await axios.get('/api/user');
        this.currentUser = response.data;
      } catch (error) {
        console.error('Error loading user:', error);
      }
    },

    async loadRooms() {
      try {
        const response = await axios.get('/api/multiplayer/rooms');
        this.availableRooms = response.data.data;
      } catch (error) {
        console.error('Error loading rooms:', error);
      }
    },

    async loadQuizzes() {
      try {
        const response = await axios.get('/api/public/quizzes');
        this.availableQuizzes = response.data.data.filter(quiz => quiz.questions.length >= 3);
      } catch (error) {
        console.error('Error loading quizzes:', error);
      }
    },

    async createRoom() {
      this.creating = true;
      try {
        const response = await axios.post('/api/multiplayer/rooms', this.newRoom);
        this.currentRoom = response.data.data.room;
        this.showNotification('¡Sala creada!', response.data.data.message);
        this.connectToRoom();
        
        // Reset form
        this.newRoom = {
          name: '',
          quiz_id: '',
          max_participants: 4,
          question_time_limit: 30,
          is_public: true
        };
      } catch (error) {
        console.error('Error creating room:', error);
        this.showNotification('Error', 'No se pudo crear la sala', 'error');
      } finally {
        this.creating = false;
      }
    },

    async quickJoin() {
      await this.joinRoom(this.roomCode);
      this.roomCode = '';
    },

    async joinRoomById(roomId) {
      const room = this.availableRooms.find(r => r.id === roomId);
      if (room) {
        await this.joinRoom(room.room_code);
      }
    },

    async joinRoom(roomCode) {
      this.joining = true;
      try {
        const response = await axios.post('/api/multiplayer/join', { room_code: roomCode });
        this.currentRoom = response.data.data.room;
        this.showNotification('¡Sala unida!', response.data.data.message);
        this.connectToRoom();
      } catch (error) {
        console.error('Error joining room:', error);
        this.showNotification('Error', error.response?.data?.message || 'No se pudo unir a la sala', 'error');
      } finally {
        this.joining = false;
      }
    },

    async leaveRoom() {
      try {
        await axios.delete(`/api/multiplayer/rooms/${this.currentRoom.id}/leave`);
        this.currentRoom = null;
        this.disconnectFromRoom();
        this.loadRooms();
        this.showNotification('Sala abandonada', 'Has salido de la competencia');
      } catch (error) {
        console.error('Error leaving room:', error);
      }
    },

    async setReady() {
      this.settingReady = true;
      try {
        await axios.post(`/api/multiplayer/rooms/${this.currentRoom.id}/ready`);
        // Update local state
        const participant = this.currentRoom.participants.find(p => p.user.id === this.currentUser.id);
        if (participant) {
          participant.status = 'ready';
        }
        this.showNotification('¡Listo!', 'Esperando que el host inicie la competencia');
      } catch (error) {
        console.error('Error setting ready:', error);
      } finally {
        this.settingReady = false;
      }
    },

    async startQuiz() {
      this.starting = true;
      try {
        await axios.post(`/api/multiplayer/rooms/${this.currentRoom.id}/start`);
        this.showNotification('¡Iniciando!', 'La competencia comenzará en breve');
      } catch (error) {
        console.error('Error starting quiz:', error);
        this.showNotification('Error', 'No se pudo iniciar la competencia', 'error');
      } finally {
        this.starting = false;
      }
    },

    selectAnswer(answer) {
      if (this.hasAnswered || this.timeRemaining <= 0) return;
      
      this.selectedAnswer = answer;
      this.submitAnswer(answer);
    },

    submitTextAnswer() {
      if (!this.textAnswer || this.hasAnswered) return;
      this.submitAnswer(this.textAnswer);
    },

    async submitAnswer(answer) {
      if (this.hasAnswered) return;
      
      const responseTime = this.currentRoom.question_time_limit - this.timeRemaining;
      
      try {
        const response = await axios.post(`/api/multiplayer/rooms/${this.currentRoom.id}/answer`, {
          answer: answer,
          response_time: responseTime,
          answer_data: null
        });

        this.hasAnswered = true;
        const result = response.data.data;
        
        this.showNotification(
          result.is_correct ? '✅ ¡Correcto!' : '❌ Incorrecto',
          `${result.total_points} puntos ${result.speed_bonus > 0 ? `(+${result.speed_bonus} bonus)` : ''}`
        );

        // Update local participant score
        const participant = this.currentRoom.participants.find(p => p.user.id === this.currentUser.id);
        if (participant) {
          participant.total_score = result.performance.total_score;
          participant.correct_answers = result.performance.correct_answers;
          participant.total_questions = result.performance.total_questions;
        }

      } catch (error) {
        console.error('Error submitting answer:', error);
      }
    },

    connectToRoom() {
      // This would connect to WebSocket channels for real-time updates
      // For now, we'll use polling to simulate real-time updates
      this.startPolling();
    },

    disconnectFromRoom() {
      this.clearTimer();
      // Disconnect from WebSocket channels
    },

    startPolling() {
      // Poll for room updates every 2 seconds
      this.pollingInterval = setInterval(async () => {
        if (this.currentRoom && this.currentRoom.status !== 'completed') {
          await this.refreshRoom();
        }
      }, 2000);
    },

    async refreshRoom() {
      try {
        const response = await axios.get(`/api/multiplayer/rooms/${this.currentRoom.id}`);
        const updatedRoom = response.data.data;
        
        // Handle status changes
        if (updatedRoom.status !== this.currentRoom.status) {
          if (updatedRoom.status === 'in_progress') {
            this.handleQuizStarted();
          } else if (updatedRoom.status === 'completed') {
            this.handleQuizCompleted();
          }
        }

        // Handle question changes
        if (updatedRoom.status === 'in_progress' && updatedRoom.current_question !== this.currentRoom.current_question) {
          this.handleNewQuestion();
        }

        this.currentRoom = updatedRoom;
      } catch (error) {
        console.error('Error refreshing room:', error);
      }
    },

    handleQuizStarted() {
      this.showNotification('¡Comenzó!', 'La competencia ha iniciado');
      this.handleNewQuestion();
    },

    async handleNewQuestion() {
      this.hasAnswered = false;
      this.selectedAnswer = '';
      this.textAnswer = '';
      this.timeRemaining = this.currentRoom.question_time_limit;
      
      // Get current question details
      try {
        const response = await axios.get(`/api/multiplayer/rooms/${this.currentRoom.id}`);
        // Extract current question from room data
        if (response.data.data.questions && response.data.data.current_question > 0) {
          const questionIndex = response.data.data.current_question - 1;
          const questionId = response.data.data.questions[questionIndex].id;
          
          // Get question details from quiz
          const quizResponse = await axios.get(`/api/public/quizzes/${this.currentRoom.quiz.id}`);
          this.currentQuestion = quizResponse.data.data.questions.find(q => q.id === questionId);
        }
      } catch (error) {
        console.error('Error loading question:', error);
      }
      
      this.startTimer();
    },

    handleQuizCompleted() {
      this.clearTimer();
      this.loadFinalLeaderboard();
      this.showNotification('¡Finalizado!', 'La competencia ha terminado');
    },

    async loadFinalLeaderboard() {
      try {
        const response = await axios.get(`/api/multiplayer/rooms/${this.currentRoom.id}/leaderboard`);
        this.finalLeaderboard = response.data.data;
      } catch (error) {
        console.error('Error loading leaderboard:', error);
      }
    },

    startTimer() {
      this.clearTimer();
      this.timerInterval = setInterval(() => {
        this.timeRemaining--;
        if (this.timeRemaining <= 0) {
          this.clearTimer();
          if (!this.hasAnswered) {
            this.showNotification('⏰ Tiempo agotado', 'No respondiste a tiempo');
          }
        }
      }, 1000);
    },

    clearTimer() {
      if (this.timerInterval) {
        clearInterval(this.timerInterval);
        this.timerInterval = null;
      }
      if (this.pollingInterval) {
        clearInterval(this.pollingInterval);
        this.pollingInterval = null;
      }
    },

    returnToLobby() {
      this.currentRoom = null;
      this.loadRooms();
    },

    // Utility methods
    getRoomStatusText() {
      switch (this.currentRoom?.status) {
        case 'waiting': return 'Esperando jugadores';
        case 'starting': return 'Iniciando...';
        case 'in_progress': return 'En progreso';
        case 'completed': return 'Finalizada';
        default: return 'Desconocido';
      }
    },

    getTimerColor() {
      if (this.timeRemaining > 15) return 'text-green-600';
      if (this.timeRemaining > 5) return 'text-yellow-600';
      return 'text-red-600';
    },

    getRankColor(position) {
      switch (position) {
        case 1: return 'text-yellow-600';
        case 2: return 'text-gray-600';
        case 3: return 'text-orange-600';
        default: return 'text-gray-800';
      }
    },

    getRankEmoji(position) {
      switch (position) {
        case 1: return '🥇';
        case 2: return '🥈';
        case 3: return '🥉';
        default: return '🏅';
      }
    },

    formatTime(seconds) {
      const mins = Math.floor(seconds / 60);
      const secs = seconds % 60;
      return `${mins}:${secs.toString().padStart(2, '0')}`;
    },

    showNotification(title, message, type = 'success') {
      this.notification = { title, message, type };
      setTimeout(() => {
        this.dismissNotification();
      }, 5000);
    },

    dismissNotification() {
      this.notification = null;
    }
  }
}
</script>

<style scoped>
.multiplayer-quiz {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}
</style>