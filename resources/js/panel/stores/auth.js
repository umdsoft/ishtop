/**
 * Auth Store - Authentication state management
 */

import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref(null);
  const token = ref(localStorage.getItem('kadrgo-token') || null);
  const loading = ref(false);
  const initialized = ref(false);
  const companies = ref([]);
  const activeCompany = ref(null);

  // Getters
  const isAuthenticated = computed(() => !!token.value && !!user.value);
  const isEmployer = computed(() => user.value?.role === 'employer');
  const isRecruiter = computed(() => user.value?.role === 'recruiter');
  const activeCompanyId = computed(() => user.value?.active_employer_id || null);
  const hasMultipleCompanies = computed(() => companies.value.length > 1);

  // Actions
  async function fetchUser() {
    if (!token.value) return;

    try {
      loading.value = true;
      const response = await axios.get('/api/recruiter/me', {
        headers: {
          Authorization: `Bearer ${token.value}`,
        },
      });
      user.value = response.data.user;
      companies.value = response.data.companies || [];
      activeCompany.value = response.data.employer || null;
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
      const response = await axios.post('/api/recruiter/login', credentials);

      token.value = response.data.token;
      user.value = response.data.user;

      localStorage.setItem('kadrgo-token', token.value);
      axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;

      // Fetch full user data including companies
      await fetchUser();

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
      const response = await axios.post('/api/recruiter/register', data);

      token.value = response.data.token;
      user.value = response.data.user;

      localStorage.setItem('kadrgo-token', token.value);
      axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;

      // Fetch full user data including companies
      await fetchUser();

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

  async function sendOtp(phone) {
    try {
      loading.value = true;
      const response = await axios.post('/api/recruiter/send-otp', { phone });
      return {
        success: true,
        message: response.data.message,
        code: response.data.code, // only in dev mode
      };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Kod yuborib bo\'lmadi',
      };
    } finally {
      loading.value = false;
    }
  }

  async function verifyOtp(phone, code) {
    try {
      loading.value = true;
      const response = await axios.post('/api/recruiter/verify-otp', { phone, code });

      token.value = response.data.token;
      user.value = response.data.user;

      localStorage.setItem('kadrgo-token', token.value);
      axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;

      // Fetch full user data including companies
      await fetchUser();

      return { success: true };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Kod noto\'g\'ri',
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

      localStorage.setItem('kadrgo-token', token.value);
      axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;

      // Fetch full user data including companies
      await fetchUser();

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

  async function switchCompany(companyId) {
    try {
      loading.value = true;
      const response = await axios.post(`/api/recruiter/companies/${companyId}/switch`);
      activeCompany.value = response.data.active_employer;
      if (user.value) {
        user.value.active_employer_id = companyId;
      }
      // Refresh full state
      await fetchUser();
      return { success: true };
    } catch (error) {
      return {
        success: false,
        message: error.response?.data?.message || 'Almashtirib bo\'lmadi',
      };
    } finally {
      loading.value = false;
    }
  }

  async function fetchCompanies() {
    try {
      const response = await axios.get('/api/recruiter/companies');
      companies.value = response.data.companies;
      return companies.value;
    } catch (error) {
      console.error('Failed to fetch companies:', error);
      return [];
    }
  }

  function logout() {
    token.value = null;
    user.value = null;
    companies.value = [];
    activeCompany.value = null;
    localStorage.removeItem('kadrgo-token');
    delete axios.defaults.headers.common['Authorization'];
  }

  // Initialize auth state and axios interceptor
  async function initialize() {
    // Auth endpoints that should NOT trigger auto-logout on 401
    const authEndpoints = ['/api/recruiter/login', '/api/recruiter/register', '/api/recruiter/send-otp', '/api/recruiter/verify-otp', '/api/auth/telegram'];

    axios.interceptors.response.use(
      (response) => response,
      (error) => {
        const requestUrl = error.config?.url || '';
        const isAuthEndpoint = authEndpoints.some(ep => requestUrl.includes(ep));

        if (error.response?.status === 401 && !isAuthEndpoint) {
          logout();
        }
        return Promise.reject(error);
      }
    );

    // Restore session from saved token
    if (token.value) {
      axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
      await fetchUser();
    }

    initialized.value = true;
  }

  return {
    // State
    user,
    token,
    loading,
    initialized,
    companies,
    activeCompany,

    // Getters
    isAuthenticated,
    isEmployer,
    isRecruiter,
    activeCompanyId,
    hasMultipleCompanies,

    // Actions
    initialize,
    fetchUser,
    login,
    register,
    sendOtp,
    verifyOtp,
    loginWithTelegram,
    logout,
    switchCompany,
    fetchCompanies,
  };
});
