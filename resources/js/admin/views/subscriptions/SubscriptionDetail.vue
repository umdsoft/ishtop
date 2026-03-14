<template>
  <div class="space-y-6">
    <div class="flex items-center gap-3">
      <button @click="$router.back()" class="p-2 hover:bg-surface-100 dark:hover:bg-surface-800 rounded-lg transition-colors">
        <ArrowLeftIcon class="w-5 h-5" />
      </button>
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">Obuna #{{ subscription?.id }}</h1>
      <span v-if="subscription" :class="['text-xs px-2 py-0.5 rounded-full font-medium ml-2', statusClass(subscription.status)]">
        {{ statusLabel(subscription.status) }}
      </span>
    </div>

    <div v-if="loading" class="text-center py-12 text-surface-500">{{ $t('common.loading') }}</div>

    <div v-else-if="subscription" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Subscription Details -->
      <AppCard title="Obuna ma'lumotlari" class="lg:col-span-2">
        <dl class="grid grid-cols-2 gap-4">
          <div>
            <dt class="text-sm text-surface-500">ID</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ subscription.id }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">Tarif</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ subscription.plan || '—' }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">{{ $t('common.status') }}</dt>
            <dd>
              <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', statusClass(subscription.status)]">
                {{ statusLabel(subscription.status) }}
              </span>
            </dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">Tugash sanasi</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ formatDate(subscription.expires_at) }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">Yaratilgan sana</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ formatDate(subscription.created_at) }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">Yangilangan sana</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ formatDate(subscription.updated_at) }}</dd>
          </div>
        </dl>
      </AppCard>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Edit Form -->
        <AppCard title="Tahrirlash">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">{{ $t('common.status') }}</label>
              <select
                v-model="editForm.status"
                class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
              >
                <option value="active">Faol</option>
                <option value="expired">Muddati o'tgan</option>
                <option value="cancelled">Bekor qilingan</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-1">Tugash sanasi</label>
              <input
                v-model="editForm.expires_at"
                type="date"
                class="w-full px-3 py-2 bg-surface-50 dark:bg-surface-800 border border-surface-300 dark:border-surface-700 rounded-lg text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
              />
            </div>
            <button
              @click="updateSubscription"
              :disabled="saving"
              class="w-full py-2 px-4 bg-brand-500 text-white text-sm font-medium rounded-lg hover:bg-brand-600 transition-colors disabled:opacity-50"
            >
              {{ saving ? 'Saqlanmoqda...' : 'Saqlash' }}
            </button>
          </div>
        </AppCard>

        <!-- User Info -->
        <AppCard title="Foydalanuvchi">
          <div class="space-y-2 text-sm" v-if="subscription.user">
            <div class="flex items-center gap-3 mb-3">
              <div class="w-10 h-10 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center text-brand-600 dark:text-brand-400 text-sm font-bold">
                {{ (subscription.user.first_name?.[0] || 'U').toUpperCase() }}
              </div>
              <div>
                <p class="font-medium text-surface-900 dark:text-surface-100">{{ subscription.user.first_name }} {{ subscription.user.last_name }}</p>
                <p v-if="subscription.user.username" class="text-xs text-surface-500">@{{ subscription.user.username }}</p>
              </div>
            </div>
            <div>
              <span class="text-surface-500">Telefon: </span>
              <span class="font-medium text-surface-900 dark:text-surface-100">{{ subscription.user.phone || '—' }}</span>
            </div>
            <button
              @click="$router.push(`/users/${subscription.user.id}`)"
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
import { ref, reactive, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { toast } from 'vue-sonner';
import AppCard from '@panel/components/ui/AppCard.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

const route = useRoute();
const subscription = ref(null);
const loading = ref(true);
const saving = ref(false);
const editForm = reactive({
  status: '',
  expires_at: '',
});

function formatDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function statusClass(status) {
  const map = {
    active: 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400',
    expired: 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400',
    cancelled: 'bg-surface-100 text-surface-700 dark:bg-surface-800 dark:text-surface-400',
  };
  return map[status] || 'bg-surface-100 text-surface-600';
}

function statusLabel(status) {
  const map = {
    active: 'Faol',
    expired: 'Muddati o\'tgan',
    cancelled: 'Bekor qilingan',
  };
  return map[status] || status;
}

async function fetchSubscription() {
  try {
    const res = await axios.get(`/api/admin/subscriptions/${route.params.id}`);
    subscription.value = res.data.subscription || res.data;
    editForm.status = subscription.value.status || '';
    editForm.expires_at = subscription.value.expires_at ? subscription.value.expires_at.split('T')[0] : '';
  } catch (err) {
    toast.error('Obuna topilmadi');
  } finally {
    loading.value = false;
  }
}

async function updateSubscription() {
  saving.value = true;
  try {
    const res = await axios.put(`/api/admin/subscriptions/${subscription.value.id}`, editForm);
    subscription.value = res.data.subscription || res.data;
    toast.success(res.data.message || 'Obuna yangilandi');
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  } finally {
    saving.value = false;
  }
}

onMounted(fetchSubscription);
</script>
