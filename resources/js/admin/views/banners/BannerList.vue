<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ $t('banners.title') }}</h1>
      <button
        @click="openCreateModal"
        class="px-4 py-2 bg-brand-500 text-white text-sm font-medium rounded-lg hover:bg-brand-600 transition-colors"
      >
        <PlusIcon class="w-4 h-4 inline mr-1" /> Qo'shish
      </button>
    </div>

    <!-- Filters -->
    <AppCard>
      <div class="flex flex-wrap items-center gap-3">
        <AppSearchInput v-model="search" :placeholder="$t('common.search')" @update:modelValue="applySearch" class="w-64" />
      </div>
    </AppCard>

    <!-- Table -->
    <AppCard noPadding>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-surface-200 dark:border-surface-800">
              <th class="text-left py-3 px-4 font-medium text-surface-500">Rasm</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500 cursor-pointer" @click="setSort('title')">Sarlavha</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">Holat</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500 cursor-pointer" @click="setSort('position')">Joylashuv</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">Boshlanishi</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">Tugashi</th>
              <th class="text-right py-3 px-4 font-medium text-surface-500">{{ $t('common.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="banner in items"
              :key="banner.id"
              class="border-b border-surface-100 dark:border-surface-800/50 hover:bg-surface-50 dark:hover:bg-surface-800/30"
            >
              <td class="py-3 px-4">
                <img
                  v-if="banner.image"
                  :src="banner.image"
                  :alt="banner.title"
                  class="w-16 h-10 object-cover rounded-lg border border-surface-200 dark:border-surface-700"
                />
                <div v-else class="w-16 h-10 bg-surface-100 dark:bg-surface-800 rounded-lg flex items-center justify-center">
                  <PhotoIcon class="w-5 h-5 text-surface-400" />
                </div>
              </td>
              <td class="py-3 px-4 font-medium text-surface-900 dark:text-surface-100">{{ banner.title || '—' }}</td>
              <td class="py-3 px-4">
                <button
                  @click.stop="toggleActive(banner)"
                  :class="['relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out', banner.is_active ? 'bg-success-500' : 'bg-surface-300 dark:bg-surface-600']"
                >
                  <span :class="['pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out', banner.is_active ? 'translate-x-5' : 'translate-x-0']" />
                </button>
              </td>
              <td class="py-3 px-4 text-surface-600 dark:text-surface-400">{{ banner.position || '—' }}</td>
              <td class="py-3 px-4 text-surface-500 text-xs">{{ formatDate(banner.starts_at) }}</td>
              <td class="py-3 px-4 text-surface-500 text-xs">{{ formatDate(banner.ends_at) }}</td>
              <td class="py-3 px-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button
                    @click="openEditModal(banner)"
                    class="text-xs px-3 py-1.5 rounded-lg font-medium bg-brand-50 text-brand-700 hover:bg-brand-100 dark:bg-brand-950/30 dark:text-brand-400 transition-colors"
                  >
                    <PencilIcon class="w-4 h-4 inline" />
                  </button>
                  <button
                    @click="deleteBanner(banner)"
                    class="text-xs px-3 py-1.5 rounded-lg font-medium bg-danger-50 text-danger-700 hover:bg-danger-100 dark:bg-danger-950/30 dark:text-danger-400 transition-colors"
                  >
                    <TrashIcon class="w-4 h-4 inline" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="loading" class="p-8 text-center text-surface-500">{{ $t('common.loading') }}</div>
      <div v-if="!loading && items.length === 0" class="p-8 text-center text-surface-500">{{ $t('common.noData') }}</div>
    </AppCard>

    <!-- Pagination -->
    <AppPagination v-if="lastPage > 1" :current-page="currentPage" :last-page="lastPage" :total="total" @page-change="goToPage" />

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="closeModal">
      <div class="bg-surface-0 dark:bg-surface-900 rounded-xl shadow-xl w-full max-w-lg mx-4 border border-surface-200 dark:border-surface-800">
        <div class="px-6 py-4 border-b border-surface-200 dark:border-surface-800">
          <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100">
            {{ editingBanner ? 'Banner tahrirlash' : 'Yangi banner' }}
          </h3>
        </div>
        <div class="px-6 py-4 space-y-4">
          <div>
            <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Sarlavha</label>
            <input
              v-model="form.title"
              type="text"
              class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
              placeholder="Banner sarlavhasi"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Rasm URL</label>
            <input
              v-model="form.image"
              type="text"
              class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
              placeholder="https://..."
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Havola (URL)</label>
            <input
              v-model="form.url"
              type="text"
              class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
              placeholder="https://..."
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Joylashuv</label>
            <input
              v-model="form.position"
              type="number"
              class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
              placeholder="0"
            />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Boshlanish sanasi</label>
              <input
                v-model="form.starts_at"
                type="date"
                class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Tugash sanasi</label>
              <input
                v-model="form.ends_at"
                type="date"
                class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
              />
            </div>
          </div>
          <div class="flex items-center gap-2">
            <input v-model="form.is_active" type="checkbox" id="is_active" class="rounded border-surface-300 text-brand-500 focus:ring-brand-500" />
            <label for="is_active" class="text-sm font-medium text-surface-700 dark:text-surface-300">Faol</label>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-surface-200 dark:border-surface-800 flex justify-end gap-3">
          <button
            @click="closeModal"
            class="px-4 py-2 text-sm font-medium text-surface-700 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-800 rounded-lg transition-colors"
          >
            Bekor qilish
          </button>
          <button
            @click="saveBanner"
            :disabled="saving"
            class="px-4 py-2 bg-brand-500 text-white text-sm font-medium rounded-lg hover:bg-brand-600 transition-colors disabled:opacity-50"
          >
            {{ saving ? 'Saqlanmoqda...' : 'Saqlash' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { useResourceList } from '../../composables/useAdminApi';
import AppCard from '@panel/components/ui/AppCard.vue';
import AppSearchInput from '@panel/components/ui/AppSearchInput.vue';
import AppPagination from '@panel/components/ui/AppPagination.vue';
import { PlusIcon, PencilIcon, TrashIcon, PhotoIcon } from '@heroicons/vue/24/outline';

const {
  items, total, currentPage, lastPage, loading, search, filters,
  fetchItems, goToPage, setSort, applySearch, applyFilter,
} = useResourceList('/banners');

const showModal = ref(false);
const editingBanner = ref(null);
const saving = ref(false);
const form = ref({
  title: '',
  image: '',
  url: '',
  position: 0,
  starts_at: '',
  ends_at: '',
  is_active: true,
});

function formatDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function openCreateModal() {
  editingBanner.value = null;
  form.value = { title: '', image: '', url: '', position: 0, starts_at: '', ends_at: '', is_active: true };
  showModal.value = true;
}

function openEditModal(banner) {
  editingBanner.value = banner;
  form.value = {
    title: banner.title || '',
    image: banner.image || '',
    url: banner.url || '',
    position: banner.position || 0,
    starts_at: banner.starts_at ? banner.starts_at.split('T')[0] : '',
    ends_at: banner.ends_at ? banner.ends_at.split('T')[0] : '',
    is_active: !!banner.is_active,
  };
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  editingBanner.value = null;
}

async function saveBanner() {
  saving.value = true;
  try {
    if (editingBanner.value) {
      const res = await axios.put(`/api/admin/banners/${editingBanner.value.id}`, form.value);
      toast.success(res.data.message || 'Banner yangilandi');
    } else {
      const res = await axios.post('/api/admin/banners', form.value);
      toast.success(res.data.message || 'Banner yaratildi');
    }
    closeModal();
    fetchItems();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  } finally {
    saving.value = false;
  }
}

async function toggleActive(banner) {
  try {
    const res = await axios.post(`/api/admin/banners/${banner.id}/toggle-active`);
    banner.is_active = !banner.is_active;
    toast.success(res.data.message || 'Holat o\'zgartirildi');
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  }
}

async function deleteBanner(banner) {
  if (!confirm(`"${banner.title}" bannerni o'chirishni xohlaysizmi?`)) return;
  try {
    const res = await axios.delete(`/api/admin/banners/${banner.id}`);
    toast.success(res.data.message || 'Banner o\'chirildi');
    fetchItems();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  }
}

onMounted(fetchItems);
</script>
