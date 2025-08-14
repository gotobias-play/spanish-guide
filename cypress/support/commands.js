// ***********************************************
// Custom Cypress Commands for Spanish English Learning App
// ***********************************************

// Authentication commands
Cypress.Commands.add('login', (email = 'user@example.com', password = 'password') => {
  cy.visit('/login')
  cy.get('input[name="email"]').type(email)
  cy.get('input[name="password"]').type(password)
  cy.get('button[type="submit"]').click()
  cy.url().should('not.include', '/login')
  cy.wait(1000)
})

Cypress.Commands.add('loginAsAdmin', () => {
  cy.login('admin@example.com', 'password')
})

Cypress.Commands.add('register', (name = 'Test User', email = 'test@example.com', password = 'password') => {
  cy.visit('/register')
  cy.get('input[name="name"]').type(name)
  cy.get('input[name="email"]').type(email)
  cy.get('input[name="password"]').type(password)
  cy.get('input[name="password_confirmation"]').type(password)
  cy.get('button[type="submit"]').click()
  cy.url().should('not.include', '/register')
  cy.wait(1000)
})

Cypress.Commands.add('logout', () => {
  cy.get('[data-testid="user-dropdown"]').click()
  cy.get('[data-testid="logout-button"]').click()
  cy.url().should('include', '/')
})

// Navigation commands
Cypress.Commands.add('navigateToQuizzes', () => {
  cy.get('[data-testid="quiz-selector-link"]').click()
  cy.url().should('include', '/quiz-selector')
  cy.wait(1000)
})

Cypress.Commands.add('navigateToGamification', () => {
  cy.get('[data-testid="user-dropdown"]').click()
  cy.get('[data-testid="gamification-link"]').click()
  cy.url().should('include', '/gamification')
  cy.wait(1000)
})

Cypress.Commands.add('navigateToAnalytics', () => {
  cy.get('[data-testid="user-dropdown"]').click()
  cy.get('[data-testid="analytics-link"]').click()
  cy.url().should('include', '/analytics')
  cy.wait(1000)
})

// Quiz interaction commands
Cypress.Commands.add('startQuiz', (courseIndex = 0) => {
  cy.get('[data-testid^="course-card-"]').eq(courseIndex).click()
  cy.wait(500)
  cy.get('[data-testid^="quiz-card-"]').first().click()
  cy.wait(1000)
})

Cypress.Commands.add('answerQuizQuestion', (optionIndex = 0) => {
  cy.get('[data-testid^="option-"]').eq(optionIndex).click()
  cy.wait(500)
})

Cypress.Commands.add('submitQuiz', () => {
  cy.get('[data-testid="submit-quiz"]').click()
  cy.wait(2000)
})

Cypress.Commands.add('completeQuiz', (courseIndex = 0, answerCorrectly = true) => {
  cy.startQuiz(courseIndex)
  
  // Answer all questions
  cy.get('[data-testid^="option-"]').then(($options) => {
    const optionIndex = answerCorrectly ? 0 : 1 // Assume first option is correct
    cy.answerQuizQuestion(optionIndex)
  })
  
  cy.submitQuiz()
})

// Language switching commands
Cypress.Commands.add('switchLanguage', (language = 'en') => {
  cy.get('[data-testid="language-switcher-button"]').click()
  cy.get(`[data-testid="language-option-${language}"]`).click()
  cy.wait(1000)
})

// Social interaction commands
Cypress.Commands.add('sendFriendRequest', (userName) => {
  cy.get('[data-testid="user-dropdown"]').click()
  cy.get('[data-testid="social-link"]').click()
  cy.get('[data-testid="search-tab"]').click()
  cy.get('[data-testid="user-search-input"]').type(userName)
  cy.get('[data-testid="search-button"]').click()
  cy.wait(1000)
  cy.get('[data-testid^="send-request-"]').first().click()
  cy.wait(500)
})

// Utility commands
Cypress.Commands.add('waitForApiResponse', (alias) => {
  cy.wait(alias)
  cy.wait(500) // Additional wait for UI updates
})

Cypress.Commands.add('checkElementExists', (selector) => {
  cy.get(selector).should('exist')
})

Cypress.Commands.add('checkElementVisible', (selector) => {
  cy.get(selector).should('be.visible')
})

Cypress.Commands.add('checkTextContent', (selector, text) => {
  cy.get(selector).should('contain.text', text)
})

// API mocking commands
Cypress.Commands.add('mockApiResponse', (method, url, response) => {
  cy.intercept(method, url, response)
})

Cypress.Commands.add('mockQuizData', () => {
  cy.mockApiResponse('GET', '/api/public/courses', { fixture: 'courses.json' })
  cy.mockApiResponse('GET', '/api/public/quizzes', { fixture: 'quizzes.json' })
})

// Performance testing commands
Cypress.Commands.add('measurePageLoadTime', (url) => {
  const startTime = Date.now()
  cy.visit(url)
  cy.window().then(() => {
    const loadTime = Date.now() - startTime
    cy.log(`Page load time: ${loadTime}ms`)
    expect(loadTime).to.be.lessThan(5000) // Page should load in under 5 seconds
  })
})

Cypress.Commands.add('checkNetworkRequests', () => {
  cy.window().its('performance').invoke('getEntriesByType', 'navigation').then((navigation) => {
    const loadTime = navigation[0].loadEventEnd - navigation[0].fetchStart
    cy.log(`Total page load time: ${loadTime}ms`)
    expect(loadTime).to.be.lessThan(3000)
  })
})