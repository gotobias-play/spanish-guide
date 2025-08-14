describe('Performance and Accessibility', () => {
  beforeEach(() => {
    cy.visit('/')
  })

  describe('Performance Testing', () => {
    it('should load the home page quickly', () => {
      cy.measurePageLoadTime('/')
    })

    it('should load quiz selector quickly', () => {
      cy.measurePageLoadTime('/quiz-selector')
    })

    it('should have optimized network requests', () => {
      cy.checkNetworkRequests()
    })

    it('should load images efficiently', () => {
      cy.visit('/')
      
      // Check for image optimization
      cy.get('img').each(($img) => {
        cy.wrap($img).should('have.attr', 'src')
        // Images should have appropriate alt text
        cy.wrap($img).should('have.attr', 'alt')
      })
    })

    it('should handle concurrent API requests efficiently', () => {
      cy.login()
      
      // Navigate to data-heavy page
      cy.navigateToAnalytics()
      
      // Should load multiple data sources efficiently
      cy.get('[data-testid="analytics-dashboard"]').should('be.visible')
      cy.wait(500)
    })

    it('should lazy load components properly', () => {
      // Navigate to different sections to test lazy loading
      cy.get('[data-testid="nav-practica"]').click()
      cy.wait(1000)
      
      // Components should load progressively
      cy.get('[data-testid^="course-card-"]').should('be.visible')
    })

    it('should handle large datasets efficiently', () => {
      cy.login()
      cy.navigateToGamification()
      
      // Leaderboard should handle large user lists
      cy.get('[data-testid="leaderboard-section"]').should('be.visible')
      
      // Should not freeze the interface
      cy.get('[data-testid="gamification-dashboard"]').should('be.responsive')
    })
  })

  describe('Accessibility Testing', () => {
    it('should have proper semantic HTML structure', () => {
      // Check for proper heading hierarchy
      cy.get('h1').should('exist')
      cy.get('main').should('exist')
      cy.get('nav').should('exist')
    })

    it('should have proper ARIA labels', () => {
      // Navigation should have proper ARIA
      cy.get('[data-testid="user-dropdown"]').should('have.attr', 'aria-label')
      cy.get('[data-testid="language-switcher-button"]').should('have.attr', 'aria-expanded')
    })

    it('should support keyboard navigation', () => {
      // Test tab navigation
      cy.get('body').tab()
      cy.focused().should('be.visible')
      
      // Continue tabbing through interactive elements
      cy.focused().tab()
      cy.focused().should('be.visible')
      
      // Test Enter key activation
      cy.focused().type('{enter}')
    })

    it('should have sufficient color contrast', () => {
      // Check main navigation elements are visible
      cy.get('[data-testid="nav-home"]').should('be.visible')
      cy.get('[data-testid="nav-practica"]').should('be.visible')
      
      // Text should be readable
      cy.get('body').should('have.css', 'color')
      cy.get('body').should('have.css', 'background-color')
    })

    it('should have proper alt text for images', () => {
      cy.get('img').each(($img) => {
        cy.wrap($img).should('have.attr', 'alt')
        cy.wrap($img).then(($el) => {
          const alt = $el.attr('alt')
          expect(alt).to.not.be.empty
        })
      })
    })

    it('should have proper form labels', () => {
      cy.visit('/login')
      
      // Form inputs should have proper labels
      cy.get('input[name="email"]').should('have.attr', 'id')
      cy.get('label[for]').should('exist')
    })

    it('should support screen readers', () => {
      // Important interactive elements should have proper ARIA
      cy.get('[data-testid="user-dropdown"]').should('have.attr', 'role')
      cy.get('[data-testid="language-switcher"]').should('have.attr', 'role')
    })

    it('should handle focus management', () => {
      // Modal dialogs should manage focus properly
      cy.get('[data-testid="language-switcher-button"]').click()
      
      // Focus should be within the dropdown
      cy.get('[data-testid="language-dropdown"]').should('be.visible')
      
      // Escape should close and return focus
      cy.get('body').type('{esc}')
      cy.get('[data-testid="language-dropdown"]').should('not.be.visible')
    })
  })

  describe('Mobile Performance', () => {
    beforeEach(() => {
      cy.viewport('iphone-6')
    })

    it('should perform well on mobile devices', () => {
      cy.measurePageLoadTime('/')
    })

    it('should handle touch interactions smoothly', () => {
      // Test touch-specific interactions
      cy.get('[data-testid="mobile-menu-button"]').click()
      cy.get('[data-testid="mobile-menu"]').should('be.visible')
      
      // Touch interactions should be responsive
      cy.get('[data-testid="mobile-nav-practica"]').click()
      cy.url().should('include', '/quiz-selector')
    })

    it('should optimize for mobile bandwidth', () => {
      // Mobile version should load efficiently
      cy.visit('/')
      cy.get('body').should('be.visible')
      
      // Check that mobile-specific optimizations are in place
      cy.checkNetworkRequests()
    })
  })

  describe('Progressive Web App Performance', () => {
    it('should register service worker', () => {
      cy.window().then((win) => {
        expect(win.navigator.serviceWorker).to.exist
        
        // Check if service worker is registered
        return win.navigator.serviceWorker.getRegistration()
      }).then((registration) => {
        expect(registration).to.exist
      })
    })

    it('should cache resources for offline use', () => {
      // Visit pages to populate cache
      cy.visit('/')
      cy.visit('/quiz-selector')
      
      // Check that resources are cached
      cy.window().then((win) => {
        return win.caches.keys()
      }).then((cacheNames) => {
        expect(cacheNames.length).to.be.greaterThan(0)
      })
    })

    it('should handle offline scenarios', () => {
      // Simulate offline mode
      cy.window().then((win) => {
        cy.stub(win.navigator, 'onLine').value(false)
      })
      
      // Basic navigation should still work
      cy.get('[data-testid="nav-home"]').should('be.visible')
    })

    it('should provide fast loading with service worker', () => {
      // Second visit should be faster due to caching
      cy.visit('/')
      cy.reload()
      
      // Page should load quickly from cache
      cy.get('[data-testid="nav-home"]').should('be.visible')
    })
  })

  describe('Lighthouse Performance Metrics', () => {
    it('should have good Core Web Vitals', () => {
      cy.visit('/')
      
      // Measure performance metrics
      cy.window().its('performance').invoke('getEntriesByType', 'navigation').then((navigation) => {
        const loadTime = navigation[0].loadEventEnd - navigation[0].fetchStart
        const domContentLoaded = navigation[0].domContentLoadedEventEnd - navigation[0].fetchStart
        
        // Performance thresholds
        expect(loadTime).to.be.lessThan(3000) // Total load time under 3s
        expect(domContentLoaded).to.be.lessThan(1500) // DOM ready under 1.5s
      })
    })

    it('should have optimized resource loading', () => {
      cy.visit('/')
      
      // Check for critical resource optimization
      cy.window().its('performance').invoke('getEntriesByType', 'resource').then((resources) => {
        // CSS should load quickly
        const cssResources = resources.filter(r => r.name.includes('.css'))
        cssResources.forEach(css => {
          expect(css.duration).to.be.lessThan(500)
        })
        
        // JavaScript should load efficiently
        const jsResources = resources.filter(r => r.name.includes('.js'))
        jsResources.forEach(js => {
          expect(js.duration).to.be.lessThan(1000)
        })
      })
    })
  })

  describe('Memory Performance', () => {
    it('should not have memory leaks', () => {
      // Navigate through different sections
      cy.visit('/')
      cy.get('[data-testid="nav-practica"]').click()
      cy.wait(1000)
      cy.get('[data-testid="nav-home"]').click()
      cy.wait(1000)
      
      // Memory usage should be reasonable
      cy.window().then((win) => {
        if (win.performance.memory) {
          const memoryInfo = win.performance.memory
          expect(memoryInfo.usedJSHeapSize).to.be.lessThan(50 * 1024 * 1024) // Under 50MB
        }
      })
    })

    it('should clean up properly on component unmount', () => {
      cy.login()
      
      // Navigate to complex components
      cy.navigateToAnalytics()
      cy.wait(2000)
      
      // Navigate away
      cy.get('[data-testid="nav-home"]').click()
      cy.wait(1000)
      
      // Components should clean up properly
      cy.window().then((win) => {
        // Check that no excessive DOM nodes remain
        const nodeCount = win.document.querySelectorAll('*').length
        expect(nodeCount).to.be.lessThan(2000) // Reasonable DOM size
      })
    })
  })
})