import { createI18n } from 'vue-i18n';
import es from './locales/es.js';
import en from './locales/en.js';

// Create i18n instance with global configuration
const i18n = createI18n({
  legacy: false, // Use Composition API mode
  locale: 'es', // Default locale (Spanish)
  fallbackLocale: 'en', // Fallback to English
  globalInjection: true, // Enable global $t function
  messages: {
    es,
    en
  },
  // Configure date/time formatting for different locales
  datetimeFormats: {
    es: {
      short: {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      },
      long: {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        weekday: 'short',
        hour: 'numeric',
        minute: 'numeric'
      }
    },
    en: {
      short: {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      },
      long: {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        weekday: 'short',
        hour: 'numeric',
        minute: 'numeric'
      }
    }
  },
  // Configure number formatting for different locales
  numberFormats: {
    es: {
      currency: {
        style: 'currency',
        currency: 'EUR',
        notation: 'standard'
      },
      decimal: {
        style: 'decimal',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      },
      percent: {
        style: 'percent',
        useGrouping: false
      }
    },
    en: {
      currency: {
        style: 'currency',
        currency: 'USD',
        notation: 'standard'
      },
      decimal: {
        style: 'decimal',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      },
      percent: {
        style: 'percent',
        useGrouping: false
      }
    }
  }
});

// Language switching utilities
export const availableLocales = [
  { code: 'es', name: 'Español', flag: '🇪🇸' },
  { code: 'en', name: 'English', flag: '🇺🇸' }
];

export const setLocale = (locale) => {
  if (availableLocales.some(l => l.code === locale)) {
    i18n.global.locale.value = locale;
    // Store preference in localStorage for persistence
    localStorage.setItem('preferred-locale', locale);
    // Update document language attribute
    document.documentElement.lang = locale;
  }
};

export const getCurrentLocale = () => {
  return i18n.global.locale.value;
};

export const getStoredLocale = () => {
  return localStorage.getItem('preferred-locale') || 'es';
};

// Initialize locale from stored preference or browser language
export const initializeLocale = () => {
  const storedLocale = getStoredLocale();
  const browserLocale = navigator.language.split('-')[0];
  
  // Use stored preference, then browser language, then default to Spanish
  const initialLocale = storedLocale || 
    (availableLocales.some(l => l.code === browserLocale) ? browserLocale : 'es');
    
  setLocale(initialLocale);
};

export default i18n;