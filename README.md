# Spanish Guide - Interactive English Learning App

This is a web application designed to help Spanish speakers learn English through a series of interactive exercises and guides.

## Tech Stack

*   **Backend:** Laravel
*   **Frontend:** Vue.js with Vue Router
*   **Styling:** Tailwind CSS
*   **Build Tool:** Vite
*   **Animations:** GSAP

## Project Setup

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/gotobias-play/spanish-guide.git
    cd spanish-guide
    ```

2.  **Install dependencies:**
    ```bash
    composer install
    npm install
    ```

3.  **Environment Configuration:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Run the development servers:**
    ```bash
    # Start the Vite server for frontend assets
    npm run dev

    # In a separate terminal, start the Laravel server
    php artisan serve
    ```

5.  Open your browser and navigate to the URL provided by `php artisan serve`.