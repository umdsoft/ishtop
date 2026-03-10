/**
 * Vue Router - KadrGo Public Website
 */

import { createRouter, createWebHistory } from 'vue-router';
import WebsiteLayout from '@website/layouts/WebsiteLayout.vue';

const routes = [
  {
    path: '/',
    component: WebsiteLayout,
    children: [
      {
        path: '',
        name: 'home',
        component: () => import('@website/pages/HomePage.vue'),
        meta: { title: 'KadrGo — O\'zbekistondagi eng yirik ish qidirish platformasi' },
      },
      {
        path: 'vacancies',
        name: 'vacancies',
        component: () => import('@website/pages/VacanciesPage.vue'),
        meta: { title: 'Vakansiyalar — KadrGo' },
      },
      {
        path: 'vacancies/:id',
        name: 'vacancy-detail',
        component: () => import('@website/pages/VacancyDetailPage.vue'),
        meta: { title: 'Vakansiya — KadrGo' },
      },
      {
        path: ':pathMatch(.*)*',
        name: 'not-found',
        component: () => import('@website/pages/NotFoundPage.vue'),
        meta: { title: '404 — KadrGo' },
      },
    ],
  },
];

const router = createRouter({
  history: createWebHistory('/'),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) return savedPosition;
    if (to.hash) return { el: to.hash, behavior: 'smooth' };
    return { top: 0 };
  },
});

router.beforeEach((to) => {
  if (to.meta.title) {
    document.title = to.meta.title;
  }
});

export default router;
