<template>
  <div class="chat-system min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 p-4">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">💬 Chat en Vivo</h1>
        <p class="text-gray-600">Conecta y practica con otros estudiantes en tiempo real</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600 mx-auto mb-4"></div>
        <p class="text-gray-600">Conectando al chat...</p>
      </div>

      <!-- Main Chat Interface -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-4 gap-6 h-96">
        <!-- Conversations Sidebar -->
        <div class="lg:col-span-1 bg-white rounded-xl shadow-lg overflow-hidden">
          <div class="bg-blue-600 text-white p-4">
            <h2 class="text-lg font-semibold flex items-center justify-between">
              📋 Conversaciones
              <span v-if="unreadCount > 0" class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">
                {{ unreadCount }}
              </span>
            </h2>
          </div>

          <div class="max-h-80 overflow-y-auto">
            <!-- No Conversations -->
            <div v-if="conversations.length === 0" class="p-4 text-center text-gray-500">
              <div class="text-4xl mb-2">💬</div>
              <p class="text-sm">No hay conversaciones aún</p>
              <p class="text-xs mt-1">¡Comienza una conversación con tus amigos!</p>
            </div>

            <!-- Conversation List -->
            <div v-else class="space-y-1">
              <div v-for="conversation in conversations" 
                   :key="conversation.other_user.id"
                   @click="selectConversation(conversation.other_user)"
                   class="conversation-item p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100"
                   :class="{ 'bg-blue-50 border-blue-200': selectedUser && selectedUser.id === conversation.other_user.id }">
                
                <div class="flex items-center space-x-3">
                  <div class="relative">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold">
                      {{ conversation.other_user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div v-if="isUserOnline(conversation.other_user.id)" 
                         class="absolute -bottom-1 -right-1 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></div>
                  </div>
                  
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                      <h3 class="font-medium text-gray-900 truncate">{{ conversation.other_user.name }}</h3>
                      <span v-if="conversation.unread_count > 0" 
                            class="bg-blue-600 text-white text-xs px-2 py-1 rounded-full">
                        {{ conversation.unread_count }}
                      </span>
                    </div>
                    <p class="text-sm text-gray-500 truncate">
                      {{ conversation.last_message.message }}
                    </p>
                    <p class="text-xs text-gray-400">
                      {{ formatTime(conversation.last_message.created_at) }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Online Users -->
          <div class="border-t border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
              🟢 En línea ({{ onlineUsers.length }})
            </h3>
            <div class="space-y-2">
              <div v-for="user in onlineUsers.slice(0, 5)" 
                   :key="user.user_id"
                   @click="startConversationWithUser(user)"
                   class="flex items-center space-x-2 p-2 hover:bg-gray-50 rounded-lg cursor-pointer">
                <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center text-white text-xs font-semibold">
                  {{ user.name.charAt(0).toUpperCase() }}
                </div>
                <span class="text-sm text-gray-700">{{ user.name }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Chat Window -->
        <div class="lg:col-span-3 bg-white rounded-xl shadow-lg overflow-hidden flex flex-col">
          <!-- Chat Header -->
          <div v-if="selectedUser" class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center text-white font-semibold">
                  {{ selectedUser.name.charAt(0).toUpperCase() }}
                </div>
                <div>
                  <h2 class="text-lg font-semibold">{{ selectedUser.name }}</h2>
                  <p class="text-blue-100 text-sm">
                    {{ isUserOnline(selectedUser.id) ? 'En línea' : 'Desconectado' }}
                  </p>
                </div>
              </div>
              <button @click="closeChatWindow" class="text-white hover:bg-white hover:bg-opacity-20 p-2 rounded-lg">
                ✕
              </button>
            </div>
          </div>

          <!-- No Chat Selected -->
          <div v-else class="flex-1 flex items-center justify-center bg-gray-50">
            <div class="text-center">
              <div class="text-6xl mb-4">💬</div>
              <h3 class="text-lg font-semibold text-gray-700 mb-2">¡Comienza una conversación!</h3>
              <p class="text-gray-500">Selecciona una conversación o usuario en línea para empezar</p>
            </div>
          </div>

          <!-- Chat Messages -->
          <div v-if="selectedUser" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50" ref="messagesContainer">
            <div v-for="message in currentMessages" 
                 :key="message.id"
                 class="flex"
                 :class="message.sender_id === currentUser.id ? 'justify-end' : 'justify-start'">
              
              <div class="max-w-xs lg:max-w-md">
                <!-- Other user's message -->
                <div v-if="message.sender_id !== currentUser.id" class="flex items-end space-x-2">
                  <div class="w-8 h-8 bg-gray-400 rounded-full flex items-center justify-center text-white text-xs font-semibold">
                    {{ message.sender.name.charAt(0).toUpperCase() }}
                  </div>
                  <div class="bg-white rounded-lg p-3 shadow-sm">
                    <p class="text-gray-800">{{ message.message }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ formatTime(message.created_at) }}</p>
                  </div>
                </div>

                <!-- Current user's message -->
                <div v-else class="flex items-end space-x-2 flex-row-reverse">
                  <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-xs font-semibold">
                    {{ currentUser.name.charAt(0).toUpperCase() }}
                  </div>
                  <div class="bg-blue-600 text-white rounded-lg p-3 shadow-sm">
                    <p>{{ message.message }}</p>
                    <div class="flex items-center justify-between mt-1">
                      <p class="text-xs text-blue-100">{{ formatTime(message.created_at) }}</p>
                      <div class="flex items-center space-x-1">
                        <span v-if="message.is_read" class="text-blue-200 text-xs">✓✓</span>
                        <span v-else class="text-blue-300 text-xs">✓</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Typing Indicator -->
            <div v-if="isTyping" class="flex justify-start">
              <div class="flex items-center space-x-2 bg-white rounded-lg p-3 shadow-sm">
                <div class="flex space-x-1">
                  <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                  <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                  <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                </div>
                <span class="text-sm text-gray-500">escribiendo...</span>
              </div>
            </div>
          </div>

          <!-- Message Input -->
          <div v-if="selectedUser" class="border-t border-gray-200 p-4">
            <form @submit.prevent="sendMessage" class="flex space-x-3">
              <input v-model="newMessage" 
                     @keypress="handleTyping"
                     type="text" 
                     class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                     placeholder="Escribe un mensaje..."
                     :disabled="sendingMessage">
              
              <button type="submit" 
                      :disabled="!newMessage.trim() || sendingMessage"
                      class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center space-x-2">
                <span v-if="sendingMessage">Enviando...</span>
                <span v-else>📤 Enviar</span>
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Connection Status -->
      <div class="mt-4 text-center">
        <div v-if="connected" class="inline-flex items-center space-x-2 bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">
          <div class="w-2 h-2 bg-green-500 rounded-full"></div>
          <span>Conectado al chat en vivo</span>
        </div>
        <div v-else class="inline-flex items-center space-x-2 bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm">
          <div class="w-2 h-2 bg-red-500 rounded-full"></div>
          <span>Reconectando...</span>
        </div>
      </div>
    </div>

    <!-- Notification Toast -->
    <div v-if="notification" 
         class="fixed bottom-4 right-4 bg-blue-600 text-white p-4 rounded-lg shadow-lg max-w-sm z-50 transform transition-transform duration-300"
         :class="notification ? 'translate-x-0' : 'translate-x-full'">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
            💬
          </div>
          <div>
            <p class="font-semibold">{{ notification.title }}</p>
            <p class="text-blue-100 text-sm">{{ notification.message }}</p>
          </div>
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
  name: 'Chat',
  data() {
    return {
      loading: true,
      connected: false,
      conversations: [],
      currentMessages: [],
      selectedUser: null,
      currentUser: null,
      newMessage: '',
      sendingMessage: false,
      onlineUsers: [],
      unreadCount: 0,
      isTyping: false,
      notification: null,
      echo: null,
      typingTimer: null
    }
  },
  async mounted() {
    try {
      await this.initializeChat();
      this.setupWebSocket();
    } catch (error) {
      console.error('Error initializing chat:', error);
    } finally {
      this.loading = false;
    }
  },
  beforeUnmount() {
    this.disconnectWebSocket();
  },
  methods: {
    async initializeChat() {
      // Get current user
      const userResponse = await axios.get('/api/user');
      this.currentUser = userResponse.data;

      // Load conversations
      await this.loadConversations();
      
      // Load unread count
      await this.loadUnreadCount();

      // Update online status
      await this.updateOnlineStatus(true);
    },

    setupWebSocket() {
      try {
        // Initialize Laravel Echo (this would need Laravel Echo and pusher-js)
        // For now, we'll simulate the connection
        this.connected = true;
        
        // Simulate receiving online users
        this.simulateOnlineUsers();
        
        // Set up intervals for real-time updates
        this.setupPolling();
      } catch (error) {
        console.error('WebSocket connection failed:', error);
        this.connected = false;
        
        // Fallback to polling
        this.setupPolling();
      }
    },

    setupPolling() {
      // Poll for new messages and online users every 3 seconds
      setInterval(async () => {
        if (this.selectedUser) {
          await this.refreshConversation();
        }
        await this.loadUnreadCount();
      }, 3000);

      // Update online status every 30 seconds
      setInterval(() => {
        this.updateOnlineStatus(true);
      }, 30000);
    },

    disconnectWebSocket() {
      if (this.echo) {
        // Disconnect Echo
      }
      
      // Update offline status
      this.updateOnlineStatus(false);
    },

    async loadConversations() {
      try {
        const response = await axios.get('/api/chat/conversations');
        this.conversations = response.data.data;
      } catch (error) {
        console.error('Error loading conversations:', error);
      }
    },

    async loadUnreadCount() {
      try {
        const response = await axios.get('/api/chat/unread-count');
        this.unreadCount = response.data.data.unread_count;
      } catch (error) {
        console.error('Error loading unread count:', error);
      }
    },

    async selectConversation(user) {
      this.selectedUser = user;
      await this.loadConversation(user.id);
    },

    async loadConversation(userId) {
      try {
        const response = await axios.get(`/api/chat/conversation/${userId}`);
        this.currentMessages = response.data.data;
        this.scrollToBottom();
        
        // Refresh conversations to update unread counts
        await this.loadConversations();
        await this.loadUnreadCount();
      } catch (error) {
        console.error('Error loading conversation:', error);
      }
    },

    async refreshConversation() {
      if (this.selectedUser) {
        await this.loadConversation(this.selectedUser.id);
      }
    },

    async sendMessage() {
      if (!this.newMessage.trim() || this.sendingMessage || !this.selectedUser) {
        return;
      }

      this.sendingMessage = true;

      try {
        const response = await axios.post('/api/chat/send', {
          receiver_id: this.selectedUser.id,
          message_type: 'direct',
          message: this.newMessage.trim(),
        });

        // Add message to current conversation
        this.currentMessages.push(response.data.data);
        this.newMessage = '';
        this.scrollToBottom();
        
        // Refresh conversations
        await this.loadConversations();

        this.showNotification('Mensaje enviado', 'Tu mensaje fue enviado correctamente');

      } catch (error) {
        console.error('Error sending message:', error);
        this.showNotification('Error', 'No se pudo enviar el mensaje', 'error');
      } finally {
        this.sendingMessage = false;
      }
    },

    async updateOnlineStatus(isOnline) {
      try {
        await axios.post('/api/chat/online-status', { is_online: isOnline });
      } catch (error) {
        console.error('Error updating online status:', error);
      }
    },

    handleTyping() {
      // Clear existing timer
      clearTimeout(this.typingTimer);
      
      // Set new timer for 2 seconds
      this.typingTimer = setTimeout(() => {
        // Stop typing indicator
        this.isTyping = false;
      }, 2000);
    },

    startConversationWithUser(user) {
      const existingConversation = this.conversations.find(conv => 
        conv.other_user.id === user.user_id
      );

      if (existingConversation) {
        this.selectConversation(existingConversation.other_user);
      } else {
        // Start new conversation
        this.selectedUser = { 
          id: user.user_id, 
          name: user.name 
        };
        this.currentMessages = [];
      }
    },

    closeChatWindow() {
      this.selectedUser = null;
      this.currentMessages = [];
    },

    scrollToBottom() {
      this.$nextTick(() => {
        const container = this.$refs.messagesContainer;
        if (container) {
          container.scrollTop = container.scrollHeight;
        }
      });
    },

    simulateOnlineUsers() {
      // This would be replaced with real WebSocket data
      this.onlineUsers = [
        { user_id: 2, name: 'Ana García' },
        { user_id: 3, name: 'Carlos López' },
        { user_id: 4, name: 'María Silva' },
      ];
    },

    isUserOnline(userId) {
      return this.onlineUsers.some(user => user.user_id === userId);
    },

    formatTime(timestamp) {
      const date = new Date(timestamp);
      const now = new Date();
      const diffInHours = (now - date) / (1000 * 60 * 60);

      if (diffInHours < 1) {
        return `hace ${Math.floor((now - date) / (1000 * 60))} min`;
      } else if (diffInHours < 24) {
        return `hace ${Math.floor(diffInHours)} h`;
      } else {
        return date.toLocaleDateString('es-ES', { 
          month: 'short', 
          day: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        });
      }
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
.chat-system {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

.conversation-item {
  transition: all 0.2s ease-in-out;
}

.conversation-item:hover {
  transform: translateX(2px);
}

.animate-bounce {
  animation: bounce 1.4s infinite;
}

@keyframes bounce {
  0%, 80%, 100% {
    transform: translateY(0);
  }
  40% {
    transform: translateY(-6px);
  }
}

/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
  width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #a1a1a1;
}
</style>