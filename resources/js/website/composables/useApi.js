/**
 * Public website API composable
 * No auth needed — public endpoints only
 */

import axios from 'axios';
import i18n from '@website/plugins/i18n';

const api = axios.create({
  baseURL: '/api/web',
  headers: {
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
});

// Set Accept-Language on each request (registered once)
api.interceptors.request.use((config) => {
  config.headers['Accept-Language'] = i18n.global.locale.value;
  return config;
});

export function useApi() {
  return {
    getHome: () => api.get('/home'),
    getVacancies: (params) => api.get('/vacancies', { params }),
    getVacancy: (id) => api.get(`/vacancies/${id}`),
    applyToVacancy: (id, data) => api.post(`/vacancies/${id}/apply`, data),
  };
}
