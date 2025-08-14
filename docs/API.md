# Spanish English Learning App - API Documentation

## Overview
This document contains comprehensive API endpoint documentation and database schema information for the Spanish English Learning App platform.

## Base API Structure
- **Base URL**: `/api/`
- **Authentication**: Laravel Sanctum (SPA authentication)
- **Content Type**: `application/json`
- **Rate Limiting**: 120 requests/minute (authenticated), 200 requests/minute (public)

## API Endpoint Categories

### Core User & Authentication
- `GET /api/user` - Current user data
- `POST /api/progress` - Quiz progress management
- `GET /api/progress` - Retrieve user progress

### Public Content APIs
- `GET /api/public/courses` - Public course listing with lessons
- `GET /api/public/quizzes` - Public quiz listing with questions and options
- `GET /api/public/quizzes/{id}` - Individual quiz with full question data
- `GET /api/public/lessons` - Public lesson access
- `GET /api/public/achievements` - All available achievements
- `GET /api/public/leaderboard` - Top users by points
- `GET /api/public/tenant/branding` - Public tenant branding information
- `POST /api/public/tenant/validate-subdomain` - Subdomain validation
- `POST /api/public/tenant/create` - Create new tenant (super admin)

### Admin Management APIs
- `GET /api/admin/users` - Admin user list (admin only)
- Admin CRUD operations for `/api/courses`, `/api/lessons`, `/api/quizzes`, `/api/questions`

### Gamification System APIs
- `POST /api/gamification/quiz-points` - Award points for quiz completion
- `GET /api/gamification/stats` - User gamification statistics
- `GET /api/gamification/achievements` - User earned achievements
- `GET /api/gamification/points-history` - Points transaction history

### Analytics & Performance APIs
- `GET /api/analytics` - Complete user analytics with all categories
- `GET /api/analytics/performance` - Performance trends and basic statistics
- `GET /api/analytics/subjects` - Subject analysis and mastery levels
- `GET /api/analytics/insights` - Learning insights and suggested goals

### Adaptive Learning APIs
- `GET /api/learning/recommendations` - Personalized learning recommendations
- `GET /api/learning/skill-levels` - User skill tracking and mastery statistics
- `POST /api/learning/update-skill` - Update skill levels after quiz completion
- `POST /api/learning/recommendations/{id}/viewed` - Mark recommendations as viewed
- `POST /api/learning/recommendations/{id}/completed` - Complete recommendations
- `DELETE /api/learning/recommendations/{id}` - Dismiss recommendations
- `GET /api/learning/spaced-repetition` - Spaced repetition schedule
- `GET /api/learning/insights` - Advanced learning analytics and predictions

### Certificate Management APIs
- `GET /api/certificates` - Get all user certificates with statistics
- `GET /api/certificates/{id}` - Get specific certificate with full details
- `POST /api/certificates/check` - Check for new certificate eligibility
- `POST /api/certificates/check-course/{course}` - Validate specific course completion

### Social Learning APIs
- `GET /api/social/dashboard` - Complete social data overview
- `GET /api/social/search` - User search functionality with friendship status
- `POST /api/social/friend-request` - Send friend requests
- `POST /api/social/friend-request/{id}/respond` - Accept or decline friend requests
- `DELETE /api/social/friend` - Remove friendships
- `GET /api/social/leaderboard` - Friend performance rankings

### Real-Time Communication APIs
- `POST /api/chat/send` - Send messages (direct, room, system types)
- `GET /api/chat/conversations` - Retrieve user conversations
- `GET /api/chat/conversation/{user}` - Load conversation history
- `GET /api/chat/room/{roomId}` - Fetch room messages
- `POST /api/chat/read/{message}` - Mark messages as read
- `GET /api/chat/unread-count` - Get total unread message count
- `POST /api/chat/online-status` - Update user online/offline status
- `POST /api/chat/join-room` - Join chat rooms
- `POST /api/chat/leave-room` - Leave chat rooms

### Multiplayer Competition APIs
- `GET /api/multiplayer/rooms` - Browse available public competition rooms
- `POST /api/multiplayer/rooms` - Create new competition rooms
- `POST /api/multiplayer/join` - Join rooms using room codes
- `GET /api/multiplayer/rooms/{room}` - Get detailed room information
- `POST /api/multiplayer/rooms/{room}/ready` - Mark participant as ready
- `POST /api/multiplayer/rooms/{room}/start` - Start competition (host only)
- `POST /api/multiplayer/rooms/{room}/answer` - Submit answers during competition
- `GET /api/multiplayer/rooms/{room}/leaderboard` - Get live/final leaderboard
- `DELETE /api/multiplayer/rooms/{room}/leave` - Leave competition room

### AI Analysis & Tutoring APIs
- `POST /api/ai/analyze-writing` - AI-powered writing analysis with CEFR estimation
- `POST /api/ai/learning-recommendations` - AI learning recommendations and study plans
- `GET /api/ai/comprehensive-insights` - Complete AI insights across 10 categories
- `GET /api/ai/performance-predictions` - Performance forecasting and trajectory analysis
- `GET /api/ai/skill-gap-analysis` - Advanced skill gap identification

### Intelligent Tutoring APIs
- `GET /api/tutoring/learning-path` - Personalized learning path generation
- `POST /api/tutoring/start-session` - Start adaptive tutoring sessions
- `GET /api/tutoring/learning-style` - Learning style analysis and detection
- `GET /api/tutoring/adaptive-exercises` - Personalized exercise recommendations
- `POST /api/tutoring/update-progress` - Session progress tracking and adaptation

### Multi-Tenant Management APIs
- `GET /api/tenant/current` - Current tenant information
- `GET /api/tenant/stats` - Tenant statistics (admin only)
- `PUT /api/tenant/settings` - Update tenant settings (admin only)
- `GET /api/tenant/feature/{name}` - Check feature availability

### Instructor System APIs
- `POST /api/instructor/roles` - Create instructor roles
- `GET /api/instructor/roles` - Get instructor roles
- `POST /api/instructor/classes` - Create classes
- `GET /api/instructor/classes` - Get instructor classes
- `GET /api/instructor/classes/{class}` - Get specific class details
- `POST /api/instructor/assignments` - Create assignments
- `GET /api/instructor/classes/{class}/assignments` - Get class assignments
- `GET /api/instructor/assignments/{assignment}/submissions` - Get assignment submissions
- `POST /api/instructor/assignments/{assignment}/submissions/{submission}/grade` - Grade submissions
- `POST /api/instructor/classes/{class}/enroll` - Enroll students in class
- `GET /api/instructor/dashboard` - Instructor dashboard analytics

### Performance Testing APIs
- `GET /api/performance/tests` - Get available test categories
- `POST /api/performance/comprehensive` - Run complete performance test suite
- `GET /api/performance/summary` - Get performance test results summary
- `POST /api/performance/test/{category}` - Run specific performance test category

### Notification System APIs
- `POST /api/notifications/subscribe` - Subscribe to push notifications
- `DELETE /api/notifications/unsubscribe` - Unsubscribe from push notifications
- `POST /api/notifications/test` - Send test notification
- `PUT /api/notifications/preferences` - Update notification preferences
- `POST /api/notifications/send` - Send notification to user

## Database Schema

### Core User & Authentication Tables

#### `users`
- Standard Laravel user fields
- `is_admin` boolean for admin privileges
- `last_seen_at` timestamp for presence tracking
- `push_subscription` JSON for push notification data
- `notification_preferences` JSON for notification settings
- Uses `HasApiTokens` trait for Sanctum

#### `user_quiz_progress`
- `user_id` (foreign key)
- `section_id` (string, e.g., 'daily-life', 'restaurant')
- `score` (integer, percentage score)
- `data` (JSON field for detailed results)
- Unique constraint on user_id + section_id

### Content Management Tables

#### `courses`
- `id`, `title`, `description`, `is_published`, `timestamps`

#### `lessons`
- `id`, `course_id`, `title`, `content`, `order`, `is_published`
- `interactive_data` (JSON), `timestamps`

#### `quizzes`
- `id`, `lesson_id`, `title`, `timestamps`
- Timer fields: `is_timed`, `time_limit_seconds`, `time_per_question_seconds`, `show_timer`, `timer_settings` (JSON)

#### `questions`
- `id`, `quiz_id`, `question_text`, `question_type`
- Advanced fields: `image_url`, `audio_url`, `question_config` (JSON), `explanation`, `difficulty_level`
- `timestamps`

#### `question_options`
- `id`, `question_id`, `option_text`, `is_correct`
- Advanced fields: `image_url`, `position`, `option_group`, `option_config` (JSON)
- `timestamps`

### Gamification System Tables

#### `user_points`
- `id`, `user_id`, `points`, `reason`, `quiz_id`, `earned_at`, `timestamps`

#### `achievements`
- `id`, `name`, `description`, `badge_icon`, `badge_color`, `points_required`
- `condition_type`, `condition_value` (JSON), `is_active`, `timestamps`

#### `user_achievements`
- `id`, `user_id`, `achievement_id`, `earned_at`, `progress` (JSON), `timestamps`

#### `user_streaks`
- `id`, `user_id`, `current_streak`, `longest_streak`, `last_activity_date`, `timestamps`

### Certificate System Tables

#### `certificates`
- `id`, `user_id`, `course_id`, `certificate_code`, `certificate_data` (JSON)
- `average_score`, `total_points`, `completion_time_hours`, `grade_level`
- `issued_at`, `timestamps`

### Adaptive Learning Tables

#### `user_skill_levels`
- `id`, `user_id`, `skill_category`, `skill_topic`, `difficulty_level`, `mastery_score`
- `correct_answers`, `total_attempts`, `accuracy_rate`, `last_practiced_at`, `consecutive_correct`
- `performance_history` (JSON), `timestamps`

#### `learning_recommendations`
- `id`, `user_id`, `recommendation_type`, `title`, `description`, `action_type`
- `action_data` (JSON), `priority`, `confidence_score`, `is_dismissed`, `is_completed`
- `expires_at`, `viewed_at`, `completed_at`, `timestamps`

### Social Learning Tables

#### `friendships`
- `id`, `user_id`, `friend_id`, `status`, `requested_at`, `accepted_at`
- `metadata` (JSON), `timestamps`

#### `social_activities`
- `id`, `user_id`, `activity_type`, `activity_data` (JSON), `is_public`
- `created_at`, `timestamps`

#### `challenges`
- `id`, `challenger_id`, `challenged_id`, `challenge_type`, `challenge_data` (JSON)
- `status`, `score_challenger`, `score_challenged`, `winner_id`
- `started_at`, `completed_at`, `expires_at`, `metadata` (JSON), `timestamps`

### Real-Time Communication Tables

#### `messages`
- `id`, `sender_id`, `receiver_id`, `room_id`, `message_type`, `message`
- `metadata` (JSON), `is_read`, `read_at`, `timestamps`

### Multiplayer Competition Tables

#### `quiz_rooms`
- `id`, `room_code`, `name`, `quiz_id`, `host_id`, `max_participants`, `status`
- `current_question`, `question_started_at`, `question_time_limit`, `is_public`
- `room_settings` (JSON), `started_at`, `ended_at`, `timestamps`

#### `quiz_room_participants`
- `id`, `quiz_room_id`, `user_id`, `total_score`, `correct_answers`, `total_questions`
- `position`, `average_response_time`, `speed_bonus`, `status`
- `joined_at`, `finished_at`, `performance_data` (JSON), `timestamps`

#### `quiz_room_answers`
- `id`, `quiz_room_id`, `participant_id`, `question_id`, `question_number`, `answer`
- `is_correct`, `points_earned`, `speed_bonus`, `response_time`, `answered_at`
- `answer_data` (JSON), `timestamps`

### Multi-Tenant Architecture Tables

#### `tenants`
- `id`, `name`, `subdomain`, `domain`, `database_name`, `plan`, `status`
- `settings` (JSON), `subscription_data` (JSON), `custom_branding` (JSON)
- `expires_at`, `created_at`, `updated_at`

### Instructor System Tables

#### `instructor_roles`
- `id`, `user_id`, `tenant_id`, `role_type`, `permissions` (JSON)
- `department`, `appointed_at`, `expires_at`, `is_active`, `timestamps`

#### `classes` (ClassModel)
- `id`, `instructor_id`, `tenant_id`, `name`, `description`, `class_code`
- `capacity`, `start_date`, `end_date`, `is_active`, `class_settings` (JSON), `timestamps`

#### `class_enrollments`
- `id`, `class_id`, `user_id`, `enrolled_at`, `status`, `enrollment_data` (JSON), `timestamps`

#### `assignments`
- `id`, `instructor_id`, `class_id`, `tenant_id`, `title`, `description`, `assignment_type`
- `content` (JSON), `due_date`, `points_possible`, `is_published`, `timestamps`

#### `assignment_submissions`
- `id`, `assignment_id`, `user_id`, `content` (JSON), `submitted_at`, `status`
- `late_penalty`, `submission_data` (JSON), `timestamps`

#### `grades`
- `id`, `assignment_submission_id`, `instructor_id`, `points_earned`, `percentage`
- `letter_grade`, `feedback`, `rubric_data` (JSON), `graded_at`, `is_published`, `timestamps`

## Response Formats

### Standard Success Response
```json
{
  "success": true,
  "data": {...},
  "message": "Operation completed successfully"
}
```

### Standard Error Response
```json
{
  "success": false,
  "error": "Error message",
  "code": "ERROR_CODE",
  "details": {...}
}
```

### Paginated Response
```json
{
  "success": true,
  "data": [...],
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 15,
    "total": 150
  }
}
```

## Authentication & Authorization

### Sanctum SPA Authentication
- Uses Laravel Sanctum for SPA-style authentication
- CSRF protection enabled for web routes
- Token-based authentication for API routes

### Permission Levels
- **Guest**: Access to public content APIs only
- **User**: Full access to learning features, progress tracking, social features
- **Instructor**: Additional access to instructor system APIs
- **Admin**: Full system administration access
- **Super Admin**: Multi-tenant management capabilities

### Rate Limiting
- **Authenticated Users**: 120 requests per minute
- **Public Endpoints**: 200 requests per minute
- **Throttling**: Laravel's built-in throttling middleware with IP-based tracking

## Performance Optimization

### Caching Strategy
- API response caching with 1-hour TTL for static content
- Database cache driver for persistence
- Intelligent cache keys with automatic invalidation
- 60% improvement in response times for cached endpoints

### Database Optimization
- 25+ strategic indexes across all major tables
- Optimized queries with proper relationship loading
- Performance-focused indexes for real-time operations

## Security Features

### Data Protection
- Multi-tenant data isolation with automatic scoping
- Role-based access control with granular permissions
- Input validation and sanitization on all endpoints
- SQL injection and XSS protection

### API Security
- Rate limiting and abuse prevention
- CORS configuration for cross-origin requests
- Secure headers and content type validation
- Authentication token management and rotation

## Integration Guidelines

### Frontend Integration
- Axios HTTP client with interceptors for authentication
- Error handling with user-friendly messages
- Loading states and optimistic UI updates
- Real-time updates using polling or WebSocket integration

### External System Integration
- RESTful API design following standard conventions
- Comprehensive documentation with example requests/responses
- Webhook support for real-time notifications
- API versioning strategy for backward compatibility

This API documentation provides comprehensive coverage of all 50+ endpoints and database schema supporting the enterprise-grade Spanish English Learning App platform.