/**
 * Admin Auth Store - Authentication state management
 */

import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useAuthStore = defineStore('adminAuth', () => {
  const user = ref(null);
  const token = ref(localStorage.getItem('kadrgo-admin-token') || null);
  const loading = ref(false);
  const initialized = ref(false);

  const isAuthenticated = computed(() => !!token.value && !!user.value);

  async function fetchUser() {
    if (!token.value) return;

    try {
      loading.value = true;
      const response = await axios.get('/api/admin/me', {
        headers: { Authorization: `Bearer ${token.value}` },
      });
      user.value = response.data.user;
    } catch (error) {
      if (error.response?.status === 401 || error.response?.status === 403) {
        logout();
      }
    } finally {
      loading.value = false;
    }
  }

  async function login(credentials) {
    try {
      loading.value = true;
      const response = await axios.post('/api/admin/login', credentials);

      token.value = response.data.token;
      user.value = response.data.user;

      localStorage.setItem('kadrgo-admin-token', token.value);
      axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;

      return { success: true };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Login xatolik',
      };
    } finally {
      loading.value = false;
    }
  }

  function logout() {
    if (token.value) {
      axios.post('/api/admin/logout').catch(() => {});
    }
    token.value = null;
    user.value = null;
    localStorage.removeItem('kadrgo-admin-token');
    delete axios.defaults.headers.common['Authorization'];
  }

  async function initialize() {
    axios.interceptors.response.use(
      (response) => response,
      (error) => {
        const requestUrl = error.config?.url || '';
        if (error.response?.status === 401 && !requestUrl.includes('/api/admin/login')) {
          logout();
        }
        return Promise.reject(error);
      }
    );

    if (token.value) {
      axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
      await fetchUser();
    }

    initialized.value = true;
  }

  return {
    user,
    token,
    loading,
    initialized,
    isAuthenticated,
    initialize,
    fetchUser,
    login,
    logout,
  };
});
