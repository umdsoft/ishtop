<template>
  <div class="space-y-6">
    <div class="flex items-center gap-3">
      <button @click="$router.back()" class="p-2 hover:bg-surface-100 dark:hover:bg-surface-800 rounded-lg transition-colors">
        <ArrowLeftIcon class="w-5 h-5" />
      </button>
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">To'lov #{{ payment?.id }}</h1>
      <span v-if="payment" :class="['text-xs px-2 py-0.5 rounded-full font-medium ml-2', statusClass(payment.status)]">
        {{ statusLabel(payment.status) }}
      </span>
    </div>

    <div v-if="loading" class="text-center py-12 text-surface-500">{{ $t('common.loading') }}</div>

    <div v-else-if="payment" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Payment Details -->
      <AppCard title="To'lov ma'lumotlari" class="lg:col-span-2">
        <dl class="grid grid-cols-2 gap-4">
          <div>
            <dt class="text-sm text-surface-500">ID</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ payment.id }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">{{ $t('common.status') }}</dt>
            <dd>
              <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', statusClass(payment.status)]">
                {{ statusLabel(payment.status) }}
              </span>
            </dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">Summa</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100 text-lg">{{ Number(payment.amount).toLocaleString() }} so'm</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">To'lov usuli</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ payment.payment_method || '—' }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">Tranzaksiya ID</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ payment.transaction_id || '—' }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">Yaratilgan sana</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ formatDate(payment.created_at) }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">Yangilangan sana</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ formatDate(payment.updated_at) }}</dd>
          </div>
          <div v-if="payment.description" class="col-span-2">
            <dt class="text-sm text-surface-500">Tavsif</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ payment.description }}</dd>
          </div>
        </dl>
      </AppCard>

      <!-- User Info -->
      <AppCard title="Foydalanuvchi">
        <div class="space-y-3 text-sm" v-if="payment.user">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 dark:text-brand-400 text-lg font-bold">
              {{ (payment.user.first_name?.[0] || 'U').toUpperCase() }}
            </div>
            <div>
              <p class="font-medium text-surface-900 dark:text-surface-100">{{ payment.user.first_name }} {{ payment.user.last_name }}</p>
              <p v-if="payment.user.username" class="text-xs text-surface-500">@{{ payment.user.username }}</p>
            </div>
          </div>
          <div>
            <span class="text-surface-500">Telefon: </span>
            <span class="font-medium text-surface-900 dark:text-surface-100">{{ payment.user.phone || '—' }}</span>
          </div>
          <div>
            <span class="text-surface-500">Email: </span>
            <span class="font-medium text-surface-900 dark:text-surface-100">{{ payment.user.email || '—' }}</span>
          </div>
          <button
            @click="$router.push(`/users/${payment.user.id}`)"
            class="text-xs text-brand-600 dark:text-brand-400 hover:underline mt-2"
          >
            Profilni ko'rish
          </button>
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
const payment = ref(null);
const loading = ref(true);

function formatDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function statusClass(status) {
  const map = {
    pending: 'bg-warning-100 text-warning-700 dark:bg-warning-900/30 dark:text-warning-400',
    completed: 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400',
    failed: 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400',
    refunded: 'bg-info-100 text-info-700 dark:bg-info-900/30 dark:text-info-400',
  };
  return map[status] || 'bg-surface-100 text-surface-600';
}

function statusLabel(status) {
  const map = {
    pending: 'Kutilmoqda',
    completed: 'Muvaffaqiyatli',
    failed: 'Muvaffaqiyatsiz',
    refunded: 'Qaytarilgan',
  };
  return map[status] || status;
}

async function fetchPayment() {
  try {
    const res = await axios.get(`/api/admin/payments/${route.params.id}`);
    payment.value = res.data.payment || res.data;
  } catch (err) {
    toast.error('To\'lov topilmadi');
  } finally {
    loading.value = false;
  }
}

onMounted(fetchPayment);
</script>
