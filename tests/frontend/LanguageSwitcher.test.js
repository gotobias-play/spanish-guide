import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import LanguageSwitcher from '@/components/LanguageSwitcher.vue'
import { createI18n } from 'vue-i18n'

// Create mock i18n instance
const createMockI18n = () => {
  return createI18n({
    legacy: false,
    locale: 'es',
    fallbackLocale: 'en',
    messages: {
      es: {
        nav: {
          language: 'Idioma',
        },
      },
      en: {
        nav: {
          language: 'Language',
        },
      },
    },
  })
}

describe('LanguageSwitcher', () => {
  let wrapper
  let i18n

  beforeEach(() => {
    i18n = createMockI18n()
    
    // Mock localStorage
    const localStorageMock = {
      getItem: vi.fn(),
      setItem: vi.fn(),
      removeItem: vi.fn(),
    }
    global.localStorage = localStorageMock

    wrapper = mount(LanguageSwitcher, {
      global: {
        plugins: [i18n],
      },
    })
  })

  it('renders correctly', () => {
    expect(wrapper.exists()).toBe(true)
    expect(wrapper.find('[data-testid="language-switcher"]').exists()).toBe(true)
  })

  it('displays current language flag and name', () => {
    const currentLanguage = wrapper.find('[data-testid="current-language"]')
    expect(currentLanguage.exists()).toBe(true)
    
    // Should show Spanish flag and name by default
    expect(currentLanguage.text()).toContain('🇪🇸')
    expect(currentLanguage.text()).toContain('Español')
  })

  it('shows dropdown when clicked', async () => {
    const button = wrapper.find('[data-testid="language-switcher-button"]')
    
    // Initially dropdown should be hidden
    expect(wrapper.find('[data-testid="language-dropdown"]').isVisible()).toBe(false)
    
    // Click to open dropdown
    await button.trigger('click')
    
    // Dropdown should now be visible
    expect(wrapper.find('[data-testid="language-dropdown"]').isVisible()).toBe(true)
  })

  it('displays available language options', async () => {
    const button = wrapper.find('[data-testid="language-switcher-button"]')
    await button.trigger('click')
    
    const languageOptions = wrapper.findAll('[data-testid^="language-option-"]')
    expect(languageOptions).toHaveLength(2)
    
    // Check Spanish option
    const spanishOption = wrapper.find('[data-testid="language-option-es"]')
    expect(spanishOption.exists()).toBe(true)
    expect(spanishOption.text()).toContain('🇪🇸')
    expect(spanishOption.text()).toContain('Español')
    
    // Check English option
    const englishOption = wrapper.find('[data-testid="language-option-en"]')
    expect(englishOption.exists()).toBe(true)
    expect(englishOption.text()).toContain('🇺🇸')
    expect(englishOption.text()).toContain('English')
  })

  it('switches language when option is clicked', async () => {
    const button = wrapper.find('[data-testid="language-switcher-button"]')
    await button.trigger('click')
    
    const englishOption = wrapper.find('[data-testid="language-option-en"]')
    await englishOption.trigger('click')
    
    // Should emit language-changed event
    expect(wrapper.emitted('language-changed')).toBeTruthy()
    expect(wrapper.emitted('language-changed')[0]).toEqual(['en'])
    
    // Should save to localStorage
    expect(localStorage.setItem).toHaveBeenCalledWith('preferred-language', 'en')
  })

  it('closes dropdown when clicking outside', async () => {
    const button = wrapper.find('[data-testid="language-switcher-button"]')
    await button.trigger('click')
    
    // Dropdown should be open
    expect(wrapper.find('[data-testid="language-dropdown"]').isVisible()).toBe(true)
    
    // Simulate click outside
    await wrapper.trigger('click')
    
    // Dropdown should be closed
    expect(wrapper.find('[data-testid="language-dropdown"]').isVisible()).toBe(false)
  })

  it('handles keyboard navigation', async () => {
    const button = wrapper.find('[data-testid="language-switcher-button"]')
    
    // Test Enter key to open dropdown
    await button.trigger('keydown.enter')
    expect(wrapper.find('[data-testid="language-dropdown"]').isVisible()).toBe(true)
    
    // Test Escape key to close dropdown
    await button.trigger('keydown.escape')
    expect(wrapper.find('[data-testid="language-dropdown"]').isVisible()).toBe(false)
  })

  it('maintains accessibility attributes', () => {
    const button = wrapper.find('[data-testid="language-switcher-button"]')
    
    expect(button.attributes('role')).toBe('button')
    expect(button.attributes('aria-label')).toBeTruthy()
    expect(button.attributes('aria-expanded')).toBe('false')
    
    // Check dropdown accessibility
    const dropdown = wrapper.find('[data-testid="language-dropdown"]')
    expect(dropdown.attributes('role')).toBe('menu')
  })

  it('updates aria-expanded when dropdown state changes', async () => {
    const button = wrapper.find('[data-testid="language-switcher-button"]')
    
    // Initially closed
    expect(button.attributes('aria-expanded')).toBe('false')
    
    // Open dropdown
    await button.trigger('click')
    expect(button.attributes('aria-expanded')).toBe('true')
    
    // Close dropdown
    await button.trigger('click')
    expect(button.attributes('aria-expanded')).toBe('false')
  })

  it('loads saved language preference on mount', () => {
    localStorage.getItem.mockReturnValue('en')
    
    const newWrapper = mount(LanguageSwitcher, {
      global: {
        plugins: [i18n],
      },
    })
    
    expect(localStorage.getItem).toHaveBeenCalledWith('preferred-language')
    
    newWrapper.unmount()
  })

  it('handles missing language preference gracefully', () => {
    localStorage.getItem.mockReturnValue(null)
    
    const newWrapper = mount(LanguageSwitcher, {
      global: {
        plugins: [i18n],
      },
    })
    
    // Should not throw error and use default language
    expect(newWrapper.exists()).toBe(true)
    
    newWrapper.unmount()
  })

  it('emits correct events with proper data', async () => {
    const button = wrapper.find('[data-testid="language-switcher-button"]')
    await button.trigger('click')
    
    const englishOption = wrapper.find('[data-testid="language-option-en"]')
    await englishOption.trigger('click')
    
    // Check emitted event structure
    const emittedEvents = wrapper.emitted('language-changed')
    expect(emittedEvents).toHaveLength(1)
    expect(emittedEvents[0]).toEqual(['en'])
  })

  it('handles rapid clicks gracefully', async () => {
    const button = wrapper.find('[data-testid="language-switcher-button"]')
    
    // Rapid clicks
    await button.trigger('click')
    await button.trigger('click')
    await button.trigger('click')
    
    // Should not break functionality
    expect(wrapper.exists()).toBe(true)
    expect(wrapper.find('[data-testid="language-dropdown"]').isVisible()).toBe(true)
  })

  it('supports custom CSS classes', () => {
    const customWrapper = mount(LanguageSwitcher, {
      props: {
        class: 'custom-language-switcher',
      },
      global: {
        plugins: [i18n],
      },
    })
    
    expect(customWrapper.classes()).toContain('custom-language-switcher')
    customWrapper.unmount()
  })
})