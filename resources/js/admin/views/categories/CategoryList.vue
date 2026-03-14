<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ $t('categories.title') }}</h1>
      <button
        @click="openCreateModal(null)"
        class="inline-flex items-center gap-2 px-4 py-2 bg-brand-500 text-white text-sm font-medium rounded-lg hover:bg-brand-600 transition-colors"
      >
        <PlusIcon class="w-4 h-4" />
        {{ $t('categories.addNew') }}
      </button>
    </div>

    <!-- Search -->
    <AppCard>
      <div class="flex flex-wrap items-center gap-3">
        <AppSearchInput v-model="searchQuery" :placeholder="$t('common.search')" @update:modelValue="debouncedFetch" class="w-64" />
        <span class="text-xs text-surface-500">{{ filteredCategories.length }} {{ $t('categories.root').toLowerCase() }}</span>
      </div>
    </AppCard>

    <!-- Loading -->
    <div v-if="loading" class="space-y-4">
      <AppCard v-for="i in 5" :key="i">
        <div class="animate-pulse flex items-center gap-4">
          <div class="w-10 h-10 bg-surface-200 dark:bg-surface-700 rounded-xl"></div>
          <div class="flex-1 space-y-2">
            <div class="h-4 bg-surface-200 dark:bg-surface-700 rounded w-1/3"></div>
            <div class="h-3 bg-surface-100 dark:bg-surface-800 rounded w-1/4"></div>
          </div>
        </div>
      </AppCard>
    </div>

    <!-- Category Tree -->
    <div v-else-if="filteredCategories.length > 0" class="space-y-3">
      <AppCard
        v-for="parent in filteredCategories"
        :key="parent.id"
        noPadding
        class="overflow-hidden"
      >
        <!-- Parent row -->
        <div
          class="flex items-center gap-4 px-5 py-4 cursor-pointer hover:bg-surface-50 dark:hover:bg-surface-800/30 transition-colors"
          @click="toggleExpand(parent.id)"
        >
          <!-- Expand arrow -->
          <div class="w-5 flex items-center justify-center">
            <ChevronRightIcon
              v-if="parent.children && parent.children.length > 0"
              class="w-4 h-4 text-surface-400 transition-transform duration-200"
              :class="{ 'rotate-90': expanded[parent.id] }"
            />
          </div>

          <!-- Icon -->
          <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg shrink-0"
            :class="parent.is_active
              ? 'bg-brand-100 dark:bg-brand-900/30 text-brand-600 dark:text-brand-400'
              : 'bg-surface-100 dark:bg-surface-800 text-surface-400'"
          >
            <component :is="resolveIcon(parent.icon)" class="w-5 h-5" />
          </div>

          <!-- Name & meta -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
              <p class="font-semibold text-surface-900 dark:text-surface-100 truncate">{{ parent.name_uz }}</p>
              <span v-if="!parent.is_active" class="text-[10px] px-1.5 py-0.5 rounded bg-surface-200 dark:bg-surface-700 text-surface-500 font-medium">
                {{ $t('common.inactive') }}
              </span>
            </div>
            <p v-if="parent.name_ru" class="text-xs text-surface-500 dark:text-surface-400 truncate mt-0.5">{{ parent.name_ru }}</p>
          </div>

          <!-- Stats -->
          <div class="flex items-center gap-6 shrink-0">
            <div class="text-right">
              <p class="text-sm font-semibold text-surface-900 dark:text-surface-100">{{ parent.vacancies_count || 0 }}</p>
              <p class="text-[10px] text-surface-500">{{ $t('categories.vacancies') }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm font-semibold text-surface-900 dark:text-surface-100">{{ parent.children?.length || 0 }}</p>
              <p class="text-[10px] text-surface-500">{{ $t('categories.children') }}</p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-1 shrink-0" @click.stop>
            <button
              @click="openCreateModal(parent)"
              class="p-2 rounded-lg text-surface-400 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-950/30 dark:hover:text-brand-400 transition-colors"
              :title="$t('categories.addNew')"
            >
              <PlusIcon class="w-4 h-4" />
            </button>
            <button
              @click="openEditModal(parent)"
              class="p-2 rounded-lg text-surface-400 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-950/30 dark:hover:text-brand-400 transition-colors"
              :title="$t('common.edit')"
            >
              <PencilIcon class="w-4 h-4" />
            </button>
            <button
              @click="deleteCategory(parent)"
              class="p-2 rounded-lg text-surface-400 hover:text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-950/30 dark:hover:text-danger-400 transition-colors"
              :title="$t('common.delete')"
            >
              <TrashIcon class="w-4 h-4" />
            </button>
          </div>
        </div>

        <!-- Children rows -->
        <div
          v-if="expanded[parent.id] && parent.children && parent.children.length > 0"
          class="border-t border-surface-100 dark:border-surface-800/50"
        >
          <div
            v-for="(child, idx) in parent.children"
            :key="child.id"
            class="flex items-center gap-4 px-5 py-3 pl-16 hover:bg-surface-50 dark:hover:bg-surface-800/30 transition-colors"
            :class="{ 'border-t border-surface-100 dark:border-surface-800/50': idx > 0 }"
          >
            <!-- Connector line -->
            <div class="w-5 flex items-center justify-center">
              <div class="w-2 h-2 rounded-full bg-surface-300 dark:bg-surface-600"></div>
            </div>

            <!-- Name -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2">
                <p class="text-sm font-medium text-surface-800 dark:text-surface-200 truncate">{{ child.name_uz }}</p>
                <span v-if="!child.is_active" class="text-[10px] px-1.5 py-0.5 rounded bg-surface-200 dark:bg-surface-700 text-surface-500 font-medium">
                  {{ $t('common.inactive') }}
                </span>
              </div>
              <p v-if="child.name_ru" class="text-[10px] text-surface-500 truncate mt-0.5">{{ child.name_ru }}</p>
            </div>

            <!-- Vacancies count -->
            <div class="text-right shrink-0">
              <span class="text-xs font-medium text-surface-600 dark:text-surface-400">
                {{ child.vacancies_count || 0 }} {{ $t('categories.vacancies').toLowerCase() }}
              </span>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-1 shrink-0">
              <button
                @click="openEditModal(child)"
                class="p-1.5 rounded-lg text-surface-400 hover:text-brand-600 hover:bg-brand-50 dark:hover:bg-brand-950/30 dark:hover:text-brand-400 transition-colors"
              >
                <PencilIcon class="w-3.5 h-3.5" />
              </button>
              <button
                @click="deleteCategory(child)"
                class="p-1.5 rounded-lg text-surface-400 hover:text-danger-600 hover:bg-danger-50 dark:hover:bg-danger-950/30 dark:hover:text-danger-400 transition-colors"
              >
                <TrashIcon class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
        </div>

        <!-- No children message -->
        <div
          v-else-if="expanded[parent.id] && (!parent.children || parent.children.length === 0)"
          class="border-t border-surface-100 dark:border-surface-800/50 px-5 py-4 pl-16"
        >
          <p class="text-xs text-surface-400 italic">{{ $t('categories.noChildren') }}</p>
        </div>
      </AppCard>
    </div>

    <!-- Empty state -->
    <AppCard v-else>
      <div class="py-12 text-center">
        <TagIcon class="w-12 h-12 mx-auto text-surface-300 dark:text-surface-600 mb-3" />
        <p class="text-surface-500">{{ $t('common.noData') }}</p>
      </div>
    </AppCard>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="closeModal">
      <div class="bg-surface-0 dark:bg-surface-900 rounded-xl shadow-xl w-full max-w-lg mx-4 border border-surface-200 dark:border-surface-800">
        <div class="px-6 py-4 border-b border-surface-200 dark:border-surface-800">
          <h3 class="text-lg font-semibold text-surface-900 dark:text-surface-100">
            {{ editingCategory ? $t('categories.editCategory') : $t('categories.addNew') }}
          </h3>
          <p v-if="parentForCreate && !editingCategory" class="text-xs text-surface-500 mt-1">
            {{ parentForCreate.name_uz }} → {{ $t('categories.children').toLowerCase() }}
          </p>
        </div>
        <div class="px-6 py-5 space-y-4">
          <!-- Name UZ -->
          <div>
            <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">{{ $t('categories.nameUz') }} *</label>
            <input
              v-model="form.name_uz"
              type="text"
              class="w-full px-3 py-2.5 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors"
              :placeholder="$t('categories.nameUz')"
            />
          </div>

          <!-- Name RU -->
          <div>
            <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">{{ $t('categories.nameRu') }}</label>
            <input
              v-model="form.name_ru"
              type="text"
              class="w-full px-3 py-2.5 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors"
              :placeholder="$t('categories.nameRu')"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <!-- Slug -->
            <div>
              <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">{{ $t('categories.slug') }}</label>
              <input
                v-model="form.slug"
                type="text"
                class="w-full px-3 py-2.5 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors"
                placeholder="it-dasturlash"
              />
            </div>

            <!-- Sort order -->
            <div>
              <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">{{ $t('categories.sortOrder') }}</label>
              <input
                v-model.number="form.sort_order"
                type="number"
                class="w-full px-3 py-2.5 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors"
                placeholder="0"
              />
            </div>
          </div>

          <!-- Icon -->
          <div>
            <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1.5">{{ $t('categories.icon') }}</label>
            <input
              v-model="form.icon"
              type="text"
              class="w-full px-3 py-2.5 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-colors"
              placeholder="heroicon-o-computer-desktop"
            />
          </div>

          <!-- Active toggle -->
          <div class="flex items-center justify-between py-1">
            <span class="text-sm font-medium text-surface-700 dark:text-surface-300">{{ $t('common.active') }}</span>
            <button
              type="button"
              @click="form.is_active = !form.is_active"
              class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
              :class="form.is_active ? 'bg-brand-500' : 'bg-surface-300 dark:bg-surface-600'"
            >
              <span
                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                :class="form.is_active ? 'translate-x-6' : 'translate-x-1'"
              />
            </button>
          </div>
        </div>
        <div class="px-6 py-4 border-t border-surface-200 dark:border-surface-800 flex justify-end gap-3">
          <button
            @click="closeModal"
            class="px-4 py-2 text-sm font-medium text-surface-700 dark:text-surface-300 hover:bg-surface-100 dark:hover:bg-surface-800 rounded-lg transition-colors"
          >
            {{ $t('common.cancel') }}
          </button>
          <button
            @click="saveCategory"
            :disabled="saving"
            class="px-5 py-2 bg-brand-500 text-white text-sm font-medium rounded-lg hover:bg-brand-600 transition-colors disabled:opacity-50"
          >
            {{ saving ? '...' : $t('common.save') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import AppCard from '@panel/components/ui/AppCard.vue';
import AppSearchInput from '@panel/components/ui/AppSearchInput.vue';
import {
  PlusIcon, PencilIcon, TrashIcon, ChevronRightIcon, TagIcon,
  ComputerDesktopIcon, ShoppingBagIcon, CakeIcon, TruckIcon,
  WrenchScrewdriverIcon, HeartIcon, AcademicCapIcon, BanknotesIcon,
  MegaphoneIcon, CubeIcon, ShieldCheckIcon, SparklesIcon,
  BuildingOfficeIcon, Cog6ToothIcon, EllipsisHorizontalCircleIcon,
  FolderIcon,
} from '@heroicons/vue/24/outline';

const categories = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const expanded = reactive({});

// Modal
const showModal = ref(false);
const editingCategory = ref(null);
const parentForCreate = ref(null);
const saving = ref(false);
const form = ref({
  name_uz: '',
  name_ru: '',
  slug: '',
  icon: '',
  sort_order: 0,
  is_active: true,
  parent_id: null,
});

// Icon resolver
const iconMap = {
  'heroicon-o-computer-desktop': ComputerDesktopIcon,
  'heroicon-o-shopping-bag': ShoppingBagIcon,
  'heroicon-o-cake': CakeIcon,
  'heroicon-o-truck': TruckIcon,
  'heroicon-o-wrench-screwdriver': WrenchScrewdriverIcon,
  'heroicon-o-heart': HeartIcon,
  'heroicon-o-academic-cap': AcademicCapIcon,
  'heroicon-o-banknotes': BanknotesIcon,
  'heroicon-o-megaphone': MegaphoneIcon,
  'heroicon-o-cube': CubeIcon,
  'heroicon-o-shield-check': ShieldCheckIcon,
  'heroicon-o-sparkles': SparklesIcon,
  'heroicon-o-building-office': BuildingOfficeIcon,
  'heroicon-o-cog-6-tooth': Cog6ToothIcon,
  'heroicon-o-ellipsis-horizontal-circle': EllipsisHorizontalCircleIcon,
};

function resolveIcon(iconName) {
  return iconMap[iconName] || FolderIcon;
}

// Filter
const filteredCategories = computed(() => {
  if (!searchQuery.value.trim()) return categories.value;
  const q = searchQuery.value.toLowerCase();
  return categories.value.filter(cat => {
    const parentMatch = cat.name_uz?.toLowerCase().includes(q) || cat.name_ru?.toLowerCase().includes(q);
    const childMatch = cat.children?.some(c => c.name_uz?.toLowerCase().includes(q) || c.name_ru?.toLowerCase().includes(q));
    return parentMatch || childMatch;
  });
});

// Fetch
let fetchTimeout = null;
function debouncedFetch() {
  clearTimeout(fetchTimeout);
  fetchTimeout = setTimeout(fetchCategories, 300);
}

async function fetchCategories() {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/admin/categories', { params: { tree: 1 } });
    categories.value = data.data || [];
  } catch (err) {
    toast.error('Kategoriyalarni yuklashda xatolik');
  } finally {
    loading.value = false;
  }
}

function toggleExpand(id) {
  expanded[id] = !expanded[id];
}

// Modal
function openCreateModal(parent) {
  editingCategory.value = null;
  parentForCreate.value = parent;
  form.value = {
    name_uz: '',
    name_ru: '',
    slug: '',
    icon: parent?.icon || '',
    sort_order: 0,
    is_active: true,
    parent_id: parent?.id || null,
  };
  showModal.value = true;
}

function openEditModal(category) {
  editingCategory.value = category;
  parentForCreate.value = null;
  form.value = {
    name_uz: category.name_uz || '',
    name_ru: category.name_ru || '',
    slug: category.slug || '',
    icon: category.icon || '',
    sort_order: category.sort_order || 0,
    is_active: category.is_active !== false,
    parent_id: category.parent_id || null,
  };
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  editingCategory.value = null;
  parentForCreate.value = null;
}

async function saveCategory() {
  if (!form.value.name_uz.trim()) {
    toast.error('Nom kiritish shart');
    return;
  }
  saving.value = true;
  try {
    if (editingCategory.value) {
      await axios.put(`/api/admin/categories/${editingCategory.value.id}`, form.value);
      toast.success('Kategoriya yangilandi');
    } else {
      await axios.post('/api/admin/categories', form.value);
      toast.success('Kategoriya yaratildi');
    }
    closeModal();
    fetchCategories();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  } finally {
    saving.value = false;
  }
}

async function deleteCategory(category) {
  const name = category.name_uz || category.name_ru;
  if (!confirm(`"${name}" kategoriyasini o'chirishni xohlaysizmi?`)) return;
  try {
    await axios.delete(`/api/admin/categories/${category.id}`);
    toast.success('Kategoriya o\'chirildi');
    fetchCategories();
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  }
}

onMounted(() => {
  fetchCategories();
});
</script>
