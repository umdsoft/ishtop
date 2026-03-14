/**
 * Admin API composable - axios wrapper for /api/admin/ prefix
 */

import { ref } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';

const API_PREFIX = '/api/admin';

export function useAdminApi() {
  const loading = ref(false);
  const error = ref(null);

  async function get(url, params = {}) {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.get(`${API_PREFIX}${url}`, { params });
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Xatolik yuz berdi';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function post(url, data = {}) {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.post(`${API_PREFIX}${url}`, data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Xatolik yuz berdi';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function put(url, data = {}) {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.put(`${API_PREFIX}${url}`, data);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Xatolik yuz berdi';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  async function del(url) {
    loading.value = true;
    error.value = null;
    try {
      const response = await axios.delete(`${API_PREFIX}${url}`);
      return response.data;
    } catch (err) {
      error.value = err.response?.data?.message || 'Xatolik yuz berdi';
      throw err;
    } finally {
      loading.value = false;
    }
  }

  return { get, post, put, del, loading, error };
}

/**
 * Resource list composable - pagination, filter, sort, search
 */
export function useResourceList(resourceUrl, defaultPerPage = 15) {
  const items = ref([]);
  const total = ref(0);
  const currentPage = ref(1);
  const lastPage = ref(1);
  const perPage = ref(defaultPerPage);
  const loading = ref(false);
  const search = ref('');
  const filters = ref({});
  const sortField = ref('created_at');
  const sortDirection = ref('desc');

  async function fetchItems() {
    loading.value = true;
    try {
      const params = {
        page: currentPage.value,
        per_page: perPage.value,
        sort: sortField.value,
        direction: sortDirection.value,
        ...Object.fromEntries(
          Object.entries(filters.value).filter(([, v]) => v !== null && v !== '')
        ),
      };
      if (search.value) {
        params.search = search.value;
      }

      const response = await axios.get(`${API_PREFIX}${resourceUrl}`, { params });

      // Handle paginated response
      if (response.data.data) {
        items.value = response.data.data;
        total.value = response.data.total || 0;
        lastPage.value = response.data.last_page || 1;
        currentPage.value = response.data.current_page || 1;
      } else {
        items.value = response.data;
        total.value = response.data.length;
      }
    } catch (err) {
      toast.error(err.response?.data?.message || 'Ma\'lumotlarni yuklashda xatolik');
    } finally {
      loading.value = false;
    }
  }

  function goToPage(page) {
    currentPage.value = page;
    fetchItems();
  }

  function setSort(field) {
    if (sortField.value === field) {
      sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
      sortField.value = field;
      sortDirection.value = 'desc';
    }
    currentPage.value = 1;
    fetchItems();
  }

  function applySearch(val) {
    search.value = val;
    currentPage.value = 1;
    fetchItems();
  }

  function applyFilter(key, value) {
    filters.value[key] = value;
    currentPage.value = 1;
    fetchItems();
  }

  function resetFilters() {
    filters.value = {};
    search.value = '';
    currentPage.value = 1;
    fetchItems();
  }

  return {
    items,
    total,
    currentPage,
    lastPage,
    perPage,
    loading,
    search,
    filters,
    sortField,
    sortDirection,
    fetchItems,
    goToPage,
    setSort,
    applySearch,
    applyFilter,
    resetFilters,
  };
}
