<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ $t('banners.title') }}</h1>
      <button
        @click="openCreateModal"
        class="inline-flex items-center gap-1.5 px-4 py-2 bg-brand-500 text-white text-sm font-medium rounded-lg hover:bg-brand-600 transition-colors"
      >
        <PlusIcon class="w-4 h-4" /> {{ $t('banners.addNew') }}
      </button>
    </div>

    <!-- Filters -->
    <AppCard>
      <div class="flex flex-wrap items-center gap-3">
        <AppSearchInput v-model="search" :placeholder="$t('common.search')" @update:modelValue="applySearch" class="w-64" />
        <select
          v-model="filters.status"
          @change="applyFilter('status', filters.status)"
          class="px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm"
        >
          <option value="">{{ $t('common.all') }}</option>
          <option value="active">{{ $t('banners.active') }}</option>
          <option value="paused">{{ $t('banners.paused') }}</option>
          <option value="draft">{{ $t('banners.draft') }}</option>
        </select>
      </div>
    </AppCard>

    <!-- Banner Cards Grid -->
    <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
      <div v-for="i in 6" :key="i" class="rounded-xl border border-surface-200 dark:border-surface-700/60 bg-white dark:bg-surface-800/80 overflow-hidden animate-pulse">
        <div class="h-36 bg-surface-200 dark:bg-surface-700" />
        <div class="p-4 space-y-2">
          <div class="h-4 w-3/4 bg-surface-200 dark:bg-surface-700 rounded" />
          <div class="h-3 w-1/2 bg-surface-100 dark:bg-surface-700/50 rounded" />
        </div>
      </div>
    </div>

    <div v-else-if="items.length === 0" class="text-center py-12">
      <PhotoIcon class="w-12 h-12 mx-auto text-surface-300 dark:text-surface-600 mb-3" />
      <p class="text-surface-500">{{ $t('common.noData') }}</p>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
      <div
        v-for="banner in items"
        :key="banner.id"
        class="rounded-xl border border-surface-200 dark:border-surface-700/60 bg-white dark:bg-surface-800/80 overflow-hidden group hover:shadow-md transition-shadow"
      >
        <!-- Image Preview -->
        <div class="relative h-36 bg-surface-100 dark:bg-surface-800 overflow-hidden">
          <img
            v-if="banner.image_url"
            :src="banner.image_url"
            :alt="banner.title"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
          />
          <div v-else class="w-full h-full flex items-center justify-center">
            <PhotoIcon class="w-10 h-10 text-surface-300 dark:text-surface-600" />
          </div>
          <!-- Status badge -->
          <span :class="['absolute top-2 left-2 text-xs px-2 py-0.5 rounded-full font-medium backdrop-blur-sm', bannerStatusClass(banner.status)]">
            {{ bannerStatusLabel(banner.status) }}
          </span>
          <!-- Priority badge -->
          <span v-if="banner.priority" class="absolute top-2 right-2 text-xs px-2 py-0.5 rounded-full font-medium bg-black/40 text-white backdrop-blur-sm">
            #{{ banner.priority }}
          </span>
        </div>

        <!-- Content -->
        <div class="p-4">
          <h3 class="font-semibold text-surface-900 dark:text-surface-100 truncate">{{ banner.title || 'Nomsiz banner' }}</h3>

          <div class="flex items-center gap-3 mt-2 text-xs text-surface-500 dark:text-surface-400">
            <span v-if="banner.advertiser_name" class="truncate">{{ banner.advertiser_name }}</span>
            <span v-if="banner.starts_at || banner.ends_at">
              {{ formatDateShort(banner.starts_at) }} — {{ formatDateShort(banner.ends_at) }}
            </span>
          </div>

          <!-- Stats row -->
          <div class="flex items-center gap-4 mt-3 pt-3 border-t border-surface-100 dark:border-surface-800">
            <div class="text-center flex-1">
              <p class="text-sm font-semibold text-surface-900 dark:text-surface-100">{{ (banner.impressions_count || 0).toLocaleString() }}</p>
              <p class="text-xs text-surface-500">{{ $t('banners.impressions') }}</p>
            </div>
            <div class="text-center flex-1">
              <p class="text-sm font-semibold text-surface-900 dark:text-surface-100">{{ (banner.clicks_count || 0).toLocaleString() }}</p>
              <p class="text-xs text-surface-500">{{ $t('banners.clicks') }}</p>
            </div>
            <div class="text-center flex-1">
              <p class="text-sm font-semibold text-surface-900 dark:text-surface-100">{{ ctr(banner) }}%</p>
              <p class="text-xs text-surface-500">{{ $t('banners.ctr') }}</p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-2 mt-3">
            <button
              @click="toggleStatus(banner)"
              :class="[
                'flex-1 py-1.5 text-xs font-medium rounded-lg transition-colors text-center',
                banner.status === 'active'
                  ? 'bg-warning-50 text-warning-700 hover:bg-warning-100 dark:bg-warning-950/30 dark:text-warning-400'
                  : 'bg-success-50 text-success-700 hover:bg-success-100 dark:bg-success-950/30 dark:text-success-400',
              ]"
            >
              {{ banner.status === 'active' ? 'Pauza' : 'Faollashtirish' }}
            </button>
            <button
              @click="openEditModal(banner)"
              class="p-1.5 rounded-lg text-brand-600 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-950/30 transition-colors"
            >
              <PencilIcon class="w-4 h-4" />
            </button>
            <button
              @click="deleteBanner(banner)"
              class="p-1.5 rounded-lg text-danger-600 dark:text-danger-400 hover:bg-danger-50 dark:hover:bg-danger-950/30 transition-colors"
            >
              <TrashIcon class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <AppPagination v-if="lastPage > 1" :current-page="currentPage" :last-page="lastPage" :total="total" @page-change="goToPage" />

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="closeModal">
      <div class="bg-white dark:bg-surface-900 rounded-xl shadow-xl w-full max-w-lg mx-4 border border-surface-200 dark:border-surface-800 max-h-[90vh] overflow-y-auto">
        <div class="px-6 py-4 border-b border-surface-200 dark:border-surface-800 sticky top-0 bg-white dark:bg-surface-900 z-10">
          <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100">
            {{ editingBanner ? 'Banner tahrirlash' : 'Yangi banner' }}
          </h3>
        </div>
        <div class="px-6 py-4 space-y-4">
          <!-- Image preview -->
          <div v-if="form.image_url" class="rounded-lg overflow-hidden border border-surface-200 dark:border-surface-700">
            <img :src="form.image_url" class="w-full h-32 object-cover" />
          </div>

          <div>
            <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Sarlavha</label>
            <input v-model="form.title" type="text"
              class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
              placeholder="Banner sarlavhasi" />
          </div>
          <div>
            <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Rasm URL</label>
            <input v-model="form.image_url" type="text"
              class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
              placeholder="https://..." />
          </div>
          <div>
            <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Havola (URL)</label>
            <input v-model="form.click_url" type="text"
              class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
              placeholder="https://..." />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Reklama beruvchi</label>
              <input v-model="form.advertiser_name" type="text"
                class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                placeholder="Kompaniya nomi" />
            </div>
            <div>
              <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Aloqa</label>
              <input v-model="form.advertiser_contact" type="text"
                class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                placeholder="Telefon/email" />
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Ustuvorlik</label>
              <input v-model.number="form.priority" type="number"
                class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                placeholder="0" />
            </div>
            <div>
              <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Holat</label>
              <select v-model="form.status"
                class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                <option value="active">Faol</option>
                <option value="paused">Pauza</option>
                <option value="draft">Qoralama</option>
              </select>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Boshlanish sanasi</label>
              <input v-model="form.starts_at" type="date"
                class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500" />
            </div>
            <div>
              <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Tugash sanasi</label>
              <input v-model="form.ends_at" type="date"
                class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500" />
            </div>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-surface-200 dark:border-surface-800 flex justify-end gap-3 sticky bottom-0 bg-white dark:bg-surface-900">
          <button @click="closeModal"
            class="px-4 py-2 text-sm font-medium text-surface-700 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-800 rounded-lg transition-colors">
            Bekor qilish
          </button>
          <button @click="saveBanner" :disabled="saving"
            class="px-4 py-2 bg-brand-500 text-white text-sm font-medium rounded-lg hover:bg-brand-600 transition-colors disabled:opacity-50">
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
const defaultForm = () => ({
  title: '', image_url: '', click_url: '', advertiser_name: '', advertiser_contact: '',
  priority: 0, status: 'draft', starts_at: '', ends_at: '',
});
const form = ref(defaultForm());

function formatDateShort(d) {
  if (!d) return '—';
  return new Date(d).toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit' });
}

function ctr(banner) {
  if (!banner.impressions_count) return '0.0';
  return ((banner.clicks_count || 0) / banner.impressions_count * 100).toFixed(1);
}

function bannerStatusClass(status) {
  const map = {
    active: 'bg-success-500/90 text-white',
    paused: 'bg-warning-500/90 text-white',
    draft: 'bg-surface-500/90 text-white',
  };
  return map[status] || 'bg-surface-500/90 text-white';
}

function bannerStatusLabel(status) {
  const map = { active: 'Faol', paused: 'Pauza', draft: 'Qoralama' };
  return map[status] || status;
}

function openCreateModal() {
  editingBanner.value = null;
  form.value = defaultForm();
  showModal.value = true;
}

function openEditModal(banner) {
  editingBanner.value = banner;
  form.value = {
    title: banner.title || '',
    image_url: banner.image_url || '',
    click_url: banner.click_url || '',
    advertiser_name: banner.advertiser_name || '',
    advertiser_contact: banner.advertiser_contact || '',
    priority: banner.priority || 0,
    status: banner.status || 'draft',
    starts_at: banner.starts_at ? banner.starts_at.split('T')[0] : '',
    ends_at: banner.ends_at ? banner.ends_at.split('T')[0] : '',
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
      await axios.put(`/api/admin/banners/${editingBanner.value.id}`, form.value);
      toast.success('Banner yangilandi');
    } else {
      await axios.post('/api/admin/banners', form.value);
      toast.success('Banner yaratildi');
    }
    closeModal();
    fetchItems();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  } finally {
    saving.value = false;
  }
}

async function toggleStatus(banner) {
  const newStatus = banner.status === 'active' ? 'paused' : 'active';
  try {
    await axios.put(`/api/admin/banners/${banner.id}`, { status: newStatus });
    banner.status = newStatus;
    toast.success(newStatus === 'active' ? 'Banner faollashtirildi' : 'Banner pauzaga olindi');
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  }
}

async function deleteBanner(banner) {
  if (!confirm(`"${banner.title || 'Banner'}" ni o'chirishni xohlaysizmi?`)) return;
  try {
    await axios.delete(`/api/admin/banners/${banner.id}`);
    toast.success("Banner o'chirildi");
    fetchItems();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  }
}

onMounted(fetchItems);
</script>
