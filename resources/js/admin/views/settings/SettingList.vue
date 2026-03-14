<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-surface-900 dark:text-surface-100">{{ $t('settings.title') }}</h1>
    </div>

    <div v-if="loading" class="text-center py-12 text-surface-500">{{ $t('common.loading') }}</div>

    <div v-else-if="settings && Object.keys(settings).length" class="space-y-6">
      <AppCard v-for="(group, groupKey) in groupedSettings" :key="groupKey" :title="groupKey">
        <div class="divide-y divide-surface-100 dark:divide-surface-800">
          <div
            v-for="setting in group"
            :key="setting.key"
            class="flex items-center justify-between py-3 first:pt-0 last:pb-0"
          >
            <div>
              <p class="text-sm font-medium text-surface-900 dark:text-surface-100">{{ setting.key }}</p>
              <p v-if="setting.description" class="text-xs text-surface-500 mt-0.5">{{ setting.description }}</p>
            </div>
            <div class="text-sm text-surface-600 dark:text-surface-400 font-mono bg-surface-50 dark:bg-surface-800 px-3 py-1 rounded-lg max-w-xs truncate">
              {{ displayValue(setting.value) }}
            </div>
          </div>
        </div>
      </AppCard>

      <!-- Flat display fallback -->
      <AppCard v-if="!hasGroups" title="Sozlamalar">
        <div class="divide-y divide-surface-100 dark:divide-surface-800">
          <div
            v-for="(value, key) in settings"
            :key="key"
            class="flex items-center justify-between py-3 first:pt-0 last:pb-0"
          >
            <p class="text-sm font-medium text-surface-900 dark:text-surface-100">{{ key }}</p>
            <div class="text-sm text-surface-600 dark:text-surface-400 font-mono bg-surface-50 dark:bg-surface-800 px-3 py-1 rounded-lg max-w-xs truncate">
              {{ displayValue(value) }}
            </div>
          </div>
        </div>
      </AppCard>
    </div>

    <AppCard v-else>
      <div class="p-8 text-center text-surface-500">{{ $t('common.noData') }}</div>
    </AppCard>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { toast } from 'vue-sonner';
import AppCard from '@panel/components/ui/AppCard.vue';

const settings = ref(null);
const loading = ref(true);

const hasGroups = computed(() => {
  if (!settings.value) return false;
  return Array.isArray(settings.value) && settings.value.length > 0 && settings.value[0]?.group;
});

const groupedSettings = computed(() => {
  if (!hasGroups.value) return {};
  const groups = {};
  for (const setting of settings.value) {
    const group = setting.group || 'Umumiy';
    if (!groups[group]) groups[group] = [];
    groups[group].push(setting);
  }
  return groups;
});

function displayValue(value) {
  if (value === null || value === undefined) return '—';
  if (typeof value === 'boolean') return value ? 'Ha' : 'Yo\'q';
  if (typeof value === 'object') return JSON.stringify(value);
  return String(value);
}

async function fetchSettings() {
  try {
    const res = await axios.get('/api/admin/settings');
    settings.value = res.data.settings || res.data;
  } catch (err) {
    toast.error('Sozlamalarni yuklashda xatolik');
  } finally {
    loading.value = false;
  }
}

onMounted(fetchSettings);
</script>
