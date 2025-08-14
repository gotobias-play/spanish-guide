# Spanish Guide - Interactive English Learning App

This is a comprehensive web application designed to help Spanish speakers learn English through interactive exercises, quizzes, and gamified learning experiences.

🎉 **Latest Update (August 9, 2025):** Advanced Question Types with multimedia content, drag-and-drop, matching, and interactive learning!

## Features

### 📚 **Learning System**
*   **Interactive Lessons:** Engaging exercises for core English concepts (To Be/To Have, Present Simple, Prepositions, Quantifiers, Wh-questions)
*   **Database-Driven Quizzes:** Advanced quiz system with question randomization and multiple question types
*   **Course Organization:** 5 structured courses with lessons and comprehensive question banks
*   **Multiple Question Types:** Multiple choice and fill-in-the-blank questions with immediate feedback
*   **✅ NEW: Timed Quizzes:** Countdown timer challenges with speed bonus points
*   **✅ NEW: Advanced Question Types:** Image-based, audio-based, drag-and-drop, matching, and ordering questions

### 🎮 **Gamification System**
*   **Points System:** Earn 10-50 points based on quiz performance + ✅ NEW speed bonus points (1-10+ extra for fast answers)
*   **Achievement System:** 12+ achievements including First Steps, Quiz Master, Perfect Score, Study Streaks, and ✅ NEW time-based achievements
*   **Study Streaks:** Daily activity tracking with current and longest streak records
*   **Leaderboard:** Real-time ranking of top users by points and achievements
*   **Progress Dashboard:** Comprehensive view of points, achievements, streaks, and transaction history

### 👤 **User Management**
*   **Progress Tracking:** Automatic saving of quiz results and gamification progress
*   **User Authentication:** Secure login and registration with Laravel Sanctum
*   **Admin Panel:** Admin functionalities for user and content management
*   **Personal Dashboard:** Individual progress overview with detailed analytics
*   **✅ NEW: Enhanced Analytics:** Comprehensive insights dashboard with performance trends, subject mastery, learning recommendations, and goal tracking

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

## User Access Levels

### 🔓 **Guest Access (No Login Required)**
- **Static Learning Modules**: Foundations, Daily Life, City, Restaurant, Questions
- **Basic Content**: Interactive exercises and educational content without progress tracking

### 🔐 **Authenticated User Access (Login Required)**
- **All Guest Features** plus:
- **Database-Driven Quizzes**: Randomized questions with progress saving
- **Advanced Question Types**: Image, audio, drag-drop, matching, ordering
- **Timed Quizzes**: Speed challenges with bonus points
- **Gamification System**: Points, achievements, streaks, leaderboard
- **Enhanced Analytics**: Personal performance insights and recommendations
- **Progress Tracking**: Quiz history, results review, and learning analytics

### ⭐ **Admin User Access (Admin Login Required)**
- **All User Features** plus:
- **Admin Panel**: User management and system oversight
- **Content Management**: Course, lesson, quiz, and question creation

## Testing Credentials

*   **Regular User:**
    *   **Email:** `user@example.com`
    *   **Password:** `password`
*   **Admin User:**
    *   **Email:** `admin@example.com`
    *   **Password:** `password`

---

## 🎮 How to Use the Gamification System

### **Getting Started**
1. **Register or Login** using the test credentials above
2. **Navigate to "Práctica"** from the main menu
3. **Choose a course** (Grammar, Daily Life, City, Restaurant, or Questions)
4. **Complete quizzes** to earn points and unlock achievements

### **Earning Points**
- **Perfect Score (100%)**: 50 points 🌟
- **Good Score (80%+)**: 30 points 📈  
- **Passing Score (60%+)**: 20 points ✅
- **Participation**: 10 points 👍

### **Unlocking Achievements**
- 🌟 **First Steps**: Complete your first quiz
- 🎓 **Quiz Master**: Complete 5 quizzes
- 💯 **Perfect Score**: Get 100% on any quiz
- 🔥 **Streak Starter**: Study for 3 consecutive days
- 💪 **Dedicated Learner**: Study for 7 consecutive days
- 💎 **Point Collector**: Accumulate 100 points
- 📚 **Grammar Expert**: Complete all grammar quizzes
- 🌅 **Daily Life Pro**: Complete all daily life quizzes
- ✅ **NEW Timed Quiz Achievements**:
  - ⚡ **Speed Demon**: Complete a timed quiz with speed bonus
  - 🏃‍♂️ **Lightning Fast**: Earn 10+ speed bonus points in one quiz
  - ⏱️ **Time Master**: Complete 5 timed quizzes
  - 💥 **Under Pressure**: Get perfect score on a timed quiz

### **Tracking Your Progress**
- Click your name in the top-right corner
- Select **"📊 Mi Progreso"** to view:
  - Total points and quiz count
  - Current and longest study streaks
  - Achievement gallery with unlock dates
  - Points transaction history
  - Live leaderboard rankings

---

## ✅ Recent Updates - August 9, 2025

### **🎮 Phase 2 Gamification System - COMPLETE**
- **Points System**: Dynamic point calculation with instant rewards
- **8 Launch Achievements**: From first quiz to course completion milestones  
- **Study Streaks**: Daily activity tracking with streak-based achievements
- **Gamification Dashboard**: Comprehensive stats, achievement gallery, leaderboard
- **Real-time Notifications**: Celebration alerts for achievement unlocks
- **Status**: ✅ **Fully Operational**

### **Current Features (All Working)**
1. **Original Learning Modules**: Foundations, Daily Life, City, Restaurant, Questions - ✅ Functional
2. **Enhanced Práctica Section**: Database-driven quiz system with randomization - ✅ Functional  
3. **Complete Gamification**: Points, achievements, streaks, leaderboard - ✅ Functional
4. **✅ NEW: Timed Quiz System**: Countdown timers, speed bonuses, time-based achievements - ✅ Functional
5. **Progress Tracking**: Quiz history, points history, achievement gallery - ✅ Functional
6. **Admin Panel**: Content and user management - ✅ Functional

### **System Specifications**
- **Learning Content**: 5 courses, 6 lessons, 8+ quizzes (including 2 timed), 11+ questions, 20+ answer options
- **Gamification**: 12+ achievements (8 original + 4 timed), dynamic points with speed bonuses, daily streak tracking
- **Question Types**: Multiple choice, fill-in-the-blank with immediate feedback
- **✅ NEW Timed Features**: Countdown timers, speed bonuses (1-10+ points), time-based achievements
- **Features**: Question randomization, achievement notifications, real-time leaderboard, timer challenges
- **Languages**: Spanish interface with English learning content

### **Development Status**
- **Phase 1**: ✅ Complete - Enhanced database structure, quiz engine, API endpoints
- **Phase 2**: ✅ Complete - Gamification system with points, achievements, streaks
- **Phase 3.1**: ✅ Complete - Timed quiz system with countdown timers and speed bonuses
- **Phase 3.2**: ✅ Complete - Enhanced analytics dashboard with comprehensive insights and visualizations  
- **Phase 3.3**: ✅ Complete - Advanced question types with multimedia content and interactive elements
- **Next**: Phase 3.4+ - Course certificates, social features, adaptive difficulty

---

Ready to start learning and earning achievements? 🚀🎓
