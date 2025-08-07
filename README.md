# Spanish Guide - Interactive English Learning App

This is a web application designed to help Spanish speakers learn English through a series of interactive exercises and guides.

## Features

*   **Interactive Lessons:** Engaging exercises for core English concepts.
*   **Centralized Quizzes:** Dedicated section for various quizzes, including mixed questions.
*   **Progress Tracking:** Dashboard to view overall progress and quiz history.
*   **User Authentication:** Secure login and registration with admin functionalities.

## Tech Stack

*   **Backend:** Laravel
*   **Frontend:** Vue.js with Vue Router
*   **Styling:** Tailwind CSS
*   **Build Tool:** Vite
*   **Animations:** GSAP
*   **Authentication:** Laravel Sanctum

## Project Setup

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/gotobias-play/spanish-guide.git
    cd spanish-guide
    ```

2.  **Install PHP dependencies:**
    ```bash
    composer install
    ```

3.  **Install JavaScript dependencies (resolve peer dependency issues):**
    ```bash
    npm install --legacy-peer-deps
    ```

4.  **Environment Configuration:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    *   **Update `.env`:**
        Ensure `APP_URL` matches your Herd domain (e.g., `http://spanish-guide.test`).
        Add/update `FRONTEND_URL` to match your Vite development server (e.g., `FRONTEND_URL=http://spanish-guide.test`).
        Add/update `SANCTUM_STATEFUL_DOMAINS` to include both your Vite server and app domain (e.g., `SANCTUM_STATEFUL_DOMAINS=localhost:5173,spanish-guide.test`).

5.  **Configure Vite Host:**
    In `vite.config.js`, add `host: 'spanish-guide.test',` to the `server` object:
    ```javascript
    export default defineConfig({
        server: {
            host: 'spanish-guide.test',
        },
        plugins: [
            // ...
        ],
    });
    ```

6.  **Install and Configure Laravel Sanctum:**
    ```bash
    php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
    php artisan migrate
    ```
    *   **Update `app/Models/User.php`:** Add `use Laravel\Sanctum\HasApiTokens;` and include `HasApiTokens` in the `use` statement for the `User` model.
    *   **Update `bootstrap/app.php`:**
        *   Ensure API routes are loaded: Add `api: __DIR__.'/../routes/api.php',` to the `withRouting` method.
        *   Add Sanctum middleware: Prepend `\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class` to the `api` middleware group.
        *   Add CORS middleware: Prepend `\Illuminate\Http\Middleware\HandleCors::class` to the `web` middleware group.

7.  **Clear Laravel Caches:**
    ```bash
    php artisan route:clear
    php artisan config:clear
    ```

8.  **Run the development server (Vite):**
    ```bash
    start "Vite Dev Server" cmd /c "cd spanish-guide && npm run dev"
    ```
    (This command opens a new terminal window and runs the Vite server. Do not close it.)

9.  Open your browser and navigate to your `APP_URL` (e.g., `http://spanish-guide.test`).

## Testing Credentials

*   **Regular User:**
    *   **Email:** `user@example.com`
    *   **Password:** `password`
*   **Admin User:**
    *   **Email:** `admin@example.com`
    *   **Password:** `password`
