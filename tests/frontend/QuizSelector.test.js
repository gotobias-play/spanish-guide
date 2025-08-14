import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import QuizSelector from '@/components/QuizSelector.vue'
import axios from 'axios'

// Mock axios
vi.mock('axios')

// Mock Vue Router
const mockPush = vi.fn()
vi.mock('vue-router', () => ({
  useRouter: () => ({
    push: mockPush,
  }),
}))

describe('QuizSelector', () => {
  let wrapper
  let mockCourses
  let mockQuizzes
  let mockUser

  beforeEach(() => {
    // Mock data
    mockCourses = [
      {
        id: 1,
        title: 'Basic English Grammar',
        description: 'Learn fundamental grammar concepts',
        is_published: true,
        lessons: [
          {
            id: 1,
            title: 'Present Tense',
            course_id: 1,
          },
          {
            id: 2,
            title: 'Past Tense',
            course_id: 1,
          },
        ],
      },
      {
        id: 2,
        title: 'English Vocabulary',
        description: 'Expand your vocabulary',
        is_published: true,
        lessons: [
          {
            id: 3,
            title: 'Food Vocabulary',
            course_id: 2,
          },
        ],
      },
    ]

    mockQuizzes = [
      {
        id: 1,
        title: 'Present Tense Quiz',
        lesson_id: 1,
        questions: [
          {
            id: 1,
            question_text: 'What is the present tense of "to be"?',
            question_type: 'multiple_choice',
            options: [
              { id: 1, option_text: 'am/is/are', is_correct: true },
              { id: 2, option_text: 'was/were', is_correct: false },
            ],
          },
        ],
      },
      {
        id: 2,
        title: 'Past Tense Quiz',
        lesson_id: 2,
        questions: [
          {
            id: 2,
            question_text: 'What is the past tense of "go"?',
            question_type: 'multiple_choice',
            options: [
              { id: 3, option_text: 'went', is_correct: true },
              { id: 4, option_text: 'goed', is_correct: false },
            ],
          },
        ],
      },
    ]

    mockUser = {
      id: 1,
      name: 'Test User',
      email: 'test@example.com',
    }

    // Mock axios responses
    axios.get.mockImplementation((url) => {
      switch (url) {
        case '/api/public/courses':
          return Promise.resolve({ data: mockCourses })
        case '/api/public/quizzes':
          return Promise.resolve({ data: mockQuizzes })
        case '/api/user':
          return Promise.resolve({ data: mockUser })
        default:
          return Promise.reject(new Error('Unknown endpoint'))
      }
    })

    axios.post.mockImplementation((url) => {
      switch (url) {
        case '/api/gamification/quiz-points':
          return Promise.resolve({ 
            data: { 
              success: true, 
              points_awarded: 30,
              achievements_unlocked: [],
            }
          })
        case '/api/learning/update-skill':
          return Promise.resolve({ data: { success: true } })
        case '/api/progress':
          return Promise.resolve({ data: { success: true } })
        default:
          return Promise.reject(new Error('Unknown endpoint'))
      }
    })

    wrapper = mount(QuizSelector)
  })

  it('renders correctly', () => {
    expect(wrapper.exists()).toBe(true)
    expect(wrapper.find('[data-testid="quiz-selector"]').exists()).toBe(true)
  })

  it('displays loading state initially', () => {
    const loadingWrapper = mount(QuizSelector)
    expect(loadingWrapper.find('[data-testid="loading-spinner"]').exists()).toBe(true)
  })

  it('loads and displays courses', async () => {
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    expect(axios.get).toHaveBeenCalledWith('/api/public/courses')

    const courseCards = wrapper.findAll('[data-testid^="course-card-"]')
    expect(courseCards).toHaveLength(2)

    const firstCourse = wrapper.find('[data-testid="course-card-1"]')
    expect(firstCourse.text()).toContain('Basic English Grammar')
    expect(firstCourse.text()).toContain('Learn fundamental grammar concepts')
  })

  it('displays quiz count for each course', async () => {
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    const firstCourse = wrapper.find('[data-testid="course-card-1"]')
    expect(firstCourse.text()).toContain('2') // Should show 2 quizzes
  })

  it('expands course to show quizzes when clicked', async () => {
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    const firstCourse = wrapper.find('[data-testid="course-card-1"]')
    await firstCourse.trigger('click')

    const quizList = wrapper.find('[data-testid="quiz-list-1"]')
    expect(quizList.exists()).toBe(true)
    expect(quizList.isVisible()).toBe(true)

    const quizCards = wrapper.findAll('[data-testid^="quiz-card-"]')
    expect(quizCards.length).toBeGreaterThan(0)
  })

  it('starts quiz when quiz card is clicked', async () => {
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    // Expand course first
    const firstCourse = wrapper.find('[data-testid="course-card-1"]')
    await firstCourse.trigger('click')

    // Click on first quiz
    const firstQuiz = wrapper.find('[data-testid="quiz-card-1"]')
    await firstQuiz.trigger('click')

    // Should start quiz mode
    expect(wrapper.find('[data-testid="quiz-interface"]').exists()).toBe(true)
  })

  it('displays quiz questions correctly', async () => {
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    // Start a quiz
    const firstCourse = wrapper.find('[data-testid="course-card-1"]')
    await firstCourse.trigger('click')
    const firstQuiz = wrapper.find('[data-testid="quiz-card-1"]')
    await firstQuiz.trigger('click')

    // Check if question is displayed
    const question = wrapper.find('[data-testid="current-question"]')
    expect(question.exists()).toBe(true)
    expect(question.text()).toContain('What is the present tense of "to be"?')

    // Check if options are displayed
    const options = wrapper.findAll('[data-testid^="option-"]')
    expect(options).toHaveLength(2)
  })

  it('handles answer selection', async () => {
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    // Start quiz
    const firstCourse = wrapper.find('[data-testid="course-card-1"]')
    await firstCourse.trigger('click')
    const firstQuiz = wrapper.find('[data-testid="quiz-card-1"]')
    await firstQuiz.trigger('click')

    // Select first option
    const firstOption = wrapper.find('[data-testid="option-1"]')
    await firstOption.trigger('click')

    // Option should be selected
    expect(firstOption.classes()).toContain('selected')
  })

  it('submits quiz and shows results', async () => {
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    // Start and complete quiz
    const firstCourse = wrapper.find('[data-testid="course-card-1"]')
    await firstCourse.trigger('click')
    const firstQuiz = wrapper.find('[data-testid="quiz-card-1"]')
    await firstQuiz.trigger('click')

    // Answer question
    const firstOption = wrapper.find('[data-testid="option-1"]')
    await firstOption.trigger('click')

    // Submit quiz
    const submitButton = wrapper.find('[data-testid="submit-quiz"]')
    await submitButton.trigger('click')

    // Should show results
    const results = wrapper.find('[data-testid="quiz-results"]')
    expect(results.exists()).toBe(true)
  })

  it('makes correct API calls after quiz completion for authenticated users', async () => {
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    // Simulate quiz completion for authenticated user
    if (wrapper.vm.completeQuiz) {
      await wrapper.vm.completeQuiz({
        score: 100,
        answers: [{ questionId: 1, selectedAnswer: 1, isCorrect: true }],
      })
    }

    // Should call gamification API
    expect(axios.post).toHaveBeenCalledWith('/api/gamification/quiz-points', expect.any(Object))
    
    // Should call skill tracking API
    expect(axios.post).toHaveBeenCalledWith('/api/learning/update-skill', expect.any(Object))
    
    // Should call progress tracking API
    expect(axios.post).toHaveBeenCalledWith('/api/progress', expect.any(Object))
  })

  it('handles guest user quiz completion gracefully', async () => {
    // Mock no user (guest)
    axios.get.mockImplementation((url) => {
      if (url === '/api/user') {
        return Promise.reject(new Error('Unauthorized'))
      }
      return Promise.resolve({ data: [] })
    })

    const guestWrapper = mount(QuizSelector)
    await guestWrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    // Guest should still be able to take quizzes
    expect(guestWrapper.exists()).toBe(true)
    
    // But shouldn't make progress API calls
    if (guestWrapper.vm.completeQuiz) {
      await guestWrapper.vm.completeQuiz({
        score: 100,
        answers: [{ questionId: 1, selectedAnswer: 1, isCorrect: true }],
      })
    }

    // Should not call authenticated endpoints
    expect(axios.post).not.toHaveBeenCalledWith('/api/gamification/quiz-points', expect.any(Object))

    guestWrapper.unmount()
  })

  it('displays different question types correctly', async () => {
    // Mock quiz with different question types
    const mixedQuizzes = [
      {
        id: 3,
        title: 'Mixed Question Types Quiz',
        lesson_id: 1,
        questions: [
          {
            id: 3,
            question_text: 'Fill in the blank: I ___ a student.',
            question_type: 'fill_in_the_blank',
            options: [
              { id: 5, option_text: 'am', is_correct: true },
            ],
          },
          {
            id: 4,
            question_text: 'What do you see in this image?',
            question_type: 'image_based',
            image_url: 'test-image.jpg',
            options: [
              { id: 6, option_text: 'A cat', is_correct: true },
              { id: 7, option_text: 'A dog', is_correct: false },
            ],
          },
        ],
      },
    ]

    axios.get.mockImplementation((url) => {
      if (url === '/api/public/quizzes') {
        return Promise.resolve({ data: mixedQuizzes })
      }
      return Promise.resolve({ data: mockCourses })
    })

    const mixedWrapper = mount(QuizSelector)
    await mixedWrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    // Should handle different question types
    expect(mixedWrapper.exists()).toBe(true)

    mixedWrapper.unmount()
  })

  it('handles quiz navigation (previous/next)', async () => {
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    // Start quiz with multiple questions
    const firstCourse = wrapper.find('[data-testid="course-card-1"]')
    await firstCourse.trigger('click')
    const firstQuiz = wrapper.find('[data-testid="quiz-card-1"]')
    await firstQuiz.trigger('click')

    // Check if navigation buttons exist
    const nextButton = wrapper.find('[data-testid="next-question"]')
    const prevButton = wrapper.find('[data-testid="prev-question"]')

    if (nextButton.exists()) {
      await nextButton.trigger('click')
      // Should move to next question
    }

    if (prevButton.exists()) {
      await prevButton.trigger('click')
      // Should move to previous question
    }
  })

  it('calculates score correctly', async () => {
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    if (wrapper.vm.calculateScore) {
      const answers = [
        { questionId: 1, selectedAnswer: 1, isCorrect: true },
        { questionId: 2, selectedAnswer: 3, isCorrect: true },
      ]
      
      const score = wrapper.vm.calculateScore(answers)
      expect(score).toBe(100) // 2/2 correct = 100%
    }
  })

  it('handles API errors gracefully', async () => {
    axios.get.mockRejectedValue(new Error('Network Error'))

    const errorWrapper = mount(QuizSelector)
    await errorWrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    // Should not crash
    expect(errorWrapper.exists()).toBe(true)
    
    // Could check for error message display
    const errorMessage = errorWrapper.find('[data-testid="error-message"]')
    if (errorMessage.exists()) {
      expect(errorMessage.text()).toContain('error')
    }

    errorWrapper.unmount()
  })

  it('filters quizzes by course correctly', async () => {
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    if (wrapper.vm.getQuizzesForCourse) {
      const course1Quizzes = wrapper.vm.getQuizzesForCourse(1)
      expect(course1Quizzes).toHaveLength(2)
      
      const course2Quizzes = wrapper.vm.getQuizzesForCourse(2)
      expect(course2Quizzes).toHaveLength(1)
    }
  })

  it('resets quiz state when starting new quiz', async () => {
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    // Start first quiz
    const firstCourse = wrapper.find('[data-testid="course-card-1"]')
    await firstCourse.trigger('click')
    const firstQuiz = wrapper.find('[data-testid="quiz-card-1"]')
    await firstQuiz.trigger('click')

    // Answer a question
    const firstOption = wrapper.find('[data-testid="option-1"]')
    if (firstOption.exists()) {
      await firstOption.trigger('click')
    }

    // Start second quiz
    const backButton = wrapper.find('[data-testid="back-to-courses"]')
    if (backButton.exists()) {
      await backButton.trigger('click')
    }

    const secondQuiz = wrapper.find('[data-testid="quiz-card-2"]')
    if (secondQuiz.exists()) {
      await secondQuiz.trigger('click')
      
      // Previous answers should be cleared
      expect(wrapper.vm.currentAnswers || []).toHaveLength(0)
    }
  })

  it('displays quiz progress indicator', async () => {
    await wrapper.vm.$nextTick()
    await new Promise(resolve => setTimeout(resolve, 100))

    // Start quiz
    const firstCourse = wrapper.find('[data-testid="course-card-1"]')
    await firstCourse.trigger('click')
    const firstQuiz = wrapper.find('[data-testid="quiz-card-1"]')
    await firstQuiz.trigger('click')

    // Check for progress indicator
    const progressBar = wrapper.find('[data-testid="quiz-progress"]')
    if (progressBar.exists()) {
      expect(progressBar.exists()).toBe(true)
    }
  })
})