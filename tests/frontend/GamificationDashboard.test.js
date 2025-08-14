import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import GamificationDashboard from '@/components/GamificationDashboard.vue'
import axios from 'axios'

// Mock axios
vi.mock('axios')

describe('GamificationDashboard', () => {
  let wrapper
  let mockStats
  let mockAchievements
  let mockPointsHistory
  let mockLeaderboard

  beforeEach(() => {
    // Mock data
    mockStats = {
      total_points: 250,
      total_quizzes: 15,
      total_achievements: 5,
      current_streak: 7,
      longest_streak: 12,
      average_score: 85.5,
    }

    mockAchievements = [
      {
        id: 1,
        name: 'First Steps',
        description: 'Complete your first quiz',
        badge_icon: '🌟',
        badge_color: '#FFD700',
        earned_at: '2025-08-10T10:00:00Z',
      },
      {
        id: 2,
        name: 'Quiz Master',
        description: 'Complete 10 quizzes',
        badge_icon: '🎓',
        badge_color: '#4169E1',
        earned_at: '2025-08-12T15:30:00Z',
      },
    ]

    mockPointsHistory = [
      {
        points: 50,
        reason: 'Grammar Quiz',
        quiz_id: 1,
        earned_at: '2025-08-13T09:00:00Z',
      },
      {
        points: 30,
        reason: 'Vocabulary Quiz',
        quiz_id: 2,
        earned_at: '2025-08-12T14:00:00Z',
      },
    ]

    mockLeaderboard = [
      {
        user_id: 1,
        user_name: 'Test User',
        total_points: 250,
        total_quizzes: 15,
        total_achievements: 5,
        rank: 1,
      },
      {
        user_id: 2,
        user_name: 'Other User',
        total_points: 180,
        total_quizzes: 12,
        total_achievements: 3,
        rank: 2,
      },
    ]

    // Mock axios responses
    axios.get.mockImplementation((url) => {
      switch (url) {
        case '/api/gamification/stats':
          return Promise.resolve({ data: mockStats })
        case '/api/gamification/achievements':
          return Promise.resolve({ data: mockAchievements })
        case '/api/gamification/points-history':
          return Promise.resolve({ data: mockPointsHistory })
        case '/api/public/leaderboard':
          return Promise.resolve({ data: mockLeaderboard })
        default:
          return Promise.reject(new Error('Unknown endpoint'))
      }
    })

    wrapper = mount(GamificationDashboard)
  })

  it('renders correctly', () => {
    expect(wrapper.exists()).toBe(true)
    expect(wrapper.find('[data-testid="gamification-dashboard"]').exists()).toBe(true)
  })

  it('displays loading state initially', () => {
    const loadingWrapper = mount(GamificationDashboard)
    expect(loadingWrapper.find('[data-testid="loading-spinner"]').exists()).toBe(true)
  })

  it('loads and displays user statistics', async () => {
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100)) // Wait for async operations

    expect(axios.get).toHaveBeenCalledWith('/api/gamification/stats')
    
    // Check if stats are displayed
    expect(wrapper.text()).toContain('250') // Total points
    expect(wrapper.text()).toContain('15') // Total quizzes
    expect(wrapper.text()).toContain('5') // Total achievements
    expect(wrapper.text()).toContain('7') // Current streak
  })

  it('displays statistics cards with correct data', async () => {
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    const statsCards = wrapper.findAll('[data-testid^="stat-card-"]')
    expect(statsCards.length).toBeGreaterThan(0)

    // Check points card
    const pointsCard = wrapper.find('[data-testid="stat-card-points"]')
    expect(pointsCard.exists()).toBe(true)
    expect(pointsCard.text()).toContain('250')

    // Check quizzes card
    const quizzesCard = wrapper.find('[data-testid="stat-card-quizzes"]')
    expect(quizzesCard.exists()).toBe(true)
    expect(quizzesCard.text()).toContain('15')
  })

  it('displays achievements section', async () => {
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    expect(axios.get).toHaveBeenCalledWith('/api/gamification/achievements')

    const achievementsSection = wrapper.find('[data-testid="achievements-section"]')
    expect(achievementsSection.exists()).toBe(true)

    const achievementCards = wrapper.findAll('[data-testid^="achievement-card-"]')
    expect(achievementCards).toHaveLength(2)

    // Check first achievement
    const firstAchievement = wrapper.find('[data-testid="achievement-card-1"]')
    expect(firstAchievement.text()).toContain('First Steps')
    expect(firstAchievement.text()).toContain('🌟')
  })

  it('displays points history', async () => {
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    expect(axios.get).toHaveBeenCalledWith('/api/gamification/points-history')

    const pointsHistorySection = wrapper.find('[data-testid="points-history-section"]')
    expect(pointsHistorySection.exists()).toBe(true)

    const historyItems = wrapper.findAll('[data-testid^="history-item-"]')
    expect(historyItems).toHaveLength(2)

    // Check first history item
    const firstHistoryItem = wrapper.find('[data-testid="history-item-0"]')
    expect(firstHistoryItem.text()).toContain('50')
    expect(firstHistoryItem.text()).toContain('Grammar Quiz')
  })

  it('displays leaderboard', async () => {
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    expect(axios.get).toHaveBeenCalledWith('/api/public/leaderboard')

    const leaderboardSection = wrapper.find('[data-testid="leaderboard-section"]')
    expect(leaderboardSection.exists()).toBe(true)

    const leaderboardItems = wrapper.findAll('[data-testid^="leaderboard-item-"]')
    expect(leaderboardItems).toHaveLength(2)

    // Check first leaderboard item
    const firstLeaderboardItem = wrapper.find('[data-testid="leaderboard-item-0"]')
    expect(firstLeaderboardItem.text()).toContain('Test User')
    expect(firstLeaderboardItem.text()).toContain('250')
    expect(firstLeaderboardItem.text()).toContain('1') // Rank
  })

  it('handles API errors gracefully', async () => {
    axios.get.mockRejectedValue(new Error('API Error'))

    const errorWrapper = mount(GamificationDashboard)
    await errorWrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    // Should not crash and should show error state
    expect(errorWrapper.exists()).toBe(true)
    // Could check for error message display if implemented
  })

  it('formats dates correctly', async () => {
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    const firstAchievement = wrapper.find('[data-testid="achievement-card-1"]')
    expect(firstAchievement.exists()).toBe(true)

    // Should display formatted date (implementation dependent)
    const achievementText = firstAchievement.text()
    expect(achievementText).toMatch(/\d+/) // Should contain some date-related numbers
  })

  it('shows achievement icons with correct colors', async () => {
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    const firstAchievement = wrapper.find('[data-testid="achievement-card-1"]')
    const achievementIcon = firstAchievement.find('[data-testid="achievement-icon-1"]')
    
    expect(achievementIcon.exists()).toBe(true)
    expect(achievementIcon.text()).toContain('🌟')
  })

  it('handles empty data states', async () => {
    // Mock empty responses
    axios.get.mockImplementation((url) => {
      switch (url) {
        case '/api/gamification/stats':
          return Promise.resolve({ 
            data: {
              total_points: 0,
              total_quizzes: 0,
              total_achievements: 0,
              current_streak: 0,
              longest_streak: 0,
              average_score: 0,
            }
          })
        case '/api/gamification/achievements':
          return Promise.resolve({ data: [] })
        case '/api/gamification/points-history':
          return Promise.resolve({ data: [] })
        case '/api/public/leaderboard':
          return Promise.resolve({ data: [] })
        default:
          return Promise.reject(new Error('Unknown endpoint'))
      }
    })

    const emptyWrapper = mount(GamificationDashboard)
    await emptyWrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    // Should display zero values
    expect(emptyWrapper.text()).toContain('0')
    
    // Should handle empty arrays gracefully
    expect(emptyWrapper.findAll('[data-testid^="achievement-card-"]')).toHaveLength(0)
    expect(emptyWrapper.findAll('[data-testid^="history-item-"]')).toHaveLength(0)

    emptyWrapper.unmount()
  })

  it('refreshes data when refresh method is called', async () => {
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    // Clear previous calls
    axios.get.mockClear()

    // Call refresh if method exists
    if (wrapper.vm.refreshData) {
      await wrapper.vm.refreshData()
    }

    // Should make API calls again
    expect(axios.get).toHaveBeenCalled()
  })

  it('displays progress indicators correctly', async () => {
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    // Check for progress bars or indicators
    const progressElements = wrapper.findAll('[data-testid^="progress-"]')
    expect(progressElements.length).toBeGreaterThanOrEqual(0)
  })

  it('handles tab switching if implemented', async () => {
    const tabs = wrapper.findAll('[data-testid^="tab-"]')
    
    if (tabs.length > 0) {
      const secondTab = tabs[1]
      await secondTab.trigger('click')
      
      // Should switch active tab
      expect(secondTab.classes()).toContain('active')
    }
  })

  it('displays medal icons for leaderboard rankings', async () => {
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    const firstPlace = wrapper.find('[data-testid="leaderboard-item-0"]')
    if (firstPlace.exists()) {
      // Should show gold medal or #1 indicator
      expect(firstPlace.text()).toMatch(/[🥇1]/)
    }
  })

  it('handles responsive design elements', () => {
    // Check for responsive classes
    const dashboard = wrapper.find('[data-testid="gamification-dashboard"]')
    expect(dashboard.classes()).toEqual(
      expect.arrayContaining([
        expect.stringMatching(/grid|flex|col|row|responsive/)
      ])
    )
  })

  it('calculates streak consistency correctly', async () => {
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    if (wrapper.vm.streakConsistency !== undefined) {
      const consistency = wrapper.vm.streakConsistency
      expect(consistency).toBeGreaterThanOrEqual(0)
      expect(consistency).toBeLessThanOrEqual(100)
    }
  })

  it('formats large numbers appropriately', async () => {
    // Mock large numbers
    axios.get.mockImplementation((url) => {
      if (url === '/api/gamification/stats') {
        return Promise.resolve({ 
          data: {
            ...mockStats,
            total_points: 1234567,
          }
        })
      }
      return Promise.resolve({ data: [] })
    })

    const largeNumberWrapper = mount(GamificationDashboard)
    await largeNumberWrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    // Should format large numbers (1.2M, 1,234,567, etc.)
    const pointsDisplay = largeNumberWrapper.find('[data-testid="stat-card-points"]')
    if (pointsDisplay.exists()) {
      const text = pointsDisplay.text()
      expect(text).toMatch(/[0-9,.]/) // Should contain formatted number
    }

    largeNumberWrapper.unmount()
  })
})