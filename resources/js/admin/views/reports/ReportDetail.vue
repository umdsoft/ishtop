<template>
  <div class="space-y-6">
    <div class="flex items-center gap-3">
      <button @click="$router.back()" class="p-2 hover:bg-surface-100 dark:hover:bg-surface-800 rounded-lg transition-colors">
        <ArrowLeftIcon class="w-5 h-5" />
      </button>
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">Shikoyat #{{ report?.id }}</h1>
      <span v-if="report" :class="['text-xs px-2 py-0.5 rounded-full font-medium ml-2', statusClass(report.status)]">
        {{ statusLabel(report.status) }}
      </span>
    </div>

    <div v-if="loading" class="text-center py-12 text-surface-500">{{ $t('common.loading') }}</div>

    <div v-else-if="report" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Report Details -->
      <AppCard title="Shikoyat ma'lumotlari" class="lg:col-span-2">
        <dl class="grid grid-cols-2 gap-4">
          <div>
            <dt class="text-sm text-surface-500">ID</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ report.id }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">{{ $t('common.status') }}</dt>
            <dd>
              <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', statusClass(report.status)]">
                {{ statusLabel(report.status) }}
              </span>
            </dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">Turi</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">
              <span class="text-xs px-2 py-0.5 rounded-full bg-surface-100 text-surface-700 dark:bg-surface-800 dark:text-surface-400">
                {{ reportableType(report.reportable_type) }}
              </span>
            </dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">Ob'ekt ID</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ report.reportable_id || '—' }}</dd>
          </div>
          <div class="col-span-2">
            <dt class="text-sm text-surface-500">Sabab</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100 whitespace-pre-line">{{ report.reason || '—' }}</dd>
          </div>
          <div class="col-span-2" v-if="report.description">
            <dt class="text-sm text-surface-500">Tavsif</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100 whitespace-pre-line">{{ report.description }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">Yaratilgan sana</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ formatDateTime(report.created_at) }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">Yangilangan sana</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ formatDateTime(report.updated_at) }}</dd>
          </div>
        </dl>
      </AppCard>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Actions -->
        <AppCard title="Amallar">
          <div class="space-y-3">
            <button
              v-if="report.status === 'pending'"
              @click="resolveReport"
              class="w-full py-2 px-4 text-sm font-medium rounded-lg bg-success-50 text-success-700 hover:bg-success-100 dark:bg-success-950/30 dark:text-success-400 transition-colors"
            >
              <CheckIcon class="w-4 h-4 inline mr-1" /> Hal qilish
            </button>
            <button
              v-if="report.status === 'pending'"
              @click="dismissReport"
              class="w-full py-2 px-4 text-sm font-medium rounded-lg bg-surface-100 text-surface-700 hover:bg-surface-200 dark:bg-surface-800 dark:text-surface-300 transition-colors"
            >
              <XMarkIcon class="w-4 h-4 inline mr-1" /> Rad etish
            </button>
            <p v-if="report.status !== 'pending'" class="text-sm text-surface-500 text-center py-2">
              Bu shikoyat allaqachon ko'rib chiqilgan
            </p>
          </div>
        </AppCard>

        <!-- User Info -->
        <AppCard title="Shikoyatchi">
          <div class="space-y-2 text-sm" v-if="report.user">
            <div class="flex items-center gap-3 mb-3">
              <div class="w-10 h-10 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 dark:text-brand-400 text-sm font-bold">
                {{ (report.user.first_name?.[0] || 'U').toUpperCase() }}
              </div>
              <div>
                <p class="font-medium text-surface-900 dark:text-surface-100">{{ report.user.first_name }} {{ report.user.last_name }}</p>
                <p v-if="report.user.username" class="text-xs text-surface-500">@{{ report.user.username }}</p>
              </div>
            </div>
            <div>
              <span class="text-surface-500">Telefon: </span>
              <span class="font-medium text-surface-900 dark:text-surface-100">{{ report.user.phone || '—' }}</span>
            </div>
            <button
              @click="$router.push(`/users/${report.user.id}`)"
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
import { ArrowLeftIcon, CheckIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import { formatDateTime } from '@/shared/formatters';

const route = useRoute();
const report = ref(null);
const loading = ref(true);

function reportableType(type) {
  if (!type) return '—';
  const parts = type.split('\\');
  return parts[parts.length - 1] || type;
}

function statusClass(status) {
  const map = {
    pending: 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400',
    resolved: 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400',
    dismissed: 'bg-surface-100 text-surface-700 dark:bg-surface-800 dark:text-surface-400',
  };
  return map[status] || 'bg-surface-100 text-surface-600';
}

function statusLabel(status) {
  const map = {
    pending: 'Kutilmoqda',
    resolved: 'Hal qilingan',
    dismissed: 'Rad etilgan',
  };
  return map[status] || status;
}

async function fetchReport() {
  try {
    const res = await axios.get(`/api/admin/reports/${route.params.id}`);
    report.value = res.data.report || res.data;
  } catch (err) {
    toast.error('Shikoyat topilmadi');
  } finally {
    loading.value = false;
  }
}

async function resolveReport() {
  try {
    const res = await axios.post(`/api/admin/reports/${report.value.id}/resolve`);
    report.value.status = 'resolved';
    toast.success(res.data.message || 'Shikoyat hal qilindi');
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  }
}

async function dismissReport() {
  try {
    const res = await axios.post(`/api/admin/reports/${report.value.id}/dismiss`);
    report.value.status = 'dismissed';
    toast.success(res.data.message || 'Shikoyat rad etildi');
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  }
}

onMounted(fetchReport);
</script>
