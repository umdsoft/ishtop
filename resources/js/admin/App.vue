<template>
  <div id="app" class="min-h-screen bg-surface-50 dark:bg-surface-950 text-surface-900 dark:text-surface-100 transition-colors duration-200">
    <router-view />
    <Toaster
      position="top-right"
      :theme="isDark ? 'dark' : 'light'"
      richColors
      closeButton
    />
  </div>
</template>

<script setup>
import { computed, onMounted, watch } from 'vue';
import { useUIStore } from './stores/ui';

const uiStore = useUIStore();

const isDark = computed(() => uiStore.isDark);

onMounted(() => {
  uiStore.initializeTheme();
});

watch(isDark, (newValue) => {
  if (newValue) {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
}, { immediate: true });
</script>
