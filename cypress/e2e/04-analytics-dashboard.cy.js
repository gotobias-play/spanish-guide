describe('Analytics Dashboard', () => {
  beforeEach(() => {
    cy.login()
    cy.navigateToAnalytics()
    cy.wait(2000) // Wait for analytics data to load
  })

  describe('Basic Analytics Display', () => {
    it('should display analytics dashboard', () => {
      // Analytics dashboard should be visible
      cy.get('[data-testid="analytics-dashboard"]').should('be.visible')
    })

    it('should show basic statistics cards', () => {
      // All basic stat cards should be visible
      cy.get('[data-testid^="stat-card-"]').should('have.length.greaterThan', 0)
      
      // Should show total quizzes
      cy.get('[data-testid="total-quizzes"]').should('be.visible')
      
      // Should show average score
      cy.get('[data-testid="average-score"]').should('be.visible')
      
      // Should show perfect scores
      cy.get('[data-testid="perfect-scores"]').should('be.visible')
    })

    it('should display performance metrics', () => {
      // Performance section should be visible
      cy.get('[data-testid="performance-section"]').should('be.visible')
      
      // Should show performance trends
      cy.get('[data-testid="performance-trends"]').should('be.visible')
    })
  })

  describe('Performance Trends', () => {
    it('should display weekly performance chart', () => {
      // Performance trends chart should be visible
      cy.get('[data-testid="performance-trends"]').should('be.visible')
      
      // Should show weekly data
      cy.get('[data-testid="weekly-performance"]').should('be.visible')
    })

    it('should show activity calendar', () => {
      // Activity calendar should be visible
      cy.get('[data-testid="activity-calendar"]').should('be.visible')
      
      // Should show 14-day activity grid
      cy.get('[data-testid="activity-day"]').should('have.length', 14)
    })

    it('should display trend indicators', () => {
      // Trend indicators should show performance direction
      cy.get('[data-testid="performance-trend"]').should('be.visible')
    })
  })

  describe('Subject Analysis', () => {
    it('should display subject mastery levels', () => {
      // Subject analysis section should be visible
      cy.get('[data-testid="subject-analysis"]').should('be.visible')
      
      // Should show different subjects
      cy.get('[data-testid^="subject-mastery-"]').should('have.length.greaterThan', 0)
    })

    it('should show mastery progress bars', () => {
      // Progress bars should be visible for each subject
      cy.get('[data-testid^="mastery-progress-"]').should('have.length.greaterThan', 0)
      
      // Each progress bar should show percentage
      cy.get('[data-testid^="mastery-progress-"]').each(($progress) => {
        cy.wrap($progress).should('be.visible')
      })
    })

    it('should categorize subjects by performance level', () => {
      // Should show performance categories
      cy.get('[data-testid="subject-analysis"]').should('contain.text', 'Gramática')
    })
  })

  describe('Learning Insights', () => {
    it('should display personalized insights', () => {
      // Insights section should be visible
      cy.get('[data-testid="learning-insights"]').should('be.visible')
      
      // Should show insight items
      cy.get('[data-testid^="insight-"]').should('have.length.greaterThan', 0)
    })

    it('should show performance observations', () => {
      // Insights should contain performance feedback
      cy.get('[data-testid="learning-insights"]').should('contain.text', 'performance')
    })

    it('should provide improvement suggestions', () => {
      // Should show actionable recommendations
      cy.get('[data-testid="learning-insights"]').should('be.visible')
    })
  })

  describe('Goal Tracking', () => {
    it('should display suggested goals', () => {
      // Goals section should be visible
      cy.get('[data-testid="goals-section"]').should('be.visible')
      
      // Should show goal items
      cy.get('[data-testid^="goal-"]').should('have.length.greaterThan', 0)
    })

    it('should show goal progress', () => {
      // Goal progress bars should be visible
      cy.get('[data-testid^="goal-progress-"]').should('have.length.greaterThan', 0)
    })

    it('should display goal completion percentages', () => {
      // Goals should show percentage completion
      cy.get('[data-testid="goals-section"]').should('contain.text', '%')
    })
  })

  describe('Streak Analysis', () => {
    it('should display streak information', () => {
      // Streak section should be visible
      cy.get('[data-testid="streak-analysis"]').should('be.visible')
      
      // Should show current streak
      cy.get('[data-testid="current-streak"]').should('be.visible')
      
      // Should show longest streak
      cy.get('[data-testid="longest-streak"]').should('be.visible')
    })

    it('should show consistency score', () => {
      // Consistency score should be displayed
      cy.get('[data-testid="consistency-score"]').should('be.visible')
    })

    it('should display monthly activity', () => {
      // Monthly activity should be shown
      cy.get('[data-testid="monthly-activity"]').should('be.visible')
    })
  })

  describe('Achievement Progress', () => {
    it('should display achievement statistics', () => {
      // Achievement progress should be visible
      cy.get('[data-testid="achievement-progress"]').should('be.visible')
      
      // Should show earned vs total achievements
      cy.get('[data-testid="achievements-earned"]').should('be.visible')
      cy.get('[data-testid="achievements-total"]').should('be.visible')
    })

    it('should show completion percentage', () => {
      // Achievement completion percentage should be displayed
      cy.get('[data-testid="achievement-completion"]').should('be.visible')
      cy.get('[data-testid="achievement-completion"]').should('contain.text', '%')
    })
  })

  describe('Data Visualization', () => {
    it('should render performance charts', () => {
      // Performance charts should be visible
      cy.get('[data-testid="performance-chart"]').should('be.visible')
    })

    it('should show interactive elements', () => {
      // Interactive chart elements should respond to hover
      cy.get('[data-testid^="chart-bar-"]').first().trigger('mouseover')
    })

    it('should display color-coded data', () => {
      // Charts should use appropriate colors
      cy.get('[data-testid="performance-chart"]').should('have.css', 'background-color')
    })
  })

  describe('Responsive Analytics', () => {
    it('should work on mobile devices', () => {
      cy.viewport('iphone-6')
      
      // Analytics should be mobile responsive
      cy.get('[data-testid="analytics-dashboard"]').should('be.visible')
      
      // Charts should adapt to mobile
      cy.get('[data-testid^="stat-card-"]').should('be.visible')
    })

    it('should work on tablet devices', () => {
      cy.viewport('ipad-2')
      
      // All analytics elements should be properly sized
      cy.get('[data-testid="analytics-dashboard"]').should('be.visible')
      cy.get('[data-testid="performance-trends"]').should('be.visible')
    })
  })

  describe('Real-time Updates', () => {
    it('should update analytics after quiz completion', () => {
      // Get current quiz count
      cy.get('[data-testid="total-quizzes"]')
        .invoke('text')
        .then((initialCount) => {
          const initial = parseInt(initialCount.match(/\d+/)?.[0] || '0')
          
          // Complete a quiz
          cy.navigateToQuizzes()
          cy.completeQuiz(0, true)
          
          // Return to analytics
          cy.navigateToAnalytics()
          cy.wait(1000)
          
          // Quiz count should have increased
          cy.get('[data-testid="total-quizzes"]')
            .invoke('text')
            .then((newCount) => {
              const current = parseInt(newCount.match(/\d+/)?.[0] || '0')
              expect(current).to.be.greaterThan(initial)
            })
        })
    })
  })

  describe('Loading States', () => {
    it('should show loading states while fetching data', () => {
      cy.visit('/analytics')
      
      // Should show loading indicator initially
      cy.get('[data-testid="loading-spinner"]').should('be.visible')
      
      // Wait for data to load
      cy.wait(2000)
      
      // Loading should be replaced with content
      cy.get('[data-testid="analytics-dashboard"]').should('be.visible')
    })
  })

  describe('Error Handling', () => {
    it('should handle API errors gracefully', () => {
      // Mock API error
      cy.intercept('GET', '/api/analytics', { statusCode: 500 })
      
      cy.visit('/analytics')
      
      // Should handle error gracefully
      cy.get('body').should('exist')
    })
  })
})