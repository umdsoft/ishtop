/**
 * Vue Router - KadrGo Admin Panel
 */

import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

// Layouts
import AdminLayout from '../layouts/AdminLayout.vue';
import AuthLayout from '../layouts/AuthLayout.vue';

const routes = [
  // Auth Routes
  {
    path: '/auth',
    component: AuthLayout,
    children: [
      {
        path: 'login',
        name: 'admin-login',
        component: () => import('../views/auth/AdminLogin.vue'),
        meta: { title: 'Kirish', guest: true },
      },
    ],
  },

  // Dashboard Routes (requires auth)
  {
    path: '/',
    component: AdminLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'admin-dashboard',
        component: () => import('../views/dashboard/AdminDashboard.vue'),
        meta: { title: 'Bosh sahifa' },
      },

      // Users
      {
        path: 'users',
        name: 'admin-users',
        component: () => import('../views/users/UserList.vue'),
        meta: { title: 'Foydalanuvchilar' },
      },
      {
        path: 'users/:id',
        name: 'admin-user-detail',
        component: () => import('../views/users/UserDetail.vue'),
        meta: { title: 'Foydalanuvchi' },
      },

      // Workers
      {
        path: 'workers',
        name: 'admin-workers',
        component: () => import('../views/workers/WorkerList.vue'),
        meta: { title: 'Ishchilar' },
      },
      {
        path: 'workers/:id',
        name: 'admin-worker-detail',
        component: () => import('../views/workers/WorkerDetail.vue'),
        meta: { title: 'Ishchi' },
      },

      // Employers
      {
        path: 'employers',
        name: 'admin-employers',
        component: () => import('../views/employers/EmployerList.vue'),
        meta: { title: 'Ish beruvchilar' },
      },
      {
        path: 'employers/:id',
        name: 'admin-employer-detail',
        component: () => import('../views/employers/EmployerDetail.vue'),
        meta: { title: 'Ish beruvchi' },
      },

      // Vacancies
      {
        path: 'vacancies',
        name: 'admin-vacancies',
        component: () => import('../views/vacancies/VacancyList.vue'),
        meta: { title: 'Vakansiyalar' },
      },
      {
        path: 'vacancies/:id',
        name: 'admin-vacancy-detail',
        component: () => import('../views/vacancies/VacancyDetail.vue'),
        meta: { title: 'Vakansiya' },
      },

      // Applications
      {
        path: 'applications',
        name: 'admin-applications',
        component: () => import('../views/applications/ApplicationList.vue'),
        meta: { title: 'Arizalar' },
      },
      {
        path: 'applications/:id',
        name: 'admin-application-detail',
        component: () => import('../views/applications/ApplicationDetail.vue'),
        meta: { title: 'Ariza' },
      },

      // Categories
      {
        path: 'categories',
        name: 'admin-categories',
        component: () => import('../views/categories/CategoryList.vue'),
        meta: { title: 'Kategoriyalar' },
      },

      // Cities
      {
        path: 'cities',
        name: 'admin-cities',
        component: () => import('../views/cities/CityList.vue'),
        meta: { title: 'Shaharlar' },
      },

      // Payments
      {
        path: 'payments',
        name: 'admin-payments',
        component: () => import('../views/payments/PaymentList.vue'),
        meta: { title: 'To\'lovlar' },
      },
      {
        path: 'payments/:id',
        name: 'admin-payment-detail',
        component: () => import('../views/payments/PaymentDetail.vue'),
        meta: { title: 'To\'lov' },
      },

      // Subscriptions
      {
        path: 'subscriptions',
        name: 'admin-subscriptions',
        component: () => import('../views/subscriptions/SubscriptionList.vue'),
        meta: { title: 'Obunalar' },
      },
      {
        path: 'subscriptions/:id',
        name: 'admin-subscription-detail',
        component: () => import('../views/subscriptions/SubscriptionDetail.vue'),
        meta: { title: 'Obuna' },
      },

      // Banners
      {
        path: 'banners',
        name: 'admin-banners',
        component: () => import('../views/banners/BannerList.vue'),
        meta: { title: 'Bannerlar' },
      },

      // Reports
      {
        path: 'reports',
        name: 'admin-reports',
        component: () => import('../views/reports/ReportList.vue'),
        meta: { title: 'Shikoyatlar' },
      },
      {
        path: 'reports/:id',
        name: 'admin-report-detail',
        component: () => import('../views/reports/ReportDetail.vue'),
        meta: { title: 'Shikoyat' },
      },

      // Settings
      {
        path: 'settings',
        name: 'admin-settings',
        component: () => import('../views/settings/SettingList.vue'),
        meta: { title: 'Sozlamalar' },
      },
    ],
  },

  // Catch all - redirect to dashboard
  {
    path: '/:pathMatch(.*)*',
    redirect: '/',
  },
];

const router = createRouter({
  history: createWebHistory('/dash'),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) return savedPosition;
    return { top: 0 };
  },
});

// Navigation guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();

  if (!authStore.initialized) {
    await authStore.initialize();
  }

  if (to.meta.title) {
    document.title = `${to.meta.title} - KadrGo Admin`;
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'admin-login', query: { redirect: to.fullPath } });
    return;
  }

  if (to.meta.guest && authStore.isAuthenticated) {
    next({ name: 'admin-dashboard' });
    return;
  }

  next();
});

export default router;
