<template>
  <div class="container mx-auto p-4 md:p-8">
    <h2 class="text-3xl font-bold text-center mb-6">My Quiz Progress</h2>

    <div v-if="loading" class="text-center text-lg">Loading progress...</div>
    <div v-else-if="error" class="text-center text-lg text-red-500">Error loading progress: {{ error }}</div>
    <div v-else-if="progress.length === 0" class="text-center text-lg">No quiz progress found yet. Start a quiz to see your history!</div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="item in progress" :key="item.id" class="card interactive-card p-4">
        <h3 class="text-xl font-semibold text-blue-700 capitalize">{{ formatSectionId(item.section_id) }}</h3>
        <p class="text-gray-600">Score: {{ item.score !== null ? item.score : 'N/A' }}</p>
        <p class="text-gray-600 text-sm">Last Updated: {{ new Date(item.updated_at).toLocaleDateString() }}</p>
        <button @click="viewDetails(item)" class="interactive-btn mt-3">View Details</button>
      </div>
    </div>

    <Modal v-if="showDetailsModal" :title="modalTitle" @close="showDetailsModal = false">
      <div v-if="selectedQuizDetails">
        <div v-for="(value, key) in selectedQuizDetails" :key="key" class="mb-2">
          <p><strong class="capitalize">{{ key.replace(/([A-Z])/g, ' $1').trim() }}:</strong> {{ value }}</p>
        </div>
      </div>
      <div v-else>No detailed data available.</div>
    </Modal>
  </div>
</template>

<script>
import axios from 'axios';
import Modal from './Modal.vue';

export default {
  name: 'QuizHistory',
  components: { Modal },
  data() {
    return {
      progress: [],
      loading: true,
      error: null,
      showDetailsModal: false,
      selectedQuizDetails: null,
      modalTitle: '',
    };
  },
  async created() {
    try {
      const response = await axios.get('/api/progress');
      this.progress = response.data;
    } catch (err) {
      this.error = err.message;
      console.error('Error fetching quiz progress:', err);
    } finally {
      this.loading = false;
    }
  },
  methods: {
    formatSectionId(id) {
      return id.replace(/-/g, ' ');
    },
    viewDetails(item) {
      this.selectedQuizDetails = item.data;
      this.modalTitle = `Details for ${this.formatSectionId(item.section_id)}`;
      this.showDetailsModal = true;
    },
  },
};
</script>
