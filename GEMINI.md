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
    *   `App.vue`: The root component containing the navigation and router view.
    *   `Home.vue`: The welcome page.
    *   `Foundations.vue`: Interactive exercises for "To Be" and "To Have".
    *   `DailyLife.vue`: The sentence builder for the present simple tense.
    *   `City.vue`: The interactive map and preposition exercises.
    *   `Restaurant.vue`: The quantifiers quiz and food vocabulary flip cards.
    *   `Questions.vue`: The interactive "Wh" questions guide.
    *   `Modal.vue`: A reusable modal component (though it was refactored out of the `Questions` component).
*   **CSS:** `resources/css/app.css` (Contains all the custom Tailwind CSS component classes.)
*   **Git Repository:** `https://github.com/gotobias-play/spanish-guide.git`
