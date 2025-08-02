<template>
  <div class="container mx-auto p-4 md:p-8">
    <h2 class="text-3xl font-bold text-center mb-6">Admin Panel - All Users</h2>

    <div v-if="loading" class="text-center text-lg">Loading users...</div>
    <div v-else-if="error" class="text-center text-lg text-red-500">Error loading users: {{ error }}</div>
    <div v-else-if="users.length === 0" class="text-center text-lg">No users found.</div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div v-for="user in users" :key="user.id" class="card p-4">
        <h3 class="text-xl font-semibold text-blue-700">{{ user.name }}</h3>
        <p class="text-gray-600">Email: {{ user.email }}</p>
        <p class="text-gray-600">Admin: {{ user.is_admin ? 'Yes' : 'No' }}</p>
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
      users: [],
      loading: true,
      error: null,
    };
  },
  async created() {
    try {
      const response = await axios.get('/api/admin/users');
      this.users = response.data;
    } catch (err) {
      this.error = err.message;
      console.error('Error fetching users:', err);
    } finally {
      this.loading = false;
    }
  },
};
</script>
