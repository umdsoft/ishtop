/**
 * Vue Router - IshTop Recruiter Panel
 */

import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

// Layouts
import DashboardLayout from '../layouts/DashboardLayout.vue';
import AuthLayout from '../layouts/AuthLayout.vue';
import LandingLayout from '../layouts/LandingLayout.vue';

const routes = [
  // Landing Page
  {
    path: '/',
    component: LandingLayout,
    children: [
      {
        path: '',
        name: 'landing',
        component: () => import('../views/landing/LandingPage.vue'),
        meta: { title: 'IshTop - Telegram orqali ishga joylashish platformasi' },
      },
    ],
  },

  // Auth Routes
  {
    path: '/auth',
    component: AuthLayout,
    children: [
      {
        path: 'login',
        name: 'login',
        component: () => import('../views/auth/LoginPage.vue'),
        meta: { title: 'Kirish', guest: true },
      },
      {
        path: 'register',
        name: 'register',
        component: () => import('../views/auth/RegisterPage.vue'),
        meta: { title: 'Ro\'yxatdan o\'tish', guest: true },
      },
      {
        path: 'telegram',
        name: 'telegram-auth',
        component: () => import('../views/auth/TelegramAuth.vue'),
        meta: { title: 'Telegram orqali kirish', guest: true },
      },
    ],
  },

  // Dashboard Routes
  {
    path: '/dashboard',
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'dashboard',
        component: () => import('../views/dashboard/DashboardPage.vue'),
        meta: { title: 'Bosh sahifa' },
      },

      // Vacancies
      {
        path: 'vacancies',
        name: 'vacancies',
        component: () => import('../views/vacancies/VacancyList.vue'),
        meta: { title: 'Vakansiyalar' },
      },
      {
        path: 'vacancies/create',
        name: 'vacancy-create',
        component: () => import('../views/vacancies/VacancyCreate.vue'),
        meta: { title: 'Yangi e\'lon yaratish' },
      },
      {
        path: 'vacancies/:id',
        name: 'vacancy-detail',
        component: () => import('../views/vacancies/VacancyDetail.vue'),
        meta: { title: 'Vakansiya' },
      },
      {
        path: 'vacancies/:id/edit',
        name: 'vacancy-edit',
        component: () => import('../views/vacancies/VacancyEdit.vue'),
        meta: { title: 'E\'lonni tahrirlash' },
      },

      // Candidates
      {
        path: 'talent-pool',
        name: 'talent-pool',
        component: () => import('../views/candidates/TalentPool.vue'),
        meta: { title: 'Talent Pool' },
      },
      {
        path: 'candidates/compare',
        name: 'candidate-compare',
        component: () => import('../views/candidates/CandidateCompare.vue'),
        meta: { title: 'Kandidatlarni taqqoslash' },
      },

      // Questionnaires
      {
        path: 'questionnaires',
        name: 'questionnaires',
        component: () => import('../views/questionnaire/QuestionnaireList.vue'),
        meta: { title: 'Savolnomalar' },
      },
      {
        path: 'questionnaires/templates',
        name: 'questionnaire-templates',
        component: () => import('../views/questionnaire/QuestionnaireTemplates.vue'),
        meta: { title: 'Savolnoma shablonlari' },
      },
      {
        path: 'questionnaires/:vacancyId',
        name: 'questionnaire-builder',
        component: () => import('../views/questionnaire/QuestionnaireBuilder.vue'),
        meta: { title: 'Savolnoma yaratish' },
      },

      // Messages
      {
        path: 'messages/templates',
        name: 'message-templates',
        component: () => import('../views/messages/MessageTemplates.vue'),
        meta: { title: 'Xabar shablonlari' },
      },

      // Banners
      {
        path: 'banners',
        name: 'banners',
        component: () => import('../views/banners/BannerList.vue'),
        meta: { title: 'Bannerlar' },
      },
      {
        path: 'banners/create',
        name: 'banner-create',
        component: () => import('../views/banners/BannerCreate.vue'),
        meta: { title: 'Banner yaratish' },
      },

      // Analytics
      {
        path: 'analytics',
        name: 'analytics',
        component: () => import('../views/analytics/AnalyticsPage.vue'),
        meta: { title: 'Statistika' },
      },

      // Settings
      {
        path: 'settings',
        redirect: { name: 'settings-profile' },
      },
      {
        path: 'settings/profile',
        name: 'settings-profile',
        component: () => import('../views/settings/ProfileSettings.vue'),
        meta: { title: 'Profil sozlamalari' },
      },
      {
        path: 'settings/company',
        name: 'settings-company',
        component: () => import('../views/settings/CompanySettings.vue'),
        meta: { title: 'Kompaniya sozlamalari' },
      },
      {
        path: 'settings/team',
        name: 'settings-team',
        component: () => import('../views/settings/TeamSettings.vue'),
        meta: { title: 'Jamoa sozlamalari' },
      },
      {
        path: 'settings/billing',
        name: 'settings-billing',
        component: () => import('../views/settings/BillingSettings.vue'),
        meta: { title: 'To\'lov sozlamalari' },
      },
      {
        path: 'settings/notifications',
        name: 'settings-notifications',
        component: () => import('../views/settings/NotificationSettings.vue'),
        meta: { title: 'Bildirishnoma sozlamalari' },
      },
    ],
  },

  // Error pages
  {
    path: '/404',
    name: '404',
    component: () => import('../views/errors/NotFound.vue'),
    meta: { title: 'Sahifa topilmadi' },
  },
  {
    path: '/403',
    name: '403',
    component: () => import('../views/errors/Forbidden.vue'),
    meta: { title: 'Ruxsat yo\'q' },
  },

  // Catch all - 404
  {
    path: '/:pathMatch(.*)*',
    redirect: '/404',
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    } else {
      return { top: 0 };
    }
  },
});

// Navigation guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();

  // Wait for auth initialization (token restore + user fetch)
  if (!authStore.initialized) {
    await authStore.initialize();
  }

  // Set page title
  if (to.meta.title) {
    document.title = `${to.meta.title} - IshTop`;
  }

  // Check authentication
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login', query: { redirect: to.fullPath } });
    return;
  }

  // Guest only routes
  if (to.meta.guest && authStore.isAuthenticated) {
    next({ name: 'dashboard' });
    return;
  }

  next();
});

export default router;
