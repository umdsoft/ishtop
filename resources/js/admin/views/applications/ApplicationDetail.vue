<template>
  <div class="space-y-6">
    <div class="flex items-center gap-3">
      <button @click="$router.back()" class="p-2 hover:bg-surface-100 dark:hover:bg-surface-800 rounded-lg transition-colors">
        <ArrowLeftIcon class="w-5 h-5" />
      </button>
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">Ariza #{{ application?.id }}</h1>
      <span v-if="application" :class="['text-xs px-2 py-0.5 rounded-full font-medium ml-2', statusClass(application.status)]">
        {{ statusLabel(application.status) }}
      </span>
    </div>

    <div v-if="loading" class="text-center py-12 text-surface-500">{{ $t('common.loading') }}</div>

    <div v-else-if="application" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Application Details -->
      <AppCard title="Ariza ma'lumotlari" class="lg:col-span-2">
        <dl class="grid grid-cols-2 gap-4">
          <div>
            <dt class="text-sm text-surface-500">{{ $t('common.status') }}</dt>
            <dd>
              <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', statusClass(application.status)]">
                {{ statusLabel(application.status) }}
              </span>
            </dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">Yaratilgan sana</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ formatDate(application.created_at) }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">Yangilangan sana</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ formatDate(application.updated_at) }}</dd>
          </div>
          <div class="col-span-2" v-if="application.cover_letter">
            <dt class="text-sm text-surface-500">Xat</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100 whitespace-pre-line">{{ application.cover_letter }}</dd>
          </div>
        </dl>
      </AppCard>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Vacancy Info -->
        <AppCard title="Vakansiya">
          <div class="space-y-2 text-sm" v-if="application.vacancy">
            <div>
              <span class="text-surface-500">Sarlavha: </span>
              <span class="font-medium text-surface-900 dark:text-surface-100">{{ application.vacancy.title_uz }}</span>
            </div>
            <div>
              <span class="text-surface-500">Kompaniya: </span>
              <span class="font-medium text-surface-900 dark:text-surface-100">{{ application.vacancy.employer?.company_name || '—' }}</span>
            </div>
            <button
              @click="$router.push(`/vacancies/${application.vacancy.id}`)"
              class="text-xs text-brand-600 dark:text-brand-400 hover:underline mt-2"
            >
              Vakansiyani ko'rish
            </button>
          </div>
          <p v-else class="text-sm text-surface-500">Ma'lumot yo'q</p>
        </AppCard>

        <!-- User Info -->
        <AppCard title="Foydalanuvchi">
          <div class="space-y-2 text-sm" v-if="application.worker?.user">
            <div class="flex items-center gap-3 mb-3">
              <div class="w-10 h-10 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 dark:text-brand-400 text-sm font-bold">
                {{ (application.worker?.user.first_name?.[0] || 'U').toUpperCase() }}
              </div>
              <div>
                <p class="font-medium text-surface-900 dark:text-surface-100">{{ application.worker?.user.first_name }} {{ application.worker?.user.last_name }}</p>
                <p v-if="application.worker?.user.username" class="text-xs text-surface-500">@{{ application.worker?.user.username }}</p>
              </div>
            </div>
            <div>
              <span class="text-surface-500">Telefon: </span>
              <span class="font-medium text-surface-900 dark:text-surface-100">{{ application.worker?.user.phone || '—' }}</span>
            </div>
            <button
              @click="$router.push(`/users/${application.worker?.user.id}`)"
              class="text-xs text-brand-600 dark:text-brand-400 hover:underline mt-2"
            >
              Profilni ko'rish
            </button>
          </div>
          <p v-else class="text-sm text-surface-500">Ma'lumot yo'q</p>
        </AppCard>
      </div>
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
import { formatDateTime as formatDate, getApplicationStatusCss as statusClass, getApplicationStatusLabel as statusLabel } from '@/shared/formatters';

const route = useRoute();
const application = ref(null);
const loading = ref(true);

async function fetchApplication() {
  try {
    const res = await axios.get(`/api/admin/applications/${route.params.id}`);
    application.value = res.data.application || res.data;
  } catch (err) {
    toast.error('Ariza topilmadi');
  } finally {
    loading.value = false;
  }
}

onMounted(fetchApplication);
</script>
