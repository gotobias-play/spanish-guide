<template>
  <div class="container mx-auto p-4 md:p-8">
    <h2 class="text-3xl font-bold text-center mb-6">📊 Panel de Administración</h2>
    
    <!-- Tab Navigation -->
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

    <!-- Tab Content -->
    <div class="tab-content">
      
      <!-- Users Management Tab -->
      <div v-if="activeTab === 'users'" class="tab-panel">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-bold">👥 Gestión de Usuarios</h3>
          <div class="text-sm text-gray-600">Total: {{ users.length }} usuarios</div>
        </div>
        
        <div v-if="loading.users" class="text-center text-lg">Cargando usuarios...</div>
        <div v-else-if="error.users" class="text-center text-lg text-red-500">Error: {{ error.users }}</div>
        <div v-else-if="users.length === 0" class="text-center text-lg">No se encontraron usuarios.</div>
        
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div v-for="user in users" :key="user.id" class="card p-4 border rounded-lg">
            <h4 class="text-lg font-semibold text-blue-700">{{ user.name }}</h4>
            <p class="text-gray-600">📧 {{ user.email }}</p>
            <p class="text-gray-600">🔑 {{ user.is_admin ? 'Administrador' : 'Usuario' }}</p>
            <p class="text-gray-600">📅 Registro: {{ formatDate(user.created_at) }}</p>
          </div>
        </div>
      </div>

      <!-- Courses Management Tab -->
      <div v-if="activeTab === 'courses'" class="tab-panel">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-bold">📚 Gestión de Cursos</h3>
          <button @click="showCreateCourseModal = true" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
            ➕ Nuevo Curso
          </button>
        </div>
        
        <div v-if="loading.courses" class="text-center text-lg">Cargando cursos...</div>
        <div v-else-if="error.courses" class="text-center text-lg text-red-500">Error: {{ error.courses }}</div>
        
        <div v-else class="space-y-4">
          <div v-for="course in courses" :key="course.id" class="card p-4 border rounded-lg">
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <h4 class="text-lg font-semibold text-blue-700">{{ course.title }}</h4>
                <p class="text-gray-600 mb-2">{{ course.description }}</p>
                <div class="flex items-center space-x-4 text-sm text-gray-500">
                  <span>📝 {{ course.lessons_count || 0 }} lecciones</span>
                  <span>🔄 {{ course.is_published ? 'Publicado' : 'Borrador' }}</span>
                  <span>📅 {{ formatDate(course.created_at) }}</span>
                </div>
              </div>
              <div class="flex space-x-2">
                <button @click="editCourse(course)" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                  ✏️ Editar
                </button>
                <button @click="deleteCourse(course.id)" class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">
                  🗑️ Eliminar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Lessons Management Tab -->
      <div v-if="activeTab === 'lessons'" class="tab-panel">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-bold">📖 Gestión de Lecciones</h3>
          <button @click="showCreateLessonModal = true" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
            ➕ Nueva Lección
          </button>
        </div>
        
        <div v-if="loading.lessons" class="text-center text-lg">Cargando lecciones...</div>
        <div v-else-if="error.lessons" class="text-center text-lg text-red-500">Error: {{ error.lessons }}</div>
        
        <div v-else class="space-y-4">
          <div v-for="lesson in lessons" :key="lesson.id" class="card p-4 border rounded-lg">
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <h4 class="text-lg font-semibold text-blue-700">{{ lesson.title }}</h4>
                <p class="text-gray-600 mb-2">Curso: {{ lesson.course?.title || 'Sin curso' }}</p>
                <div class="flex items-center space-x-4 text-sm text-gray-500">
                  <span>📊 Orden: {{ lesson.order }}</span>
                  <span>🔄 {{ lesson.is_published ? 'Publicado' : 'Borrador' }}</span>
                  <span>📅 {{ formatDate(lesson.created_at) }}</span>
                </div>
              </div>
              <div class="flex space-x-2">
                <button @click="editLesson(lesson)" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                  ✏️ Editar
                </button>
                <button @click="deleteLesson(lesson.id)" class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">
                  🗑️ Eliminar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quizzes Management Tab -->
      <div v-if="activeTab === 'quizzes'" class="tab-panel">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-bold">🎯 Gestión de Quizzes</h3>
          <button @click="showCreateQuizModal = true" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
            ➕ Nuevo Quiz
          </button>
        </div>
        
        <div v-if="loading.quizzes" class="text-center text-lg">Cargando quizzes...</div>
        <div v-else-if="error.quizzes" class="text-center text-lg text-red-500">Error: {{ error.quizzes }}</div>
        
        <div v-else class="space-y-4">
          <div v-for="quiz in quizzes" :key="quiz.id" class="card p-4 border rounded-lg">
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <h4 class="text-lg font-semibold text-blue-700">{{ quiz.title }}</h4>
                <p class="text-gray-600 mb-2">Lección: {{ quiz.lesson?.title || 'Sin lección' }}</p>
                <div class="flex items-center space-x-4 text-sm text-gray-500">
                  <span>❓ {{ quiz.questions_count || 0 }} preguntas</span>
                  <span>⏱️ {{ quiz.is_timed ? `${quiz.time_limit_seconds}s` : 'Sin tiempo' }}</span>
                  <span>📅 {{ formatDate(quiz.created_at) }}</span>
                </div>
              </div>
              <div class="flex space-x-2">
                <button @click="editQuiz(quiz)" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                  ✏️ Editar
                </button>
                <button @click="deleteQuiz(quiz.id)" class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">
                  🗑️ Eliminar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Questions Management Tab -->
      <div v-if="activeTab === 'questions'" class="tab-panel">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-xl font-bold">❓ Gestión de Preguntas</h3>
          <button @click="showCreateQuestionModal = true" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
            ➕ Nueva Pregunta
          </button>
        </div>
        
        <div v-if="loading.questions" class="text-center text-lg">Cargando preguntas...</div>
        <div v-else-if="error.questions" class="text-center text-lg text-red-500">Error: {{ error.questions }}</div>
        
        <div v-else class="space-y-4">
          <div v-for="question in questions" :key="question.id" class="card p-4 border rounded-lg">
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <h4 class="text-md font-semibold text-blue-700">{{ question.question_text }}</h4>
                <p class="text-gray-600 mb-2">Quiz: {{ question.quiz?.title || 'Sin quiz' }}</p>
                <div class="flex items-center space-x-4 text-sm text-gray-500">
                  <span>🔧 {{ question.question_type }}</span>
                  <span>📊 {{ question.difficulty_level || 'Sin nivel' }}</span>
                  <span>✅ {{ question.options_count || 0 }} opciones</span>
                </div>
              </div>
              <div class="flex space-x-2">
                <button @click="editQuestion(question)" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600">
                  ✏️ Editar
                </button>
                <button @click="deleteQuestion(question.id)" class="bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600">
                  🗑️ Eliminar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- User Analytics Tab -->
      <div v-if="activeTab === 'analytics'" class="tab-panel">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-bold">📊 Analíticas de Usuarios</h3>
          <button @click="loadUserAnalytics" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            🔄 Actualizar Datos
          </button>
        </div>
        
        <div v-if="loading.analytics" class="text-center text-lg">Cargando analíticas...</div>
        <div v-else-if="error.analytics" class="text-center text-lg text-red-500">Error: {{ error.analytics }}</div>
        
        <div v-else class="space-y-6">
          
          <!-- Overall Statistics -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="card p-4 border rounded-lg bg-blue-50">
              <h4 class="text-lg font-semibold text-blue-700">👥 Total Usuarios</h4>
              <p class="text-2xl font-bold text-blue-900">{{ analyticsData.totalUsers || 0 }}</p>
              <p class="text-sm text-gray-600">Registrados en la plataforma</p>
            </div>
            <div class="card p-4 border rounded-lg bg-green-50">
              <h4 class="text-lg font-semibold text-green-700">✅ Quizzes Completados</h4>
              <p class="text-2xl font-bold text-green-900">{{ analyticsData.totalQuizAttempts || 0 }}</p>
              <p class="text-sm text-gray-600">Total de intentos</p>
            </div>
            <div class="card p-4 border rounded-lg bg-yellow-50">
              <h4 class="text-lg font-semibold text-yellow-700">📈 Promedio General</h4>
              <p class="text-2xl font-bold text-yellow-900">{{ (analyticsData.averageScore || 0).toFixed(1) }}%</p>
              <p class="text-sm text-gray-600">Puntuación promedio</p>
            </div>
            <div class="card p-4 border rounded-lg bg-purple-50">
              <h4 class="text-lg font-semibold text-purple-700">🏆 Usuarios Activos</h4>
              <p class="text-2xl font-bold text-purple-900">{{ analyticsData.activeUsersLast30Days || 0 }}</p>
              <p class="text-sm text-gray-600">Últimos 30 días</p>
            </div>
          </div>

          <!-- Top Performers -->
          <div class="card p-6 border rounded-lg">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">🥇 Mejores Estudiantes</h4>
            <div v-if="analyticsData.topPerformers && analyticsData.topPerformers.length > 0" class="space-y-3">
              <div v-for="(user, index) in analyticsData.topPerformers.slice(0, 10)" :key="user.id" 
                   class="flex items-center justify-between p-3 bg-gray-50 rounded">
                <div class="flex items-center">
                  <span class="text-lg font-bold mr-3">{{ index + 1 }}.</span>
                  <div>
                    <span class="font-medium">{{ user.name }}</span>
                    <span class="text-gray-600 ml-2">({{ user.email }})</span>
                  </div>
                </div>
                <div class="text-right">
                  <div class="font-bold text-blue-600">{{ user.total_points || 0 }} puntos</div>
                  <div class="text-sm text-gray-500">{{ user.quiz_count || 0 }} quizzes • {{ (user.average_score || 0).toFixed(1) }}%</div>
                </div>
              </div>
            </div>
            <div v-else class="text-center text-gray-500">No hay datos de rendimiento disponibles</div>
          </div>

          <!-- Course Performance -->
          <div class="card p-6 border rounded-lg">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">📚 Rendimiento por Curso</h4>
            <div v-if="analyticsData.coursePerformance && analyticsData.coursePerformance.length > 0" class="space-y-3">
              <div v-for="course in analyticsData.coursePerformance" :key="course.course_id" 
                   class="flex items-center justify-between p-3 bg-gray-50 rounded">
                <div>
                  <span class="font-medium">{{ course.course_title || 'Curso sin título' }}</span>
                  <div class="text-sm text-gray-600">{{ course.total_attempts || 0 }} intentos totales</div>
                </div>
                <div class="text-right">
                  <div class="font-bold text-green-600">{{ (course.average_score || 0).toFixed(1) }}%</div>
                  <div class="text-sm text-gray-500">Promedio del curso</div>
                </div>
              </div>
            </div>
            <div v-else class="text-center text-gray-500">No hay datos de cursos disponibles</div>
          </div>

          <!-- Recent Activity -->
          <div class="card p-6 border rounded-lg">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">🕐 Actividad Reciente</h4>
            <div v-if="analyticsData.recentActivity && analyticsData.recentActivity.length > 0" class="space-y-3">
              <div v-for="activity in analyticsData.recentActivity.slice(0, 15)" :key="activity.id" 
                   class="flex items-center justify-between p-3 bg-gray-50 rounded">
                <div>
                  <span class="font-medium">{{ activity.user_name }}</span>
                  <span class="text-gray-600 mx-2">completó</span>
                  <span class="font-medium">{{ activity.section_id }}</span>
                </div>
                <div class="text-right">
                  <div class="font-bold text-blue-600">{{ activity.score }}%</div>
                  <div class="text-sm text-gray-500">{{ formatDate(activity.created_at) }}</div>
                </div>
              </div>
            </div>
            <div v-else class="text-center text-gray-500">No hay actividad reciente</div>
          </div>

          <!-- User Registration Trends -->
          <div class="card p-6 border rounded-lg">
            <h4 class="text-lg font-semibold text-gray-800 mb-4">📊 Registros por Mes</h4>
            <div v-if="analyticsData.registrationTrends && analyticsData.registrationTrends.length > 0" class="space-y-2">
              <div v-for="trend in analyticsData.registrationTrends" :key="trend.month" 
                   class="flex items-center justify-between p-2 bg-gray-50 rounded">
                <span class="font-medium">{{ trend.month }}</span>
                <span class="font-bold text-blue-600">{{ trend.count }} nuevos usuarios</span>
              </div>
            </div>
            <div v-else class="text-center text-gray-500">No hay datos de tendencias disponibles</div>
          </div>

        </div>
      </div>

    </div>

    <!-- Loading Overlay -->
    <div v-if="isSubmitting" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg">
        <div class="text-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto mb-2"></div>
          <p>Procesando...</p>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'AdminPanel',
  data() {
    return {
      activeTab: 'users',
      tabs: [
        { id: 'users', name: 'Usuarios', icon: '👥' },
        { id: 'analytics', name: 'Analíticas', icon: '📊' },
        { id: 'courses', name: 'Cursos', icon: '📚' },
        { id: 'lessons', name: 'Lecciones', icon: '📖' },
        { id: 'quizzes', name: 'Quizzes', icon: '🎯' },
        { id: 'questions', name: 'Preguntas', icon: '❓' }
      ],
      
      // Data arrays
      users: [],
      courses: [],
      lessons: [],
      quizzes: [],
      questions: [],
      analyticsData: {},
      
      // Loading states
      loading: {
        users: true,
        courses: false,
        lessons: false,
        quizzes: false,
        questions: false,
        analytics: false
      },
      
      // Error states
      error: {
        users: null,
        courses: null,
        lessons: null,
        quizzes: null,
        questions: null,
        analytics: null
      },
      
      // Modal states
      showCreateCourseModal: false,
      showCreateLessonModal: false,
      showCreateQuizModal: false,
      showCreateQuestionModal: false,
      
      // Form states
      isSubmitting: false,
      editingItem: null,
    };
  },
  
  async created() {
    await this.loadUsers();
  },
  
  watch: {
    activeTab(newTab) {
      this.loadTabData(newTab);
    }
  },
  
  methods: {
    // Tab data loading
    async loadTabData(tab) {
      switch(tab) {
        case 'users':
          if (this.users.length === 0) await this.loadUsers();
          break;
        case 'analytics':
          if (!this.analyticsData.totalUsers) await this.loadUserAnalytics();
          break;
        case 'courses':
          if (this.courses.length === 0) await this.loadCourses();
          break;
        case 'lessons':
          if (this.lessons.length === 0) await this.loadLessons();
          break;
        case 'quizzes':
          if (this.quizzes.length === 0) await this.loadQuizzes();
          break;
        case 'questions':
          if (this.questions.length === 0) await this.loadQuestions();
          break;
      }
    },
    
    // Data loading methods
    async loadUsers() {
      this.loading.users = true;
      this.error.users = null;
      try {
        const response = await axios.get('/api/admin/users');
        this.users = response.data;
      } catch (err) {
        this.error.users = err.response?.data?.message || err.message;
        console.error('Error fetching users:', err);
      } finally {
        this.loading.users = false;
      }
    },
    
    async loadCourses() {
      this.loading.courses = true;
      this.error.courses = null;
      try {
        const response = await axios.get('/api/courses');
        this.courses = response.data;
      } catch (err) {
        this.error.courses = err.response?.data?.message || err.message;
        console.error('Error fetching courses:', err);
      } finally {
        this.loading.courses = false;
      }
    },
    
    async loadLessons() {
      this.loading.lessons = true;
      this.error.lessons = null;
      try {
        const response = await axios.get('/api/lessons');
        this.lessons = response.data;
      } catch (err) {
        this.error.lessons = err.response?.data?.message || err.message;
        console.error('Error fetching lessons:', err);
      } finally {
        this.loading.lessons = false;
      }
    },
    
    async loadQuizzes() {
      this.loading.quizzes = true;
      this.error.quizzes = null;
      try {
        const response = await axios.get('/api/quizzes');
        this.quizzes = response.data;
      } catch (err) {
        this.error.quizzes = err.response?.data?.message || err.message;
        console.error('Error fetching quizzes:', err);
      } finally {
        this.loading.quizzes = false;
      }
    },
    
    async loadQuestions() {
      this.loading.questions = true;
      this.error.questions = null;
      try {
        const response = await axios.get('/api/questions');
        this.questions = response.data;
      } catch (err) {
        this.error.questions = err.response?.data?.message || err.message;
        console.error('Error fetching questions:', err);
      } finally {
        this.loading.questions = false;
      }
    },
    
    async loadUserAnalytics() {
      this.loading.analytics = true;
      this.error.analytics = null;
      try {
        // Create comprehensive analytics data from available API endpoints
        const [usersResponse, progressResponse] = await Promise.all([
          axios.get('/api/admin/users'),
          axios.get('/api/progress')
        ]);
        
        const users = usersResponse.data;
        const progressData = progressResponse.data;
        
        // Calculate analytics
        const totalUsers = users.length;
        const totalQuizAttempts = progressData.length;
        const averageScore = progressData.length > 0 
          ? progressData.reduce((sum, p) => sum + p.score, 0) / progressData.length 
          : 0;
        
        // Get active users (users with recent progress)
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
        const activeUsersLast30Days = progressData.filter(p => 
          new Date(p.created_at) > thirtyDaysAgo
        ).map(p => p.user_id).filter((id, index, arr) => arr.indexOf(id) === index).length;
        
        // Calculate top performers
        const userStats = {};
        progressData.forEach(progress => {
          if (!userStats[progress.user_id]) {
            const user = users.find(u => u.id === progress.user_id);
            userStats[progress.user_id] = {
              id: progress.user_id,
              name: user?.name || 'Usuario Desconocido',
              email: user?.email || '',
              total_points: 0,
              quiz_count: 0,
              total_score: 0,
              average_score: 0
            };
          }
          userStats[progress.user_id].total_points += Math.floor(progress.score / 10); // Estimate points
          userStats[progress.user_id].quiz_count += 1;
          userStats[progress.user_id].total_score += progress.score;
        });
        
        // Calculate averages for top performers
        const topPerformers = Object.values(userStats)
          .map(user => ({
            ...user,
            average_score: user.quiz_count > 0 ? user.total_score / user.quiz_count : 0
          }))
          .sort((a, b) => b.total_points - a.total_points);
        
        // Course performance (group by section_id)
        const courseStats = {};
        progressData.forEach(progress => {
          if (!courseStats[progress.section_id]) {
            courseStats[progress.section_id] = {
              course_id: progress.section_id,
              course_title: progress.section_id.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase()),
              total_attempts: 0,
              total_score: 0,
              average_score: 0
            };
          }
          courseStats[progress.section_id].total_attempts += 1;
          courseStats[progress.section_id].total_score += progress.score;
        });
        
        const coursePerformance = Object.values(courseStats).map(course => ({
          ...course,
          average_score: course.total_attempts > 0 ? course.total_score / course.total_attempts : 0
        }));
        
        // Recent activity
        const recentActivity = progressData
          .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
          .slice(0, 15)
          .map(progress => ({
            id: progress.id,
            user_name: users.find(u => u.id === progress.user_id)?.name || 'Usuario Desconocido',
            section_id: progress.section_id,
            score: progress.score,
            created_at: progress.created_at
          }));
        
        // Registration trends (by month)
        const registrationsByMonth = {};
        users.forEach(user => {
          const date = new Date(user.created_at);
          const monthKey = date.toLocaleDateString('es-ES', { year: 'numeric', month: 'long' });
          registrationsByMonth[monthKey] = (registrationsByMonth[monthKey] || 0) + 1;
        });
        
        const registrationTrends = Object.entries(registrationsByMonth)
          .map(([month, count]) => ({ month, count }))
          .sort((a, b) => new Date(a.month) - new Date(b.month));
        
        this.analyticsData = {
          totalUsers,
          totalQuizAttempts,
          averageScore,
          activeUsersLast30Days,
          topPerformers,
          coursePerformance,
          recentActivity,
          registrationTrends
        };
        
      } catch (err) {
        this.error.analytics = err.response?.data?.message || err.message;
        console.error('Error fetching analytics:', err);
      } finally {
        this.loading.analytics = false;
      }
    },
    
    // Edit methods (to be implemented with modals)
    editCourse(course) {
      this.editingItem = course;
      this.showCreateCourseModal = true;
      console.log('Editing course:', course);
    },
    
    editLesson(lesson) {
      this.editingItem = lesson;
      this.showCreateLessonModal = true;
      console.log('Editing lesson:', lesson);
    },
    
    editQuiz(quiz) {
      this.editingItem = quiz;
      this.showCreateQuizModal = true;
      console.log('Editing quiz:', quiz);
    },
    
    editQuestion(question) {
      this.editingItem = question;
      this.showCreateQuestionModal = true;
      console.log('Editing question:', question);
    },
    
    // Delete methods
    async deleteCourse(courseId) {
      if (!confirm('¿Estás seguro de que quieres eliminar este curso? Esta acción no se puede deshacer.')) {
        return;
      }
      
      this.isSubmitting = true;
      try {
        await axios.delete(`/api/courses/${courseId}`);
        this.courses = this.courses.filter(c => c.id !== courseId);
        alert('Curso eliminado exitosamente');
      } catch (err) {
        alert('Error al eliminar el curso: ' + (err.response?.data?.message || err.message));
      } finally {
        this.isSubmitting = false;
      }
    },
    
    async deleteLesson(lessonId) {
      if (!confirm('¿Estás seguro de que quieres eliminar esta lección?')) {
        return;
      }
      
      this.isSubmitting = true;
      try {
        await axios.delete(`/api/lessons/${lessonId}`);
        this.lessons = this.lessons.filter(l => l.id !== lessonId);
        alert('Lección eliminada exitosamente');
      } catch (err) {
        alert('Error al eliminar la lección: ' + (err.response?.data?.message || err.message));
      } finally {
        this.isSubmitting = false;
      }
    },
    
    async deleteQuiz(quizId) {
      if (!confirm('¿Estás seguro de que quieres eliminar este quiz?')) {
        return;
      }
      
      this.isSubmitting = true;
      try {
        await axios.delete(`/api/quizzes/${quizId}`);
        this.quizzes = this.quizzes.filter(q => q.id !== quizId);
        alert('Quiz eliminado exitosamente');
      } catch (err) {
        alert('Error al eliminar el quiz: ' + (err.response?.data?.message || err.message));
      } finally {
        this.isSubmitting = false;
      }
    },
    
    async deleteQuestion(questionId) {
      if (!confirm('¿Estás seguro de que quieres eliminar esta pregunta?')) {
        return;
      }
      
      this.isSubmitting = true;
      try {
        await axios.delete(`/api/questions/${questionId}`);
        this.questions = this.questions.filter(q => q.id !== questionId);
        alert('Pregunta eliminada exitosamente');
      } catch (err) {
        alert('Error al eliminar la pregunta: ' + (err.response?.data?.message || err.message));
      } finally {
        this.isSubmitting = false;
      }
    },
    
    // Utility methods
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    }
  }
};
</script>
