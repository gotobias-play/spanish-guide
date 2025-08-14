<template>
  <div class="max-w-6xl mx-auto p-6 space-y-8">
    <!-- Header Section -->
    <div class="text-center">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">🏆 Mis Certificados</h1>
      <p class="text-gray-600">Celebra tus logros de aprendizaje completados</p>
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
        <button @click="fetchCertificates" class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
          Reintentar
        </button>
      </div>
    </div>

    <!-- Main Content -->
    <div v-else class="space-y-8">
      <!-- Overview Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white p-6 rounded-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-blue-100">Certificados Obtenidos</p>
              <p class="text-3xl font-bold">{{ certificateData.total_certificates }}</p>
            </div>
            <div class="text-4xl opacity-80">🏆</div>
          </div>
        </div>

        <div class="bg-gradient-to-r from-green-500 to-emerald-600 text-white p-6 rounded-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-green-100">Cursos Disponibles</p>
              <p class="text-3xl font-bold">{{ certificateData.total_available_courses }}</p>
            </div>
            <div class="text-4xl opacity-80">📚</div>
          </div>
        </div>

        <div class="bg-gradient-to-r from-purple-500 to-pink-600 text-white p-6 rounded-lg">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-purple-100">Progreso Completado</p>
              <p class="text-3xl font-bold">{{ certificateData.completion_percentage }}%</p>
            </div>
            <div class="text-4xl opacity-80">📈</div>
          </div>
        </div>
      </div>

      <!-- Progress Bar -->
      <div class="bg-white p-6 rounded-lg border border-gray-200">
        <div class="flex items-center justify-between mb-3">
          <h3 class="text-lg font-semibold text-gray-900">Progreso General de Certificaciones</h3>
          <span class="text-sm text-gray-600">{{ certificateData.total_certificates }}/{{ certificateData.total_available_courses }}</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3">
          <div 
            class="bg-gradient-to-r from-blue-500 to-indigo-600 h-3 rounded-full transition-all duration-500 ease-out"
            :style="{ width: certificateData.completion_percentage + '%' }"
          ></div>
        </div>
      </div>

      <!-- No Certificates State -->
      <div v-if="certificateData.total_certificates === 0" class="text-center py-12">
        <div class="text-6xl mb-4">📜</div>
        <h3 class="text-xl font-semibold text-gray-900 mb-2">¡Comienza tu viaje de aprendizaje!</h3>
        <p class="text-gray-600 mb-6">Completa todos los cuestionarios de un curso para obtener tu primer certificado.</p>
        <button @click="goToPractica" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
          Explorar Cursos
        </button>
      </div>

      <!-- Certificates Grid -->
      <div v-else class="space-y-6">
        <h2 class="text-2xl font-bold text-gray-900">Certificados Obtenidos</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div 
            v-for="certificate in certificateData.certificates" 
            :key="certificate.id"
            class="bg-white border-2 border-gray-200 rounded-lg p-6 hover:border-blue-300 hover:shadow-lg transition-all duration-200 cursor-pointer"
            @click="viewCertificate(certificate.id)"
          >
            <!-- Certificate Header -->
            <div class="flex items-start justify-between mb-4">
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ certificate.course_title }}</h3>
                <p class="text-sm text-gray-600">{{ certificate.certificate_code }}</p>
              </div>
              <div class="text-2xl">🏆</div>
            </div>

            <!-- Certificate Stats -->
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Calificación:</span>
                <div class="flex items-center space-x-2">
                  <span class="font-semibold text-gray-900">{{ certificate.average_score }}%</span>
                  <span class="px-2 py-1 text-xs rounded-full" :class="getGradeClass(certificate.grade_level)">
                    {{ certificate.grade_level }}
                  </span>
                </div>
              </div>

              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Puntos Obtenidos:</span>
                <span class="font-semibold text-gray-900">{{ certificate.total_points }}</span>
              </div>

              <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">Completado:</span>
                <span class="font-semibold text-gray-900">{{ certificate.completed_at }}</span>
              </div>
            </div>

            <!-- View Certificate Button -->
            <div class="mt-4 pt-4 border-t border-gray-100">
              <button class="w-full px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                Ver Certificado
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Certificate Modal -->
      <div v-if="showCertificateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" @click="closeCertificateModal">
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto" @click.stop>
          <div v-if="selectedCertificate" class="p-8">
            <!-- Certificate Design -->
            <div class="border-4 border-blue-600 bg-gradient-to-br from-blue-50 to-indigo-100 p-8 text-center rounded-lg">
              <!-- Header -->
              <div class="mb-6">
                <h2 class="text-3xl font-bold text-blue-900 mb-2">Certificado de Finalización</h2>
                <p class="text-blue-700">Spanish English Learning App</p>
              </div>

              <!-- Award Text -->
              <div class="mb-6">
                <p class="text-lg text-gray-700 mb-2">Se otorga el presente certificado a:</p>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">{{ selectedCertificate.user_name }}</h3>
                <p class="text-lg text-gray-700 mb-2">Por completar exitosamente el curso:</p>
                <h4 class="text-xl font-semibold text-blue-800">{{ selectedCertificate.course_title }}</h4>
              </div>

              <!-- Stats -->
              <div class="grid grid-cols-2 gap-6 mb-6 text-sm">
                <div class="bg-white bg-opacity-50 p-4 rounded-lg">
                  <p class="text-gray-600">Calificación Promedio</p>
                  <p class="text-xl font-bold text-gray-900">{{ selectedCertificate.average_score }}%</p>
                </div>
                <div class="bg-white bg-opacity-50 p-4 rounded-lg">
                  <p class="text-gray-600">Nivel Alcanzado</p>
                  <p class="text-lg font-semibold" :class="getGradeTextClass(selectedCertificate.grade_level)">{{ selectedCertificate.grade_level }}</p>
                </div>
              </div>

              <!-- Certificate Code -->
              <div class="mb-4">
                <p class="text-sm text-gray-600">Código de Certificado</p>
                <p class="font-mono text-lg font-bold text-gray-900">{{ selectedCertificate.certificate_code }}</p>
              </div>

              <!-- Date -->
              <div class="mb-6">
                <p class="text-sm text-gray-600">Fecha de Finalización</p>
                <p class="text-lg font-semibold text-gray-900">{{ selectedCertificate.course_completed_at }}</p>
              </div>

              <!-- Decorative elements -->
              <div class="flex justify-center items-center text-6xl opacity-20">
                🏆
              </div>
            </div>

            <!-- Modal Actions -->
            <div class="mt-6 flex justify-end space-x-4">
              <button @click="closeCertificateModal" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                Cerrar
              </button>
              <button @click="downloadCertificate" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Descargar
              </button>
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
  name: 'CertificateDashboard',
  data() {
    return {
      loading: true,
      error: null,
      certificateData: {
        total_certificates: 0,
        total_available_courses: 0,
        completion_percentage: 0,
        certificates: []
      },
      showCertificateModal: false,
      selectedCertificate: null
    }
  },
  mounted() {
    this.fetchCertificates()
  },
  methods: {
    async fetchCertificates() {
      try {
        this.loading = true
        this.error = null

        const response = await axios.get('/api/certificates')
        
        if (response.data.success) {
          this.certificateData = response.data.data
        } else {
          this.error = response.data.message || 'Error al cargar certificados'
        }
      } catch (error) {
        console.error('Error fetching certificates:', error)
        this.error = 'Error de conexión. Por favor, intenta de nuevo.'
      } finally {
        this.loading = false
      }
    },

    async viewCertificate(certificateId) {
      try {
        const response = await axios.get(`/api/certificates/${certificateId}`)
        
        if (response.data.success) {
          this.selectedCertificate = response.data.data
          this.showCertificateModal = true
        } else {
          alert('Error al cargar el certificado')
        }
      } catch (error) {
        console.error('Error fetching certificate:', error)
        alert('Error al cargar el certificado')
      }
    },

    closeCertificateModal() {
      this.showCertificateModal = false
      this.selectedCertificate = null
    },

    downloadCertificate() {
      // For now, we'll just show an alert - PDF generation would require additional setup
      alert('La funcionalidad de descarga PDF estará disponible próximamente.')
    },

    goToPractica() {
      this.$router.push('/quiz-selector')
    },

    getGradeClass(gradeLevel) {
      const classes = {
        'Excelente': 'bg-green-100 text-green-800',
        'Muy Bueno': 'bg-blue-100 text-blue-800',
        'Bueno': 'bg-yellow-100 text-yellow-800',
        'Satisfactorio': 'bg-orange-100 text-orange-800',
        'En Progreso': 'bg-gray-100 text-gray-800'
      }
      return classes[gradeLevel] || 'bg-gray-100 text-gray-800'
    },

    getGradeTextClass(gradeLevel) {
      const classes = {
        'Excelente': 'text-green-600',
        'Muy Bueno': 'text-blue-600',
        'Bueno': 'text-yellow-600',
        'Satisfactorio': 'text-orange-600',
        'En Progreso': 'text-gray-600'
      }
      return classes[gradeLevel] || 'text-gray-600'
    }
  }
}
</script>