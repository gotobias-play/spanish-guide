import './bootstrap';
import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';

import App from './components/App.vue';
import Home from './components/Home.vue';
import Foundations from './components/Foundations.vue';
import DailyLife from './components/DailyLife.vue';
import City from './components/City.vue';
import Restaurant from './components/Restaurant.vue';
import Questions from './components/Questions.vue';
import QuizHistory from './components/QuizHistory.vue';
import AdminPanel from './components/AdminPanel.vue';

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
    { path: '/admin', component: AdminPanel },
  ],
});

const app = createApp(App);
app.use(router);
app.mount('#app');
