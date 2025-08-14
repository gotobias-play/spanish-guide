// ***********************************************************
// Component testing support file
// ***********************************************************

import './commands'
import { mount } from 'cypress/vue'

// Add mount command for component testing
Cypress.Commands.add('mount', mount)

// Global component test configuration
beforeEach(() => {
  // Reset any global state
  cy.window().then((win) => {
    win.localStorage.clear()
    win.sessionStorage.clear()
  })
})