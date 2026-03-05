<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">Talent Pool</h1>
        <p class="mt-1 text-surface-600 dark:text-surface-400">Saqlangan nomzodlar ro'yxati</p>
      </div>
    </div>

    <!-- Search -->
    <AppCard>
      <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex-1">
          <AppInput
            v-model="search"
            type="text"
            placeholder="Ism yoki mutaxassislik bo'yicha qidirish..."
            @keyup.enter="fetchEntries"
          />
        </div>
        <AppButton variant="primary" @click="fetchEntries">
          Qidirish
        </AppButton>
      </div>
    </AppCard>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-12">
      <div class="w-8 h-8 border-2 border-brand-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <!-- Empty state -->
    <AppCard v-else-if="entries.length === 0">
      <div class="text-center py-12">
        <UsersIcon class="w-16 h-16 mx-auto text-surface-300 dark:text-surface-600" />
        <h3 class="mt-4 text-lg font-medium text-surface-900 dark:text-surface-100">
          Talent Pool bo'sh
        </h3>
        <p class="mt-2 text-sm text-surface-500 dark:text-surface-400 max-w-md mx-auto">
          Nomzodlarni vakansiya arizalaridan yoki qidiruvdan saqlashingiz mumkin.
          Saqlangan nomzodlar bu yerda ko'rinadi.
        </p>
      </div>
    </AppCard>

    <!-- Entries list -->
    <div v-else class="space-y-3">
      <AppCard v-for="entry in entries" :key="entry.id">
        <div class="flex items-center gap-4">
          <!-- Avatar -->
          <div class="w-12 h-12 rounded-full bg-brand-100 dark:bg-brand-900 flex items-center justify-center text-brand-600 dark:text-brand-400 font-semibold shrink-0">
            {{ getInitials(entry.worker_profile?.full_name) }}
          </div>

          <!-- Info -->
          <div class="flex-1 min-w-0">
            <p class="font-medium text-surface-900 dark:text-surface-100 truncate">
              {{ entry.worker_profile?.full_name || 'Nomallum' }}
            </p>
            <p class="text-sm text-surface-500 dark:text-surface-400 truncate">
              {{ entry.worker_profile?.specialty || '—' }}
              <span v-if="entry.worker_profile?.city"> · {{ entry.worker_profile.city }}</span>
            </p>
            <div v-if="entry.tags?.length" class="flex flex-wrap gap-1 mt-1">
              <span
                v-for="tag in entry.tags"
                :key="tag"
                class="px-2 py-0.5 text-xs rounded-full bg-brand-100 dark:bg-brand-900 text-brand-700 dark:text-brand-300"
              >
                {{ tag }}
              </span>
            </div>
          </div>

          <!-- Details -->
          <div class="hidden sm:block text-right shrink-0">
            <p v-if="entry.worker_profile?.experience_years" class="text-sm text-surface-600 dark:text-surface-400">
              {{ entry.worker_profile.experience_years }} yil tajriba
            </p>
            <p v-if="entry.worker_profile?.expected_salary_min" class="text-xs text-surface-500 dark:text-surface-400">
              {{ formatSalary(entry.worker_profile.expected_salary_min, entry.worker_profile.expected_salary_max) }}
            </p>
          </div>

          <!-- Actions -->
          <button
            @click="removeEntry(entry)"
            class="p-2 text-surface-400 hover:text-danger-500 dark:hover:text-danger-400 transition-colors shrink-0"
            title="Olib tashlash"
          >
            <TrashIcon class="w-5 h-5" />
          </button>
        </div>

        <!-- Notes -->
        <div v-if="entry.notes" class="mt-3 pt-3 border-t border-surface-100 dark:border-surface-800">
          <p class="text-sm text-surface-600 dark:text-surface-400">{{ entry.notes }}</p>
        </div>
      </AppCard>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="flex justify-center gap-2 pt-4">
        <AppButton
          variant="outline"
          size="sm"
          :disabled="page <= 1"
          @click="page--; fetchEntries()"
        >
          Oldingi
        </AppButton>
        <span class="flex items-center px-3 text-sm text-surface-600 dark:text-surface-400">
          {{ page }} / {{ totalPages }}
        </span>
        <AppButton
          variant="outline"
          size="sm"
          :disabled="page >= totalPages"
          @click="page++; fetchEntries()"
        >
          Keyingi
        </AppButton>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import AppCard from '../../components/ui/AppCard.vue';
import AppInput from '../../components/ui/AppInput.vue';
import AppButton from '../../components/ui/AppButton.vue';
import { UsersIcon, TrashIcon } from '@heroicons/vue/24/outline';

const loading = ref(true);
const entries = ref([]);
const search = ref('');
const page = ref(1);
const totalPages = ref(1);

async function fetchEntries() {
  loading.value = true;
  try {
    const params = { page: page.value };
    if (search.value) params.q = search.value;

    const response = await axios.get('/api/recruiter/talent-pool', { params });
    entries.value = response.data.data || [];
    totalPages.value = response.data.last_page || 1;
  } catch (e) {
    console.error('Talent pool error:', e);
  } finally {
    loading.value = false;
  }
}

async function removeEntry(entry) {
  if (!confirm('Bu nomzodni talent puldan olib tashlamoqchimisiz?')) return;

  try {
    await axios.delete(`/api/recruiter/talent-pool/${entry.id}`);
    entries.value = entries.value.filter(e => e.id !== entry.id);
    toast.success('Nomzod olib tashlandi');
  } catch (e) {
    toast.error('Xatolik yuz berdi');
  }
}

function getInitials(name) {
  if (!name) return '?';
  return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
}

function formatSalary(min, max) {
  const fmt = (n) => new Intl.NumberFormat('uz-UZ').format(n);
  if (min && max) return `${fmt(min)} - ${fmt(max)} so'm`;
  if (min) return `${fmt(min)}+ so'm`;
  return '';
}

onMounted(() => fetchEntries());
</script>
