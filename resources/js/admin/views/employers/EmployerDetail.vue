<template>
  <div class="space-y-6">
    <div class="flex items-center gap-3">
      <button @click="$router.back()" class="p-2 hover:bg-surface-100 dark:hover:bg-surface-800 rounded-lg transition-colors">
        <ArrowLeftIcon class="w-5 h-5" />
      </button>
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ employer?.company_name || 'Ish beruvchi' }}</h1>
    </div>

    <div v-if="loading" class="text-center py-12 text-surface-500">{{ $t('common.loading') }}</div>

    <div v-else-if="employer" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Company Details -->
      <AppCard title="Kompaniya ma'lumotlari" class="lg:col-span-2">
        <dl class="grid grid-cols-2 gap-4">
          <div>
            <dt class="text-sm text-surface-500">Kompaniya nomi</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ employer.company_name || '—' }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">Telefon</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ employer.phone || '—' }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">Soha</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ employer.industry || '—' }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">{{ $t('common.date') }}</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ formatDateTime(employer.created_at) }}</dd>
          </div>
          <div class="col-span-2">
            <dt class="text-sm text-surface-500">Tavsif</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100 whitespace-pre-line">{{ employer.description || '—' }}</dd>
          </div>
        </dl>
      </AppCard>

      <!-- User Info -->
      <AppCard title="Foydalanuvchi">
        <div class="space-y-3 text-sm" v-if="employer.user">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 dark:text-brand-400 text-lg font-bold">
              {{ (employer.user.first_name?.[0] || 'U').toUpperCase() }}
            </div>
            <div>
              <p class="font-medium text-surface-900 dark:text-surface-100">{{ employer.user.first_name }} {{ employer.user.last_name }}</p>
              <p v-if="employer.user.username" class="text-xs text-surface-500">@{{ employer.user.username }}</p>
            </div>
          </div>
          <div>
            <span class="text-surface-500">Telefon: </span>
            <span class="font-medium text-surface-900 dark:text-surface-100">{{ employer.user.phone || '—' }}</span>
          </div>
          <div>
            <span class="text-surface-500">Email: </span>
            <span class="font-medium text-surface-900 dark:text-surface-100">{{ employer.user.email || '—' }}</span>
          </div>
        </div>
        <p v-else class="text-sm text-surface-500">Ma'lumot yo'q</p>
      </AppCard>

      <!-- Vacancies -->
      <AppCard title="Vakansiyalar" class="lg:col-span-3" noPadding>
        <div class="overflow-x-auto">
          <table class="w-full text-sm" v-if="employer.vacancies && employer.vacancies.length">
            <thead>
              <tr class="border-b border-surface-200 dark:border-surface-800">
                <th class="text-left py-3 px-4 font-medium text-surface-500">Sarlavha</th>
                <th class="text-left py-3 px-4 font-medium text-surface-500">{{ $t('common.status') }}</th>
                <th class="text-left py-3 px-4 font-medium text-surface-500">{{ $t('common.date') }}</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="vacancy in employer.vacancies"
                :key="vacancy.id"
                class="border-b border-surface-100 dark:border-surface-800/50 hover:bg-surface-50 dark:hover:bg-surface-800/30 cursor-pointer"
                @click="$router.push(`/vacancies/${vacancy.id}`)"
              >
                <td class="py-3 px-4 font-medium text-surface-900 dark:text-surface-100">{{ vacancy.title_uz }}</td>
                <td class="py-3 px-4">
                  <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', getStatusCss(vacancy.status)]">
                    {{ vacancy.status }}
                  </span>
                </td>
                <td class="py-3 px-4 text-surface-500 text-xs">{{ formatDateTime(vacancy.created_at) }}</td>
              </tr>
            </tbody>
          </table>
          <div v-else class="p-8 text-center text-surface-500">Vakansiyalar yo'q</div>
        </div>
      </AppCard>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { toast } from 'vue-sonner';
import AppCard from '@panel/components/ui/AppCard.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';
import { formatDateTime, getStatusCss } from '@/shared/formatters';

const route = useRoute();
const employer = ref(null);
const loading = ref(true);

async function fetchEmployer() {
  try {
    const res = await axios.get(`/api/admin/employers/${route.params.id}`);
    employer.value = res.data.employer || res.data;
  } catch (err) {
    toast.error('Ish beruvchi topilmadi');
  } finally {
    loading.value = false;
  }
}

onMounted(fetchEmployer);
</script>
