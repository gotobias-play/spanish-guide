// ***********************************************************
// This file is loaded automatically before every test file
// ***********************************************************

// Import commands.js using ES2015 syntax:
import './commands'

// Global Cypress configuration
Cypress.config('defaultCommandTimeout', 10000)
Cypress.config('requestTimeout', 10000)
Cypress.config('responseTimeout', 10000)

// Prevent uncaught exceptions from failing tests
Cypress.on('uncaught:exception', (err, runnable) => {
  // Ignore common runtime errors that don't affect test execution
  if (err.message.includes('ResizeObserver loop limit exceeded')) {
    return false
  }
  if (err.message.includes('Non-Error promise rejection captured')) {
    return false
  }
  return true
})

// Global before hook
beforeEach(() => {
  // Clear localStorage before each test
  cy.clearLocalStorage()
  
  // Wait for application to be ready
  cy.visit('/', { failOnStatusCode: false })
  cy.wait(1000)
})