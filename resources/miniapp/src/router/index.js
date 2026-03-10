import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('@/views/HomeView.vue'),
    meta: { title: 'KadrGo' },
  },
  {
    path: '/search',
    name: 'search',
    component: () => import('@/views/SearchView.vue'),
    meta: { title: 'Qidirish' },
  },
  {
    path: '/post',
    name: 'post',
    component: () => import('@/views/MyVacanciesView.vue'),
    meta: { title: "Mening e'lonlarim", requiresAuth: true },
  },
  {
    path: '/post/new',
    name: 'post-new',
    component: () => import('@/views/PostVacancyView.vue'),
    meta: { title: "E'lon berish", requiresAuth: true },
  },
  {
    path: '/map',
    name: 'map',
    component: () => import('@/views/MapView.vue'),
    meta: { title: 'Xarita' },
  },
  {
    path: '/vacancies/:id',
    name: 'vacancy-detail',
    component: () => import('@/views/VacancyDetailView.vue'),
    meta: { title: 'Vakansiya' },
  },
  {
    path: '/applications',
    name: 'applications',
    component: () => import('@/views/ApplicationsView.vue'),
    meta: { title: 'Arizalar', requiresAuth: true },
  },
  {
    path: '/profile',
    name: 'profile',
    component: () => import('@/views/ProfileView.vue'),
    meta: { title: 'Profil', requiresAuth: true },
  },
  {
    path: '/questionnaire/:vacancyId',
    name: 'questionnaire',
    component: () => import('@/views/QuestionnaireView.vue'),
    meta: { title: 'Savolnoma', requiresAuth: true },
  },
  {
    path: '/saved',
    name: 'saved',
    component: () => import('@/views/SavedView.vue'),
    meta: { title: 'Saqlangan', requiresAuth: true },
  },
  {
    path: '/notifications',
    name: 'notifications',
    component: () => import('@/views/NotificationsView.vue'),
    meta: { title: 'Bildirishnomalar', requiresAuth: true },
  },
  {
    path: '/profile/worker/edit',
    name: 'edit-worker',
    component: () => import('@/views/EditWorkerProfileView.vue'),
    meta: { title: 'Profil tahrirlash', requiresAuth: true },
  },
  {
    path: '/profile/employer/edit',
    name: 'edit-employer',
    component: () => import('@/views/EditEmployerProfileView.vue'),
    meta: { title: 'Profil tahrirlash', requiresAuth: true },
  },
  {
    path: '/transactions',
    name: 'transactions',
    component: () => import('@/views/TransactionsView.vue'),
    meta: { title: 'Tranzaksiyalar', requiresAuth: true },
  },
  {
    path: '/candidates/:id',
    name: 'candidate-detail',
    component: () => import('@/views/CandidateDetailView.vue'),
    meta: { title: 'Nomzod', requiresAuth: true },
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

// Navigation guard
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  // Update Telegram WebApp header color
  if (window.Telegram?.WebApp) {
    window.Telegram.WebApp.setHeaderColor('bg_color')
  }

  // Wait for auth to complete before checking
  if (to.meta.requiresAuth && !authStore.authAttempted) {
    await authStore.authReady
  }

  // If still not authenticated after auth attempt, redirect to home
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'home' })
    return
  }

  next()
})

// After navigation
router.afterEach((to) => {
  // Set page title
  if (to.meta.title) {
    document.title = to.meta.title + ' - KadrGo'
  }

  // Scroll to top
  window.scrollTo(0, 0)

  // Global BackButton — show on all pages except home
  const tg = window.Telegram?.WebApp
  if (tg?.BackButton) {
    if (to.name !== 'home') {
      tg.BackButton.show()
      tg.BackButton.offClick()
      tg.BackButton.onClick(() => {
        router.back()
      })
    } else {
      tg.BackButton.hide()
      tg.BackButton.offClick()
    }
  }
})

export default router
