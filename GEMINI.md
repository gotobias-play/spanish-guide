## Project Overview

This project is an interactive English learning application for Spanish speakers. It was converted from a static HTML file into a full-fledged single-page application (SPA) using the TALL stack (Tailwind CSS, Alpine.js - though Vue was used instead, Livewire) and Vue.js.

### Core Technologies

*   **Framework:** Laravel
*   **Frontend:** Vue.js 3 (using the Options API)
*   **Routing:** Vue Router
*   **Styling:** Tailwind CSS (configured via `tailwind.config.js` and `resources/css/app.css`)
*   **Animations:** GSAP (installed via npm)
*   **Build System:** Vite

### Key File Structure

*   **Main Blade View:** `resources/views/welcome.blade.php` (This is the entry point for the Vue app and contains the `<div id="app"></div>`)
*   **Main JavaScript File:** `resources/js/app.js` (This file initializes Vue, sets up the router, and mounts the main `App.vue` component.)
*   **Vue Components:** Located in `resources/js/components/`
    *   `App.vue`: The root component containing the navigation and router view, now featuring a dropdown for authenticated users.
    *   `Home.vue`: The welcome page.
    *   `Foundations.vue`: Interactive exercises for "To Be" and "To Have".
    *   `DailyLife.vue`: The sentence builder for the present simple tense (now a practice area).
    *   `City.vue`: The interactive map and preposition exercises.
    *   `Restaurant.vue`: The quantifiers quiz and food vocabulary flip cards (now integrated as a quiz within `QuizMain.vue`).
    *   `Questions.vue`: The interactive "Wh" questions guide.
    *   `QuizHistory.vue`: Displays a user's past quiz attempts and detailed results.
    *   `AdminPanel.vue`: A basic panel for administrators to view user information.
    *   `Modal.vue`: A reusable modal component.
    *   `Dashboard.vue`: Provides an overall progress dashboard for users.
    *   `QuizMain.vue`: Central hub for all quizzes, including `RestaurantQuiz` and `MixedQuiz`.
    *   `MixedQuiz.vue`: A quiz component with mixed questions from various lessons.
*   **CSS:** `resources/css/app.css` (Contains all the custom Tailwind CSS component classes.)
*   **Git Repository:** `https://github.com/gotobias-play/spanish-guide.git`

### Backend Enhancements

*   **User Quiz Progress:**
    *   **Migration:** `create_user_quiz_progress_table` with `user_id`, `section_id`, `score`, and `data` (JSON) fields.
    *   **Model:** `UserQuizProgress.php`.
    *   **Controller:** `UserQuizProgressController.php` with `store` (save progress) and `index` (get all progress) methods.
    *   **API Routes:** `/api/progress` (POST) and `/api/progress` (GET).
*   **Admin Functionality:**
    *   **User Model:** Added `is_admin` boolean column.
    *   **Controller:** `AdminController.php` with `users` method to fetch all users.
    *   **API Route:** `/api/admin/users` (GET), protected by the `admin` middleware.
    *   **Middleware:** The `admin` middleware is implemented in `app/Http/Middleware/AdminMiddleware.php` and registered in `bootstrap/app.php`.
*   **Authentication:**
    *   Laravel Breeze reinstalled with Blade views for login/register. Login redirects to `/`.
    *   An `/api/user` route has been added to fetch the authenticated user's data.
    *   **Laravel Sanctum Integration:** Implemented for robust SPA authentication, including `HasApiTokens` trait on `User` model, `EnsureFrontendRequestsAreStateful` middleware, and `SANCTUM_STATEFUL_DOMAINS` configuration.

### Frontend Enhancements

*   **Quiz History Display:** Enhanced `QuizHistory.vue` to display quiz details more comprehensively (questions, user answers, correct answers).
*   **Progress Dashboard:** Added `Dashboard.vue` to provide an overall progress overview.
*   **Centralized Quizzes:** Introduced `QuizMain.vue` as a central hub for quizzes, integrating `Restaurant.vue` (quantifiers quiz) and a new `MixedQuiz.vue` (mixed questions) within it. `DailyLife.vue` is now a dedicated practice area.
*   **Improved UI Responsiveness:** Optimized Vue transitions and re-enabled subtle GSAP animations for smoother tab switching.
*   **Quiz Localization:** Translated user-facing text in quiz components (`QuizMain.vue`, `Restaurant.vue`, `MixedQuiz.vue`) to Spanish for Spanish-speaking students.
