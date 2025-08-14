<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserQuizProgressController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\LessonController;
use App\Http\Controllers\Api\V1\QuizController;
use App\Http\Controllers\Api\V1\QuestionController;
use App\Http\Controllers\Api\V1\QuestionOptionController;
use App\Http\Controllers\Api\V1\PublicCourseController;
use App\Http\Controllers\Api\V1\PublicQuizController;
use App\Http\Controllers\Api\V1\PublicLessonController;
use App\Http\Controllers\Api\V1\GamificationController;
use App\Http\Controllers\Api\V1\AnalyticsController;
use App\Http\Controllers\Api\V1\CertificateController;
use App\Http\Controllers\Api\V1\SocialController;
use App\Http\Controllers\Api\V1\LearningController;
use App\Http\Controllers\Api\V1\ChatController;
use App\Http\Controllers\Api\V1\MultiplayerQuizController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\AIAnalysisController;
use App\Http\Controllers\Api\V1\IntelligentTutoringController;
use App\Http\Controllers\Api\V1\PerformanceTestController;
use App\Http\Controllers\Api\V1\TenantController;
use App\Http\Controllers\Api\V1\InstructorController;

Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::post('/progress', [UserQuizProgressController::class, 'store']);
    Route::get('/progress/{section_id}', [UserQuizProgressController::class, 'show']);
    Route::get('/progress', [UserQuizProgressController::class, 'index']);
    
    // Gamification routes
    Route::post('/gamification/quiz-points', [GamificationController::class, 'awardQuizPoints']);
    Route::get('/gamification/stats', [GamificationController::class, 'getUserStats']);
    Route::get('/gamification/achievements', [GamificationController::class, 'getUserAchievements']);
    Route::get('/gamification/points-history', [GamificationController::class, 'getUserPointsHistory']);
    
    // Analytics routes
    Route::get('/analytics', [AnalyticsController::class, 'getUserAnalytics']);
    Route::get('/analytics/performance', [AnalyticsController::class, 'getPerformanceTrends']);
    Route::get('/analytics/subjects', [AnalyticsController::class, 'getSubjectAnalysis']);
    Route::get('/analytics/insights', [AnalyticsController::class, 'getLearningInsights']);
    
    // Certificate routes
    Route::get('/certificates', [CertificateController::class, 'index']);
    Route::get('/certificates/{certificate}', [CertificateController::class, 'show']);
    Route::post('/certificates/check', [CertificateController::class, 'checkEligibility']);
    Route::post('/certificates/check-course/{course}', [CertificateController::class, 'checkCourse']);
    
    // Social routes
    Route::get('/social/dashboard', [SocialController::class, 'dashboard']);
    Route::get('/social/search', [SocialController::class, 'searchUsers']);
    Route::post('/social/friend-request', [SocialController::class, 'sendFriendRequest']);
    Route::post('/social/friend-request/{friendship}/respond', [SocialController::class, 'respondToFriendRequest']);
    Route::delete('/social/friend', [SocialController::class, 'removeFriend']);
    Route::get('/social/leaderboard', [SocialController::class, 'friendLeaderboard']);
    
    // Adaptive Learning routes
    Route::get('/learning/recommendations', [LearningController::class, 'getRecommendations']);
    Route::get('/learning/skill-levels', [LearningController::class, 'getSkillLevels']);
    Route::post('/learning/update-skill', [LearningController::class, 'updateSkillLevel']);
    Route::post('/learning/recommendations/{recommendation}/viewed', [LearningController::class, 'markRecommendationAsViewed']);
    Route::post('/learning/recommendations/{recommendation}/completed', [LearningController::class, 'markRecommendationAsCompleted']);
    Route::delete('/learning/recommendations/{recommendation}', [LearningController::class, 'dismissRecommendation']);
    Route::get('/learning/spaced-repetition', [LearningController::class, 'getSpacedRepetitionSchedule']);
    Route::get('/learning/insights', [LearningController::class, 'getLearningInsights']);
    
    // Real-time Chat routes
    Route::post('/chat/send', [ChatController::class, 'sendMessage']);
    Route::get('/chat/conversations', [ChatController::class, 'getConversations']);
    Route::get('/chat/conversation/{user}', [ChatController::class, 'getConversation']);
    Route::get('/chat/room/{roomId}', [ChatController::class, 'getRoomMessages']);
    Route::post('/chat/read/{message}', [ChatController::class, 'markAsRead']);
    Route::get('/chat/unread-count', [ChatController::class, 'getUnreadCount']);
    Route::post('/chat/online-status', [ChatController::class, 'updateOnlineStatus']);
    Route::post('/chat/join-room', [ChatController::class, 'joinRoom']);
    Route::post('/chat/leave-room', [ChatController::class, 'leaveRoom']);

    // Multiplayer Quiz routes
    Route::get('/multiplayer/rooms', [MultiplayerQuizController::class, 'getRoomList']);
    Route::post('/multiplayer/rooms', [MultiplayerQuizController::class, 'createRoom']);
    Route::post('/multiplayer/join', [MultiplayerQuizController::class, 'joinRoom']);
    Route::get('/multiplayer/rooms/{room}', [MultiplayerQuizController::class, 'getRoomDetails']);
    Route::post('/multiplayer/rooms/{room}/ready', [MultiplayerQuizController::class, 'setReady']);
    Route::post('/multiplayer/rooms/{room}/start', [MultiplayerQuizController::class, 'startQuiz']);
    Route::post('/multiplayer/rooms/{room}/answer', [MultiplayerQuizController::class, 'submitAnswer']);
    Route::get('/multiplayer/rooms/{room}/leaderboard', [MultiplayerQuizController::class, 'getLeaderboard']);
    Route::delete('/multiplayer/rooms/{room}/leave', [MultiplayerQuizController::class, 'leaveRoom']);

    // Push Notification routes
    Route::post('/notifications/subscribe', [NotificationController::class, 'subscribe']);
    Route::delete('/notifications/unsubscribe', [NotificationController::class, 'unsubscribe']);
    Route::post('/notifications/test', [NotificationController::class, 'sendTest']);
    Route::get('/notifications/preferences', [NotificationController::class, 'getPreferences']);
    Route::put('/notifications/preferences', [NotificationController::class, 'updatePreferences']);

    // AI Analysis routes
    Route::post('/ai/analyze-writing', [AIAnalysisController::class, 'analyzeWriting']);
    Route::post('/ai/learning-recommendations', [AIAnalysisController::class, 'getLearningRecommendations']);
    Route::get('/ai/comprehensive-insights', [AIAnalysisController::class, 'getComprehensiveInsights']);
    Route::get('/ai/performance-predictions', [AIAnalysisController::class, 'getPerformancePredictions']);
    Route::get('/ai/skill-gap-analysis', [AIAnalysisController::class, 'getSkillGapAnalysis']);

    // Intelligent Tutoring routes
    Route::get('/tutoring/learning-path', [IntelligentTutoringController::class, 'generateLearningPath']);
    Route::post('/tutoring/start-session', [IntelligentTutoringController::class, 'startTutoringSession']);
    Route::get('/tutoring/learning-style', [IntelligentTutoringController::class, 'analyzeLearningStyle']);
    Route::get('/tutoring/adaptive-exercises', [IntelligentTutoringController::class, 'getAdaptiveExercises']);
    Route::post('/tutoring/update-progress', [IntelligentTutoringController::class, 'updateSessionProgress']);

    // Performance Testing routes
    Route::get('/performance/tests', [PerformanceTestController::class, 'getAvailableTests']);
    Route::post('/performance/comprehensive', [PerformanceTestController::class, 'runComprehensiveTests']);
    Route::get('/performance/summary', [PerformanceTestController::class, 'getTestSummary']);
    Route::post('/performance/test/{testCategory}', [PerformanceTestController::class, 'runSpecificTest']);

    // Tenant Management routes
    Route::get('/tenant/current', [TenantController::class, 'current']);
    Route::get('/tenant/stats', [TenantController::class, 'stats']);
    Route::put('/tenant/settings', [TenantController::class, 'updateSettings']);
    Route::get('/tenant/feature/{feature}', [TenantController::class, 'checkFeature']);

    // Instructor Management routes
    Route::post('/instructor/roles', [InstructorController::class, 'createInstructorRole']);
    Route::get('/instructor/roles', [InstructorController::class, 'getInstructorRoles']);
    Route::get('/instructor/dashboard', [InstructorController::class, 'getInstructorDashboard']);
    
    // Class Management routes
    Route::post('/instructor/classes', [InstructorController::class, 'createClass']);
    Route::get('/instructor/classes', [InstructorController::class, 'getInstructorClasses']);
    Route::get('/instructor/classes/{class}', [InstructorController::class, 'getClassDetails']);
    Route::post('/instructor/classes/{class}/enroll', [InstructorController::class, 'enrollStudent']);
    
    // Assignment Management routes
    Route::post('/instructor/assignments', [InstructorController::class, 'createAssignment']);
    Route::get('/instructor/classes/{class}/assignments', [InstructorController::class, 'getClassAssignments']);
    Route::get('/instructor/assignments/{assignment}/submissions', [InstructorController::class, 'getAssignmentSubmissions']);
    Route::post('/instructor/assignments/{assignment}/submissions/{submission}/grade', [InstructorController::class, 'gradeSubmission']);

    Route::middleware('admin')->group(function () {
        Route::get('/admin/users', [AdminController::class, 'users']);
        Route::apiResource('/courses', CourseController::class);
        Route::apiResource('/lessons', LessonController::class);
        Route::apiResource('/quizzes', QuizController::class);
        Route::apiResource('/questions', QuestionController::class);
        Route::apiResource('/question-options', QuestionOptionController::class);
    });
});

// Public routes with more generous rate limiting
Route::middleware('throttle:200,1')->group(function () {
    Route::apiResource('/public/courses', PublicCourseController::class)->only(['index', 'show']);
    Route::apiResource('/public/quizzes', PublicQuizController::class)->only(['index', 'show']);
    Route::apiResource('/public/lessons', PublicLessonController::class)->only(['index', 'show']);

    // Public gamification routes
    Route::get('/public/achievements', [GamificationController::class, 'getAllAchievements']);
    Route::get('/public/leaderboard', [GamificationController::class, 'getLeaderboard']);

    // Public tenant routes
    Route::get('/public/tenant/branding', [TenantController::class, 'branding']);
    Route::post('/public/tenant/validate-subdomain', [TenantController::class, 'validateSubdomain']);
    Route::post('/public/tenant/create', [TenantController::class, 'store']); // Super admin only
});