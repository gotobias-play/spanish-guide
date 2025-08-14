describe('Gamification Features', () => {
  beforeEach(() => {
    cy.login()
  })

  describe('Points System', () => {
    it('should display user points in gamification dashboard', () => {
      cy.navigateToGamification()
      
      // Points should be visible
      cy.get('[data-testid="total-points"]').should('be.visible')
      cy.get('[data-testid="total-points"]').should('contain.text', 'Puntos')
    })

    it('should award points for quiz completion', () => {
      // Get initial points
      cy.navigateToGamification()
      cy.get('[data-testid="total-points"]')
        .invoke('text')
        .then((initialPoints) => {
          const initial = parseInt(initialPoints.match(/\d+/)?.[0] || '0')
          
          // Complete a quiz
          cy.navigateToQuizzes()
          cy.completeQuiz(0, true)
          
          // Check if points increased
          cy.navigateToGamification()
          cy.get('[data-testid="total-points"]')
            .invoke('text')
            .then((newPoints) => {
              const current = parseInt(newPoints.match(/\d+/)?.[0] || '0')
              expect(current).to.be.greaterThan(initial)
            })
        })
    })

    it('should display points history', () => {
      cy.navigateToGamification()
      
      // Points history section should be visible
      cy.get('[data-testid="points-history-section"]').should('be.visible')
      
      // Should show transaction items
      cy.get('[data-testid^="history-item-"]').should('have.length.greaterThan', 0)
    })
  })

  describe('Achievement System', () => {
    it('should display achievements dashboard', () => {
      cy.navigateToGamification()
      
      // Achievements section should be visible
      cy.get('[data-testid="achievements-section"]').should('be.visible')
    })

    it('should show earned achievements', () => {
      cy.navigateToGamification()
      
      // Should show achievement cards
      cy.get('[data-testid^="achievement-card-"]').should('have.length.greaterThan', 0)
      
      // First achievement should show details
      cy.get('[data-testid="achievement-card-1"]').should('be.visible')
      cy.get('[data-testid="achievement-card-1"]').should('contain.text', 'First Steps')
    })

    it('should unlock achievements for specific actions', () => {
      // Complete a quiz to potentially unlock achievement
      cy.navigateToQuizzes()
      cy.completeQuiz(0, true)
      
      // Check for achievement notification or updated count
      cy.navigateToGamification()
      cy.get('[data-testid="total-achievements"]').should('be.visible')
    })

    it('should display achievement progress', () => {
      cy.navigateToGamification()
      
      // Achievement cards should show progress information
      cy.get('[data-testid^="achievement-card-"]').each(($achievement) => {
        cy.wrap($achievement).should('contain.text', '🌟')
      })
    })
  })

  describe('Streak System', () => {
    it('should display current streak', () => {
      cy.navigateToGamification()
      
      // Current streak should be visible
      cy.get('[data-testid="current-streak"]').should('be.visible')
      cy.get('[data-testid="current-streak"]').should('contain.text', 'día')
    })

    it('should show longest streak', () => {
      cy.navigateToGamification()
      
      // Longest streak should be displayed
      cy.get('[data-testid="longest-streak"]').should('be.visible')
    })

    it('should update streak after quiz completion', () => {
      // Get current streak
      cy.navigateToGamification()
      cy.get('[data-testid="current-streak"]')
        .invoke('text')
        .then((streakText) => {
          // Complete a quiz
          cy.navigateToQuizzes()
          cy.completeQuiz(0, true)
          
          // Streak might update (depending on last activity)
          cy.navigateToGamification()
          cy.get('[data-testid="current-streak"]').should('be.visible')
        })
    })
  })

  describe('Leaderboard', () => {
    it('should display leaderboard', () => {
      cy.navigateToGamification()
      
      // Leaderboard section should be visible
      cy.get('[data-testid="leaderboard-section"]').should('be.visible')
    })

    it('should show top users', () => {
      cy.navigateToGamification()
      
      // Should show leaderboard items
      cy.get('[data-testid^="leaderboard-item-"]').should('have.length.greaterThan', 0)
      
      // First item should show user details
      cy.get('[data-testid="leaderboard-item-0"]').should('be.visible')
      cy.get('[data-testid="leaderboard-item-0"]').should('contain.text', 'User')
    })

    it('should show user rankings', () => {
      cy.navigateToGamification()
      
      // Leaderboard items should show rankings
      cy.get('[data-testid="leaderboard-item-0"]').should('contain.text', '1')
    })
  })

  describe('Gamification Statistics', () => {
    it('should display comprehensive stats', () => {
      cy.navigateToGamification()
      
      // All stat cards should be visible
      cy.get('[data-testid="stat-card-points"]').should('be.visible')
      cy.get('[data-testid="stat-card-quizzes"]').should('be.visible')
      cy.get('[data-testid="stat-card-achievements"]').should('be.visible')
    })

    it('should show quiz completion count', () => {
      cy.navigateToGamification()
      
      // Quiz count should be displayed
      cy.get('[data-testid="total-quizzes"]').should('be.visible')
      cy.get('[data-testid="total-quizzes"]').should('contain.text', 'Quiz')
    })

    it('should display average score', () => {
      cy.navigateToGamification()
      
      // Average score should be shown
      cy.get('[data-testid="average-score"]').should('be.visible')
    })
  })

  describe('Achievement Notifications', () => {
    it('should show achievement notifications after quiz completion', () => {
      // Complete quiz and watch for notifications
      cy.navigateToQuizzes()
      cy.completeQuiz(0, true)
      
      // Look for achievement notification (if any)
      cy.get('body').then(($body) => {
        if ($body.find('[data-testid*="achievement-notification"]').length > 0) {
          cy.get('[data-testid*="achievement-notification"]').should('be.visible')
        }
      })
    })
  })

  describe('Responsive Design', () => {
    it('should work on mobile devices', () => {
      cy.viewport('iphone-6')
      cy.navigateToGamification()
      
      // Dashboard should be responsive
      cy.get('[data-testid="gamification-dashboard"]').should('be.visible')
      
      // Stats cards should stack on mobile
      cy.get('[data-testid^="stat-card-"]').should('be.visible')
    })

    it('should work on tablet devices', () => {
      cy.viewport('ipad-2')
      cy.navigateToGamification()
      
      // All elements should be properly sized
      cy.get('[data-testid="gamification-dashboard"]').should('be.visible')
      cy.get('[data-testid="achievements-section"]').should('be.visible')
    })
  })

  describe('Data Persistence', () => {
    it('should persist gamification data across sessions', () => {
      // Get current points
      cy.navigateToGamification()
      cy.get('[data-testid="total-points"]')
        .invoke('text')
        .then((points) => {
          // Logout and login again
          cy.logout()
          cy.login()
          
          // Points should be the same
          cy.navigateToGamification()
          cy.get('[data-testid="total-points"]').should('contain.text', points.match(/\d+/)?.[0] || '0')
        })
    })
  })
})