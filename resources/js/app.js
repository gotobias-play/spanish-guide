import './bootstrap';
import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';

// Import i18n configuration
import i18n, { initializeLocale } from './i18n.js';

// Import critical components immediately (for initial page load)
import App from './components/App.vue';
import Home from './components/Home.vue';

// Lazy load all other components for better performance
const Foundations = () => import('./components/Foundations.vue');
const DailyLife = () => import('./components/DailyLife.vue');
const City = () => import('./components/City.vue');
const Restaurant = () => import('./components/Restaurant.vue');
const Questions = () => import('./components/Questions.vue');
const QuizHistory = () => import('./components/QuizHistory.vue');
const AdminPanel = () => import('./components/AdminPanel.vue');
const Dashboard = () => import('./components/Dashboard.vue');
const QuizMain = () => import('./components/QuizMain.vue');
const QuizSelector = () => import('./components/QuizSelector.vue');
const GamificationDashboard = () => import('./components/GamificationDashboard.vue');
const AnalyticsDashboard = () => import('./components/AnalyticsDashboard.vue');
const CertificateDashboard = () => import('./components/CertificateDashboard.vue');
const SocialDashboard = () => import('./components/SocialDashboard.vue');
const AdaptiveLearning = () => import('./components/AdaptiveLearning.vue');
const SpacedRepetition = () => import('./components/SpacedRepetition.vue');
const Chat = () => import('./components/Chat.vue');
const MultiplayerQuiz = () => import('./components/MultiplayerQuiz.vue');
const WritingPractice = () => import('./components/WritingPractice.vue');
const MistakeAnalysis = () => import('./components/MistakeAnalysis.vue');
const PronunciationPractice = () => import('./components/PronunciationPractice.vue');
const ListeningComprehension = () => import('./components/ListeningComprehension.vue');
const VideoLessons = () => import('./components/VideoLessons.vue');
const ConversationPractice = () => import('./components/ConversationPractice.vue');
const DemoI18n = () => import('./components/DemoI18n.vue');
const InstructorDashboard = () => import('./components/InstructorDashboard.vue');

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: Home },
    { path: '/foundations', component: Foundations },
    { path: '/daily-life', component: DailyLife },
    { path: '/city', component: City },
    { path: '/restaurant', component: Restaurant },
    { path: '/questions', component: Questions },
    { path: '/quiz-history', component: QuizHistory },
    { path: '/quizzes', component: QuizMain },
    { path: '/quiz-selector', component: QuizSelector },
    { path: '/admin', component: AdminPanel },
    { path: '/dashboard', component: Dashboard },
    { path: '/gamification', component: GamificationDashboard },
    { path: '/analytics', component: AnalyticsDashboard },
    { path: '/certificates', component: CertificateDashboard },
    { path: '/social', component: SocialDashboard },
    { path: '/adaptive-learning', component: AdaptiveLearning },
    { path: '/spaced-repetition', component: SpacedRepetition },
    { path: '/chat', component: Chat },
    { path: '/multiplayer', component: MultiplayerQuiz },
    { path: '/writing-practice', component: WritingPractice },
    { path: '/mistake-analysis', component: MistakeAnalysis },
    { path: '/pronunciation-practice', component: PronunciationPractice },
    { path: '/listening-comprehension', component: ListeningComprehension },
    { path: '/video-lessons', component: VideoLessons },
    { path: '/conversation-practice', component: ConversationPractice },
    { path: '/demo-i18n', component: DemoI18n },
    { path: '/instructor', component: InstructorDashboard },
  ],
});

// Initialize locale from stored preference or browser language
initializeLocale();

const app = createApp(App);
app.use(router);
app.use(i18n);
app.mount('#app');
