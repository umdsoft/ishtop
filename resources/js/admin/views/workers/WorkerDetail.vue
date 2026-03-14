<template>
  <div class="space-y-6">
    <div class="flex items-center gap-3">
      <button @click="$router.back()" class="p-2 hover:bg-surface-100 dark:hover:bg-surface-800 rounded-lg transition-colors">
        <ArrowLeftIcon class="w-5 h-5" />
      </button>
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ worker?.user?.first_name }} {{ worker?.user?.last_name }}</h1>
    </div>

    <div v-if="loading" class="text-center py-12 text-surface-500">{{ $t('common.loading') }}</div>

    <div v-else-if="worker" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Worker Profile -->
      <AppCard title="Ishchi profili" class="lg:col-span-2">
        <dl class="grid grid-cols-2 gap-4">
          <div>
            <dt class="text-sm text-surface-500">Mutaxassislik</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ worker.specialization || '—' }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">Shahar</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ worker.city || '—' }}</dd>
          </div>
          <div class="col-span-2">
            <dt class="text-sm text-surface-500">O'zi haqida</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100 whitespace-pre-line">{{ worker.about || '—' }}</dd>
          </div>
          <div class="col-span-2">
            <dt class="text-sm text-surface-500">Ko'nikmalar</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">
              <div v-if="worker.skills && worker.skills.length" class="flex flex-wrap gap-1 mt-1">
                <span v-for="skill in worker.skills" :key="skill" class="text-xs px-2 py-0.5 bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400 rounded-full">
                  {{ skill }}
                </span>
              </div>
              <span v-else>—</span>
            </dd>
          </div>
          <div class="col-span-2">
            <dt class="text-sm text-surface-500">Tajriba</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100 whitespace-pre-line">{{ worker.experience || '—' }}</dd>
          </div>
          <div class="col-span-2">
            <dt class="text-sm text-surface-500">Ta'lim</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100 whitespace-pre-line">{{ worker.education || '—' }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">{{ $t('common.date') }}</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ formatDate(worker.created_at) }}</dd>
          </div>
        </dl>
      </AppCard>

      <!-- User Info -->
      <AppCard title="Foydalanuvchi">
        <div class="space-y-3 text-sm" v-if="worker.user">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 dark:text-brand-400 text-lg font-bold">
              {{ (worker.user.first_name?.[0] || 'U').toUpperCase() }}
            </div>
            <div>
              <p class="font-medium text-surface-900 dark:text-surface-100">{{ worker.user.first_name }} {{ worker.user.last_name }}</p>
              <p v-if="worker.user.username" class="text-xs text-surface-500">@{{ worker.user.username }}</p>
            </div>
          </div>
          <div>
            <span class="text-surface-500">Telefon: </span>
            <span class="font-medium text-surface-900 dark:text-surface-100">{{ worker.user.phone || '—' }}</span>
          </div>
          <div>
            <span class="text-surface-500">Email: </span>
            <span class="font-medium text-surface-900 dark:text-surface-100">{{ worker.user.email || '—' }}</span>
          </div>
          <div>
            <span class="text-surface-500">Telegram ID: </span>
            <span class="font-medium text-surface-900 dark:text-surface-100">{{ worker.user.telegram_id || '—' }}</span>
          </div>
        </div>
        <p v-else class="text-sm text-surface-500">Ma'lumot yo'q</p>
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

const route = useRoute();
const worker = ref(null);
const loading = ref(true);

function formatDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

async function fetchWorker() {
  try {
    const res = await axios.get(`/api/admin/workers/${route.params.id}`);
    worker.value = res.data.worker || res.data;
  } catch (err) {
    toast.error('Ishchi profili topilmadi');
  } finally {
    loading.value = false;
  }
}

onMounted(fetchWorker);
</script>
