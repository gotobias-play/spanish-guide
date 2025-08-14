<template>
  <div class="container mx-auto p-4 md:p-8">
    <h2 class="text-3xl font-bold text-center mb-6">Cuestionarios de Inglés</h2>
    
    <div v-if="loading" class="text-center text-lg">Loading quizzes...</div>
    <div v-else-if="error" class="text-center text-lg text-red-500">Error: {{ error }}</div>
    
    <div v-else>
      <!-- Course-based Quiz Selection -->
      <div v-if="!selectedQuizId" class="space-y-6">
        <div v-for="course in coursesWithQuizzes" :key="course.id" class="mb-8">
          <h3 class="text-2xl font-semibold text-blue-700 mb-4">{{ course.title }}</h3>
          <p class="text-gray-600 mb-4">{{ course.description }}</p>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div 
              v-for="quiz in course.quizzes" 
              :key="quiz.id"
              class="card interactive-card p-4" 
              :class="{
                'border-2 border-orange-400 bg-orange-50': quiz.is_timed,
                'relative': quiz.is_timed
              }"
              @click="selectQuiz(quiz.id)"
            >
              <!-- Timed Quiz Badge -->
              <div v-if="quiz.is_timed" class="absolute -top-2 -right-2 bg-orange-500 text-white text-xs px-2 py-1 rounded-full font-bold">
                ⏱️ TIMED
              </div>
              
              <h4 class="text-lg font-semibold text-blue-600 mb-2">{{ quiz.title }}</h4>
              <div class="text-sm text-gray-500">
                <p>{{ quiz.question_count }} preguntas</p>
                <p>Tipo: {{ getQuizTypes(quiz.question_types) }}</p>
                
                <!-- Timer Info -->
                <div v-if="quiz.is_timed" class="mt-2 p-2 bg-orange-100 rounded text-orange-700">
                  <div v-if="quiz.time_per_question_seconds" class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z"/>
                    </svg>
                    <span class="text-xs font-medium">{{ quiz.time_per_question_seconds }}s por pregunta</span>
                  </div>
                  <div v-else-if="quiz.time_limit_seconds" class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z"/>
                    </svg>
                    <span class="text-xs font-medium">{{ Math.floor(quiz.time_limit_seconds / 60) }}:{{ String(quiz.time_limit_seconds % 60).padStart(2, '0') }} total</span>
                  </div>
                  <div v-if="quiz.timer_settings?.speed_bonus_enabled" class="text-xs text-yellow-600 mt-1">
                    ⚡ Bonus por velocidad disponible
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Mixed Quiz Option -->
        <div class="card interactive-card p-4 bg-gradient-to-r from-purple-100 to-pink-100" @click="selectMixedQuiz">
          <h3 class="text-xl font-semibold text-purple-700">🎯 Quiz Mixto</h3>
          <p class="text-gray-600">Un cuestionario desafiante con preguntas aleatorias de todas las lecciones.</p>
          <p class="text-sm text-purple-600 mt-2">{{ totalQuestions }} preguntas disponibles</p>
        </div>
      </div>
      
      <!-- Selected Quiz Display -->
      <div v-else>
        <button @click="goBack" class="mb-4 px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
          ← Volver a la selección
        </button>
        
        <TimedQuiz 
          v-if="selectedQuiz?.is_timed"
          :quiz-id="selectedQuizId" 
          @quiz-completed="handleQuizCompletion"
          :key="selectedQuizId"
        />
        <DatabaseQuiz 
          v-else
          :quiz-id="selectedQuizId" 
          @quiz-completed="handleQuizCompletion"
          :key="selectedQuizId"
        />
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import DatabaseQuiz from './DatabaseQuiz.vue';
import TimedQuiz from './TimedQuiz.vue';

export default {
  name: 'QuizSelector',
  components: {
    DatabaseQuiz,
    TimedQuiz
  },
  data() {
    return {
      courses: [],
      quizzes: [],
      loading: true,
      error: null,
      selectedQuizId: null,
      selectedQuiz: null
    };
  },
  computed: {
    coursesWithQuizzes() {
      return this.courses.map(course => {
        const courseQuizzes = this.quizzes.filter(quiz => 
          course.lessons.some(lesson => lesson.id === quiz.lesson_id)
        );
        
        return {
          ...course,
          quizzes: courseQuizzes.map(quiz => ({
            ...quiz,
            question_count: quiz.questions?.length || 0,
            question_types: [...new Set(quiz.questions?.map(q => q.question_type) || [])]
          }))
        };
      }).filter(course => course.quizzes.length > 0);
    },
    
    totalQuestions() {
      return this.quizzes.reduce((total, quiz) => total + (quiz.questions?.length || 0), 0);
    }
  },
  
  async created() {
    await this.loadData();
  },
  
  methods: {
    async loadData() {
      try {
        this.loading = true;
        this.error = null;
        
        // Load courses with lessons and quizzes with questions
        const [coursesResponse, quizzesResponse] = await Promise.all([
          axios.get('/api/public/courses'),
          axios.get('/api/public/quizzes')
        ]);
        
        this.courses = coursesResponse.data;
        this.quizzes = quizzesResponse.data;
        
      } catch (err) {
        this.error = err.message || 'Failed to load quiz data';
        console.error('Error loading quiz data:', err);
      } finally {
        this.loading = false;
      }
    },
    
    selectQuiz(quizId) {
      this.selectedQuizId = quizId;
      this.selectedQuiz = this.quizzes.find(quiz => quiz.id === quizId);
    },
    
    selectMixedQuiz() {
      // Create a virtual mixed quiz by selecting random questions from all quizzes
      if (this.quizzes.length > 0) {
        const randomQuiz = this.quizzes[Math.floor(Math.random() * this.quizzes.length)];
        this.selectQuiz(randomQuiz.id);
      }
    },
    
    goBack() {
      this.selectedQuizId = null;
      this.selectedQuiz = null;
    },
    
    getQuizTypes(types) {
      const typeMap = {
        'multiple_choice': 'Opción múltiple',
        'fill_in_the_blank': 'Completar',
        'true_false': 'Verdadero/Falso'
      };
      return types.map(type => typeMap[type] || type).join(', ');
    },
    
    async handleQuizCompletion(score, data) {
      console.log(`Quiz completed! Score: ${score}%`, data);
      
      // Check if user is authenticated by checking if we can get user data
      const isAuthenticated = await this.checkAuthenticationStatus();
      
      if (!isAuthenticated) {
        // For guest users, show simple completion message without backend calls
        const message = `Has obtenido ${score}% en "${data.quizTitle}". ¡Regístrate para guardar tu progreso y ganar puntos!`;
        
        this.$emit('show-results', {
          message: message,
          score: score,
          isGuest: true,
          registerPrompt: '¿Quieres registrarte para guardar tu progreso?'
        });
        
        setTimeout(() => {
          this.goBack();
        }, 3000);
        
        return;
      }
      
      try {
        // For authenticated users, save progress and award points
        const progressData = {
          section_id: `quiz-${data.quizId}`,
          score: score,
          data: JSON.stringify({
            ...data,
            completedAt: new Date().toISOString()
          })
        };
        
        const progressResponse = await axios.post('/api/progress', progressData);
        
        // Award points and check achievements
        const gamificationPayload = {
          quiz_id: data.quizId,
          score: score
        };
        
        // Add timed quiz data if available
        if (data.isTimed) {
          gamificationPayload.is_timed = true;
          gamificationPayload.total_time_used = data.totalTimeUsed || 0;
          gamificationPayload.speed_bonus = data.speedBonus || 0;
        }
        
        const gamificationResponse = await axios.post('/api/gamification/quiz-points', gamificationPayload);
        
        const { points_earned, base_points, speed_bonus, total_points, new_achievements } = gamificationResponse.data.data;
        
        // Update skill tracking for adaptive learning
        const skillPayload = {
          quiz_id: data.quizId,
          correct_answers: data.correctAnswers || Math.ceil((score / 100) * data.totalQuestions),
          total_questions: data.totalQuestions || data.questions?.length || 5,
          completion_time: data.totalTimeUsed || null,
          accuracy: score
        };
        
        // Add timed quiz data if available
        if (data.isTimed) {
          skillPayload.is_timed = true;
          skillPayload.speed_bonus = data.speedBonus || 0;
        }
        
        // Update skill level (don't wait for response to avoid blocking user)
        axios.post('/api/learning/update-skill', skillPayload).catch(error => {
          console.warn('Could not update skill tracking:', error);
        });
        
        // Check for new certificates
        const certificateResponse = await axios.post('/api/certificates/check');
        const { new_certificates, certificates } = certificateResponse.data.data;
        
        // Prepare success message
        let message = `Has obtenido ${score}% en "${data.quizTitle}".`;
        if (speed_bonus > 0) {
          message += ` ¡Ganaste ${base_points} puntos + ${speed_bonus} bonus por velocidad = ${points_earned} puntos!`;
        } else {
          message += ` ¡Ganaste ${points_earned} puntos!`;
        }
        if (total_points > 0) {
          message += ` Puntos totales: ${total_points}`;
        }
        
        // Show certificate notifications first
        if (new_certificates > 0) {
          for (const certificate of certificates) {
            setTimeout(() => {
              this.$emit('show-message', {
                type: 'certificate',
                title: `🏆 ¡Nuevo Certificado Obtenido!`,
                message: `¡Felicidades! Has completado "${certificate.course_title}" con nivel ${certificate.grade_level}. Código: ${certificate.certificate_code}`,
                duration: 8000
              });
            }, 500);
          }
        }
        
        // Show achievement notifications
        if (new_achievements && new_achievements.length > 0) {
          for (const achievement of new_achievements) {
            setTimeout(() => {
              this.$emit('show-message', {
                type: 'achievement',
                title: `🏆 ¡Nuevo Logro Desbloqueado!`,
                message: `${achievement.badge_icon} ${achievement.name}: ${achievement.description}`,
                duration: 6000
              });
            }, new_certificates > 0 ? 2500 : 1500); // Delay if certificates were shown
          }
        }
        
        // Show main completion message
        this.$emit('show-message', {
          type: 'success',
          title: '¡Quiz Completado!',
          message: message
        });
        
      } catch (error) {
        console.error('Error saving progress or awarding points:', error);
        
        // Still try to save progress even if gamification fails
        try {
          const progressData = {
            section_id: `quiz-${data.quizId}`,
            score: score,
            data: JSON.stringify({
              ...data,
              completedAt: new Date().toISOString()
            })
          };
          await axios.post('/api/progress', progressData);
          
          this.$emit('show-message', {
            type: 'warning',
            title: '¡Quiz Completado!',
            message: `Has obtenido ${score}% en "${data.quizTitle}". Progreso guardado, pero no se pudieron otorgar puntos.`
          });
        } catch (progressError) {
          this.$emit('show-message', {
            type: 'error',
            title: 'Error',
            message: 'No se pudo guardar tu progreso. Inténtalo de nuevo.'
          });
        }
      }
    },
    
    async checkAuthenticationStatus() {
      try {
        // Try to make a simple authenticated API call to check if user is logged in
        const response = await axios.get('/api/user');
        return response.status === 200 && response.data;
      } catch (error) {
        // If the call fails with 401 or any other error, user is not authenticated
        console.log('User not authenticated:', error.response?.status);
        return false;
      }
    }
  }
};
</script>

<style scoped>
.interactive-card {
  transition: all 0.3s ease;
  cursor: pointer;
}

.interactive-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
</style>