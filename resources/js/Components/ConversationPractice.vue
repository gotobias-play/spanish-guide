<template>
  <div class="min-h-screen bg-gradient-to-br from-green-50 to-emerald-100 p-4">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">
          💬 Práctica de Conversación
        </h1>
        <p class="text-lg text-gray-600">
          Practica conversaciones reales con simulaciones interactivas de IA
        </p>
      </div>

      <!-- Progress Overview -->
      <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">📊 Tu Progreso en Conversación</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg p-4 text-white">
            <h3 class="text-sm font-medium opacity-90">Conversaciones</h3>
            <p class="text-2xl font-bold">{{ userProgress.completedConversations }}</p>
          </div>
          <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-lg p-4 text-white">
            <h3 class="text-sm font-medium opacity-90">Fluidez Promedio</h3>
            <p class="text-2xl font-bold">{{ userProgress.averageFluency }}%</p>
          </div>
          <div class="bg-gradient-to-r from-teal-500 to-teal-600 rounded-lg p-4 text-white">
            <h3 class="text-sm font-medium opacity-90">Tiempo Total</h3>
            <p class="text-2xl font-bold">{{ userProgress.totalTime }}h</p>
          </div>
          <div class="bg-gradient-to-r from-cyan-500 to-cyan-600 rounded-lg p-4 text-white">
            <h3 class="text-sm font-medium opacity-90">Nivel Actual</h3>
            <p class="text-2xl font-bold">{{ userProgress.currentLevel }}</p>
          </div>
        </div>
      </div>

      <!-- Conversation Scenarios -->
      <div v-if="currentView === 'scenarios'" class="space-y-6">
        <div v-for="category in conversationCategories" :key="category.id" class="bg-white rounded-xl shadow-lg p-6">
          <div class="flex items-center mb-4">
            <span class="text-3xl mr-3">{{ category.icon }}</span>
            <div>
              <h3 class="text-2xl font-semibold text-gray-800">{{ category.title }}</h3>
              <p class="text-gray-600">{{ category.description }}</p>
            </div>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div 
              v-for="scenario in category.scenarios" 
              :key="scenario.id"
              @click="startConversation(scenario)"
              class="border-2 border-gray-200 rounded-lg p-4 hover:border-green-400 hover:shadow-md transition-all cursor-pointer"
              :class="{ 'bg-green-50 border-green-300': scenario.completed }"
            >
              <div class="flex items-center justify-between mb-2">
                <h4 class="font-semibold text-gray-800">{{ scenario.title }}</h4>
                <span v-if="scenario.completed" class="text-green-500 text-xl">✓</span>
              </div>
              <p class="text-sm text-gray-600 mb-3">{{ scenario.description }}</p>
              <div class="flex items-center justify-between text-sm">
                <span class="bg-green-100 text-green-800 px-2 py-1 rounded">{{ scenario.difficulty }}</span>
                <span class="text-gray-500">{{ scenario.turns }} intercambios</span>
              </div>
              <div class="mt-2">
                <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded mr-1">{{ scenario.focusArea }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Conversation Interface -->
      <div v-if="currentView === 'conversation'" class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Conversation Header -->
        <div class="bg-green-600 text-white p-4 flex items-center justify-between">
          <div>
            <h2 class="text-xl font-semibold">{{ currentScenario.title }}</h2>
            <p class="text-green-200">{{ currentScenario.focusArea }} • {{ currentScenario.partner }}</p>
          </div>
          <button 
            @click="exitConversation"
            class="bg-green-700 px-4 py-2 rounded-lg hover:bg-green-800 transition-colors"
          >
            Salir
          </button>
        </div>

        <!-- Conversation Area -->
        <div class="h-96 overflow-y-auto p-4 bg-gray-50">
          <div v-for="(message, index) in conversationHistory" :key="index" class="mb-4">
            <!-- AI Assistant Message -->
            <div v-if="message.speaker === 'assistant'" class="flex items-start">
              <div class="bg-blue-500 rounded-full p-2 mr-3">
                <span class="text-white text-sm">🤖</span>
              </div>
              <div class="bg-white rounded-lg p-3 shadow-sm max-w-md">
                <p class="text-gray-800">{{ message.text }}</p>
                <div v-if="message.audioUrl" class="mt-2">
                  <button 
                    @click="playAudio(message.audioUrl)"
                    class="bg-blue-100 text-blue-600 px-2 py-1 rounded text-xs hover:bg-blue-200 transition-colors"
                  >
                    🔊 Escuchar
                  </button>
                </div>
                <p v-if="message.translation" class="text-xs text-gray-500 mt-1 italic">{{ message.translation }}</p>
              </div>
            </div>

            <!-- User Message -->
            <div v-else class="flex items-start justify-end">
              <div class="bg-green-500 rounded-lg p-3 shadow-sm max-w-md mr-3">
                <p class="text-white">{{ message.text }}</p>
                <div v-if="message.feedback" class="mt-2 text-xs">
                  <div class="bg-green-600 rounded p-2">
                    <p class="text-green-100">
                      <strong>Retroalimentación:</strong> {{ message.feedback }}
                    </p>
                    <div v-if="message.suggestions" class="mt-1">
                      <p class="text-green-200 text-xs">
                        <strong>Sugerencia:</strong> {{ message.suggestions }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
              <div class="bg-green-500 rounded-full p-2">
                <span class="text-white text-sm">👤</span>
              </div>
            </div>
          </div>

          <!-- Typing Indicator -->
          <div v-if="isTyping" class="flex items-start mb-4">
            <div class="bg-blue-500 rounded-full p-2 mr-3">
              <span class="text-white text-sm">🤖</span>
            </div>
            <div class="bg-white rounded-lg p-3 shadow-sm">
              <div class="flex space-x-1">
                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Input Area -->
        <div class="border-t p-4">
          <div v-if="showSuggestions" class="mb-4">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">💡 Sugerencias de respuesta:</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
              <button 
                v-for="suggestion in currentSuggestions" 
                :key="suggestion.id"
                @click="selectSuggestion(suggestion.text)"
                class="text-left border border-gray-300 rounded-lg p-2 hover:border-green-400 hover:bg-green-50 transition-colors text-sm"
              >
                {{ suggestion.text }}
              </button>
            </div>
          </div>

          <div class="flex items-center space-x-2">
            <div class="flex-1 relative">
              <input 
                v-model="userInput"
                @keyup.enter="sendMessage"
                type="text"
                class="w-full border rounded-lg p-3 pr-12 focus:outline-none focus:ring-2 focus:ring-green-500"
                placeholder="Escribe tu respuesta en inglés..."
                :disabled="isTyping || conversationEnded"
              />
              <button 
                @click="startVoiceInput"
                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-green-500 transition-colors"
                :class="{ 'text-red-500': isRecording }"
              >
                🎤
              </button>
            </div>
            
            <button 
              @click="sendMessage"
              class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 transition-colors"
              :disabled="!userInput.trim() || isTyping || conversationEnded"
            >
              Enviar
            </button>
          </div>

          <!-- Recording Indicator -->
          <div v-if="isRecording" class="mt-2 text-center">
            <div class="inline-flex items-center bg-red-100 text-red-800 px-3 py-1 rounded-full text-sm">
              <div class="w-2 h-2 bg-red-500 rounded-full mr-2 animate-pulse"></div>
              Grabando... Habla ahora
            </div>
          </div>

          <!-- Conversation Controls -->
          <div class="flex items-center justify-between mt-4 text-sm">
            <div class="flex items-center space-x-4">
              <label class="flex items-center">
                <input 
                  type="checkbox" 
                  v-model="showSuggestions" 
                  class="mr-2"
                />
                <span>Mostrar sugerencias</span>
              </label>
              
              <label class="flex items-center">
                <input 
                  type="checkbox" 
                  v-model="showTranslations" 
                  class="mr-2"
                />
                <span>Mostrar traducciones</span>
              </label>
            </div>
            
            <div class="flex items-center space-x-2">
              <span class="text-gray-500">Intercambio {{ currentTurn }}/{{ currentScenario.turns }}</span>
              <button 
                @click="endConversation"
                class="bg-gray-500 text-white px-3 py-1 rounded hover:bg-gray-600 transition-colors"
              >
                Finalizar
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Conversation Results -->
      <div v-if="currentView === 'results'" class="bg-white rounded-xl shadow-lg p-6">
        <div class="text-center mb-6">
          <h2 class="text-3xl font-bold text-gray-800 mb-2">🎉 ¡Conversación Completada!</h2>
          <p class="text-lg text-gray-600">{{ currentScenario.title }}</p>
        </div>

        <!-- Performance Scores -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
          <div class="bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl p-6 text-white text-center">
            <h3 class="text-lg font-semibold mb-2">Fluidez</h3>
            <p class="text-3xl font-bold">{{ conversationResults.fluency }}%</p>
            <p class="text-sm opacity-90">Naturalidad del diálogo</p>
          </div>
          
          <div class="bg-gradient-to-r from-blue-500 to-cyan-600 rounded-xl p-6 text-white text-center">
            <h3 class="text-lg font-semibold mb-2">Precisión</h3>
            <p class="text-3xl font-bold">{{ conversationResults.accuracy }}%</p>
            <p class="text-sm opacity-90">Corrección gramatical</p>
          </div>
          
          <div class="bg-gradient-to-r from-purple-500 to-pink-600 rounded-xl p-6 text-white text-center">
            <h3 class="text-lg font-semibold mb-2">Vocabulario</h3>
            <p class="text-3xl font-bold">{{ conversationResults.vocabulary }}%</p>
            <p class="text-sm opacity-90">Variedad y apropiación</p>
          </div>
        </div>

        <!-- Detailed Feedback -->
        <div class="bg-gray-50 rounded-lg p-6 mb-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">📝 Análisis Detallado</h3>
          
          <div class="space-y-4">
            <div>
              <h4 class="font-semibold text-green-700 mb-2">✅ Fortalezas</h4>
              <ul class="list-disc list-inside text-gray-700 space-y-1">
                <li v-for="strength in conversationResults.strengths" :key="strength">{{ strength }}</li>
              </ul>
            </div>
            
            <div>
              <h4 class="font-semibold text-orange-700 mb-2">💡 Áreas de Mejora</h4>
              <ul class="list-disc list-inside text-gray-700 space-y-1">
                <li v-for="improvement in conversationResults.improvements" :key="improvement">{{ improvement }}</li>
              </ul>
            </div>
            
            <div>
              <h4 class="font-semibold text-blue-700 mb-2">📚 Vocabulario Nuevo</h4>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                <div 
                  v-for="word in conversationResults.newVocabulary" 
                  :key="word.word"
                  class="bg-white border rounded p-2 text-center"
                >
                  <p class="font-semibold text-sm">{{ word.word }}</p>
                  <p class="text-xs text-gray-600">{{ word.translation }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Conversation Transcript -->
        <div class="bg-gray-50 rounded-lg p-6 mb-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">📜 Transcripción de la Conversación</h3>
          <div class="max-h-64 overflow-y-auto space-y-2">
            <div v-for="(message, index) in conversationHistory" :key="index" class="flex">
              <div class="w-20 text-sm font-semibold text-gray-600">
                {{ message.speaker === 'assistant' ? currentScenario.partner : 'Tú' }}:
              </div>
              <div class="flex-1 text-sm text-gray-800">{{ message.text }}</div>
            </div>
          </div>
        </div>

        <!-- Next Steps -->
        <div class="text-center space-x-4">
          <button 
            @click="practiceAgain"
            class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 transition-colors"
          >
            🔄 Practicar de Nuevo
          </button>
          <button 
            @click="goToScenarios"
            class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition-colors"
          >
            📚 Más Conversaciones
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ConversationPractice',
  data() {
    return {
      currentView: 'scenarios', // 'scenarios', 'conversation', 'results'
      currentScenario: null,
      conversationHistory: [],
      userInput: '',
      isTyping: false,
      isRecording: false,
      conversationEnded: false,
      currentTurn: 1,
      showSuggestions: true,
      showTranslations: false,
      currentSuggestions: [],
      conversationResults: {},
      userProgress: {
        completedConversations: 15,
        averageFluency: 78,
        totalTime: 6.5,
        currentLevel: 'B1'
      },
      conversationCategories: [
        {
          id: 'everyday',
          icon: '🏠',
          title: 'Situaciones Cotidianas',
          description: 'Conversaciones comunes del día a día',
          scenarios: [
            {
              id: 'coffee_shop',
              title: 'En la Cafetería',
              description: 'Ordenar café y conversar con el barista',
              difficulty: 'Principiante',
              turns: 8,
              focusArea: 'Servicios',
              partner: 'Barista',
              completed: true,
              initialMessage: 'Good morning! Welcome to our coffee shop. What can I get you today?',
              context: 'Estás en una cafetería local y quieres ordenar tu bebida favorita.',
              goals: ['Hacer un pedido', 'Preguntar sobre opciones', 'Pagar y agradecer']
            },
            {
              id: 'grocery_store',
              title: 'En el Supermercado',
              description: 'Preguntar por productos y ubicaciones',
              difficulty: 'Principiante',
              turns: 6,
              focusArea: 'Compras',
              partner: 'Empleado',
              completed: false,
              initialMessage: 'Hi there! Can I help you find anything today?',
              context: 'Necesitas encontrar productos específicos en un supermercado.',
              goals: ['Preguntar por ubicaciones', 'Solicitar ayuda', 'Agradecer la asistencia']
            },
            {
              id: 'pharmacy',
              title: 'En la Farmacia',
              description: 'Pedir medicamentos y consejos de salud',
              difficulty: 'Intermedio',
              turns: 10,
              focusArea: 'Salud',
              partner: 'Farmacéutico',
              completed: false,
              initialMessage: 'Good afternoon! How can I assist you with your health needs today?',
              context: 'Tienes síntomas de resfriado y necesitas medicamentos sin receta.',
              goals: ['Explicar síntomas', 'Pedir recomendaciones', 'Entender instrucciones']
            }
          ]
        },
        {
          id: 'work',
          icon: '💼',
          title: 'Ambiente Laboral',
          description: 'Conversaciones profesionales y de negocios',
          scenarios: [
            {
              id: 'job_interview',
              title: 'Entrevista de Trabajo',
              description: 'Responder preguntas de entrevista profesional',
              difficulty: 'Avanzado',
              turns: 12,
              focusArea: 'Entrevistas',
              partner: 'Entrevistador',
              completed: false,
              initialMessage: 'Good morning! Thank you for coming in today. Please tell me about yourself.',
              context: 'Estás en una entrevista para un trabajo que realmente quieres.',
              goals: ['Presentarte profesionalmente', 'Responder preguntas', 'Hacer preguntas relevantes']
            },
            {
              id: 'team_meeting',
              title: 'Reunión de Equipo',
              description: 'Participar en discusiones de trabajo en equipo',
              difficulty: 'Intermedio',
              turns: 15,
              focusArea: 'Reuniones',
              partner: 'Colega',
              completed: false,
              initialMessage: 'Hi everyone! Let\'s start our weekly team meeting. How was your week?',
              context: 'Estás participando en la reunión semanal de tu equipo.',
              goals: ['Compartir updates', 'Participar en discusiones', 'Proponer ideas']
            }
          ]
        },
        {
          id: 'social',
          icon: '👥',
          title: 'Situaciones Sociales',
          description: 'Conversaciones amigables e informales',
          scenarios: [
            {
              id: 'making_friends',
              title: 'Conociendo Nuevos Amigos',
              description: 'Conversación casual para hacer amistades',
              difficulty: 'Intermedio',
              turns: 10,
              focusArea: 'Socialización',
              partner: 'Nuevo Amigo',
              completed: false,
              initialMessage: 'Hi! I don\'t think we\'ve met before. I\'m Alex. Are you new to the area?',
              context: 'Estás en una fiesta local y quieres conocer gente nueva.',
              goals: ['Presentarte', 'Encontrar intereses comunes', 'Intercambiar contactos']
            },
            {
              id: 'restaurant_dinner',
              title: 'Cena en Restaurante',
              description: 'Conversación durante una cena con amigos',
              difficulty: 'Intermedio',
              turns: 12,
              focusArea: 'Restaurantes',
              partner: 'Mesero',
              completed: false,
              initialMessage: 'Good evening! Welcome to our restaurant. Do you have a reservation?',
              context: 'Estás cenando en un restaurante elegante con amigos.',
              goals: ['Hacer reservación', 'Ordenar comida', 'Manejar problemas']
            }
          ]
        },
        {
          id: 'travel',
          icon: '✈️',
          title: 'Viajes y Turismo',
          description: 'Conversaciones útiles para viajeros',
          scenarios: [
            {
              id: 'hotel_checkin',
              title: 'Check-in en Hotel',
              description: 'Proceso de registro en un hotel',
              difficulty: 'Intermedio',
              turns: 8,
              focusArea: 'Hospedaje',
              partner: 'Recepcionista',
              completed: false,
              initialMessage: 'Welcome to our hotel! Do you have a reservation with us?',
              context: 'Acabas de llegar a tu hotel después de un largo viaje.',
              goals: ['Confirmar reservación', 'Solicitar servicios', 'Resolver problemas']
            },
            {
              id: 'tourist_info',
              title: 'Centro de Información Turística',
              description: 'Pidiendo recomendaciones de lugares para visitar',
              difficulty: 'Intermedio',
              turns: 10,
              focusArea: 'Turismo',
              partner: 'Guía Turístico',
              completed: false,
              initialMessage: 'Hello! Welcome to our city! How can I help you make the most of your visit?',
              context: 'Estás visitando una nueva ciudad y necesitas recomendaciones.',
              goals: ['Pedir recomendaciones', 'Obtener direcciones', 'Planificar itinerario']
            }
          ]
        }
      ]
    }
  },
  methods: {
    startConversation(scenario) {
      this.currentScenario = scenario;
      this.currentView = 'conversation';
      this.resetConversationState();
      this.initializeConversation();
    },

    resetConversationState() {
      this.conversationHistory = [];
      this.userInput = '';
      this.isTyping = false;
      this.conversationEnded = false;
      this.currentTurn = 1;
      this.currentSuggestions = [];
    },

    initializeConversation() {
      // Add initial assistant message
      this.conversationHistory.push({
        speaker: 'assistant',
        text: this.currentScenario.initialMessage,
        translation: this.showTranslations ? this.translateToSpanish(this.currentScenario.initialMessage) : null,
        audioUrl: '/audio/conversations/greeting.mp3'
      });

      // Generate initial suggestions
      this.generateSuggestions();
    },

    generateSuggestions() {
      const suggestionSets = {
        coffee_shop: [
          { id: 1, text: "I'd like a large coffee, please." },
          { id: 2, text: "What do you recommend?" },
          { id: 3, text: "Do you have any decaf options?" },
          { id: 4, text: "Can I get that to go?" }
        ],
        grocery_store: [
          { id: 1, text: "Where can I find the dairy section?" },
          { id: 2, text: "Do you have organic vegetables?" },
          { id: 3, text: "I'm looking for gluten-free bread." },
          { id: 4, text: "Thank you for your help!" }
        ],
        job_interview: [
          { id: 1, text: "I'm a software developer with 3 years of experience." },
          { id: 2, text: "I'm passionate about solving complex problems." },
          { id: 3, text: "I work well both independently and in teams." },
          { id: 4, text: "What opportunities for growth does this position offer?" }
        ],
        making_friends: [
          { id: 1, text: "Hi Alex! I'm Maria. Nice to meet you!" },
          { id: 2, text: "Yes, I just moved here last month." },
          { id: 3, text: "I'm originally from Spain." },
          { id: 4, text: "What do you like to do for fun around here?" }
        ],
        default: [
          { id: 1, text: "That sounds great!" },
          { id: 2, text: "Could you tell me more about that?" },
          { id: 3, text: "I'm not sure I understand." },
          { id: 4, text: "Thank you for your help!" }
        ]
      };

      this.currentSuggestions = suggestionSets[this.currentScenario.id] || suggestionSets.default;
    },

    selectSuggestion(text) {
      this.userInput = text;
    },

    sendMessage() {
      if (!this.userInput.trim() || this.isTyping) return;

      // Add user message
      const userMessage = {
        speaker: 'user',
        text: this.userInput,
        feedback: this.generateFeedback(this.userInput),
        suggestions: this.generateSuggestions(this.userInput)
      };

      this.conversationHistory.push(userMessage);
      this.userInput = '';
      this.currentTurn++;

      // Show typing indicator
      this.isTyping = true;

      // Simulate AI response delay
      setTimeout(() => {
        this.generateAIResponse();
        this.isTyping = false;
      }, 2000);
    },

    generateAIResponse() {
      const responses = {
        coffee_shop: [
          "Excellent choice! Would you like any milk or sugar with that?",
          "Our specialty roast is very popular. What size would you prefer?",
          "That'll be $4.50. Will you be paying with card or cash?",
          "Here's your order! Have a wonderful day!"
        ],
        grocery_store: [
          "The dairy section is in aisle 3, on your right.",
          "Yes, our organic produce is right over there by the entrance.",
          "You'll find our gluten-free products in aisle 7.",
          "You're very welcome! Is there anything else I can help you find?"
        ],
        job_interview: [
          "That's impressive experience. What technologies do you work with?",
          "Can you give me an example of a challenging project you've completed?",
          "How do you handle working under tight deadlines?",
          "Do you have any questions about our company culture?"
        ],
        making_friends: [
          "Nice to meet you, Maria! Spain - how exciting! What brought you here?",
          "How are you finding the adjustment to living here?",
          "There are great hiking trails nearby, and lots of cultural events downtown.",
          "Would you like to grab coffee sometime and I can show you around?"
        ]
      };

      const scenarioResponses = responses[this.currentScenario.id] || [
        "That's interesting! Tell me more.",
        "I see. How do you feel about that?",
        "That makes sense. What would you like to do next?",
        "Thank you for sharing that with me."
      ];

      const responseIndex = Math.min(this.currentTurn - 2, scenarioResponses.length - 1);
      const responseText = scenarioResponses[responseIndex];

      this.conversationHistory.push({
        speaker: 'assistant',
        text: responseText,
        translation: this.showTranslations ? this.translateToSpanish(responseText) : null,
        audioUrl: '/audio/conversations/response.mp3'
      });

      // Check if conversation should end
      if (this.currentTurn >= this.currentScenario.turns) {
        this.endConversation();
      }
    },

    generateFeedback(userInput) {
      const feedbacks = [
        "Excelente uso del vocabulario apropiado para la situación.",
        "Buena estructura gramatical. Considera usar más conectores.",
        "Muy natural. Tu pronunciación está mejorando.",
        "Perfecto! Esa es exactamente la respuesta esperada."
      ];
      
      return feedbacks[Math.floor(Math.random() * feedbacks.length)];
    },

    generateSuggestionText(userInput) {
      const suggestions = [
        "Intenta agregar 'please' para sonar más cortés.",
        "Considera usar 'Could you...' para una pregunta más formal.",
        "Excelente elección de palabras para esta situación.",
        "Podrías expandir tu respuesta con más detalles."
      ];
      
      return suggestions[Math.floor(Math.random() * suggestions.length)];
    },

    translateToSpanish(text) {
      // Simple translation simulation
      const translations = {
        "Good morning! Welcome to our coffee shop. What can I get you today?": "¡Buenos días! Bienvenido a nuestra cafetería. ¿Qué puedo servirle hoy?",
        "Excellent choice! Would you like any milk or sugar with that?": "¡Excelente elección! ¿Le gustaría leche o azúcar con eso?",
        "Hi there! Can I help you find anything today?": "¡Hola! ¿Puedo ayudarle a encontrar algo hoy?",
        "The dairy section is in aisle 3, on your right.": "La sección de lácteos está en el pasillo 3, a su derecha."
      };
      
      return translations[text] || "Traducción no disponible";
    },

    startVoiceInput() {
      this.isRecording = !this.isRecording;
      
      if (this.isRecording) {
        // Simulate voice recording
        setTimeout(() => {
          this.isRecording = false;
          // Simulate speech recognition result
          this.userInput = "I would like a coffee, please.";
        }, 3000);
      }
    },

    playAudio(audioUrl) {
      // Simulate audio playback
      console.log('Playing audio:', audioUrl);
    },

    endConversation() {
      this.conversationEnded = true;
      this.calculateResults();
      this.currentView = 'results';
    },

    calculateResults() {
      // Simulate conversation analysis
      this.conversationResults = {
        fluency: Math.floor(Math.random() * 30) + 70, // 70-100
        accuracy: Math.floor(Math.random() * 25) + 75, // 75-100
        vocabulary: Math.floor(Math.random() * 35) + 65, // 65-100
        strengths: [
          "Uso natural del vocabulario específico del contexto",
          "Buena comprensión de las respuestas del interlocutor",
          "Cortesía apropiada para el nivel de formalidad"
        ],
        improvements: [
          "Practicar tiempos verbales en pasado",
          "Expandir respuestas con más detalles",
          "Mejorar pronunciación de sonidos específicos"
        ],
        newVocabulary: [
          { word: "barista", translation: "baristá" },
          { word: "to-go", translation: "para llevar" },
          { word: "decaf", translation: "descafeinado" },
          { word: "specialty", translation: "especialidad" }
        ]
      };
    },

    exitConversation() {
      this.currentView = 'scenarios';
      this.currentScenario = null;
    },

    practiceAgain() {
      this.resetConversationState();
      this.initializeConversation();
      this.currentView = 'conversation';
    },

    goToScenarios() {
      // Mark scenario as completed if finished
      if (this.currentTurn >= this.currentScenario.turns) {
        this.currentScenario.completed = true;
        this.userProgress.completedConversations++;
      }
      
      this.updateUserProgress();
      this.currentView = 'scenarios';
      this.currentScenario = null;
    },

    updateUserProgress() {
      // Update user statistics
      const newFluency = (this.userProgress.averageFluency * (this.userProgress.completedConversations - 1) + this.conversationResults.fluency) / this.userProgress.completedConversations;
      this.userProgress.averageFluency = Math.round(newFluency);
      this.userProgress.totalTime += 0.5; // Add 30 minutes
      
      // Save to localStorage
      localStorage.setItem('conversationProgress', JSON.stringify(this.userProgress));
    }
  },

  mounted() {
    // Load saved progress
    const savedProgress = localStorage.getItem('conversationProgress');
    if (savedProgress) {
      this.userProgress = { ...this.userProgress, ...JSON.parse(savedProgress) };
    }
  }
}
</script>

<style scoped>
.transition-all {
  transition: all 0.3s ease;
}

.hover\:border-green-400:hover {
  border-color: #4ade80;
}

.hover\:shadow-md:hover {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.animate-bounce {
  animation: bounce 1s infinite;
}

@keyframes bounce {
  0%, 100% {
    transform: translateY(-25%);
    animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
  }
  50% {
    transform: none;
    animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
  }
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

button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

input:focus {
  outline: none;
  box-shadow: 0 0 0 2px #10b981;
}

.max-h-64 {
  max-height: 16rem;
}

.overflow-y-auto {
  overflow-y: auto;
}
</style>