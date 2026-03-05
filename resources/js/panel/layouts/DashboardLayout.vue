<template>
  <div class="min-h-screen bg-surface-50 dark:bg-surface-950">
    <!-- Sidebar -->
    <TheSidebar />

    <!-- Main Content -->
    <div
      class="transition-all duration-300"
      :class="[
        sidebarCollapsed ? 'lg:ml-20' : 'lg:ml-64',
      ]"
    >
      <!-- Topbar -->
      <TheTopbar />

      <!-- Page Content -->
      <main class="p-4 md:p-6 lg:p-8">
        <router-view v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </main>
    </div>

    <!-- Mobile sidebar overlay -->
    <transition name="fade">
      <div
        v-if="sidebarMobileOpen"
        class="fixed inset-0 z-40 bg-surface-900/50 backdrop-blur-sm lg:hidden"
        @click="closeMobileSidebar"
      />
    </transition>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useUIStore } from '../stores/ui';
import TheSidebar from '../components/layout/TheSidebar.vue';
import TheTopbar from '../components/layout/TheTopbar.vue';

const uiStore = useUIStore();

const sidebarCollapsed = computed(() => uiStore.sidebarCollapsed);
const sidebarMobileOpen = computed(() => uiStore.sidebarMobileOpen);

function closeMobileSidebar() {
  uiStore.closeMobileSidebar();
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
