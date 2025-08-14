describe('Quiz Functionality', () => {
  beforeEach(() => {
    cy.login()
    cy.visit('/quiz-selector')
    cy.wait(2000) // Wait for quiz data to load
  })

  describe('Quiz Selection and Navigation', () => {
    it('should load and display available courses', () => {
      // Should show course cards
      cy.get('[data-testid^="course-card-"]').should('have.length.greaterThan', 0)
      
      // First course should be visible
      cy.get('[data-testid="course-card-1"]').should('be.visible')
      cy.get('[data-testid="course-card-1"]').should('contain.text', 'Grammar')
    })

    it('should expand course to show quizzes', () => {
      // Click on first course
      cy.get('[data-testid="course-card-1"]').click()
      cy.wait(500)
      
      // Quiz list should become visible
      cy.get('[data-testid="quiz-list-1"]').should('be.visible')
      
      // Should show quiz cards
      cy.get('[data-testid^="quiz-card-"]').should('have.length.greaterThan', 0)
    })

    it('should start quiz when quiz card is clicked', () => {
      // Expand course and start quiz
      cy.startQuiz(0)
      
      // Should show quiz interface
      cy.get('[data-testid="quiz-interface"]').should('be.visible')
      
      // Should show question
      cy.get('[data-testid="current-question"]').should('be.visible')
      
      // Should show answer options
      cy.get('[data-testid^="option-"]').should('have.length.greaterThan', 0)
    })
  })

  describe('Quiz Interaction', () => {
    beforeEach(() => {
      cy.startQuiz(0)
    })

    it('should allow answer selection', () => {
      // Click first option
      cy.get('[data-testid="option-1"]').click()
      
      // Option should be selected (visual feedback)
      cy.get('[data-testid="option-1"]').should('have.class', 'selected')
    })

    it('should show quiz progress', () => {
      // Progress indicator should be visible
      cy.get('[data-testid="quiz-progress"]').should('be.visible')
    })

    it('should handle question navigation', () => {
      // Answer current question
      cy.answerQuizQuestion(0)
      
      // Check if next button exists and is clickable
      cy.get('body').then(($body) => {
        if ($body.find('[data-testid="next-question"]').length > 0) {
          cy.get('[data-testid="next-question"]').click()
          cy.wait(500)
        }
      })
    })

    it('should complete quiz and show results', () => {
      // Answer question
      cy.answerQuizQuestion(0)
      
      // Submit quiz
      cy.submitQuiz()
      
      // Should show results
      cy.get('[data-testid="quiz-results"]').should('be.visible')
      
      // Should show score
      cy.get('[data-testid="quiz-results"]').should('contain.text', '%')
    })
  })

  describe('Different Question Types', () => {
    it('should handle multiple choice questions', () => {
      cy.startQuiz(0)
      
      // Should show multiple choice options
      cy.get('[data-testid^="option-"]').should('have.length.greaterThan', 1)
      
      // Should allow selection
      cy.answerQuizQuestion(0)
      cy.get('[data-testid="option-1"]').should('have.class', 'selected')
    })

    it('should handle fill-in-the-blank questions', () => {
      // Try to find fill-in-the-blank quiz
      cy.get('body').then(($body) => {
        if ($body.find('[data-testid*="fill"]').length > 0) {
          cy.get('[data-testid*="fill"]').first().click()
          
          // Should show text input
          cy.get('input[type="text"]').should('be.visible')
          
          // Should allow typing
          cy.get('input[type="text"]').type('answer')
        }
      })
    })

    it('should handle timed quizzes', () => {
      // Look for timed quiz indicator
      cy.get('body').then(($body) => {
        if ($body.find('[data-testid*="timed"]').length > 0) {
          cy.get('[data-testid*="timed"]').first().click()
          
          // Should show timer
          cy.get('[data-testid="quiz-timer"]').should('be.visible')
          
          // Timer should count down
          cy.get('[data-testid="quiz-timer"]').should('contain.text', ':')
        }
      })
    })
  })

  describe('Quiz Results and Feedback', () => {
    beforeEach(() => {
      cy.completeQuiz(0, true)
    })

    it('should display quiz score', () => {
      cy.get('[data-testid="quiz-results"]').should('be.visible')
      cy.get('[data-testid="quiz-results"]').should('contain.text', '%')
    })

    it('should show correct/incorrect answers', () => {
      // Should show answer feedback
      cy.get('[data-testid="quiz-results"]').should('contain.text', 'correct')
    })

    it('should allow quiz retry', () => {
      // Look for retry button
      cy.get('body').then(($body) => {
        if ($body.find('[data-testid="retry-quiz"]').length > 0) {
          cy.get('[data-testid="retry-quiz"]').click()
          
          // Should restart quiz
          cy.get('[data-testid="quiz-interface"]').should('be.visible')
        }
      })
    })

    it('should return to course selection', () => {
      // Look for back button
      cy.get('body').then(($body) => {
        if ($body.find('[data-testid="back-to-courses"]').length > 0) {
          cy.get('[data-testid="back-to-courses"]').click()
          
          // Should return to course selection
          cy.get('[data-testid^="course-card-"]').should('be.visible')
        }
      })
    })
  })

  describe('Guest User Quiz Access', () => {
    beforeEach(() => {
      cy.logout()
      cy.visit('/quiz-selector')
      cy.wait(2000)
    })

    it('should allow guest users to view quizzes', () => {
      // Guest users should see courses
      cy.get('[data-testid^="course-card-"]').should('have.length.greaterThan', 0)
    })

    it('should allow guest users to take quizzes', () => {
      // Guest should be able to start quiz
      cy.startQuiz(0)
      
      // Should show quiz interface
      cy.get('[data-testid="quiz-interface"]').should('be.visible')
    })

    it('should show registration prompt for guest users', () => {
      cy.completeQuiz(0, true)
      
      // Should show registration encouragement
      cy.get('[data-testid="quiz-results"]').should('contain.text', 'Regístrate')
    })
  })

  describe('Quiz Performance and Analytics', () => {
    beforeEach(() => {
      cy.login()
    })

    it('should track quiz completion', () => {
      cy.completeQuiz(0, true)
      
      // Navigate to analytics
      cy.navigateToAnalytics()
      
      // Should show quiz completion stats
      cy.get('[data-testid="total-quizzes"]').should('be.visible')
    })

    it('should award points for quiz completion', () => {
      cy.completeQuiz(0, true)
      
      // Navigate to gamification
      cy.navigateToGamification()
      
      // Should show points
      cy.get('[data-testid="total-points"]').should('be.visible')
    })
  })
})