<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ $t('vacancies.title') }}</h1>
      <span class="text-sm text-surface-500 dark:text-surface-400">Jami: {{ total }}</span>
    </div>

    <!-- City filter badge -->
    <div v-if="cityFilterLabel" class="flex items-center gap-3">
      <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-brand-100 dark:bg-brand-900/30 text-brand-700 dark:text-brand-400 text-sm font-medium">
        📍 {{ cityFilterLabel }}
        <button @click="clearCityFilter" class="ml-1 hover:text-brand-900 dark:hover:text-brand-200">&times;</button>
      </span>
    </div>

    <!-- Filters -->
    <AppCard>
      <div class="flex flex-wrap items-center gap-3">
        <AppSearchInput v-model="search" :placeholder="$t('common.search')" @update:modelValue="applySearch" class="w-64" />
        <select
          v-model="filters.status"
          @change="applyFilter('status', filters.status)"
          class="px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm text-surface-900 dark:text-surface-100"
        >
          <option value="">Barcha status</option>
          <option value="pending">Kutilmoqda</option>
          <option value="active">Faol</option>
          <option value="closed">Yopilgan</option>
          <option value="expired">Muddati o'tgan</option>
        </select>
        <select
          v-model="filters.work_type"
          @change="applyFilter('work_type', filters.work_type)"
          class="px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm text-surface-900 dark:text-surface-100"
        >
          <option value="">Barcha ish turi</option>
          <option value="full_time">To'liq stavka</option>
          <option value="part_time">Yarim stavka</option>
          <option value="remote">Masofaviy</option>
          <option value="freelance">Frilanser</option>
        </select>
      </div>
    </AppCard>

    <!-- Table -->
    <AppCard noPadding>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-surface-200 dark:border-surface-800">
              <th class="text-left py-3 px-4 font-medium text-surface-500 cursor-pointer hover:text-surface-700 dark:hover:text-surface-300 transition-colors" @click="setSort('title_uz')">
                Sarlavha
                <span v-if="currentSort === 'title_uz'" class="ml-1 text-xs">{{ sortDir === 'asc' ? '↑' : '↓' }}</span>
              </th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">Kompaniya</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">Kategoriya</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">Shahar</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">{{ $t('common.status') }}</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500 cursor-pointer hover:text-surface-700 dark:hover:text-surface-300 transition-colors" @click="setSort('views_count')">
                Ko'rishlar
                <span v-if="currentSort === 'views_count'" class="ml-1 text-xs">{{ sortDir === 'asc' ? '↑' : '↓' }}</span>
              </th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">Arizalar</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500 cursor-pointer hover:text-surface-700 dark:hover:text-surface-300 transition-colors" @click="setSort('created_at')">
                {{ $t('common.date') }}
                <span v-if="currentSort === 'created_at'" class="ml-1 text-xs">{{ sortDir === 'asc' ? '↑' : '↓' }}</span>
              </th>
              <th class="text-right py-3 px-4 font-medium text-surface-500">{{ $t('common.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="vacancy in items"
              :key="vacancy.id"
              class="border-b border-surface-100 dark:border-surface-800/50 hover:bg-surface-50 dark:hover:bg-surface-800/30 cursor-pointer transition-colors"
              @click="$router.push(`/vacancies/${vacancy.id}`)"
            >
              <td class="py-3 px-4">
                <div class="flex items-center gap-2">
                  <div>
                    <p class="font-medium text-surface-900 dark:text-surface-100 line-clamp-1">{{ vacancy.title_uz }}</p>
                    <p v-if="vacancy.title_ru && vacancy.title_ru !== vacancy.title_uz" class="text-xs text-surface-400 dark:text-surface-500 line-clamp-1 mt-0.5">{{ vacancy.title_ru }}</p>
                  </div>
                  <span v-if="vacancy.is_top" class="shrink-0 text-xs px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 font-medium">TOP</span>
                </div>
              </td>
              <td class="py-3 px-4">
                <p class="text-surface-600 dark:text-surface-400 line-clamp-1">{{ vacancy.employer?.company_name || '—' }}</p>
              </td>
              <td class="py-3 px-4">
                <span v-if="vacancy.category_relation" class="inline-flex items-center gap-1 text-surface-600 dark:text-surface-400">
                  <span v-if="vacancy.category_relation.emoji">{{ vacancy.category_relation.emoji }}</span>
                  <span class="line-clamp-1">{{ vacancy.category_relation.name_uz }}</span>
                </span>
                <span v-else-if="vacancy.category" class="text-surface-500 dark:text-surface-400 line-clamp-1">{{ vacancy.category }}</span>
                <span v-else class="text-surface-400">—</span>
              </td>
              <td class="py-3 px-4 text-surface-600 dark:text-surface-400">
                <span class="line-clamp-1">{{ shortenCity(vacancy.city) }}</span>
              </td>
              <td class="py-3 px-4">
                <div class="flex items-center gap-2">
                  <button
                    v-if="vacancy.status === 'active' || vacancy.status === 'closed'"
                    @click.stop="toggleVacancyStatus(vacancy)"
                    :disabled="vacancy._toggling"
                    :class="[
                      'shrink-0 relative w-9 h-5 rounded-full transition-colors duration-200 focus:outline-none',
                      vacancy.status === 'active'
                        ? 'bg-success-500'
                        : 'bg-surface-300 dark:bg-surface-600',
                      vacancy._toggling ? 'opacity-50' : '',
                    ]"
                    :title="vacancy.status === 'active' ? 'Noaktiv qilish' : 'Aktiv qilish'"
                  >
                    <span
                      :class="[
                        'absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200',
                        vacancy.status === 'active' ? 'translate-x-4' : 'translate-x-0',
                      ]"
                    />
                  </button>
                  <span :class="['text-xs px-2 py-0.5 rounded-full font-medium whitespace-nowrap', statusClass(vacancy.status)]">
                    {{ statusLabel(vacancy.status) }}
                  </span>
                </div>
              </td>
              <td class="py-3 px-4 text-surface-600 dark:text-surface-400 tabular-nums">{{ vacancy.views_count || 0 }}</td>
              <td class="py-3 px-4" @click.stop>
                <span
                  v-if="vacancy.applications_count > 0"
                  @click="router.push({ path: '/applications', query: { vacancy_id: vacancy.id } })"
                  class="inline-flex items-center justify-center min-w-[1.5rem] px-2 py-0.5 rounded-md text-sm font-bold bg-brand-100 dark:bg-brand-900/30 text-brand-700 dark:text-brand-400 hover:bg-brand-200 dark:hover:bg-brand-800/40 cursor-pointer transition-all hover:scale-105"
                  :title="`${vacancy.applications_count} ta ariza`"
                >
                  {{ vacancy.applications_count }}
                </span>
                <span v-else class="text-surface-400">0</span>
              </td>
              <td class="py-3 px-4 text-surface-500 text-xs whitespace-nowrap">{{ formatDate(vacancy.created_at) }}</td>
              <td class="py-3 px-4 text-right" @click.stop>
                <div class="flex items-center justify-end gap-1.5">
                  <button
                    v-if="vacancy.status === 'pending'"
                    @click="approveVacancy(vacancy)"
                    class="p-1.5 rounded-lg bg-success-50 text-success-600 hover:bg-success-100 dark:bg-success-950/30 dark:text-success-400 dark:hover:bg-success-900/40 transition-colors"
                    title="Tasdiqlash"
                  >
                    <CheckIcon class="w-4 h-4" />
                  </button>
                  <button
                    v-if="vacancy.status === 'pending'"
                    @click="rejectVacancy(vacancy)"
                    class="p-1.5 rounded-lg bg-danger-50 text-danger-600 hover:bg-danger-100 dark:bg-danger-950/30 dark:text-danger-400 dark:hover:bg-danger-900/40 transition-colors"
                    title="Rad etish"
                  >
                    <XMarkIcon class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="loading" class="p-8 text-center text-surface-500">{{ $t('common.loading') }}</div>
      <div v-if="!loading && items.length === 0" class="p-12 text-center">
        <DocumentTextIcon class="w-10 h-10 mx-auto text-surface-300 dark:text-surface-600 mb-3" />
        <p class="text-surface-500 dark:text-surface-400">{{ $t('common.noData') }}</p>
      </div>
    </AppCard>

    <!-- Pagination -->
    <AppPagination v-if="lastPage > 1" :current-page="currentPage" :last-page="lastPage" :total="total" @page-change="goToPage" />

    <!-- Close Reason Modal -->
    <div v-if="showReasonModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="cancelClose">
      <div class="bg-white dark:bg-surface-900 rounded-xl shadow-xl w-full max-w-md mx-4 border border-surface-200 dark:border-surface-800">
        <div class="px-6 py-4 border-b border-surface-200 dark:border-surface-800">
          <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100">Vakansiyani noaktiv qilish</h3>
          <p class="text-sm text-surface-500 dark:text-surface-400 mt-1">Iltimos, sababni kiriting</p>
        </div>
        <div class="px-6 py-4">
          <textarea
            v-model="closeReason"
            rows="3"
            class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm text-surface-900 dark:text-surface-100 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 resize-none"
            placeholder="Masalan: Vakansiya muddati tugagan, nomuvofiq kontent..."
          />
        </div>
        <div class="px-6 py-4 border-t border-surface-200 dark:border-surface-800 flex justify-end gap-3">
          <button @click="cancelClose"
            class="px-4 py-2 text-sm font-medium text-surface-700 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-800 rounded-lg transition-colors">
            Bekor qilish
          </button>
          <button @click="confirmClose" :disabled="!closeReason.trim()"
            class="px-4 py-2 bg-danger-500 text-white text-sm font-medium rounded-lg hover:bg-danger-600 transition-colors disabled:opacity-50">
            Noaktiv qilish
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { toast } from 'vue-sonner';
import { useResourceList } from '../../composables/useAdminApi';
import AppCard from '@panel/components/ui/AppCard.vue';
import AppSearchInput from '@panel/components/ui/AppSearchInput.vue';
import AppPagination from '@panel/components/ui/AppPagination.vue';
import { CheckIcon, XMarkIcon, DocumentTextIcon } from '@heroicons/vue/24/outline';
import { shortenCity, formatDate, getStatusCss as statusClass, getStatusLabel as statusLabel } from '@/shared/formatters';

const route = useRoute();
const router = useRouter();

const currentSort = ref('created_at');
const sortDir = ref('desc');

const {
  items, total, currentPage, lastPage, loading, search, filters,
  fetchItems, goToPage, setSort: _setSort, applySearch, applyFilter,
} = useResourceList('/vacancies');

function setSort(field) {
  if (currentSort.value === field) {
    sortDir.value = sortDir.value === 'desc' ? 'asc' : 'desc';
  } else {
    currentSort.value = field;
    sortDir.value = 'desc';
  }
  _setSort(field);
}

// Apply city filter from query param
const cityFilterLabel = ref(null);
if (route.query.city) {
  cityFilterLabel.value = route.query.city;
  filters.value.city = route.query.city;
}

function clearCityFilter() {
  cityFilterLabel.value = null;
  delete filters.value.city;
  router.replace({ path: '/vacancies' });
  fetchItems();
}

async function approveVacancy(vacancy) {
  try {
    const res = await axios.post(`/api/admin/vacancies/${vacancy.id}/approve`);
    vacancy.status = 'active';
    toast.success(res.data.message || 'Vakansiya tasdiqlandi');
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  }
}

async function rejectVacancy(vacancy) {
  try {
    const res = await axios.post(`/api/admin/vacancies/${vacancy.id}/reject`);
    vacancy.status = 'rejected';
    toast.success(res.data.message || 'Vakansiya rad etildi');
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  }
}

// Toggle status with reason modal
const showReasonModal = ref(false);
const closeReason = ref('');
const closingVacancy = ref(null);

function toggleVacancyStatus(vacancy) {
  if (vacancy.status === 'active') {
    closingVacancy.value = vacancy;
    closeReason.value = '';
    showReasonModal.value = true;
  } else {
    doToggle(vacancy, 'active');
  }
}

function cancelClose() {
  showReasonModal.value = false;
  closingVacancy.value = null;
  closeReason.value = '';
}

async function confirmClose() {
  if (!closingVacancy.value || !closeReason.value.trim()) return;
  showReasonModal.value = false;
  await doToggle(closingVacancy.value, 'closed', closeReason.value.trim());
  closingVacancy.value = null;
  closeReason.value = '';
}

async function doToggle(vacancy, newStatus, reason = null) {
  vacancy._toggling = true;
  try {
    const payload = { status: newStatus };
    if (reason) payload.close_reason = reason;
    await axios.put(`/api/admin/vacancies/${vacancy.id}`, payload);
    vacancy.status = newStatus;
    toast.success(newStatus === 'active' ? 'Vakansiya faollashtirildi' : 'Vakansiya noaktiv qilindi');
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  }
  vacancy._toggling = false;
}

onMounted(fetchItems);
</script>
