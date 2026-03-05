/**
 * Auth Store - Authentication state management
 */

import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref(null);
  const token = ref(localStorage.getItem('ishtop-token') || null);
  const loading = ref(false);

  // Getters
  const isAuthenticated = computed(() => !!token.value && !!user.value);
  const isEmployer = computed(() => user.value?.role === 'employer');
  const isRecruiter = computed(() => user.value?.role === 'recruiter');

  // Actions
  async function fetchUser() {
    if (!token.value) return;

    try {
      loading.value = true;
      const response = await axios.get('/api/user', {
        headers: {
          Authorization: `Bearer ${token.value}`,
        },
      });
      user.value = response.data;
    } catch (error) {
      console.error('Failed to fetch user:', error);
      if (error.response?.status === 401) {
        logout();
      }
    } finally {
      loading.value = false;
    }
  }

  async function login(credentials) {
    try {
      loading.value = true;
      const response = await axios.post('/api/auth/login', credentials);

      token.value = response.data.token;
      user.value = response.data.user;

      localStorage.setItem('ishtop-token', token.value);

      // Set axios default header
      axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;

      return { success: true };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Login failed',
      };
    } finally {
      loading.value = false;
    }
  }

  async function register(data) {
    try {
      loading.value = true;
      const response = await axios.post('/api/auth/register', data);

      token.value = response.data.token;
      user.value = response.data.user;

      localStorage.setItem('ishtop-token', token.value);
      axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;

      return { success: true };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Registration failed',
        errors: error.response?.data?.errors || {},
      };
    } finally {
      loading.value = false;
    }
  }

  async function loginWithTelegram(initData) {
    try {
      loading.value = true;
      const response = await axios.post('/api/auth/telegram', {
        init_data: initData,
      });

      token.value = response.data.token;
      user.value = response.data.user;

      localStorage.setItem('ishtop-token', token.value);
      axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;

      return { success: true };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Telegram login failed',
      };
    } finally {
      loading.value = false;
    }
  }

  function logout() {
    token.value = null;
    user.value = null;
    localStorage.removeItem('ishtop-token');
    delete axios.defaults.headers.common['Authorization'];
  }

  // Initialize axios interceptor
  function initializeAxiosInterceptor() {
    axios.interceptors.response.use(
      (response) => response,
      (error) => {
        if (error.response?.status === 401) {
          logout();
          window.location.href = '/auth/login';
        }
        return Promise.reject(error);
      }
    );

    // Set initial token if exists
    if (token.value) {
      axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
      fetchUser();
    }
  }

  return {
    // State
    user,
    token,
    loading,

    // Getters
    isAuthenticated,
    isEmployer,
    isRecruiter,

    // Actions
    fetchUser,
    login,
    register,
    loginWithTelegram,
    logout,
    initializeAxiosInterceptor,
  };
});
