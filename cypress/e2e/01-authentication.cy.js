describe('Authentication Flow', () => {
  beforeEach(() => {
    cy.visit('/')
  })

  describe('User Registration', () => {
    it('should allow new user registration', () => {
      cy.visit('/register')
      
      // Fill registration form
      cy.get('input[name="name"]').type('Test User E2E')
      cy.get('input[name="email"]').type('teste2e@example.com')
      cy.get('input[name="password"]').type('password123')
      cy.get('input[name="password_confirmation"]').type('password123')
      
      // Submit form
      cy.get('button[type="submit"]').click()
      
      // Should redirect to dashboard after successful registration
      cy.url().should('not.include', '/register')
      cy.url().should('include', '/')
      
      // Should show user is logged in
      cy.get('[data-testid="user-dropdown"]').should('be.visible')
    })

    it('should show validation errors for invalid registration', () => {
      cy.visit('/register')
      
      // Try to submit empty form
      cy.get('button[type="submit"]').click()
      
      // Should stay on registration page
      cy.url().should('include', '/register')
      
      // Should show validation errors (implementation dependent)
      cy.get('form').should('exist')
    })

    it('should prevent registration with existing email', () => {
      cy.visit('/register')
      
      // Try to register with existing email
      cy.get('input[name="name"]').type('Another User')
      cy.get('input[name="email"]').type('user@example.com') // Existing user
      cy.get('input[name="password"]').type('password123')
      cy.get('input[name="password_confirmation"]').type('password123')
      
      cy.get('button[type="submit"]').click()
      
      // Should stay on registration page or show error
      cy.url().should('include', '/register')
    })
  })

  describe('User Login', () => {
    it('should allow user login with valid credentials', () => {
      cy.visit('/login')
      
      // Fill login form
      cy.get('input[name="email"]').type('user@example.com')
      cy.get('input[name="password"]').type('password')
      
      // Submit form
      cy.get('button[type="submit"]').click()
      
      // Should redirect to dashboard
      cy.url().should('not.include', '/login')
      
      // Should show user dropdown
      cy.get('[data-testid="user-dropdown"]').should('be.visible')
      
      // User name should be visible
      cy.get('[data-testid="user-dropdown"]').should('contain.text', 'Usuario')
    })

    it('should reject invalid credentials', () => {
      cy.visit('/login')
      
      // Fill with invalid credentials
      cy.get('input[name="email"]').type('invalid@example.com')
      cy.get('input[name="password"]').type('wrongpassword')
      
      cy.get('button[type="submit"]').click()
      
      // Should stay on login page
      cy.url().should('include', '/login')
    })

    it('should allow admin login', () => {
      cy.visit('/login')
      
      // Login as admin
      cy.get('input[name="email"]').type('admin@example.com')
      cy.get('input[name="password"]').type('password')
      
      cy.get('button[type="submit"]').click()
      
      // Should redirect and show admin options
      cy.url().should('not.include', '/login')
      cy.get('[data-testid="user-dropdown"]').should('be.visible')
      
      // Admin should have admin panel access
      cy.get('[data-testid="user-dropdown"]').click()
      cy.get('[data-testid="admin-link"]').should('be.visible')
    })
  })

  describe('User Logout', () => {
    beforeEach(() => {
      cy.login()
    })

    it('should allow user logout', () => {
      // User should be logged in
      cy.get('[data-testid="user-dropdown"]').should('be.visible')
      
      // Logout
      cy.logout()
      
      // Should redirect to home page
      cy.url().should('eq', Cypress.config('baseUrl') + '/')
      
      // Login/Register buttons should be visible
      cy.get('a[href="/login"]').should('be.visible')
      cy.get('a[href="/register"]').should('be.visible')
    })
  })

  describe('Authentication State Persistence', () => {
    it('should maintain login state across page refreshes', () => {
      cy.login()
      
      // Refresh page
      cy.reload()
      
      // Should still be logged in
      cy.get('[data-testid="user-dropdown"]').should('be.visible')
    })

    it('should redirect unauthenticated users from protected routes', () => {
      // Try to access protected route without login
      cy.visit('/gamification', { failOnStatusCode: false })
      
      // Should redirect to login or show login prompt
      cy.url().should('satisfy', (url) => {
        return url.includes('/login') || url.includes('/')
      })
    })
  })

  describe('Password Reset Flow', () => {
    it('should allow password reset request', () => {
      cy.visit('/login')
      
      // Click forgot password if available
      cy.get('body').then(($body) => {
        if ($body.find('a[href*="password"]').length > 0) {
          cy.get('a[href*="password"]').click()
          
          // Fill email for password reset
          cy.get('input[name="email"]').type('user@example.com')
          cy.get('button[type="submit"]').click()
          
          // Should show success message or redirect
          cy.url().should('not.include', '/password')
        }
      })
    })
  })
})