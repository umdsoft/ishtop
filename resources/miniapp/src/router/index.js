import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('@/views/HomeView.vue'),
    meta: { title: 'IshTop' },
  },
  {
    path: '/search',
    name: 'search',
    component: () => import('@/views/SearchView.vue'),
    meta: { title: 'Qidirish' },
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
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

// Navigation guard
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  // Update Telegram WebApp header color
  if (window.Telegram?.WebApp) {
    window.Telegram.WebApp.setHeaderColor('bg_color')
  }

  // Check authentication
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    // Show alert and stay on current page
    if (window.Telegram?.WebApp) {
      window.Telegram.WebApp.showAlert('Bu sahifani ko\'rish uchun tizimga kirish kerak')
    }
    next(false)
    return
  }

  next()
})

// After navigation
router.afterEach((to) => {
  // Set page title
  if (to.meta.title) {
    document.title = to.meta.title + ' - IshTop'
  }

  // Scroll to top
  window.scrollTo(0, 0)
})

export default router
