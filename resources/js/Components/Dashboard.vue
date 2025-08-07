<template>
  <div class="container mx-auto p-4 md:p-8">
    <h2 class="text-3xl font-bold text-center mb-6">My Progress Dashboard</h2>

    <div v-if="loading" class="text-center text-lg">Loading dashboard...</div>
    <div v-else-if="error" class="text-center text-lg text-red-500">Error loading dashboard: {{ error }}</div>
    <div v-else-if="!hasProgress" class="text-center text-lg">No progress to display yet.</div>

    <div v-else>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="card p-4 text-center">
          <h3 class="text-xl font-semibold">Overall Average Score</h3>
          <p class="text-3xl font-bold text-blue-600">{{ overallAverageScore.toFixed(2) }}%</p>
        </div>
        <div class="card p-4 text-center">
          <h3 class="text-xl font-semibold">Quizzes Completed</h3>
          <p class="text-3xl font-bold text-blue-600">{{ totalQuizzes }}</p>
        </div>
      </div>

      <div class="card p-4">
        <h3 class="text-xl font-semibold mb-4">Score by Section</h3>
        <div v-for="(score, section) in scoresBySection" :key="section">
          <div class="flex justify-between items-center mb-2">
            <span class="capitalize">{{ formatSectionId(section) }}</span>
            <span>{{ score.toFixed(2) }}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2.5">
            <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: score + '%' }"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'Dashboard',
  data() {
    return {
      progress: [],
      loading: true,
      error: null,
    };
  },
  async created() {
    try {
      const response = await axios.get('/api/progress');
      this.progress = response.data;
    } catch (err) {
      this.error = err.message;
      console.error('Error fetching progress:', err);
    } finally {
      this.loading = false;
    }
  },
  computed: {
    hasProgress() {
      return this.progress.length > 0;
    },
    overallAverageScore() {
      if (!this.hasProgress) return 0;
      const totalScore = this.progress.reduce((sum, item) => sum + (item.score || 0), 0);
      return totalScore / this.progress.length;
    },
    totalQuizzes() {
      return this.progress.length;
    },
    scoresBySection() {
      if (!this.hasProgress) return {};
      const sections = {};
      this.progress.forEach(item => {
        if (!sections[item.section_id]) {
          sections[item.section_id] = [];
        }
        sections[item.section_id].push(item.score || 0);
      });

      const averageScores = {};
      for (const section in sections) {
        const scores = sections[section];
        averageScores[section] = scores.reduce((sum, score) => sum + score, 0) / scores.length;
      }
      return averageScores;
    },
  },
  methods: {
    formatSectionId(id) {
      return id.replace(/-/g, ' ');
    },
  },
};
</script>
