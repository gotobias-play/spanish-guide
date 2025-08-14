<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Panel de Instructor</h1>
            <p class="text-gray-600 mt-1">Gestiona tus clases, tareas y estudiantes</p>
          </div>
          <div class="flex items-center space-x-4">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
              👨‍🏫 Instructor
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center h-64">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Main Content -->
    <div v-else class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Dashboard Stats -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                📚
              </div>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-semibold text-gray-900">{{ dashboardStats.total_classes || 0 }}</h3>
              <p class="text-sm text-gray-600">Clases Total</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                👨‍🎓
              </div>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-semibold text-gray-900">{{ dashboardStats.total_students || 0 }}</h3>
              <p class="text-sm text-gray-600">Estudiantes</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                📝
              </div>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-semibold text-gray-900">{{ dashboardStats.total_assignments || 0 }}</h3>
              <p class="text-sm text-gray-600">Tareas</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                ⏰
              </div>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-semibold text-gray-900">{{ dashboardStats.pending_grades || 0 }}</h3>
              <p class="text-sm text-gray-600">Pendientes</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab Navigation -->
      <div class="bg-white rounded-lg shadow mb-6">
        <div class="border-b border-gray-200">
          <nav class="-mb-px flex space-x-8 px-6">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                'py-4 px-1 border-b-2 font-medium text-sm',
                activeTab === tab.id
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              {{ tab.icon }} {{ tab.name }}
            </button>
          </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
          <!-- Classes Tab -->
          <div v-if="activeTab === 'classes'">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-xl font-semibold text-gray-900">Mis Clases</h2>
              <button
                @click="showCreateClassModal = true"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center"
              >
                ➕ Nueva Clase
              </button>
            </div>

            <!-- Classes Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div
                v-for="classItem in classes"
                :key="classItem.id"
                class="bg-gray-50 rounded-lg p-6 border-l-4 border-blue-500"
              >
                <div class="flex justify-between items-start mb-4">
                  <h3 class="text-lg font-semibold text-gray-900">{{ classItem.name }}</h3>
                  <span
                    :class="[
                      'px-2 py-1 rounded-full text-xs font-medium',
                      classItem.is_active
                        ? 'bg-green-100 text-green-800'
                        : 'bg-gray-100 text-gray-800'
                    ]"
                  >
                    {{ classItem.is_active ? 'Activa' : 'Inactiva' }}
                  </span>
                </div>
                
                <p class="text-gray-600 text-sm mb-4">{{ classItem.description }}</p>
                
                <div class="flex justify-between items-center text-sm text-gray-600 mb-4">
                  <span>👨‍🎓 {{ classItem.enrollment_count }}/{{ classItem.max_students }}</span>
                  <span>🔑 {{ classItem.class_code }}</span>
                </div>

                <div class="flex space-x-2">
                  <button
                    @click="viewClass(classItem)"
                    class="flex-1 bg-blue-100 text-blue-700 px-3 py-2 rounded text-sm hover:bg-blue-200"
                  >
                    Ver Detalles
                  </button>
                  <button
                    @click="manageAssignments(classItem)"
                    class="flex-1 bg-green-100 text-green-700 px-3 py-2 rounded text-sm hover:bg-green-200"
                  >
                    Tareas
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Assignments Tab -->
          <div v-if="activeTab === 'assignments'">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-xl font-semibold text-gray-900">Tareas y Calificaciones</h2>
              <button
                @click="showCreateAssignmentModal = true"
                class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center"
              >
                📝 Nueva Tarea
              </button>
            </div>

            <!-- Recent Submissions -->
            <div class="bg-white rounded-lg border">
              <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-medium text-gray-900">Entregas Recientes</h3>
              </div>
              <div class="divide-y divide-gray-200">
                <div
                  v-for="submission in recentSubmissions"
                  :key="submission.id"
                  class="px-6 py-4 hover:bg-gray-50"
                >
                  <div class="flex items-center justify-between">
                    <div class="flex-1">
                      <p class="text-sm font-medium text-gray-900">
                        {{ submission.assignment?.title || 'Sin título' }}
                      </p>
                      <p class="text-sm text-gray-600">
                        Por {{ submission.student?.name || 'Estudiante' }}
                      </p>
                      <p class="text-xs text-gray-500">
                        {{ formatDate(submission.submitted_at) }}
                      </p>
                    </div>
                    <div class="flex items-center space-x-3">
                      <span
                        :class="[
                          'px-2 py-1 rounded-full text-xs font-medium',
                          submission.status === 'submitted'
                            ? 'bg-yellow-100 text-yellow-800'
                            : submission.status === 'graded'
                            ? 'bg-green-100 text-green-800'
                            : 'bg-gray-100 text-gray-800'
                        ]"
                      >
                        {{ getStatusText(submission.status) }}
                      </span>
                      <button
                        @click="gradeSubmission(submission)"
                        class="text-blue-600 hover:text-blue-800 text-sm"
                      >
                        {{ submission.status === 'submitted' ? 'Calificar' : 'Ver Calificación' }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Students Tab -->
          <div v-if="activeTab === 'students'">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Gestión de Estudiantes</h2>
            
            <!-- Class Filter -->
            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-2">Filtrar por Clase:</label>
              <select
                v-model="selectedClassFilter"
                @change="filterStudents"
                class="block w-full md:w-1/3 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Todas las Clases</option>
                <option
                  v-for="classItem in classes"
                  :key="classItem.id"
                  :value="classItem.id"
                >
                  {{ classItem.name }}
                </option>
              </select>
            </div>

            <!-- Students Table -->
            <div class="bg-white rounded-lg border overflow-hidden">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Estudiante
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Clase
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Estado
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Inscrito
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Acciones
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="student in filteredStudents" :key="`${student.class_id}-${student.student_id}`">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">{{ student.student_name }}</div>
                      <div class="text-sm text-gray-500">{{ student.student_email }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ student.class_name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                        :class="[
                          'px-2 py-1 rounded-full text-xs font-medium',
                          student.status === 'active'
                            ? 'bg-green-100 text-green-800'
                            : 'bg-gray-100 text-gray-800'
                        ]"
                      >
                        {{ student.status === 'active' ? 'Activo' : 'Inactivo' }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ formatDate(student.enrolled_at) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <button
                        @click="viewStudentProgress(student)"
                        class="text-blue-600 hover:text-blue-900 mr-3"
                      >
                        Ver Progreso
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Analytics Tab -->
          <div v-if="activeTab === 'analytics'">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Análisis de Rendimiento</h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Class Performance -->
              <div class="bg-white rounded-lg border p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Rendimiento por Clase</h3>
                <div class="space-y-4">
                  <div
                    v-for="classItem in classes"
                    :key="classItem.id"
                    class="flex items-center justify-between p-3 bg-gray-50 rounded"
                  >
                    <span class="text-sm font-medium text-gray-900">{{ classItem.name }}</span>
                    <div class="flex items-center space-x-2">
                      <span class="text-sm text-gray-600">{{ classItem.enrollment_count }} estudiantes</span>
                      <div class="w-16 bg-gray-200 rounded-full h-2">
                        <div
                          class="bg-blue-600 h-2 rounded-full"
                          :style="{ width: `${(classItem.enrollment_count / classItem.max_students) * 100}%` }"
                        ></div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Assignment Status -->
              <div class="bg-white rounded-lg border p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Estado de Tareas</h3>
                <div class="space-y-3">
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Total de Tareas</span>
                    <span class="text-sm font-medium text-gray-900">{{ dashboardStats.total_assignments || 0 }}</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Pendientes de Calificar</span>
                    <span class="text-sm font-medium text-red-600">{{ dashboardStats.pending_grades || 0 }}</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Calificadas</span>
                    <span class="text-sm font-medium text-green-600">
                      {{ (dashboardStats.total_assignments || 0) - (dashboardStats.pending_grades || 0) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Class Modal -->
    <div v-if="showCreateClassModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Crear Nueva Clase</h3>
          <form @submit.prevent="createClass">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de la Clase</label>
                <input
                  v-model="newClass.name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Ej: Inglés Intermedio A2"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                <textarea
                  v-model="newClass.description"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Descripción del curso..."
                ></textarea>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Máximo de Estudiantes</label>
                  <input
                    v-model.number="newClass.max_students"
                    type="number"
                    min="1"
                    max="100"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Inicio</label>
                  <input
                    v-model="newClass.start_date"
                    type="date"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Finalización (Opcional)</label>
                <input
                  v-model="newClass.end_date"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                />
              </div>
            </div>
            <div class="flex items-center justify-end space-x-3 mt-6">
              <button
                type="button"
                @click="showCreateClassModal = false"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
              >
                Cancelar
              </button>
              <button
                type="submit"
                :disabled="creatingClass"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50"
              >
                {{ creatingClass ? 'Creando...' : 'Crear Clase' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'InstructorDashboard',
  data() {
    return {
      loading: true,
      activeTab: 'classes',
      dashboardStats: {},
      classes: [],
      recentSubmissions: [],
      filteredStudents: [],
      selectedClassFilter: '',
      showCreateClassModal: false,
      showCreateAssignmentModal: false,
      creatingClass: false,
      newClass: {
        name: '',
        description: '',
        max_students: 25,
        start_date: '',
        end_date: ''
      },
      tabs: [
        { id: 'classes', name: 'Clases', icon: '📚' },
        { id: 'assignments', name: 'Tareas', icon: '📝' },
        { id: 'students', name: 'Estudiantes', icon: '👨‍🎓' },
        { id: 'analytics', name: 'Análisis', icon: '📊' }
      ]
    };
  },
  async mounted() {
    await this.loadDashboardData();
  },
  methods: {
    async loadDashboardData() {
      try {
        this.loading = true;
        
        // Load dashboard statistics
        const statsResponse = await axios.get('/api/instructor/dashboard');
        this.dashboardStats = statsResponse.data.dashboard;
        this.recentSubmissions = statsResponse.data.dashboard.recent_submissions || [];

        // Load classes
        const classesResponse = await axios.get('/api/instructor/classes');
        this.classes = classesResponse.data.classes;

        // Load students from all classes
        await this.loadAllStudents();

      } catch (error) {
        console.error('Error loading dashboard data:', error);
        alert('Error al cargar los datos del panel');
      } finally {
        this.loading = false;
      }
    },

    async loadAllStudents() {
      try {
        const students = [];
        for (const classItem of this.classes) {
          const response = await axios.get(`/api/instructor/classes/${classItem.id}`);
          const classDetails = response.data.class;
          
          if (classDetails.students) {
            classDetails.students.forEach(student => {
              students.push({
                class_id: classItem.id,
                class_name: classItem.name,
                student_id: student.id,
                student_name: student.name,
                student_email: student.email,
                status: student.pivot?.status || 'active',
                enrolled_at: student.pivot?.enrolled_at
              });
            });
          }
        }
        this.filteredStudents = students;
      } catch (error) {
        console.error('Error loading students:', error);
      }
    },

    async createClass() {
      try {
        this.creatingClass = true;
        
        const response = await axios.post('/api/instructor/classes', this.newClass);
        
        this.classes.push(response.data.class);
        this.showCreateClassModal = false;
        this.resetNewClass();
        
        alert('Clase creada exitosamente');
        
        // Reload dashboard stats
        await this.loadDashboardData();
        
      } catch (error) {
        console.error('Error creating class:', error);
        alert('Error al crear la clase');
      } finally {
        this.creatingClass = false;
      }
    },

    resetNewClass() {
      this.newClass = {
        name: '',
        description: '',
        max_students: 25,
        start_date: '',
        end_date: ''
      };
    },

    filterStudents() {
      if (!this.selectedClassFilter) {
        this.loadAllStudents();
        return;
      }

      this.filteredStudents = this.filteredStudents.filter(
        student => student.class_id == this.selectedClassFilter
      );
    },

    viewClass(classItem) {
      // Navigate to class details view
      this.$router.push(`/instructor/class/${classItem.id}`);
    },

    manageAssignments(classItem) {
      // Navigate to assignments management for this class
      this.$router.push(`/instructor/class/${classItem.id}/assignments`);
    },

    gradeSubmission(submission) {
      // Navigate to grading interface
      this.$router.push(`/instructor/grade/${submission.assignment.id}/${submission.id}`);
    },

    viewStudentProgress(student) {
      // Navigate to student progress view
      this.$router.push(`/instructor/student/${student.student_id}/progress`);
    },

    formatDate(dateString) {
      if (!dateString) return '';
      return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    },

    getStatusText(status) {
      const statusMap = {
        'draft': 'Borrador',
        'submitted': 'Entregado',
        'graded': 'Calificado',
        'returned': 'Devuelto'
      };
      return statusMap[status] || status;
    }
  }
};
</script>