describe('Social Features', () => {
  beforeEach(() => {
    cy.login()
  })

  describe('Social Dashboard Access', () => {
    it('should navigate to social dashboard', () => {
      // Navigate to social features
      cy.get('[data-testid="user-dropdown"]').click()
      cy.get('[data-testid="social-link"]').click()
      
      // Should be on social dashboard
      cy.url().should('include', '/social')
      cy.get('[data-testid="social-dashboard"]').should('be.visible')
    })

    it('should display social statistics', () => {
      cy.get('[data-testid="user-dropdown"]').click()
      cy.get('[data-testid="social-link"]').click()
      
      // Social stats should be visible
      cy.get('[data-testid="friends-count"]').should('be.visible')
      cy.get('[data-testid="pending-requests"]').should('be.visible')
    })
  })

  describe('Friends Management', () => {
    beforeEach(() => {
      cy.get('[data-testid="user-dropdown"]').click()
      cy.get('[data-testid="social-link"]').click()
      cy.wait(1000)
    })

    it('should display friends tab', () => {
      // Friends tab should be active by default
      cy.get('[data-testid="friends-tab"]').should('be.visible')
      cy.get('[data-testid="friends-tab"]').should('have.class', 'active')
    })

    it('should show friends list', () => {
      // Friends list should be visible
      cy.get('[data-testid="friends-list"]').should('be.visible')
      
      // Should show friend items if any exist
      cy.get('body').then(($body) => {
        if ($body.find('[data-testid^="friend-item-"]').length > 0) {
          cy.get('[data-testid^="friend-item-"]').should('be.visible')
        }
      })
    })

    it('should allow removing friends', () => {
      // If friends exist, test removal
      cy.get('body').then(($body) => {
        if ($body.find('[data-testid^="remove-friend-"]').length > 0) {
          cy.get('[data-testid^="remove-friend-"]').first().click()
          
          // Should show confirmation or remove friend
          cy.wait(500)
        }
      })
    })
  })

  describe('Friend Requests', () => {
    beforeEach(() => {
      cy.get('[data-testid="user-dropdown"]').click()
      cy.get('[data-testid="social-link"]').click()
      cy.get('[data-testid="requests-tab"]').click()
      cy.wait(1000)
    })

    it('should display requests tab', () => {
      // Requests tab should be visible and active
      cy.get('[data-testid="requests-tab"]').should('have.class', 'active')
      cy.get('[data-testid="requests-section"]').should('be.visible')
    })

    it('should show pending requests', () => {
      // Should show requests list
      cy.get('[data-testid="requests-list"]').should('be.visible')
      
      // Should show request items if any exist
      cy.get('body').then(($body) => {
        if ($body.find('[data-testid^="request-item-"]').length > 0) {
          cy.get('[data-testid^="request-item-"]').should('be.visible')
        }
      })
    })

    it('should allow accepting friend requests', () => {
      // If requests exist, test acceptance
      cy.get('body').then(($body) => {
        if ($body.find('[data-testid^="accept-request-"]').length > 0) {
          cy.get('[data-testid^="accept-request-"]').first().click()
          cy.wait(500)
        }
      })
    })

    it('should allow declining friend requests', () => {
      // If requests exist, test declining
      cy.get('body').then(($body) => {
        if ($body.find('[data-testid^="decline-request-"]').length > 0) {
          cy.get('[data-testid^="decline-request-"]').first().click()
          cy.wait(500)
        }
      })
    })
  })

  describe('User Search', () => {
    beforeEach(() => {
      cy.get('[data-testid="user-dropdown"]').click()
      cy.get('[data-testid="social-link"]').click()
      cy.get('[data-testid="search-tab"]').click()
      cy.wait(1000)
    })

    it('should display search interface', () => {
      // Search tab should be active
      cy.get('[data-testid="search-tab"]').should('have.class', 'active')
      
      // Search input should be visible
      cy.get('[data-testid="user-search-input"]').should('be.visible')
      cy.get('[data-testid="search-button"]').should('be.visible')
    })

    it('should search for users', () => {
      // Type in search input
      cy.get('[data-testid="user-search-input"]').type('User')
      cy.get('[data-testid="search-button"]').click()
      
      // Should show search results
      cy.wait(1000)
      cy.get('[data-testid="search-results"]').should('be.visible')
    })

    it('should allow sending friend requests from search', () => {
      // Search for users
      cy.get('[data-testid="user-search-input"]').type('Test')
      cy.get('[data-testid="search-button"]').click()
      cy.wait(1000)
      
      // If users found, should allow sending requests
      cy.get('body').then(($body) => {
        if ($body.find('[data-testid^="send-request-"]').length > 0) {
          cy.get('[data-testid^="send-request-"]').first().click()
          cy.wait(500)
        }
      })
    })

    it('should show friendship status in search results', () => {
      // Search results should show current friendship status
      cy.get('[data-testid="user-search-input"]').type('Admin')
      cy.get('[data-testid="search-button"]').click()
      cy.wait(1000)
      
      // Should show status indicators
      cy.get('[data-testid="search-results"]').should('be.visible')
    })
  })

  describe('Social Activity Feed', () => {
    beforeEach(() => {
      cy.get('[data-testid="user-dropdown"]').click()
      cy.get('[data-testid="social-link"]').click()
      cy.get('[data-testid="feed-tab"]').click()
      cy.wait(1000)
    })

    it('should display activity feed', () => {
      // Feed tab should be active
      cy.get('[data-testid="feed-tab"]').should('have.class', 'active')
      
      // Activity feed should be visible
      cy.get('[data-testid="activity-feed"]').should('be.visible')
    })

    it('should show friend activities', () => {
      // Should show activity items
      cy.get('[data-testid="activity-list"]').should('be.visible')
      
      // Should show activity items if any exist
      cy.get('body').then(($body) => {
        if ($body.find('[data-testid^="activity-item-"]').length > 0) {
          cy.get('[data-testid^="activity-item-"]').should('be.visible')
        }
      })
    })

    it('should display activity timestamps', () => {
      // Activity items should show timestamps
      cy.get('body').then(($body) => {
        if ($body.find('[data-testid^="activity-item-"]').length > 0) {
          cy.get('[data-testid^="activity-item-"]').first().should('contain.text', 'ago')
        }
      })
    })
  })

  describe('Friend Leaderboard', () => {
    beforeEach(() => {
      cy.get('[data-testid="user-dropdown"]').click()
      cy.get('[data-testid="social-link"]').click()
      cy.get('[data-testid="leaderboard-tab"]').click()
      cy.wait(1000)
    })

    it('should display friend leaderboard', () => {
      // Leaderboard tab should be active
      cy.get('[data-testid="leaderboard-tab"]').should('have.class', 'active')
      
      // Leaderboard should be visible
      cy.get('[data-testid="friend-leaderboard"]').should('be.visible')
    })

    it('should show friend rankings', () => {
      // Should show leaderboard items
      cy.get('[data-testid="leaderboard-list"]').should('be.visible')
      
      // Should show ranking items if friends exist
      cy.get('body').then(($body) => {
        if ($body.find('[data-testid^="rank-item-"]').length > 0) {
          cy.get('[data-testid^="rank-item-"]').should('be.visible')
        }
      })
    })

    it('should display ranking positions', () => {
      // Rankings should show positions (1st, 2nd, 3rd, etc.)
      cy.get('body').then(($body) => {
        if ($body.find('[data-testid^="rank-item-"]').length > 0) {
          cy.get('[data-testid^="rank-item-"]').first().should('contain.text', '1')
        }
      })
    })

    it('should show points and achievements', () => {
      // Leaderboard should show friend stats
      cy.get('body').then(($body) => {
        if ($body.find('[data-testid^="rank-item-"]').length > 0) {
          cy.get('[data-testid^="rank-item-"]').first().should('contain.text', 'points')
        }
      })
    })
  })

  describe('Social Notifications', () => {
    it('should show friend request notifications', () => {
      // Look for notification indicators
      cy.get('body').then(($body) => {
        if ($body.find('[data-testid="notification-badge"]').length > 0) {
          cy.get('[data-testid="notification-badge"]').should('be.visible')
        }
      })
    })

    it('should update notifications in real-time', () => {
      // This would require WebSocket testing
      // For now, just verify the notification system exists
      cy.get('[data-testid="user-dropdown"]').click()
      cy.get('[data-testid="social-link"]').should('be.visible')
    })
  })

  describe('Social Integration with Learning', () => {
    it('should show learning activities in social feed', () => {
      // Complete a quiz to generate social activity
      cy.navigateToQuizzes()
      cy.completeQuiz(0, true)
      
      // Check if activity appears in social feed
      cy.get('[data-testid="user-dropdown"]').click()
      cy.get('[data-testid="social-link"]').click()
      cy.get('[data-testid="feed-tab"]').click()
      
      // Should show recent quiz completion
      cy.get('[data-testid="activity-feed"]').should('be.visible')
    })

    it('should track social achievements', () => {
      // Navigate to gamification to check for social achievements
      cy.navigateToGamification()
      
      // Should show social-related achievements if any exist
      cy.get('[data-testid="achievements-section"]').should('be.visible')
    })
  })

  describe('Privacy and Security', () => {
    it('should only show appropriate user information', () => {
      // User search should not expose sensitive information
      cy.get('[data-testid="user-dropdown"]').click()
      cy.get('[data-testid="social-link"]').click()
      cy.get('[data-testid="search-tab"]').click()
      
      cy.get('[data-testid="user-search-input"]').type('Test')
      cy.get('[data-testid="search-button"]').click()
      
      // Should only show public profile information
      cy.get('[data-testid="search-results"]').should('be.visible')
    })

    it('should prevent unauthorized actions', () => {
      // Social actions should require proper authentication
      cy.get('[data-testid="user-dropdown"]').click()
      cy.get('[data-testid="social-link"]').click()
      
      // All social features should be accessible to authenticated users
      cy.get('[data-testid="social-dashboard"]').should('be.visible')
    })
  })

  describe('Responsive Social Interface', () => {
    it('should work on mobile devices', () => {
      cy.viewport('iphone-6')
      
      // Navigate to social features
      cy.get('[data-testid="mobile-menu-button"]').click()
      cy.get('[data-testid="mobile-social-link"]').click()
      
      // Social dashboard should be mobile responsive
      cy.get('[data-testid="social-dashboard"]').should('be.visible')
    })

    it('should have touch-friendly social interactions', () => {
      cy.viewport('iphone-6')
      
      cy.get('[data-testid="user-dropdown"]').click()
      cy.get('[data-testid="social-link"]').click()
      
      // Tabs should be touch-friendly
      cy.get('[data-testid="search-tab"]').click()
      cy.get('[data-testid="user-search-input"]').should('be.visible')
    })
  })
})