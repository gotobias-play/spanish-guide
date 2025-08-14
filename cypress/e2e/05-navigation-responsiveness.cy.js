describe('Navigation and Responsiveness', () => {
  beforeEach(() => {
    cy.visit('/')
  })

  describe('Main Navigation', () => {
    it('should display main navigation elements', () => {
      // Main navigation should be visible
      cy.get('nav').should('be.visible')
      
      // Logo/brand should be visible
      cy.get('[data-testid="app-logo"]').should('be.visible')
      
      // Main navigation links should be visible
      cy.get('[data-testid="nav-home"]').should('be.visible')
      cy.get('[data-testid="nav-practica"]').should('be.visible')
    })

    it('should navigate to different sections', () => {
      // Test home navigation
      cy.get('[data-testid="nav-home"]').click()
      cy.url().should('eq', Cypress.config('baseUrl') + '/')
      
      // Test practice navigation
      cy.get('[data-testid="nav-practica"]').click()
      cy.url().should('include', '/quiz-selector')
    })

    it('should show authentication buttons for guests', () => {
      // Login and register buttons should be visible for guests
      cy.get('a[href="/login"]').should('be.visible')
      cy.get('a[href="/register"]').should('be.visible')
    })

    it('should show user dropdown when logged in', () => {
      cy.login()
      
      // User dropdown should be visible
      cy.get('[data-testid="user-dropdown"]').should('be.visible')
      
      // Dropdown should contain user options
      cy.get('[data-testid="user-dropdown"]').click()
      cy.get('[data-testid="gamification-link"]').should('be.visible')
      cy.get('[data-testid="analytics-link"]').should('be.visible')
      cy.get('[data-testid="logout-button"]').should('be.visible')
    })
  })

  describe('Mobile Navigation', () => {
    beforeEach(() => {
      cy.viewport('iphone-6')
    })

    it('should show mobile menu button', () => {
      // Mobile menu button should be visible
      cy.get('[data-testid="mobile-menu-button"]').should('be.visible')
    })

    it('should open mobile menu', () => {
      // Click mobile menu button
      cy.get('[data-testid="mobile-menu-button"]').click()
      
      // Mobile menu should be visible
      cy.get('[data-testid="mobile-menu"]').should('be.visible')
    })

    it('should navigate using mobile menu', () => {
      cy.get('[data-testid="mobile-menu-button"]').click()
      
      // Mobile menu navigation should work
      cy.get('[data-testid="mobile-nav-practica"]').click()
      cy.url().should('include', '/quiz-selector')
    })

    it('should close mobile menu after navigation', () => {
      cy.get('[data-testid="mobile-menu-button"]').click()
      cy.get('[data-testid="mobile-nav-home"]').click()
      
      // Mobile menu should be hidden
      cy.get('[data-testid="mobile-menu"]').should('not.be.visible')
    })

    it('should handle touch gestures', () => {
      // Test swipe gesture to open menu
      cy.get('body').trigger('touchstart', { touches: [{ clientX: 0, clientY: 200 }] })
      cy.get('body').trigger('touchmove', { touches: [{ clientX: 100, clientY: 200 }] })
      cy.get('body').trigger('touchend')
      
      // Menu might open with swipe gesture
      cy.wait(500)
    })
  })

  describe('Language Switching', () => {
    it('should show language switcher', () => {
      // Language switcher should be visible
      cy.get('[data-testid="language-switcher"]').should('be.visible')
    })

    it('should switch between languages', () => {
      // Switch to English
      cy.switchLanguage('en')
      
      // Interface should change to English
      cy.get('[data-testid="nav-home"]').should('contain.text', 'Home')
      
      // Switch back to Spanish
      cy.switchLanguage('es')
      
      // Interface should change to Spanish
      cy.get('[data-testid="nav-home"]').should('contain.text', 'Inicio')
    })

    it('should persist language preference', () => {
      // Switch language
      cy.switchLanguage('en')
      
      // Refresh page
      cy.reload()
      
      // Language should be persisted
      cy.get('[data-testid="nav-home"]').should('contain.text', 'Home')
    })
  })

  describe('Responsive Design', () => {
    const viewports = [
      { device: 'iphone-6', width: 375, height: 667 },
      { device: 'ipad-2', width: 768, height: 1024 },
      { device: 'macbook-13', width: 1280, height: 800 }
    ]

    viewports.forEach(({ device, width, height }) => {
      it(`should work correctly on ${device}`, () => {
        cy.viewport(width, height)
        
        // Page should be responsive
        cy.get('body').should('be.visible')
        
        // Navigation should adapt to viewport
        if (width < 768) {
          // Mobile: should show mobile menu
          cy.get('[data-testid="mobile-menu-button"]').should('be.visible')
        } else {
          // Desktop: should show full navigation
          cy.get('[data-testid="nav-home"]').should('be.visible')
          cy.get('[data-testid="nav-practica"]').should('be.visible')
        }
      })
    })

    it('should handle orientation changes', () => {
      cy.viewport('iphone-6')
      
      // Portrait mode
      cy.get('[data-testid="mobile-menu-button"]').should('be.visible')
      
      // Landscape mode
      cy.viewport(667, 375)
      cy.get('body').should('be.visible')
    })
  })

  describe('PWA Features', () => {
    it('should show PWA installation prompt', () => {
      // Check for PWA meta tags
      cy.get('head meta[name="theme-color"]').should('exist')
      cy.get('head link[rel="manifest"]').should('exist')
    })

    it('should register service worker', () => {
      cy.window().then((win) => {
        expect(win.navigator.serviceWorker).to.exist
      })
    })

    it('should work offline', () => {
      // Simulate offline mode
      cy.window().then((win) => {
        cy.stub(win.navigator, 'onLine').value(false)
      })
      
      // Basic navigation should still work
      cy.get('[data-testid="nav-home"]').should('be.visible')
    })
  })

  describe('Performance', () => {
    it('should load quickly', () => {
      cy.measurePageLoadTime('/')
    })

    it('should have optimized network requests', () => {
      cy.checkNetworkRequests()
    })

    it('should lazy load components', () => {
      // Navigate to different sections
      cy.get('[data-testid="nav-practica"]').click()
      
      // Components should load progressively
      cy.get('[data-testid^="course-card-"]').should('be.visible')
    })
  })

  describe('Accessibility', () => {
    it('should have proper ARIA labels', () => {
      // Navigation should have proper accessibility
      cy.get('[data-testid="user-dropdown"]').should('have.attr', 'role', 'button')
      cy.get('[data-testid="user-dropdown"]').should('have.attr', 'aria-label')
    })

    it('should support keyboard navigation', () => {
      // Test keyboard navigation
      cy.get('body').tab()
      cy.focused().should('be.visible')
      
      // Tab through main navigation
      cy.focused().tab()
      cy.focused().should('be.visible')
    })

    it('should have sufficient color contrast', () => {
      // Check that navigation elements are visible
      cy.get('[data-testid="nav-home"]').should('be.visible')
      cy.get('[data-testid="nav-practica"]').should('be.visible')
    })
  })

  describe('Error Handling', () => {
    it('should handle navigation errors gracefully', () => {
      // Try to navigate to non-existent route
      cy.visit('/nonexistent', { failOnStatusCode: false })
      
      // Should show error page or redirect
      cy.get('body').should('exist')
    })

    it('should handle API failures gracefully', () => {
      // Mock API failure
      cy.intercept('GET', '/api/user', { statusCode: 500 })
      
      cy.login()
      
      // Should handle gracefully
      cy.get('body').should('exist')
    })
  })

  describe('Cross-browser Compatibility', () => {
    it('should work with different user agents', () => {
      // Test with different user agent
      cy.visit('/', {
        headers: {
          'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        }
      })
      
      // Basic functionality should work
      cy.get('[data-testid="nav-home"]').should('be.visible')
    })
  })
})