# Troubleshooting Guide: Blank Screen and Authentication Issues

This document outlines the common issues encountered during the development setup of the Spanish Guide application, specifically focusing on a blank screen on initial load and persistent login/register buttons even after authentication.

## Problem Description

Users reported two primary issues:
1.  **Blank Screen on Website Load:** The application's main page (`http://spanish-guide.test`) appeared blank.
2.  **Authentication UI Mismatch:** Even after successfully logging in, the navigation bar continued to display "Login" and "Register" buttons instead of the authenticated user's menu (Account Settings, Quiz History, Dashboard, Logout). Additionally, guest users were initially forced to log in.

## Root Causes and Solutions

The issues stemmed from a combination of frontend build problems, misconfigured environment variables, and incorrect Laravel API authentication setup for a Single-Page Application (SPA).

### 1. Blank Screen: Vite Manifest Not Found

*   **Symptom:** Laravel logs showed `Vite manifest not found at: .../public/build/manifest.json`.
*   **Cause:** The frontend assets (JavaScript and CSS) were not being compiled and served by Vite. This can happen if `npm install` or `npm run dev` haven't been executed, or if there are dependency conflicts.
*   **Solution Steps:**
    1.  **Dependency Resolution:**
        *   Deleted `spanish-guide/package-lock.json` and `spanish-guide/node_modules` to clear corrupted dependencies.
        *   Ran `npm install --legacy-peer-deps` within the `spanish-guide` directory to resolve peer dependency conflicts.
    2.  **Vite Development Server:**
        *   Started the Vite development server using `start "Vite Dev Server" cmd /c "cd spanish-guide && npm run dev"` (for Windows) in a separate terminal window. This command ensures Vite compiles and serves the frontend assets.

### 2. Authentication UI Mismatch & API 404s

This was a multi-faceted problem involving several misconfigurations:

*   **Symptom 1:** Login/Register buttons persisted after login; authenticated user menu did not appear.
*   **Symptom 2:** Browser console showed `404 Not Found` for `/api/user` requests, with the message "The route api/user could not be found."
*   **Symptom 3:** Initially, all users (including guests) were forced to log in.

*   **Causes & Solutions:**

    *   **Incorrect Axios `withCredentials`:**
        *   **Cause:** The frontend (Axios) was not sending cookies with cross-origin requests, preventing Laravel from recognizing the authenticated session.
        *   **Solution:** Added `window.axios.defaults.withCredentials = true;` to `resources/js/bootstrap.js`.

    *   **Missing CORS Configuration:**
        *   **Cause:** Laravel's default CORS policy did not explicitly allow requests from the Vite development server, especially with credentials.
        *   **Solution:**
            *   Created `config/cors.php` with a permissive configuration, allowing all methods, headers, and supporting credentials, and setting `allowed_origins` to `env('FRONTEND_URL')`.
            *   Added `\Illuminate\Http\Middleware\HandleCors::class` to the `web` middleware group in `bootstrap/app.php` using `$middleware->prependToGroup('web', ...);`.

    *   **`APP_URL` / `FRONTEND_URL` / `SANCTUM_STATEFUL_DOMAINS` Mismatch:**
        *   **Cause:** Inconsistencies between the Laravel application URL (`APP_URL`), the frontend development server URL (`FRONTEND_URL`), and the domains Sanctum considers "stateful" (`SANCTUM_STATEFUL_DOMAINS`) prevented proper cookie handling and session recognition.
        *   **Solution:**
            *   Ensured `APP_URL` in `.env` was set to the actual domain (e.g., `http://spanish-guide.test`).
            *   Set `FRONTEND_URL` in `.env` to match the Vite development server's URL (e.g., `http://spanish-guide.test`).
            *   Configured `SANCTUM_STATEFUL_DOMAINS` in `.env` to include both the Vite development server's address and the application's domain (e.g., `SANCTUM_STATEFUL_DOMAINS=localhost:5173,spanish-guide.test`).
            *   Configured Vite to serve from the correct host by adding `server: { host: 'spanish-guide.test' }` to `vite.config.js`.

    *   **Laravel Sanctum Middleware Not Applied:**
        *   **Cause:** The `EnsureFrontendRequestsAreStateful` middleware, crucial for Sanctum's SPA authentication, was not correctly applied to the API routes.
        *   **Solution:** Added `\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class` to the `api` middleware group in `bootstrap/app.php` using `$middleware->api(prepend: [...]);`.

    *   **`User` Model Missing `HasApiTokens` Trait:**
        *   **Cause:** The `App\Models\User` model did not include the `HasApiTokens` trait, which is necessary for Laravel Sanctum to issue and manage API tokens.
        *   **Solution:** Added `use Laravel\Sanctum\HasApiTokens;` and `use HasFactory, Notifiable, HasApiTokens;` to `app/Models/User.php`.

    *   **API Routes Not Loaded:**
        *   **Cause:** The `routes/api.php` file was not explicitly included in Laravel's route loading process in `bootstrap/app.php`.
        *   **Solution:** Added `api: __DIR__.'/../routes/api.php',` to the `withRouting` method in `bootstrap/app.php`.

    *   **Stale Laravel Caches:**
        *   **Cause:** Laravel caches (routes, configuration) can sometimes hold old definitions, preventing new changes from taking effect.
        *   **Solution:** Regularly cleared caches using `php artisan route:clear` and `php artisan config:clear`.

    *   **Forced Guest Login:**
        *   **Cause:** The `auth` middleware was mistakenly applied to the root route (`/`) in `routes/web.php`, preventing unauthenticated users from accessing the main page.
        *   **Solution:** Removed the `->middleware('auth')` from the root route definition in `routes/web.php`.

### 3. UI Responsiveness / Tab Switching Delay

*   **Symptom:** A noticeable delay when switching between navigation tabs, making the application feel sluggish.
*   **Cause:**
    *   The primary cause was the `transition` duration set on the Vue `transition` component in `resources/js/components/App.vue`, which was `0.5s`. This caused a half-second fade-out/fade-in effect on every tab switch.
    *   Secondary cause was the `gsap.from(this.$el, ...)` animations in the `mounted()` hooks of individual components, which added another `0.5s` animation on top of the Vue transition.
*   **Solution:**
    1.  **Reduced Vue Transition Duration:** Changed `transition: opacity 0.5s;` to `transition: opacity 0.1s;` in `resources/js/components/App.vue`'s `<style>` section. This significantly sped up the overall page transitions.
    2.  **Re-enabled GSAP Animations (Optional):** After addressing the primary delay, the `gsap.from(this.$el, ...)` animations were re-added to the `mounted()` hooks of components (`Foundations.vue`, `DailyLife.vue`, `City.vue`, `Restaurant.vue`, `Questions.vue`, `Home.vue`). While these add a subtle animation, the overall responsiveness is maintained due to the faster Vue transition.

## Final Steps After Troubleshooting

After applying all the solutions, ensure the following:

1.  **Laravel Development Server:** If not using Herd, ensure `php artisan serve` is running in a separate terminal. (Note: With Herd, this is usually not necessary as Herd handles serving the Laravel app).
2.  **Vite Development Server:** Ensure `npm run dev` is running in a separate terminal.
3.  **Browser Cache:** Always clear your browser's cache and cookies (or use incognito mode) after making significant configuration changes, especially related to authentication.
4.  **Access URL:** Access the application using the exact `APP_URL` defined in your `.env` (e.g., `http://spanish-guide.test`).

By addressing these points systematically, the application should now correctly display the frontend, handle authentication, and show the appropriate user interface elements.