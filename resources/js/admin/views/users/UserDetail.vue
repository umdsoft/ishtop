<template>
  <div class="space-y-6">
    <div class="flex items-center gap-3">
      <button @click="$router.back()" class="p-2 hover:bg-surface-100 dark:hover:bg-surface-800 rounded-lg transition-colors">
        <ArrowLeftIcon class="w-5 h-5" />
      </button>
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ user?.first_name }} {{ user?.last_name }}</h1>
    </div>

    <div v-if="loading" class="text-center py-12 text-surface-500">{{ $t('common.loading') }}</div>

    <div v-else-if="user" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- User Info -->
      <AppCard :title="$t('users.title')" class="lg:col-span-2">
        <dl class="grid grid-cols-2 gap-4">
          <div>
            <dt class="text-sm text-surface-500">{{ $t('users.firstName') }}</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ user.first_name }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">{{ $t('users.lastName') }}</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ user.last_name || '—' }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">{{ $t('users.phone') }}</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ user.phone || '—' }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">{{ $t('users.email') }}</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ user.email || '—' }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">Username</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ user.username || '—' }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">Telegram ID</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ user.telegram_id || '—' }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">{{ $t('users.registered') }}</dt>
            <dd class="font-medium text-surface-900 dark:text-surface-100">{{ formatDate(user.created_at) }}</dd>
          </div>
          <div>
            <dt class="text-sm text-surface-500">{{ $t('common.status') }}</dt>
            <dd>
              <span :class="['text-xs px-2 py-0.5 rounded-full font-medium', user.is_blocked ? 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-400' : 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400']">
                {{ user.is_blocked ? $t('common.blocked') : $t('common.active') }}
              </span>
            </dd>
          </div>
        </dl>
      </AppCard>

      <!-- Actions -->
      <AppCard title="Amallar">
        <div class="space-y-3">
          <button
            @click="toggleBlock"
            :class="['w-full py-2 px-4 text-sm font-medium rounded-lg transition-colors', user.is_blocked ? 'bg-success-50 text-success-700 hover:bg-success-100 dark:bg-success-950/30 dark:text-success-400' : 'bg-danger-50 text-danger-700 hover:bg-danger-100 dark:bg-danger-950/30 dark:text-danger-400']"
          >
            {{ user.is_blocked ? $t('users.unblock') : $t('users.block') }}
          </button>
        </div>

        <!-- Roles -->
        <div class="mt-4 pt-4 border-t border-surface-200 dark:border-surface-800">
          <h4 class="text-sm font-medium text-surface-500 mb-2">Rollar</h4>
          <div class="flex flex-wrap gap-1">
            <span v-for="role in user.roles" :key="role.id" class="text-xs px-2 py-0.5 bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400 rounded-full">
              {{ role.name }}
            </span>
            <span v-if="!user.roles?.length" class="text-xs text-surface-500">Rol yo'q</span>
          </div>
        </div>

        <!-- Profiles -->
        <div class="mt-4 pt-4 border-t border-surface-200 dark:border-surface-800">
          <h4 class="text-sm font-medium text-surface-500 mb-2">Profillar</h4>
          <div class="space-y-1 text-sm">
            <p v-if="user.worker_profile" class="text-surface-700 dark:text-surface-300">Ishchi profili bor</p>
            <p v-if="user.employer_profiles?.length" class="text-surface-700 dark:text-surface-300">
              {{ user.employer_profiles.length }} ta kompaniya
            </p>
          </div>
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

const route = useRoute();
const user = ref(null);
const loading = ref(true);

function formatDate(d) {
  if (!d) return '';
  return new Date(d).toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

async function fetchUser() {
  try {
    const res = await axios.get(`/api/admin/users/${route.params.id}`);
    user.value = res.data.user;
  } catch (err) {
    toast.error('Foydalanuvchi topilmadi');
  } finally {
    loading.value = false;
  }
}

async function toggleBlock() {
  try {
    const res = await axios.post(`/api/admin/users/${user.value.id}/toggle-block`);
    user.value.is_blocked = res.data.user.is_blocked;
    toast.success(res.data.message);
  } catch (err) {
    toast.error(err.response?.data?.message || 'Xatolik');
  }
}

onMounted(fetchUser);
</script>
