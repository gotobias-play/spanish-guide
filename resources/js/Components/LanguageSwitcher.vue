<template>
  <div class="language-switcher relative inline-block">
    <button
      @click="toggleDropdown"
      class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
      :aria-expanded="isOpen"
      aria-haspopup="true"
    >
      <span class="text-lg">{{ currentLanguage.flag }}</span>
      <span class="hidden sm:inline">{{ currentLanguage.name }}</span>
      <svg 
        class="w-4 h-4 ml-1 transition-transform duration-200" 
        :class="{ 'rotate-180': isOpen }"
        fill="none" 
        stroke="currentColor" 
        viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </button>

    <!-- Dropdown Menu -->
    <transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-show="isOpen"
        class="absolute right-0 z-50 mt-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
        role="menu"
        aria-orientation="vertical"
      >
        <div class="py-1" role="none">
          <button
            v-for="language in availableLocales"
            :key="language.code"
            @click="changeLanguage(language.code)"
            class="group flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors"
            :class="{ 'bg-blue-50 text-blue-600': currentLocale === language.code }"
            role="menuitem"
          >
            <span class="text-lg mr-3">{{ language.flag }}</span>
            <span class="flex-1 text-left">{{ language.name }}</span>
            <svg
              v-if="currentLocale === language.code"
              class="w-4 h-4 text-blue-600"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path
                fill-rule="evenodd"
                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                clip-rule="evenodd"
              />
            </svg>
          </button>
        </div>
      </div>
    </transition>

    <!-- Overlay to close dropdown when clicking outside -->
    <div
      v-if="isOpen"
      class="fixed inset-0 z-40"
      @click="closeDropdown"
      aria-hidden="true"
    />
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useI18n } from 'vue-i18n';
import { availableLocales, setLocale, getCurrentLocale } from '../i18n.js';

export default {
  name: 'LanguageSwitcher',
  setup() {
    const { locale } = useI18n();
    const isOpen = ref(false);

    const currentLocale = computed(() => getCurrentLocale());
    
    const currentLanguage = computed(() => {
      return availableLocales.find(lang => lang.code === currentLocale.value) || availableLocales[0];
    });

    const toggleDropdown = () => {
      isOpen.value = !isOpen.value;
    };

    const closeDropdown = () => {
      isOpen.value = false;
    };

    const changeLanguage = (newLocale) => {
      if (newLocale !== currentLocale.value) {
        setLocale(newLocale);
        
        // Emit custom event for analytics or other tracking
        window.dispatchEvent(new CustomEvent('language-changed', {
          detail: { from: currentLocale.value, to: newLocale }
        }));
        
        // Show user feedback
        const languageName = availableLocales.find(l => l.code === newLocale)?.name;
        console.log(`Idioma cambiado a: ${languageName}`);
      }
      
      closeDropdown();
    };

    // Close dropdown on escape key
    const handleEscape = (event) => {
      if (event.key === 'Escape' && isOpen.value) {
        closeDropdown();
      }
    };

    onMounted(() => {
      document.addEventListener('keydown', handleEscape);
    });

    onUnmounted(() => {
      document.removeEventListener('keydown', handleEscape);
    });

    return {
      isOpen,
      currentLocale,
      currentLanguage,
      availableLocales,
      toggleDropdown,
      closeDropdown,
      changeLanguage
    };
  }
};
</script>

<style scoped>
.language-switcher .rotate-180 {
  transform: rotate(180deg);
}

/* Ensure dropdown appears above other elements */
.language-switcher .absolute {
  z-index: 50;
}

/* Mobile optimizations */
@media (max-width: 640px) {
  .language-switcher button {
    padding: 0.5rem;
  }
  
  .language-switcher .absolute {
    right: 0;
    left: auto;
    min-width: 150px;
  }
}

/* Smooth transitions for better UX */
.transition-colors {
  transition-property: color, background-color, border-color;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

/* Focus styles for accessibility */
.language-switcher button:focus,
.language-switcher button:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}
</style>