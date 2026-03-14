<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ $t('categories.title') }}</h1>
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
              <th class="text-left py-3 px-4 font-medium text-surface-500">ID</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500 cursor-pointer" @click="setSort('name')">Nomi</th>
              <th class="text-left py-3 px-4 font-medium text-surface-500">Vakansiyalar</th>
              <th class="text-right py-3 px-4 font-medium text-surface-500">{{ $t('common.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="category in items"
              :key="category.id"
              class="border-b border-surface-100 dark:border-surface-800/50 hover:bg-surface-50 dark:hover:bg-surface-800/30"
            >
              <td class="py-3 px-4 text-surface-500">{{ category.id }}</td>
              <td class="py-3 px-4 font-medium text-surface-900 dark:text-surface-100">{{ category.name }}</td>
              <td class="py-3 px-4 text-surface-600 dark:text-surface-400">{{ category.vacancies_count || 0 }}</td>
              <td class="py-3 px-4 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button
                    @click="openEditModal(category)"
                    class="text-xs px-3 py-1.5 rounded-lg font-medium bg-brand-50 text-brand-700 hover:bg-brand-100 dark:bg-brand-950/30 dark:text-brand-400 transition-colors"
                  >
                    <PencilIcon class="w-4 h-4 inline" />
                  </button>
                  <button
                    @click="deleteCategory(category)"
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
      <div class="bg-surface-0 dark:bg-surface-900 rounded-xl shadow-xl w-full max-w-md mx-4 border border-surface-200 dark:border-surface-800">
        <div class="px-6 py-4 border-b border-surface-200 dark:border-surface-800">
          <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100">
            {{ editingCategory ? 'Tahrirlash' : 'Yangi kategoriya' }}
          </h3>
        </div>
        <div class="px-6 py-4 space-y-4">
          <div>
            <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Nomi</label>
            <input
              v-model="form.name"
              type="text"
              class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
              placeholder="Kategoriya nomi"
            />
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
            @click="saveCategory"
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
import { PlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';

const {
  items, total, currentPage, lastPage, loading, search, filters,
  fetchItems, goToPage, setSort, applySearch, applyFilter,
} = useResourceList('/categories', 50);

const showModal = ref(false);
const editingCategory = ref(null);
const saving = ref(false);
const form = ref({ name: '' });

function openCreateModal() {
  editingCategory.value = null;
  form.value = { name: '' };
  showModal.value = true;
}

function openEditModal(category) {
  editingCategory.value = category;
  form.value = { name: category.name };
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  editingCategory.value = null;
  form.value = { name: '' };
}

async function saveCategory() {
  if (!form.value.name.trim()) {
    toast.error('Nom kiritish shart');
    return;
  }
  saving.value = true;
  try {
    if (editingCategory.value) {
      const res = await axios.put(`/api/admin/categories/${editingCategory.value.id}`, form.value);
      toast.success(res.data.message || 'Kategoriya yangilandi');
    } else {
      const res = await axios.post('/api/admin/categories', form.value);
      toast.success(res.data.message || 'Kategoriya yaratildi');
    }
    closeModal();
    fetchItems();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  } finally {
    saving.value = false;
  }
}

async function deleteCategory(category) {
  if (!confirm(`"${category.name}" kategoriyasini o'chirishni xohlaysizmi?`)) return;
  try {
    const res = await axios.delete(`/api/admin/categories/${category.id}`);
    toast.success(res.data.message || 'Kategoriya o\'chirildi');
    fetchItems();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  }
}

onMounted(fetchItems);
</script>
