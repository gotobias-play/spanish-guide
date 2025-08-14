<template>
  <div class="container mx-auto p-6 max-w-4xl">
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
      <h1 class="text-3xl font-bold text-center mb-8 text-blue-700">
        {{ $t('nav.home') }} - {{ $t('learning.ai_tutor') }}
      </h1>

      <!-- Navigation Example -->
      <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">{{ $t('nav.home') }}</h2>
        <div class="flex flex-wrap gap-2">
          <button class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            {{ $t('nav.foundations') }}
          </button>
          <button class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
            {{ $t('nav.daily_life') }}
          </button>
          <button class="px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600">
            {{ $t('nav.practice') }}
          </button>
          <button class="px-4 py-2 bg-orange-500 text-white rounded hover:bg-orange-600">
            {{ $t('nav.progress') }}
          </button>
        </div>
      </div>

      <!-- Quiz Example -->
      <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">{{ $t('quiz.title') }}</h2>
        <div class="bg-gray-50 p-4 rounded-lg">
          <p class="mb-2">{{ $t('quiz.select_quiz') }}</p>
          <div class="flex gap-2 mb-4">
            <button class="px-4 py-2 bg-blue-500 text-white rounded">
              {{ $t('quiz.start_quiz') }}
            </button>
            <button class="px-4 py-2 bg-gray-500 text-white rounded">
              {{ $t('actions.cancel') }}
            </button>
          </div>
          <p class="text-sm text-gray-600">
            {{ $t('quiz.score') }}: 85% | {{ $t('quiz.questions') }}: 10
          </p>
        </div>
      </div>

      <!-- Gamification Example -->
      <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">{{ $t('gamification.achievements') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
            <h3 class="font-semibold text-yellow-800">🏆 {{ $t('gamification.achievements') }}</h3>
            <p class="text-sm text-yellow-700">{{ $t('gamification.earned') }}: 5</p>
          </div>
          <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <h3 class="font-semibold text-blue-800">⭐ {{ $t('gamification.points') }}</h3>
            <p class="text-sm text-blue-700">{{ $t('gamification.total_points') }}: 1,250</p>
          </div>
          <div class="bg-green-50 p-4 rounded-lg border border-green-200">
            <h3 class="font-semibold text-green-800">🔥 {{ $t('gamification.streak') }}</h3>
            <p class="text-sm text-green-700">{{ $t('gamification.current_streak') }}: 7 {{ $t('time.days') }}</p>
          </div>
        </div>
      </div>

      <!-- Analytics Example -->
      <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">{{ $t('analytics.overview') }}</h2>
        <div class="bg-gray-50 p-4 rounded-lg">
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div>
              <div class="text-2xl font-bold text-blue-600">25</div>
              <div class="text-sm text-gray-600">{{ $t('analytics.completed_quizzes') }}</div>
            </div>
            <div>
              <div class="text-2xl font-bold text-green-600">87%</div>
              <div class="text-sm text-gray-600">{{ $t('analytics.average_score') }}</div>
            </div>
            <div>
              <div class="text-2xl font-bold text-purple-600">15</div>
              <div class="text-sm text-gray-600">{{ $t('time.hours') }}</div>
            </div>
            <div>
              <div class="text-2xl font-bold text-orange-600">{{ $t('analytics.skill_level') }}</div>
              <div class="text-sm text-gray-600">{{ $t('learning.intermediate') }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Social Features Example -->
      <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">{{ $t('social.friends') }}</h2>
        <div class="bg-gray-50 p-4 rounded-lg">
          <div class="flex justify-between items-center mb-4">
            <span>{{ $t('social.online') }}: 3 {{ $t('social.friends') }}</span>
            <button class="px-3 py-1 bg-blue-500 text-white rounded text-sm">
              {{ $t('social.add_friend') }}
            </button>
          </div>
          <div class="text-sm text-gray-600">
            <p>{{ $t('social.last_seen') }} 2 {{ $t('time.minutes') }} {{ $t('time.ago') }}</p>
          </div>
        </div>
      </div>

      <!-- Learning Features Example -->
      <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">{{ $t('learning.ai_tutor') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-blue-50 p-4 rounded-lg">
            <h3 class="font-semibold text-blue-800">{{ $t('learning.writing_practice') }}</h3>
            <p class="text-sm text-blue-700 mb-2">{{ $t('learning.level') }}: {{ $t('learning.beginner') }}</p>
            <button class="px-3 py-1 bg-blue-500 text-white rounded text-sm">
              {{ $t('actions.continue') }}
            </button>
          </div>
          <div class="bg-green-50 p-4 rounded-lg">
            <h3 class="font-semibold text-green-800">{{ $t('learning.pronunciation') }}</h3>
            <p class="text-sm text-green-700 mb-2">{{ $t('analytics.progress') }}: 65%</p>
            <button class="px-3 py-1 bg-green-500 text-white rounded text-sm">
              {{ $t('actions.next') }}
            </button>
          </div>
        </div>
      </div>

      <!-- Messages Example -->
      <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">{{ $t('messages.info') }}</h2>
        <div class="space-y-2">
          <div class="bg-green-100 border border-green-300 text-green-700 px-4 py-2 rounded">
            {{ $t('messages.success') }}: {{ $t('quiz.quiz_completed') }}
          </div>
          <div class="bg-blue-100 border border-blue-300 text-blue-700 px-4 py-2 rounded">
            {{ $t('messages.loading') }}...
          </div>
          <div class="bg-yellow-100 border border-yellow-300 text-yellow-700 px-4 py-2 rounded">
            {{ $t('messages.warning') }}: {{ $t('quiz.register_to_save') }}
          </div>
        </div>
      </div>

      <!-- Current Locale Information -->
      <div class="bg-gray-100 p-4 rounded-lg">
        <h3 class="font-semibold mb-2">{{ getCurrentLocale() === 'es' ? 'Información del Idioma' : 'Language Information' }}</h3>
        <p class="text-sm text-gray-600">
          {{ getCurrentLocale() === 'es' ? 'Idioma actual' : 'Current Language' }}: 
          <span class="font-semibold">{{ getCurrentLanguageName() }}</span>
        </p>
        <p class="text-sm text-gray-600 mt-1">
          {{ getCurrentLocale() === 'es' ? 'Código de idioma' : 'Language Code' }}: 
          <span class="font-mono bg-gray-200 px-1 rounded">{{ getCurrentLocale() }}</span>
        </p>
      </div>
    </div>
  </div>
</template>

<script>
import { getCurrentLocale, availableLocales } from '../i18n.js';

export default {
  name: 'DemoI18n',
  methods: {
    getCurrentLocale() {
      return getCurrentLocale();
    },
    getCurrentLanguageName() {
      const current = availableLocales.find(lang => lang.code === getCurrentLocale());
      return current ? current.name : 'Unknown';
    }
  }
};
</script>

<style scoped>
/* Demo specific styles */
.container {
  font-family: 'Inter', sans-serif;
}

h1, h2, h3 {
  font-weight: 600;
}

button {
  transition: all 0.2s ease;
}

button:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.grid {
  gap: 1rem;
}

/* Responsive design */
@media (max-width: 768px) {
  .container {
    padding: 1rem;
  }
  
  .grid-cols-2 {
    grid-template-columns: 1fr;
  }
  
  .flex {
    flex-direction: column;
  }
  
  .flex.gap-2 > * {
    margin-bottom: 0.5rem;
  }
}
</style>